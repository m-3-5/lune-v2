<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Support\CheckfrontDates;
use Illuminate\Console\Command;

class JluneStatus extends Command
{
    protected $signature = 'jlune:status
                            {--date= : Data da controllare (Y-m-d), default oggi}';

    protected $description = 'Riepilogo appartamenti, fuso orario e arrivi (per verifica su Plesk)';

    public function handle(): int
    {
        $date = $this->option('date') ?? today()->toDateString();

        $this->info('--- Jlune — stato sistema ---');
        $this->line('App timezone: '.config('app.timezone'));
        $this->line('Checkfront timezone: '.CheckfrontDates::timezone());
        $this->line('Data controllo: '.$date);
        $this->newLine();

        $apartments = Apartment::orderBy('display_order')->orderBy('sku')->get();
        $this->info('Appartamenti: '.$apartments->count());

        foreach ($apartments as $apt) {
            $itemId = $apt->checkfront_item_id ?: '—';
            $this->line("  {$apt->sku} (item_id {$itemId})");
        }

        $withoutItem = $apartments->whereNull('checkfront_item_id')->pluck('sku')->all();
        if ($withoutItem !== []) {
            $this->comment('Senza checkfront_item_id: '.implode(', ', $withoutItem));
        }

        $this->newLine();
        $this->info('Prenotazioni in DB: '.Reservation::count());

        $arrivals = Reservation::query()
            ->notCancelled()
            ->arrivingOn($date)
            ->with('apartment')
            ->orderBy('check_in')
            ->get();

        $this->info("Arrivi il {$date}: ".$arrivals->count());

        foreach ($arrivals as $r) {
            $sku = $r->apartment?->sku ?? '?';
            $this->line(sprintf(
                '  %s | %s | %s | %s',
                $r->booking_code ?? '#'.$r->checkfront_booking_id,
                $r->check_in?->format('Y-m-d H:i') ?? '—',
                $sku,
                $r->guest_name ?? '—'
            ));
        }

        $inHouse = Reservation::query()->inHouseOn($date)->notCancelled()->count();
        $this->newLine();
        $this->line("In casa il {$date}: {$inHouse} / ".$apartments->count());

        return self::SUCCESS;
    }
}
