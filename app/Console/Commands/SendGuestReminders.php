<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\GuestNotificationService;
use Illuminate\Console\Command;

class SendGuestReminders extends Command
{
    protected $signature = 'jlune:guest-reminders {--days=14 : Considera prenotazioni con arrivo entro N giorni}';

    protected $description = 'Invia promemoria automatici agli ospiti (pagamento, documenti, codice fiscale, firma) per le prenotazioni in arrivo';

    public function handle(GuestNotificationService $notifications): int
    {
        $days = max(1, (int) $this->option('days'));

        $reservations = Reservation::query()
            ->notCancelled()
            ->notPast()
            ->whereDate('check_in', '<=', today()->addDays($days))
            ->with('apartment')
            ->get();

        foreach ($reservations as $reservation) {
            $notifications->syncStatusNotifications($reservation);
        }

        $this->info("Promemoria sincronizzati per {$reservations->count()} prenotazioni (arrivo entro {$days} giorni).");
        $this->line('Le notifiche duplicate vengono evitate automaticamente (finestre di dedupe per tipo).');

        return self::SUCCESS;
    }
}
