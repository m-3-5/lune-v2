<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\DocumentAIService;
use App\Services\GuestDataExtractionService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

class TestDocumentExtraction extends Command
{
    protected $signature = 'jlune:test-extraction {reservation_id? : ID prenotazione}';

    protected $description = 'Testa estrazione Document AI su una prenotazione (diagnostica locale)';

    public function handle(GuestDataExtractionService $extraction, DocumentAIService $documentAI): int
    {
        $this->info('=== Diagnostica estrazione documenti ===');

        $columns = ['id', 'document_type', 'status', 'file_path', 'ai_raw_response', 'extracted_name', 'first_name', 'last_name', 'tax_code'];
        $missing = array_filter($columns, fn ($c) => ! Schema::hasColumn('guest_documents', $c));
        if ($missing) {
            $this->error('Colonne mancanti su guest_documents: '.implode(', ', $missing));
            $this->line('Esegui: php artisan migrate');

            return self::FAILURE;
        }
        $this->info('Schema guest_documents: OK');

        $id = $this->argument('reservation_id');
        $reservation = $id
            ? Reservation::with('guestDocuments')->find($id)
            : Reservation::with('guestDocuments')->whereHas('guestDocuments')->latest()->first();

        if (! $reservation) {
            $this->error('Nessuna prenotazione con documenti trovata.');

            return self::FAILURE;
        }

        $this->line("Prenotazione #{$reservation->id} ({$reservation->booking_code})");

        foreach ($reservation->guestDocuments as $doc) {
            $path = storage_path('app/public/'.$doc->file_path);
            $exists = is_file($path);
            $this->table(
                ['Campo', 'Valore'],
                [
                    ['Document ID', $doc->id],
                    ['Tipo', $doc->document_type],
                    ['Status', $doc->status],
                    ['File path DB', $doc->file_path],
                    ['File su disco', $exists ? 'SI' : 'NO — '.$path],
                ]
            );

            if (! $exists) {
                continue;
            }

            $type = in_array($doc->document_type, ['tax_front', 'tax_back'], true) ? 'tax_code' : 'identity';
            if ($doc->document_type === 'tax_back') {
                $this->warn('  (tax_back: solo CF, i dati anagrafici vengono dal fronte tessera)');

                continue;
            }
            $this->line("→ Chiamata Document AI ({$type})...");

            try {
                $uploaded = new UploadedFile(
                    $path,
                    basename($path),
                    mime_content_type($path) ?: 'image/jpeg',
                    null,
                    true
                );

                $result = $type === 'tax_code'
                    ? $documentAI->analyzeTaxCode($uploaded)
                    : $documentAI->analyzeIdentityDocument($uploaded);

                $this->line('  Status: '.($result['status'] ?? '?'));
                $this->line('  Message: '.($result['message'] ?? ''));
                $this->line('  Data: '.json_encode($result['extracted_data'] ?? [], JSON_UNESCAPED_UNICODE));
            } catch (\Throwable $e) {
                $this->error('  ECCEZIONE: '.$e->getMessage());
            }

            $this->newLine();
        }

        $this->info('→ Estrazione completa prenotazione...');
        $full = $extraction->extractForReservation($reservation->fresh());
        $this->line('Success: '.($full['success'] ? 'sì' : 'no'));
        $this->line('Message: '.$full['message']);
        $this->line('Guests: '.json_encode($full['guests'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $reportDir = storage_path('app/document-ai-reports/'.$reservation->id);
        $this->line('(I report sono in storage/app/document-ai-reports/, NON in storage/app/private/)');
        if (is_dir($reportDir)) {
            $this->info("Report salvati in: {$reportDir}");
            foreach (glob($reportDir.'/*.json') as $f) {
                $this->line('  - '.basename($f));
            }
        } else {
            $this->warn('Nessun report in storage/app/document-ai-reports/ (estrazione non ha persistito nulla).');
        }

        return self::SUCCESS;
    }
}
