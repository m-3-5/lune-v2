<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Promemoria automatici agli ospiti (pagamento, documenti, CF, firma)
Schedule::command('jlune:guest-reminders')
    ->dailyAt('10:00')
    ->timezone('Europe/Rome');

// Ospiti in uscita oggi (per ora al team, in futuro anche alla cameriera)
Schedule::command('jlune:checkout-reminders')
    ->dailyAt('08:00')
    ->timezone('Europe/Rome');

// Cancellazione documenti d'identità dopo il check-out (privacy)
Schedule::command('jlune:cleanup-documents')
    ->dailyAt('03:30')
    ->timezone('Europe/Rome');

// Cancellazione copie PROVA automatiche (create durante la manutenzione) dopo 5 giorni
Schedule::command('jlune:cleanup-auto-test-bookings')
    ->dailyAt('03:45')
    ->timezone('Europe/Rome');
