<?php

namespace App\Console\Commands;

use App\Services\CheckfrontBookingSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportCheckfrontLog extends Command
{
    protected $signature = 'checkfront:import-log
                            {path? : Percorso al file laravel.log (default: app/checkfront_data/laravel.log)}
                            {--dry-run : Analizza senza scrivere nel database}';

    protected $description = 'Importa prenotazioni dagli webhook registrati nel log Laravel (Plesk o locale)';

    public function handle(CheckfrontBookingSync $sync): int
    {
        $path = $this->argument('path') ?? base_path('app/checkfront_data/laravel.log');

        if (! File::exists($path)) {
            $this->error("File non trovato: {$path}");

            return self::FAILURE;
        }

        $this->info("Lettura log: {$path}");
        $content = File::get($path);
        $marker = '🔔 Webhook Checkfront Ricevuto:';
        $parts = explode($marker, $content);

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach (array_slice($parts, 1) as $chunk) {
            $json = trim(explode("\n", $chunk, 2)[0] ?? '');
            if ($json === '' || ! str_starts_with($json, '{')) {
                $skipped++;

                continue;
            }

            $payload = json_decode($json, true);
            if (! is_array($payload) || ! isset($payload['booking'])) {
                $skipped++;

                continue;
            }

            $bookingId = $payload['booking']['@attributes']['booking_id'] ?? '?';
            $code = $payload['booking']['code'] ?? '?';

            if ($this->option('dry-run')) {
                $this->line("  [dry-run] Booking {$bookingId} ({$code})");
                $imported++;

                continue;
            }

            $result = $sync->syncFromWebhook($payload);

            if (isset($result['reservation'])) {
                $imported++;
            } else {
                $errors++;
                $this->warn("  Errore booking {$bookingId}: ".($result['error'] ?? 'sconosciuto'));
            }
        }

        $this->newLine();
        $this->info("Completato: {$imported} elaborati, {$skipped} saltati, {$errors} errori.");
        $this->comment('Nota: lo stesso booking_id viene aggiornato (ultimo stato nel log vince).');

        return self::SUCCESS;
    }
}
