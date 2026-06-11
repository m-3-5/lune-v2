<?php

namespace App\Services;

use App\Support\GoogleCredentials;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Google\Cloud\DocumentAI\V1\RawDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class DocumentAIService
{
    public function analyzeIdentityDocument(UploadedFile $front, ?UploadedFile $back = null): array
    {
        return $this->processWithGoogleAI($front, 'identity');
    }

    public function analyzeTaxCode(UploadedFile $front, ?UploadedFile $back = null): array
    {
        return $this->processWithGoogleAI($front, 'tax_code');
    }

    private function processWithGoogleAI(UploadedFile $file, string $type): array
    {
        $credentialsError = $this->credentialsConfigurationError();
        if ($credentialsError !== null) {
            return $credentialsError;
        }

        $projectId  = config('google.project_id');
        $location   = config('google.document_ai.location', 'us');
        $processorId = config('google.document_ai.processor_id');

        if (! filled($projectId) || ! filled($processorId)) {
            return [
                'status'         => 'error',
                'extracted_data' => [],
                'message'        => 'Configurazione Google incompleta (project o processor ID mancante nel .env).',
            ];
        }

        try {
            $credentialsPath = GoogleCredentials::resolvePath();
            $client = new DocumentProcessorServiceClient(['credentials' => $credentialsPath]);
            $name   = $client->processorName($projectId, $location, $processorId);

            $content     = file_get_contents($file->getRealPath());
            $rawDocument = (new RawDocument())
                ->setContent($content)
                ->setMimeType($file->getMimeType());

            $request = (new ProcessRequest())
                ->setName($name)
                ->setRawDocument($rawDocument);

            $response = $client->processDocument($request);
            $document = $response->getDocument();
            $client->close();

            $fullText = $this->normalizeOcrText($document->getText());
            $ocrPreview = implode("\n", array_slice(array_filter(explode("\n", $fullText)), 0, 25));

            Log::info("DocumentAI OCR testo ({$type}): ".substr($fullText, 0, 800));

            // Prova prima le entity strutturate (se disponibili su quel processor)
            $entityData = $this->extractFromEntities($document, $type);

            if ($type === 'identity') {
                if (! empty($entityData['first_name']) || ! empty($entityData['last_name'])) {
                    return $this->buildIdentityResult($entityData, true, $ocrPreview);
                }

                $ocrData = $this->parseIdentityFromOcrText($fullText);

                return $this->buildIdentityResult($ocrData, false, $ocrPreview);
            }

            if ($type === 'tax_code') {
                return $this->parseTaxCodeFromText($fullText, $ocrPreview);
            }

        } catch (\Exception $e) {
            Log::error('Errore Document AI: '.$e->getMessage(), GoogleCredentials::diagnostics());

            return [
                'status'         => 'error',
                'extracted_data' => [],
                'message'        => 'Errore Document AI: '.$e->getMessage(),
            ];
        }

        return ['status' => 'error', 'extracted_data' => [], 'message' => 'Tipo documento non gestito.'];
    }

    /** Corregge caratteri OCR tipici (greco al posto di latino, ecc.). */
    private function normalizeOcrText(string $text): string
    {
        $text = strtr($text, [
            'Α' => 'A', 'Β' => 'B', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Ι' => 'I',
            'Κ' => 'K', 'Μ' => 'M', 'Ν' => 'N', 'Ο' => 'O', 'Ρ' => 'P', 'Τ' => 'T',
            'Υ' => 'Y', 'Χ' => 'X',
            'α' => 'a', 'β' => 'b', 'ε' => 'e', 'ι' => 'i', 'κ' => 'k', 'μ' => 'm',
            'ν' => 'n', 'ο' => 'o', 'ρ' => 'p', 'τ' => 't', 'υ' => 'y', 'χ' => 'x',
        ]);

        return $text;
    }

    // -----------------------------------------------------------------------
    // Entity strutturate (Identity Document Parser se disponibile)
    // -----------------------------------------------------------------------

    private function extractFromEntities(\Google\Cloud\DocumentAI\V1\Document $document, string $type): array
    {
        $data = [];

        foreach ($document->getEntities() as $entity) {
            $entityType = $entity->getType();
            $text       = trim($entity->getMentionText());

            if ($type === 'identity') {
                if (in_array($entityType, ['First Name', 'Given Names', 'given-name'])) {
                    $data['first_name'] = $text;
                }
                if (in_array($entityType, ['Last Name', 'Family Name', 'family-name', 'surname'])) {
                    $data['last_name'] = $text;
                }
                if (in_array($entityType, ['Birth Date', 'Date Of Birth', 'DOB', 'Date of Birth', 'birth-date'])) {
                    $data['birth_date'] = $text;
                }
                if (in_array($entityType, ['Document Number', 'document-number', 'id-number'])) {
                    $data['document_number'] = $text;
                }
                if ($entityType === 'MRZ Code' || str_contains($text, '<<')) {
                    $data['has_mrz'] = true;
                    // Prova a estrarre dalla MRZ se i campi non sono stati trovati
                    $mrz = $this->parseMrz($text);
                    if (! empty($mrz)) {
                        $data = array_merge($mrz, array_filter($data));
                    }
                }
            }
        }

        return $data;
    }

    // -----------------------------------------------------------------------
    // Parsing OCR testo grezzo — CI italiana / passaporto
    // -----------------------------------------------------------------------

    private function parseIdentityFromOcrText(string $text): array
    {
        // Retro CI: "DI" su riga da sola → cognome → nome (come nel log OCR)
        // NON usare \bDI perché matcha anche "COMUNE DI" + riga successiva (es. SENISE)
        $back = $this->parseIdentityCardBack($text);
        if (! empty($back)) {
            return $back;
        }

        $data = [];

        // MRZ (CI elettronica / passaporto)
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $text))));
        $mrzLines = array_values(array_filter($lines, fn ($l) => substr_count($l, '<') >= 3));
        if (count($mrzLines) >= 2) {
            $mrz = $this->parseMrz(implode("\n", array_slice($mrzLines, 0, 3)));
            if ($this->isPlausiblePersonName($mrz['last_name'] ?? null)) {
                $data = array_merge($data, array_filter($mrz));
            }
        }

        // MRZ spezzata su più righe (retro CIE): "CASTRONUOVO<<SILVIO<<<<"
        if (empty($data['last_name']) && preg_match('/^([A-Z]{2,30})<<([A-Z]+(?:<[A-Z]+)*)<*$/m', strtoupper($text), $m)) {
            $last = $this->formatPersonName($m[1]);
            $first = $this->formatPersonName(str_replace('<', ' ', $m[2]));
            if ($this->isPlausiblePersonName($last) && $this->isPlausiblePersonName($first)) {
                $data['last_name'] = $last;
                $data['first_name'] = $first;
                $data['parse_source'] = 'mrz_line';
            }
        }

        // Fronte CI cartacea/CIE: Cognome / Nome / nato il (anche etichette bilingue COGNOME/SURNAME)
        $data['last_name'] = $data['last_name'] ?? $this->valueAfterLabel($text, ['Cognome', 'COGNOME', 'Surname', 'SURNAME']);
        $data['first_name'] = $data['first_name'] ?? $this->valueAfterLabel($text, ['Nome', 'NOME', 'Name', 'NAME', 'Given names']);

        $birth = $this->parseBirthDateLabeled($text);
        if ($birth) {
            $data['birth_date'] = $birth;
        }

        if (preg_match('/\b([A-Z]{2}\s?\d{7})\b/i', $text, $m)) {
            $data['document_number'] = strtoupper(preg_replace('/\s+/', '', $m[1]));
        }

        // Numero documento CIE: 2 lettere + 5 cifre + 2 lettere (es. CA41761HW)
        if (empty($data['document_number']) && preg_match('/\b([A-Z]{2}\d{5}[A-Z]{2})\b/', strtoupper($text), $m)) {
            $data['document_number'] = $m[1];
        }

        // Codice Fiscale stampato sul retro della CIE
        $taxCode = $this->extractTaxCodeFromIdentityText($text);
        if ($taxCode) {
            $data['tax_code'] = $taxCode;
        }

        foreach (['first_name', 'last_name'] as $key) {
            if (! $this->isPlausiblePersonName($data[$key] ?? null)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Retro carta d'identità: blocco
     *   DI
     *   CASTRONUOVO
     *   ANTONIO MASSIMO
     *
     * @return array<string, mixed>
     */
    private function parseIdentityCardBack(string $text): array
    {
        if (! preg_match('/CARTA\s+D.?IDENTIT|DATA\s+SCADENZA|SCADENZA:/i', $text)) {
            return [];
        }

        if (! preg_match(
            '/(?:^|\R)DI\s*\R\s*([A-ZÀÈÉÌÒÙ][A-ZÀÈÉÌÒÙ\'\-]+)\s*\R\s*([A-ZÀ-ÿ][A-Za-zÀ-ÿ\s\'\-]{2,50})/u',
            $text,
            $m
        )) {
            return [];
        }

        $data = [
            'last_name' => $this->formatPersonName($m[1]),
            'first_name' => $this->formatPersonName($m[2]),
            'parse_source' => 'di_block',
        ];

        if (preg_match('/scadenza[:\s]*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/iu', $text, $exp)) {
            $data['document_expiry'] = $exp[1];
        }

        if (preg_match('/\b([A-Z]{2}\s?\d{7})\b/i', $text, $dn)) {
            $data['document_number'] = strtoupper(preg_replace('/\s+/', '', $dn[1]));
        }

        return array_filter($data);
    }

    /** Legge il valore dopo un'etichetta (stessa riga o riga successiva). */
    private function valueAfterLabel(string $text, array $labels): ?string
    {
        // Su tessera/CI i nomi sono in MAIUSCOLO: fermati prima della prossima etichetta
        $value = "([A-ZÀÈÉÌÒÙ][A-ZÀÈÉÌÒÙ'\\-]+(?:\\s+[A-ZÀÈÉÌÒÙ][A-ZÀÈÉÌÒÙ'\\-]+)*)";
        $stop = '(?=\s*(?:Nome|NOME|Cognome|COGNOME|Data|DATA|Codice|CODICE|Luogo|LUOGO|Sesso|SESSO|Provincia|\n|$))';
        // CIE bilingue: "COGNOME/SURNAME" → ignora la traduzione dopo lo slash
        $bilingual = '(?:\s*\/\s*(?:SURNAME|NAME|GIVEN\s+NAMES?|COGNOME|NOME)\b)?';

        foreach ($labels as $label) {
            // "Cognome CASTRONUOVO" oppure "Cognome....CASTRONUOVO" oppure "Nome.\nANTONIO"
            $sameLine = '/\b'.preg_quote($label, '/').$bilingual.'[.\s:]*'.$value.$stop.'/u';
            if (preg_match($sameLine, $text, $m)) {
                $v = $this->formatPersonName($m[1]);
                if ($this->isPlausiblePersonName($v)) {
                    return $v;
                }
            }

            $nextLine = '/\b'.preg_quote($label, '/').$bilingual.'[.\s:]*\n\s*'.$value.'/u';
            if (preg_match($nextLine, $text, $m)) {
                $v = $this->formatPersonName($m[1]);
                if ($this->isPlausiblePersonName($v)) {
                    return $v;
                }
            }
        }

        return null;
    }

    private function parseBirthDateLabeled(string $text): ?string
    {
        // Tessera: "Data\n16/01/1982\ndi nascita"
        if (preg_match('/\bData\s*\n\s*(\d{2})[\/\-\.](\d{2})[\/\-\.](\d{4})\s*\n\s*di\s+nascita/ius', $text, $m)) {
            return sprintf('%02d/%02d/%04d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // "Data di nascita 16/01/1982" (stessa riga)
        if (preg_match('/data\s+di\s+nascita[^\d]{0,15}(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/iu', $text, $m)) {
            return sprintf('%02d/%02d/%04d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // CIE: "LUOGO E DATA DI NASCITA\nPLACE AND DATE OF BIRTH\nSENISE (PZ) 20.06.1945"
        if (preg_match('/(?:LUOGO\s+E\s+DATA\s+DI\s+NASCITA|PLACE\s+AND\s+DATE\s+OF\s+BIRTH)[^\d]{0,60}(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/iu', $text, $m)) {
            return sprintf('%02d/%02d/%04d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        $months = 'gennaio|febbraio|marzo|aprile|maggio|giugno|luglio|agosto|settembre|ottobre|novembre|dicembre';

        // CI cartacea: "nato il.\n16-gennaio 1982"
        if (preg_match('/nato\s+il[.\s]*\n?\s*(\d{1,2})[\-\s]+('.$months.')\s+(\d{4})/iu', $text, $m)) {
            return $this->dateFromItalianMonth((int) $m[1], $m[2], (int) $m[3]);
        }

        if (preg_match('/nato\s+il[.\s]*\n?\s*(\d{1,2})\s+('.$months.')\s+(\d{4})/iu', $text, $m)) {
            return $this->dateFromItalianMonth((int) $m[1], $m[2], (int) $m[3]);
        }

        return null;
    }

    private function dateFromItalianMonth(int $day, string $monthName, int $year): ?string
    {
        $monthMap = [
            'gennaio' => 1, 'febbraio' => 2, 'marzo' => 3, 'aprile' => 4,
            'maggio' => 5, 'giugno' => 6, 'luglio' => 7, 'agosto' => 8,
            'settembre' => 9, 'ottobre' => 10, 'novembre' => 11, 'dicembre' => 12,
        ];
        $month = $monthMap[strtolower(trim($monthName))] ?? 0;

        return $month > 0 ? sprintf('%02d/%02d/%04d', $day, $month, $year) : null;
    }

    private function formatPersonName(string $raw): string
    {
        $raw = trim(preg_replace('/\s+/', ' ', $raw));
        // Suffissi OCR dal retro CI (es. LPZS.AC.C.V.-ROMA)
        $raw = preg_replace('/\s+(LPZS|ROMA|AC\.?\s*C\.?\s*V\.?).*$/iu', '', $raw);

        return ucwords(strtolower($raw));
    }

    private function isPlausiblePersonName(?string $name): bool
    {
        if ($name === null || strlen(trim($name)) < 2) {
            return false;
        }

        $upper = strtoupper($name);
        $blocked = [
            'COMUNE', 'REPUBBLICA', 'ITALIANA', 'CARTA', 'IDENTITA', 'IDENTITY',
            'TECNICO', 'IMPIANTISTA', 'PROFESSIONE', 'PROFESSION', 'AGENZIA',
            'MINISTERO', 'SALUTE', 'REGIONE', 'TESSERA', 'SCADENZA', 'VALID',
            'DOCUMENTO', 'RILASCIO', 'FIRMA', 'CITTADINANZA', 'STATURA',
            'MALATTIA', 'SANITARIO', 'SERVIZIO', 'NAZIONALE', 'EUROPEAN', 'HEALTH',
            'DATA DI', 'CODICE',
        ];

        foreach ($blocked as $word) {
            if (str_contains($upper, $word)) {
                return false;
            }
        }

        return (bool) preg_match("/^[A-Za-zÀ-ÿ'\\-\\s]{2,50}$/u", $name);
    }

    // -----------------------------------------------------------------------
    // Parsing MRZ (macchina di lettura passport/ID)
    // -----------------------------------------------------------------------

    private function parseMrz(string $mrz): array
    {
        $data  = [];
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r?\n/', $mrz)),
            fn ($l) => strlen($l) >= 20 && substr_count($l, '<') >= 2
        ));

        if (empty($lines)) {
            return $data;
        }

        // Passaporto TD3: riga 1 = P<COD_PAESE+COGNOME<<NOME
        if (str_starts_with($lines[0], 'P<') || str_starts_with($lines[0], 'P ')) {
            $nameField = substr($lines[0], 5);
            if (str_contains($nameField, '<<')) {
                [$surnameRaw, $givenRaw] = explode('<<', $nameField, 2);
                $data['last_name']  = ucwords(strtolower(str_replace('<', ' ', $surnameRaw)));
                $data['first_name'] = ucwords(strtolower(str_replace('<', ' ', $givenRaw)));
            }
        }

        // ID carta (TD1/TD2): riga 2, posizioni 0-5 = data nascita AAMMGG (+ eventuale cifra di controllo)
        foreach ($lines as $line) {
            if (preg_match('/^(\d{6})\d?[MF<]/', $line, $m)) {
                $raw = $m[1]; // AAMMGG
                $yy  = (int) substr($raw, 0, 2);
                $year = $yy > 30 ? "19{$yy}" : "20{$yy}";
                $data['birth_date'] = substr($raw, 4, 2).'/'.substr($raw, 2, 2).'/'.$year;
                break;
            }
        }

        // Per TD1 (CI): riga 0 = tipo, riga 1 = data; riga 2 = COGNOME<<NOME
        if (empty($data['last_name']) && count($lines) >= 3) {
            $nameLine = $lines[2];
            if (str_contains($nameLine, '<<')) {
                [$surnameRaw, $givenRaw] = explode('<<', $nameLine, 2);
                $data['last_name']  = ucwords(strtolower(str_replace('<', ' ', $surnameRaw)));
                $data['first_name'] = ucwords(strtolower(str_replace('<', ' ', $givenRaw)));
            }
        }

        // Pulizia spazi multipli
        foreach (['last_name', 'first_name'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim(preg_replace('/\s+/', ' ', $data[$key]));
            }
        }

        return $data;
    }

    // -----------------------------------------------------------------------
    // Build result identity
    // -----------------------------------------------------------------------

    private function buildIdentityResult(array $data, bool $fromEntities, string $ocrPreview = ''): array
    {
        $hasName = ! empty($data['first_name']) || ! empty($data['last_name']);
        $payload = array_filter([
            'first_name'       => $data['first_name']       ?? null,
            'last_name'        => $data['last_name']         ?? null,
            'birth_date'       => $data['birth_date']        ?? null,
            'tax_code'         => $data['tax_code']          ?? null,
            'document_number'  => $data['document_number']   ?? null,
            'document_expiry'  => $data['document_expiry']   ?? null,
            'parse_source'     => $data['parse_source']      ?? ($fromEntities ? 'entities' : 'ocr'),
        ]);

        $base = [
            'ocr_preview' => $ocrPreview,
        ];

        if (! $hasName) {
            return array_merge($base, [
                'status'         => 'partial',
                'extracted_data' => $payload,
                'message'        => 'Documento letto, ma nome/cognome non rilevati automaticamente. Inserire manualmente.',
            ]);
        }

        return array_merge($base, [
            'status'         => 'success',
            'extracted_data' => $payload,
            'message'        => 'Documento acquisito'.($fromEntities ? ' (strutturato).' : ' (OCR).'),
        ]);
    }

    // -----------------------------------------------------------------------
    // Codice Fiscale
    // -----------------------------------------------------------------------

    private function parseTaxCodeFromText(string $fullText, string $ocrPreview = ''): array
    {
        $upper = strtoupper($fullText);
        $extracted = [];

        $extracted['last_name'] = $this->valueAfterLabel($fullText, ['Cognome', 'COGNOME']);
        $extracted['first_name'] = $this->valueAfterLabel($fullText, ['Nome', 'NOME']);
        $extracted['birth_date'] = $this->parseBirthDateLabeled($fullText);

        $pattern = '/\b([A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z])\b/';
        if (preg_match($pattern, $upper, $matches)) {
            $extracted['tax_code'] = $matches[1];
        } elseif (preg_match('/\b([A-Z0-9]{16})\b/', $upper, $matches)) {
            // Tolleranza errori OCR (es. "I" letto come "1")
            $fixed = $this->normalizeTaxCode($matches[1]);
            if ($fixed) {
                $extracted['tax_code'] = $fixed;
            }
        }

        $extracted = array_filter($extracted);

        if (! empty($extracted['tax_code'])) {
            $isRealCard = preg_match('/TESSERA|AGENZIA\s+DELLE\s+ENTRATE|MINISTERO|SALUTE|REGIONE/i', $fullText);

            return [
                'status'         => 'success',
                'extracted_data' => array_merge($extracted, ['parse_source' => 'tessera_labels']),
                'message'        => $isRealCard
                    ? 'Tessera sanitaria acquisita (CF e dati anagrafici).'
                    : 'CF acquisito.',
                'ocr_preview'    => $ocrPreview,
            ];
        }

        return [
            'status'         => 'error',
            'extracted_data' => [],
            'message'        => 'Nessun Codice Fiscale valido rilevato. Assicurati di inquadrare bene la tessera.',
            'ocr_preview'    => $ocrPreview,
        ];
    }

    /**
     * Estrae il CF dal testo di un documento d'identità (es. retro CIE,
     * che riporta "CODICE FISCALE / FISCAL CODE" seguito dal codice).
     */
    private function extractTaxCodeFromIdentityText(string $text): ?string
    {
        if (! preg_match('/CODICE\s+FISCALE|FISCAL\s+CODE/i', $text)) {
            return null;
        }

        $upper = strtoupper($text);

        if (preg_match('/\b([A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z])\b/', $upper, $m)) {
            return $m[1];
        }

        if (preg_match('/\b([A-Z0-9]{16})\b/', $upper, $m)) {
            return $this->normalizeTaxCode($m[1]);
        }

        return null;
    }

    /**
     * Corregge errori OCR comuni in un candidato CF di 16 caratteri,
     * in base alla struttura attesa (es. "1" letto al posto di "I").
     */
    private function normalizeTaxCode(string $candidate): ?string
    {
        // Struttura CF: L = lettera, D = cifra
        $structure = 'LLLLLLDDLDDLDDDL';
        $toLetter = ['0' => 'O', '1' => 'I', '2' => 'Z', '5' => 'S', '6' => 'G', '8' => 'B'];
        $toDigit = ['O' => '0', 'I' => '1', 'Z' => '2', 'S' => '5', 'G' => '6', 'B' => '8'];

        $chars = str_split(strtoupper($candidate));
        if (count($chars) !== 16) {
            return null;
        }

        foreach ($chars as $i => $c) {
            $expectLetter = $structure[$i] === 'L';
            if ($expectLetter && ctype_digit($c)) {
                if (! isset($toLetter[$c])) {
                    return null;
                }
                $chars[$i] = $toLetter[$c];
            } elseif (! $expectLetter && ctype_alpha($c)) {
                if (! isset($toDigit[$c])) {
                    return null;
                }
                $chars[$i] = $toDigit[$c];
            }
        }

        $fixed = implode('', $chars);

        return preg_match('/^[A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z]$/', $fixed) ? $fixed : null;
    }

    // -----------------------------------------------------------------------
    // Credentials check
    // -----------------------------------------------------------------------

    /** @return array{status: string, extracted_data: array, message: string}|null */
    protected function credentialsConfigurationError(): ?array
    {
        if (! GoogleCredentials::isReadable()) {
            $diag = GoogleCredentials::diagnostics();
            Log::error('Document AI: credenziali Google non disponibili', $diag);

            $hint = filled($diag['configured']) && ! $diag['exists']
                ? 'File JSON non trovato. Carica google-credentials.json e usa percorso assoluto nel .env (Plesk).'
                : 'File credenziali mancante o non leggibile.';

            return ['status' => 'error', 'extracted_data' => [], 'message' => $hint];
        }

        return null;
    }
}
