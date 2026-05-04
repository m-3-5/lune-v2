<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\RawDocument;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
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
        try {
            $projectId = env('GOOGLE_CLOUD_PROJECT_ID');
            $location = env('GOOGLE_DOCUMENT_AI_LOCATION', 'eu');
            $processorId = env('GOOGLE_DOCUMENT_AI_PROCESSOR_ID');

            $client = new DocumentProcessorServiceClient();
            $name = $client->processorName($projectId, $location, $processorId);

            $content = file_get_contents($file->getRealPath());
            $rawDocument = (new RawDocument())
                ->setContent($content)
                ->setMimeType($file->getMimeType());

            $request = (new ProcessRequest())
                ->setName($name)
                ->setRawDocument($rawDocument);

            $response = $client->processDocument($request);
            $document = $response->getDocument();
            $client->close();

            $extractedData = [];
            $fullText = strtoupper($document->getText());
            
            // ==========================================
            // 1. LOGICA DOCUMENTI D'IDENTITÀ (BLINDATA)
            // ==========================================
            if ($type === 'identity') {
                $hasMRZ = false;
                $confidenceScores = [];

                // Verifica anti-falso basilare: deve esserci almeno la parola Repubblica, Passaporto, Carta, Identity ecc.
                $isProbablyOfficial = preg_match('/REPUBBLICA|ITALIANA|IDENTITY|CARD|PASSPORT|DRIVING|PATENTE|CARTA/i', $fullText);

                foreach ($document->getEntities() as $entity) {
                    $entityType = $entity->getType();
                    $text = trim($entity->getMentionText());
                    $confidence = $entity->getConfidence();

                    if ($entityType === 'MRZ Code' || str_contains($text, '<<<<')) {
                        $hasMRZ = true;
                    }
                    if (in_array($entityType, ['First Name', 'Given Names']) && strlen($text) > 1) {
                        $extractedData['first_name'] = $text;
                        $confidenceScores['first_name'] = $confidence;
                    }
                    if (in_array($entityType, ['Last Name', 'Family Name']) && strlen($text) > 1) {
                        $extractedData['last_name'] = $text;
                        $confidenceScores['last_name'] = $confidence;
                    }
                    if (in_array($entityType, ['Birth Date', 'Date Of Birth', 'DOB', 'Date of Birth'])) {
                        // Accetta formati come DD/MM/YYYY, DD.MM.YYYY, DD-MM-YYYY
                        if (preg_match('/\b\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4}\b/', $text)) {
                            $extractedData['birth_date'] = $text;
                            $confidenceScores['birth_date'] = $confidence;
                        }
                    }
                }

                $hasBasicFields = isset($extractedData['first_name']) && 
                                  isset($extractedData['last_name']) && 
                                  isset($extractedData['birth_date']);

                if (!$hasBasicFields || !$isProbablyOfficial) {
                    return [
                        'status' => 'error',
                        'extracted_data' => [],
                        'message' => "Immagine non conforme. Assicurati che nomi e date siano ben visibili e il documento sia reale."
                    ];
                }

                // Se ha la striscia MRZ è praticamente garantito, sennò chiediamo una fiducia altissima (es. patente vecchia o ID cartacea)
                $minConfidence = empty($confidenceScores) ? 0 : min($confidenceScores);
                $isSuccess = $hasMRZ ? ($minConfidence >= 0.75) : ($minConfidence >= 0.90);

                return [
                    'status' => $isSuccess ? 'success' : 'error',
                    'extracted_data' => $extractedData,
                    'message' => $isSuccess ? 'Documento valido acquisito.' : 'Documento non sufficientemente chiaro o non valido. Riprova.'
                ];
            }

            // ==========================================
            // 2. LOGICA CODICE FISCALE (ANTIFRODE)
            // ==========================================
            if ($type === 'tax_code') {
                // Regex perfetta per il Codice Fiscale Italiano
                $pattern = '/\b[A-Z]{6}\d{2}[A-Z]\d{2}[A-Z]\d{3}[A-Z]\b/';
                
                // Deve contenere termini che indicano sia una tessera reale
                $isRealCard = preg_match('/TESSERA|AGENZIA DELLE ENTRATE|MINISTERO|SALUTE|REGIONE/i', $fullText);

                if (preg_match($pattern, $fullText, $matches)) {
                    if ($isRealCard) {
                        $extractedData['tax_code'] = $matches[0];
                        return [
                            'status' => 'success',
                            'extracted_data' => $extractedData,
                            'message' => 'Codice Fiscale valido acquisito.'
                        ];
                    } else {
                        return [
                            'status' => 'error',
                            'extracted_data' => [],
                            'message' => "CF rilevato, ma non sembra una vera Tessera Sanitaria. Inquadra il tesserino originale."
                        ];
                    }
                }

                return [
                    'status' => 'error',
                    'extracted_data' => [],
                    'message' => 'Nessun Codice Fiscale valido rilevato. Assicurati di inquadrare bene la tessera.'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Errore Document AI: ' . $e->getMessage());
            return [
                'status' => 'error',
                'extracted_data' => [],
                'message' => 'Errore di connessione ai server di validazione.'
            ];
        }
    }
}