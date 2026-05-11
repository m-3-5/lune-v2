<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout; // <-- Importiamo l'attributo
use App\Models\Reservation;

#[Layout('components.layouts.admin')] // <-- Diciamo a Livewire di usare il layout admin
class ReservationsModule extends Component
{
    // Proprietà per gestire il tab attivo ('active' di default, o 'cancelled')
    public $viewMode = 'active'; 

    // Metodo per cambiare vista cliccando sui tab nell'interfaccia
    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    // Metodo per eliminare fisicamente la prenotazione dal database
    public function deleteReservation($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        
        // Inviamo un messaggio di successo alla vista
        session()->flash('message', 'Prenotazione eliminata definitivamente.');
    }

    public function render()
    {
        // Prepariamo la query base caricando l'appartamento collegato
        $query = Reservation::with('apartment');

        // Filtriamo in base al Tab selezionato
        if ($this->viewMode === 'active') {
            // Mostriamo tutte le prenotazioni TRANNE quelle cancellate
            $query->where('status', '!=', 'CANCELLED');
        } else {
            // Mostriamo SOLO le prenotazioni cancellate
            $query->where('status', 'CANCELLED');
        }

        // Eseguiamo la query ordinandola per data di arrivo 
        // (Ho tolto il ->take(10) così Serenella può vederle tutte, ma se vuoi puoi rimetterlo)
        $reservations = $query->orderBy('check_in', 'asc')->get();

        return view('livewire.admin.reservations-module', [
            'reservations' => $reservations
        ]);
    }
}