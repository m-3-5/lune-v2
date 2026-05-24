<?php

namespace App\Console\Commands;

use App\Services\CheckfrontBookingSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncFromCheckfrontSnapshot extends Command
{
    protected $signature = 'checkfront:sync-from-snapshot
                            {path? : File snapshot (default: app/checkfront_data/snapshot.json)}
                            {--dry-run : Elenca senza scrivere nel database}';

    protected $description = 'Aggiorna le prenotazioni locali dal file snapshot Checkfront (offline)';

    public function handle(CheckfrontBookingSync $sync): int
    {
        $path = base_path($this->argument('path') ?? 'app/checkfront_data/snapshot.json');

        if (! File::exists($path)) {
            $this->error("File non trovato: {$path}");
            $this->comment('Genera prima lo snapshot: php artisan checkfront:pull-snapshot');

            return self::FAILURE;
        }

        $data = json_decode(File::get($path), true);
        if (! is_array($data)) {
            $this->error('JSON non valido');

            return self::FAILURE;
        }

        $bookings = $data['bookings'] ?? [];
        if ($bookings === []) {
            $this->error('Nessun dettaglio prenotazione nello snapshot. Esegui pull senza --index-only.');

            return self::FAILURE;
        }

        $fetched = $data['meta']['fetched_at'] ?? '?';
        $this->info("Snapshot del {$fetched} — ".count($bookings).' prenotazioni');

        $ok = 0;
        $fail = 0;

        foreach ($bookings as $id => $booking) {
            if (! is_array($booking)) {
                continue;
            }

            if ($this->option('dry-run')) {
                $code = $booking['code'] ?? $id;
                $this->line("  [dry-run] {$code}");
                $ok++;

                continue;
            }

            $result = $sync->syncFromBooking($booking);
            if (isset($result['reservation'])) {
                $ok++;
            } else {
                $fail++;
                $this->warn("  #{$id}: ".($result['error'] ?? 'errore'));
            }
        }

        $this->newLine();
        $this->info("Completato: {$ok} ok, {$fail} errori.");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
