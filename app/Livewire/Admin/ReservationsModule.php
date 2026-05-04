<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Reservation;

class ReservationsModule extends Component
{
    public function render()
    {
        // Peschiamo le ultime 10 prenotazioni dal database, ordinate per data di arrivo
        $reservations = Reservation::with('apartment')
            ->orderBy('check_in', 'asc')
            ->take(10)
            ->get();

        return view('livewire.admin.reservations-module', [
            'reservations' => $reservations
        ]);
    }
}