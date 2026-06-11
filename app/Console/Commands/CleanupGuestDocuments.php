<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupGuestDocuments extends Command
{
    protected $signature = 'jlune:cleanup-documents {--dry-run : Mostra cosa verrebbe cancellato senza eliminare nulla}';

    protected $description = 'Cancella i documenti d\'identità degli ospiti dopo il check-out (privacy/GDPR). Il contratto firmato resta archiviato.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $reservations = Reservation::query()
            ->past()
            ->whereHas('guestDocuments')
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('Nessun documento da cancellare: tutto pulito.');

            return self::SUCCESS;
        }

        $filesDeleted = 0;
        $rowsDeleted = 0;

        foreach ($reservations as $reservation) {
            $documents = $reservation->guestDocuments()->get();

            foreach ($documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    if (! $dryRun) {
                        Storage::disk('public')->delete($document->file_path);
                    }
                    $filesDeleted++;
                }

                if (! $dryRun) {
                    $document->delete();
                }
                $rowsDeleted++;
            }

            // Report IA salvati per la prenotazione
            $reportsDir = storage_path("app/document-ai-reports/{$reservation->id}");
            if (File::isDirectory($reportsDir) && ! $dryRun) {
                File::deleteDirectory($reportsDir);
            }

            $this->line(($dryRun ? '[DRY-RUN] ' : '')."Prenotazione {$reservation->booking_code}: {$documents->count()} documenti ".($dryRun ? 'da cancellare' : 'cancellati').'.');
        }

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$prefix}Totale: {$rowsDeleted} record e {$filesDeleted} file su {$reservations->count()} prenotazioni dopo il check-out.");

        if (! $dryRun && $rowsDeleted > 0) {
            Log::info('Pulizia documenti post check-out completata', [
                'reservations' => $reservations->count(),
                'documents' => $rowsDeleted,
                'files' => $filesDeleted,
            ]);
        }

        return self::SUCCESS;
    }
}
