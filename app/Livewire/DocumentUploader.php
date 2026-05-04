<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class DocumentUploader extends Component
{
    use WithFileUploads;

    public ?Reservation $reservation = null;
    public int $totalGuests = 1;
    public array $guestSlots = [];
    public array $uploads = []; 
    public bool $isLocked = true;

    public function mount(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->totalGuests = ($reservation->adults ?? 1) + ($reservation->children ?? 0);
        $this->initializeSlots();
    }

    private function initializeSlots()
    {
        for ($i = 1; $i <= $this->totalGuests; $i++) {
            $this->guestSlots[$i] = [
                'name' => '',
                'is_foreigner' => false,
                'documents' => [
                    'id_front'  => ['status' => 'empty', 'file_path' => null],
                    'id_back'   => ['status' => 'empty', 'file_path' => null],
                    'tax_front' => ['status' => 'empty', 'file_path' => null],
                    'tax_back'  => ['status' => 'empty', 'file_path' => null],
                ],
                'data' => [], 
                'is_approved' => false
            ];
        }
    }

    // Intercetta la fine del caricamento del file (Foto o PDF) da parte del browser
    public function updatedUploads($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $this->processNormalUpload($parts[0], $parts[1]);
        }
    }

    // Caricamento standard: salva il file (immagine o pdf) sul server
    public function processNormalUpload($guestIndex, $docType)
    {
        $file = data_get($this->uploads, "{$guestIndex}.{$docType}");
        if (!$file) return;

        try {
            // Salva il file fisicamente nella dashboard per Serenella
            $path = $file->store('documents', 'public');

            $this->guestSlots[$guestIndex]['documents'][$docType]['status'] = 'approved';
            $this->guestSlots[$guestIndex]['documents'][$docType]['file_path'] = $path;

            // Prepariamo i dati inseriti a mano per compilare il contratto
            $this->guestSlots[$guestIndex]['data']['first_name'] = $this->guestSlots[$guestIndex]['name'] ?: 'Ospite';
            $this->guestSlots[$guestIndex]['data']['last_name'] = '';
            $this->guestSlots[$guestIndex]['data']['tax_code'] = 'In attesa di verifica manuale';

        } catch (\Exception $e) {
            Log::error("Errore salvataggio file: " . $e->getMessage());
            $this->guestSlots[$guestIndex]['documents'][$docType]['status'] = 'error';
        }

        $this->checkCompletion();
    }

    public function updatedGuestSlots($value, $key)
    {
        if (str_ends_with($key, '.is_foreigner')) {
            $this->checkCompletion();
        }
    }

    public function checkCompletion()
    {
        $allApproved = true;
        foreach ($this->guestSlots as $index => $slot) {
            $guestApproved = true;
            
            if ($slot['documents']['id_front']['status'] !== 'approved' || 
                $slot['documents']['id_back']['status'] !== 'approved') {
                $guestApproved = false;
            }
            
            if (!$slot['is_foreigner']) {
                if ($slot['documents']['tax_front']['status'] !== 'approved' || 
                    $slot['documents']['tax_back']['status'] !== 'approved') {
                    $guestApproved = false;
                }
            }
            
            $this->guestSlots[$index]['is_approved'] = $guestApproved;
            if (!$guestApproved) $allApproved = false;
        }
        $this->isLocked = !$allApproved;
    }

    public function salvaEProcedi()
    {
        if ($this->isLocked) {
            return;
        }

        // --- COLLEGAMENTO AL DATABASE: SALVATAGGIO REALE DEI DOCUMENTI ---
        $this->reservation->guestDocuments()->delete(); // 1. Pulisce eventuali vecchi tentativi
        foreach ($this->guestSlots as $slot) { // 2. Scorre tutti gli ospiti
            foreach (['id_front', 'id_back', 'tax_front', 'tax_back'] as $type) { // 3. Scorre i 4 tipi di file
                if (!empty($slot['documents'][$type]['file_path'])) {
                    $this->reservation->guestDocuments()->create([
                        'guest_name' => $slot['name'] ?: 'Ospite', 
                        'document_type' => $type, 
                        'file_path' => $slot['documents'][$type]['file_path']
                    ]); // 4. Salva nel DB!
                }
            }
        }
        // ------------------------------------------------------------------

        // Aggiorniamo lo stato della prenotazione
        $this->reservation->update([
            'documents_validated' => true
        ]);

        // =========================================================================
        // QUI INSERIREMO IL CODICE PER LE NOTIFICHE A SERENELLA (Dashboard e WhatsApp)
        // Log::info("Notifica inviata a Serenella per la prenotazione: " . $this->reservation->id);
        // =========================================================================

        // Andiamo alla firma del contratto
        return redirect()->route('checkin.documents', ['token' => $this->reservation->token])->with('success', 'Documenti acquisiti. Procedi alla firma.');
    }

    public function render()
    {
        return view('livewire.document-uploader');
    }
}