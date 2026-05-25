<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Support\ReservationMovements;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ReservationsModule extends Component
{
    /** dashboard | full */
    public string $context = 'dashboard';

    /** today | tomorrow | imminent | Y-m-d | upcoming | archive | cancelled */
    public string $viewMode = 'today';

    /** Giorni per vista «Arrivi imminenti» (solo dashboard). */
    public int $imminentDays = 14;

    public bool $showInHouse = false;

    public ?string $expandedKey = null;

    public function mount(string $context = 'dashboard'): void
    {
        $this->context = $context;
        if ($context === 'full') {
            $this->viewMode = 'upcoming';
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->expandedKey = null;
    }

    public function toggleExpanded(string $key): void
    {
        $this->expandedKey = $this->expandedKey === $key ? null : $key;
    }

    public function toggleInHouse(): void
    {
        $this->showInHouse = ! $this->showInHouse;
    }

    public function deleteReservation(int $id): void
    {
        Reservation::findOrFail($id)->delete();
        session()->flash('message', 'Prenotazione eliminata definitivamente.');
    }

    protected function selectedDate(): Carbon
    {
        if ($this->viewMode === 'today') {
            return today();
        }
        if ($this->viewMode === 'tomorrow') {
            return today()->addDay();
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->viewMode)) {
            return Carbon::parse($this->viewMode);
        }

        return today();
    }

    protected function isAgendaDateMode(): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->viewMode) === 1;
    }

    public function render()
    {
        $agendaDays = [];
        $movements = collect();
        $inHouse = collect();
        $inHouseCount = 0;
        $apartmentTotal = Apartment::count();
        $reservations = collect();
        $sectionTitle = '';

        $imminentArrivals = collect();

        if ($this->context === 'dashboard') {
            $inHouse = Reservation::query()->inHouseOn(today())->with('apartment')->orderBy('check_in')->get();
            $inHouseCount = $inHouse->count();

            if ($this->viewMode === 'imminent') {
                $imminentArrivals = Reservation::query()
                    ->notCancelled()
                    ->with('apartment')
                    ->whereDate('check_in', '>=', today())
                    ->whereDate('check_in', '<=', today()->addDays($this->imminentDays))
                    ->orderBy('check_in')
                    ->get();
                $sectionTitle = "Arrivi imminenti — prossimi {$this->imminentDays} giorni";
            } else {
                $date = $this->selectedDate();
                $movements = ReservationMovements::forDate($date);
                $agendaDays = ReservationMovements::agendaDaySummaries(2, 7);
                $sectionTitle = match (true) {
                    $this->viewMode === 'today' => 'Oggi — '.today()->locale('it')->isoFormat('dddd D MMMM'),
                    $this->viewMode === 'tomorrow' => 'Domani — '.today()->addDay()->locale('it')->isoFormat('dddd D MMMM'),
                    default => $date->locale('it')->isoFormat('dddd D MMMM'),
                };
            }
        } else {
            $query = Reservation::with('apartment');

            if ($this->viewMode === 'upcoming') {
                $query->notCancelled()->notPast()->orderBy('check_in');
                $sectionTitle = 'Prenotazioni future';
            } elseif ($this->viewMode === 'archive') {
                $query->notCancelled()->past()->orderByDesc('check_out');
                $sectionTitle = 'Archivio (soggiorni conclusi)';
            } else {
                $query->cancelled()->orderByDesc('check_in');
                $sectionTitle = 'Prenotazioni cancellate';
            }

            $reservations = $query->get();
        }

        return view('livewire.admin.reservations-module', [
            'movements' => $movements,
            'agendaDays' => $agendaDays,
            'imminentArrivals' => $imminentArrivals,
            'inHouse' => $inHouse,
            'inHouseCount' => $inHouseCount,
            'apartmentTotal' => $apartmentTotal,
            'reservations' => $reservations,
            'sectionTitle' => $sectionTitle,
            'isAgendaDateMode' => $this->isAgendaDateMode(),
            'isImminentMode' => $this->viewMode === 'imminent',
        ]);
    }
}
