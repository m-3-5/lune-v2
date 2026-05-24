<?php

namespace App\Console\Commands;

use App\Services\CheckfrontBookingSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportCheckfrontLog extends Command
{
    protected $signature = 'checkfront:import-log
                            {path? : Percorso al laravel.log (default: app/checkfront_data o storage/logs)}
                            {--dry-run : Analizza senza scrivere nel database}';

    protected $description = 'Importa prenotazioni dagli webhook registrati nel log Laravel (Plesk o locale)';

    public function handle(CheckfrontBookingSync $sync): int
    {
        $path = $this->resolveLogPath();

        if ($path === null) {
            $this->error('File log non trovato.');
            $this->line('  Locale: aggiorna app/checkfront_data/laravel.log');
            $this->line('  Plesk:  usa storage/logs/laravel.log (si aggiorna da solo)');
            $this->line('  Percorsi cercati:');
            foreach ($this->candidateLogPaths() as $candidate) {
                $this->line("    - {$candidate}");
            }

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

    protected function resolveLogPath(): ?string
    {
        if ($this->argument('path')) {
            $path = $this->argument('path');
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:\\\\#', $path)) {
                $path = base_path($path);
            }

            return File::exists($path) ? $path : null;
        }

        foreach ($this->candidateLogPaths() as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function candidateLogPaths(): array
    {
        return array_values(array_unique(array_filter([
            config('checkfront.webhook_log_path'),
            base_path('app/checkfront_data/laravel.log'),
            storage_path('logs/laravel.log'),
        ])));
    }
}
