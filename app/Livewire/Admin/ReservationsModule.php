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

    /** today | tomorrow | Y-m-d | upcoming | archive | cancelled */
    public string $viewMode = 'today';

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

        if ($this->context === 'dashboard') {
            $date = $this->selectedDate();
            $movements = ReservationMovements::forDate($date);
            $agendaDays = ReservationMovements::agendaDaySummaries(2, 7);
            $inHouse = Reservation::query()->inHouseOn(today())->with('apartment')->orderBy('check_in')->get();
            $inHouseCount = $inHouse->count();
            $sectionTitle = match (true) {
                $this->viewMode === 'today' => 'Oggi — '.today()->locale('it')->isoFormat('dddd D MMMM'),
                $this->viewMode === 'tomorrow' => 'Domani — '.today()->addDay()->locale('it')->isoFormat('dddd D MMMM'),
                default => $date->locale('it')->isoFormat('dddd D MMMM'),
            };
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
            'inHouse' => $inHouse,
            'inHouseCount' => $inHouseCount,
            'apartmentTotal' => $apartmentTotal,
            'reservations' => $reservations,
            'sectionTitle' => $sectionTitle,
            'isAgendaDateMode' => $this->isAgendaDateMode(),
        ]);
    }
}
