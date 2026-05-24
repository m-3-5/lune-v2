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

            if ($idFront) {
                $identity = $this->analyzeStoredFile($idFront, 'identity');
                if ($identity['status'] === 'success') {
                    $guest['data'] = array_merge($guest['data'], $identity['extracted_data']);
                    $this->persistDocumentAi($idFront, $identity);
                } else {
                    $guest['extraction_notes'][] = 'ID fronte: '.$identity['message'];
                }
            }

            if ($idBack && empty($guest['data']['birth_date'])) {
                $identityBack = $this->analyzeStoredFile($idBack, 'identity');
                if ($identityBack['status'] === 'success') {
                    $guest['data'] = array_merge($guest['data'], array_filter($identityBack['extracted_data']));
                    $this->persistDocumentAi($idBack, $identityBack);
                }
            }

            if (! $isForeigner && $taxFront) {
                $tax = $this->analyzeStoredFile($taxFront, 'tax_code');
                if ($tax['status'] === 'success' && ! empty($tax['extracted_data']['tax_code'])) {
                    $guest['data']['tax_code'] = $tax['extracted_data']['tax_code'];
                    $taxFront->update(['tax_code' => $guest['data']['tax_code']]);
                    $this->persistDocumentAi($taxFront, $tax);
                } else {
                    $guest['extraction_notes'][] = 'CF: '.($tax['message'] ?? 'non rilevato');
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
