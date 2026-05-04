<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Reservation;
use App\Models\GuestDocument; // Assicurati di avere il modello per i singoli file

class DettaglioArrivo extends Component
{
    public Reservation $reservation;

    public function mount($id)
    {
        $this->reservation = Reservation::with('guestDocuments')->findOrFail($id);
    }

    // Approva o Rifiuta un singolo file
    public function setDocumentStatus($documentId, $status)
    {
        $doc = GuestDocument::findOrFail($documentId);
        $doc->update(['status' => $status]); // status può essere 'approved' o 'rejected'
        
        $this->reservation->load('guestDocuments'); // Ricarica per aggiornare la grafica
        $this->checkGeneralStatus();
    }

    public function approvaTutto()
    {
        $this->reservation->guestDocuments()->update(['status' => 'approved']);
        $this->reservation->update(['documents_validated' => true]);
        session()->flash('message', 'Tutti i documenti sono stati approvati!');
    }

    private function checkGeneralStatus()
    {
        // Se tutti i documenti sono approved, segna la prenotazione come validata
        $allApproved = $this->reservation->guestDocuments->every(fn($doc) => $doc->status === 'approved');
        $this->reservation->update(['documents_validated' => $allApproved]);
    }

    public function render()
    {
        return view('livewire.admin.dettaglio-arrivo')->layout('components.layouts.app');
    }
}