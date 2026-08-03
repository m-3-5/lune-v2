<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\TestReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupAutoTestBookings extends Command
{
    protected $signature = 'jlune:cleanup-auto-test-bookings {--dry-run : Mostra cosa verrebbe cancellato senza eliminare nulla}';

    protected $description = 'Cancella le copie PROVA create in automatico sulle prenotazioni reali (durante la manutenzione), dopo 5 giorni';

    public function handle(TestReservationService $tests): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $old = Reservation::query()
            ->whereNotNull('source_reservation_id')
            ->where('created_at', '<=', now()->subDays(5))
            ->get();

        if ($old->isEmpty()) {
            $this->info('Nessuna copia PROVA automatica da cancellare.');

            return self::SUCCESS;
        }

        foreach ($old as $reservation) {
            $this->line(($dryRun ? '[DRY-RUN] ' : '')."Copia PROVA {$reservation->booking_code} (creata {$reservation->created_at->diffForHumans()})".($dryRun ? ' da cancellare' : ' cancellata').'.');

            if (! $dryRun) {
                $tests->delete($reservation);
            }
        }

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$prefix}Totale: {$old->count()} copie PROVA automatiche.");

        if (! $dryRun) {
            Log::info('Pulizia copie PROVA automatiche completata', ['count' => $old->count()]);
        }

        return self::SUCCESS;
    }
}
