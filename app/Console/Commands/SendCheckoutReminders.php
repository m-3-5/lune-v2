<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\AdminNotificationService;
use Illuminate\Console\Command;

class SendCheckoutReminders extends Command
{
    protected $signature = 'jlune:checkout-reminders';

    protected $description = 'Notifica al team (per ora) gli ospiti in uscita oggi, per organizzare le pulizie';

    public function handle(AdminNotificationService $notifications): int
    {
        $reservations = Reservation::query()
            ->notCancelled()
            ->whereDate('check_out', today())
            ->with('apartment')
            ->get();

        foreach ($reservations as $reservation) {
            $notifications->checkoutSoon($reservation);
        }

        $this->info("Notifiche check-out inviate per {$reservations->count()} prenotazioni in uscita oggi.");

        return self::SUCCESS;
    }
}
