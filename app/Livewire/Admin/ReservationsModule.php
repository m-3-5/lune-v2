<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout; // <-- 1. Importiamo l'attributo
use App\Models\Reservation;

#[Layout('components.layouts.admin')] // <-- 2. Diciamo a Livewire di usare il layout admin
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