<?php

namespace App\Services;

use App\Models\GuestDocument;
use App\Models\Reservation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GuestDataExtractionService
{
    public function __construct(
        protected DocumentAIService $documentAI
    ) {}

    /**
     * Estrae dati da documenti approvati e salva su reservation.extracted_guests.
     *
     * @return array{success: bool, guests: array, message: string}
     */
    public function extractForReservation(Reservation $reservation): array
    {
        set_time_limit(300);

        $documents = $reservation->guestDocuments()
            ->where('status', 'approved')
            ->get();

        if ($documents->isEmpty()) {
            return [
                'success' => false,
                'guests' => [],
                'message' => 'Nessun documento approvato da analizzare.',
            ];
        }

        $slots = $documents->groupBy('guest_slot');
        $guests = [];

        foreach ($slots as $slot => $docs) {
            $first = $docs->first();
            $isForeigner = (bool) $first->is_foreigner;

            $guest = [
                'slot' => (int) $slot,
                'name' => $first->guest_name ?: "Ospite {$slot}",
                'is_foreigner' => $isForeigner,
                'data' => [
                    'first_name' => null,
                    'last_name' => null,
                    'birth_date' => null,
                    'tax_code' => null,
                    'document_number' => null,
                ],
                'extraction_notes' => [],
            ];

            $idFront = $docs->firstWhere('document_type', 'id_front');
            $idBack = $docs->firstWhere('document_type', 'id_back');
            $taxFront = $docs->firstWhere('document_type', 'tax_front');

            // 1) Tessera sanitaria per prima (etichette più affidabili: Cognome, Nome, CF, data nascita)
            if (! $isForeigner && $taxFront) {
                $tax = $this->analyzeStoredFile($taxFront, 'tax_code');
                if (in_array($tax['status'], ['success', 'partial']) && ! empty($tax['extracted_data'])) {
                    $guest['data'] = array_merge($guest['data'], array_filter($tax['extracted_data']));
                    if (! empty($tax['extracted_data']['tax_code'])) {
                        $taxFront->update(['tax_code' => $tax['extracted_data']['tax_code']]);
                    }
                    $this->persistDocumentAi($taxFront, $tax);
                } else {
                    $guest['extraction_notes'][] = 'Tessera: '.($tax['message'] ?? 'non rilevato');
                }
            }

            // 2) CI fronte — solo campi ancora vuoti
            if ($idFront) {
                $identity = $this->analyzeStoredFile($idFront, 'identity');
                if (in_array($identity['status'], ['success', 'partial']) && ! empty($identity['extracted_data'])) {
                    $guest['data'] = $this->mergeGuestData($guest['data'], $identity['extracted_data']);
                    $this->persistDocumentAi($idFront, $identity);
                }
                if ($identity['status'] !== 'success' && empty($identity['extracted_data'])) {
                    $guest['extraction_notes'][] = 'ID fronte: '.$identity['message'];
                }
            }

            // 3) CI retro — solo campi vuoti (numero doc, eventuale "DI COGNOME NOME")
            if ($idBack) {
                $identityBack = $this->analyzeStoredFile($idBack, 'identity');
                if (in_array($identityBack['status'], ['success', 'partial']) && ! empty($identityBack['extracted_data'])) {
                    $guest['data'] = $this->mergeGuestData($guest['data'], $identityBack['extracted_data']);
                    $this->persistDocumentAi($idBack, $identityBack);
                }
            }

            $guests[] = $guest;
        }

        usort($guests, fn ($a, $b) => $a['slot'] <=> $b['slot']);

        $locale = $this->resolveContractLocale($reservation, $guests);

        $reservation->update([
            'extracted_guests' => $guests,
            'contract_locale' => $locale,
            'contract_extracted_at' => now(),
            'contract_ready_for_guest' => false,
        ]);

        // Default italiano se non tutti stranieri (evita EN errato)
        if ($locale === 'en' && ! collect($guests)->every(fn ($g) => $g['is_foreigner'] ?? false)) {
            $reservation->update(['contract_locale' => 'it']);
        }

        return [
            'success' => true,
            'guests' => $guests,
            'message' => 'Estrazione completata. Controlla i dati e autorizza il contratto per l\'ospite.',
        ];
    }

    /**
     * @return array{status: string, extracted_data: array, message: string}
     */
    protected function analyzeStoredFile(GuestDocument $doc, string $type): array
    {
        $path = storage_path('app/public/'.$doc->file_path);

        if (! file_exists($path)) {
            return [
                'status' => 'error',
                'extracted_data' => [],
                'message' => 'File non trovato',
            ];
        }

        try {
            $uploaded = new UploadedFile(
                $path,
                basename($path),
                mime_content_type($path) ?: 'image/jpeg',
                null,
                true
            );

            return $type === 'tax_code'
                ? $this->documentAI->analyzeTaxCode($uploaded)
                : $this->documentAI->analyzeIdentityDocument($uploaded);
        } catch (\Throwable $e) {
            Log::error('Estrazione IA documento '.$doc->id.': '.$e->getMessage());

            return [
                'status' => 'error',
                'extracted_data' => [],
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function persistDocumentAi(GuestDocument $doc, array $result): void
    {
        $data = $result['extracted_data'] ?? [];
        $name = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

        $doc->update([
            'ai_raw_response' => $result,
            'extracted_name' => $name !== '' ? $name : $doc->extracted_name,
            'first_name' => $data['first_name'] ?? $doc->first_name,
            'last_name' => $data['last_name'] ?? $doc->last_name,
            'date_of_birth' => isset($data['birth_date'])
                ? $this->parseBirthDate($data['birth_date'])
                : $doc->date_of_birth,
        ]);

        // Report leggibile: storage/app/document-ai-reports/{reservation_id}/{guest_document_id}.json
        try {
            $reservationId = (string) ($doc->reservation_id ?? 'unknown');
            $dir = storage_path("app/document-ai-reports/{$reservationId}");
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $target = "{$dir}/{$doc->id}.json";
            file_put_contents($target, json_encode([
                'at' => now()->toIso8601String(),
                'reservation_id' => $doc->reservation_id,
                'guest_document_id' => $doc->id,
                'document_type' => $doc->document_type,
                'file_path' => $doc->file_path,
                'status' => $result['status'] ?? null,
                'message' => $result['message'] ?? null,
                'extracted_data' => $result['extracted_data'] ?? [],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::warning('Document AI: impossibile salvare report locale: '.$e->getMessage());
        }
    }

    /**
     * Integra dati dal retro senza sovrascrivere nome/cognome già trovati sul fronte.
     *
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    protected function mergeGuestData(array $existing, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (empty($existing[$key])) {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }

    protected function parseBirthDate(string $raw): ?string
    {
        if (preg_match('/(\d{2})[\/\-\.](\d{2})[\/\-\.](\d{4})/', $raw, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $guests
     */
    protected function resolveContractLocale(Reservation $reservation, array $guests): string
    {
        $lang = strtolower((string) $reservation->checkfront_language);
        if (str_contains($lang, 'en') || str_contains($lang, 'english')) {
            return 'en';
        }

        $allForeign = count($guests) > 0 && collect($guests)->every(fn ($g) => $g['is_foreigner'] ?? false);

        return $allForeign ? 'en' : 'it';
    }
}
