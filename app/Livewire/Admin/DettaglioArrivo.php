<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout; // <-- 1. Importiamo l'attributo
use App\Models\Reservation;
use App\Models\GuestDocument;
use App\Services\ContractRenderService;
use App\Services\GuestDataExtractionService;

#[Layout('components.layouts.admin')] // <-- 2. Diciamo a Livewire di usare il layout admin
class DettaglioArrivo extends Component
{
    public Reservation $reservation;

    public function mount($id)
    {
        $this->reservation = Reservation::with(['guestDocuments', 'apartment'])->findOrFail($id);
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
        $this->reservation->update([
            'documents_validated' => true,
            'contract_ready_for_guest' => false,
        ]);
        $this->reservation->refresh();
        session()->flash('message', 'Documenti approvati. Esegui l\'estrazione IA e autorizza il contratto.');
    }

    public function rifiutaTutto()
    {
        $this->reservation->guestDocuments()->update(['status' => 'rejected']);
        $this->reservation->update([
            'documents_validated' => false,
            'documents_submitted_at' => null,
            'extracted_guests' => null,
            'contract_ready_for_guest' => false,
            'contract_extracted_at' => null,
        ]);
        $this->reservation->refresh();
        session()->flash('message', 'Documenti rifiutati. L\'ospite dovrà ricaricarli.');
    }

    public function estraiDatiDocumenti(GuestDataExtractionService $extraction)
    {
        $result = $extraction->extractForReservation($this->reservation);
        $this->reservation->refresh();

        session()->flash($result['success'] ? 'message' : 'error', $result['message']);
    }

    public function autorizzaContrattoOspite(ContractRenderService $contracts)
    {
        if (empty($this->reservation->extracted_guests)) {
            session()->flash('error', 'Esegui prima l\'estrazione IA dei documenti.');

            return;
        }

        $contracts->saveHtmlSnapshot($this->reservation);

        $this->reservation->update([
            'contract_ready_for_guest' => true,
        ]);
        $this->reservation->refresh();

        session()->flash('message', 'Contratto autorizzato: l\'ospite può firmare dal suo link.');
    }

    private function checkGeneralStatus()
    {
        // Se tutti i documenti sono approved, segna la prenotazione come validata
        $allApproved = $this->reservation->guestDocuments->every(fn($doc) => $doc->status === 'approved');
        $this->reservation->update(['documents_validated' => $allApproved]);
    }

    public function render()
    {
        // 3. Ritorna solo la vista. Il layout è già dichiarato in alto!
        return view('livewire.admin.dettaglio-arrivo'); 
    }
}