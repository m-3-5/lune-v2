<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout; // <-- 1. Importiamo l'attributo
use App\Models\Reservation;
use App\Models\GuestDocument;
use App\Services\AdminNotificationService;
use App\Services\GuestNotificationService;
use App\Services\ContractRenderService;
use App\Services\GuestDataExtractionService;

#[Layout('components.layouts.admin')] // <-- 2. Diciamo a Livewire di usare il layout admin
class DettaglioArrivo extends Component
{
    public Reservation $reservation;

    public string $contractLocaleToSend = 'it';

  /** @var array<int, string> */
    public array $adminTaxCodes = [];

    public function toggleNotificationsPilot(): void
    {
        $this->reservation->update([
            'notifications_pilot' => ! $this->reservation->notifications_pilot,
        ]);
        $this->reservation->refresh();

        session()->flash(
            'message',
            $this->reservation->notifications_pilot
                ? 'Prova notifiche attiva su questa prenotazione: l\'ospite riceverà email/WhatsApp se i toggle in Progetto sono accesi.'
                : 'Prova notifiche disattivata: essendo una prenotazione TEST, non riceverà notifiche reali.'
        );
    }

    public function mount($id)
    {
        $this->reservation = Reservation::with(['guestDocuments', 'apartment'])->findOrFail($id);
        $this->contractLocaleToSend = $this->reservation->contract_locale === 'en' ? 'en' : 'it';
        $this->syncAdminTaxCodesFromReservation();
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
        app(AdminNotificationService::class)->documentsApproved($this->reservation);
        app(GuestNotificationService::class)->documentsApproved($this->reservation);
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
        app(AdminNotificationService::class)->documentsRejected($this->reservation);
        app(GuestNotificationService::class)->documentsRejected($this->reservation);
        session()->flash('message', 'Documenti rifiutati. L\'ospite dovrà ricaricarli.');
    }

    public function estraiDatiDocumenti(GuestDataExtractionService $extraction, AdminNotificationService $notifications)
    {
        // Document AI può richiedere 1–2 minuti (più chiamate API).
        set_time_limit(300);

        try {
            $result = $extraction->extractForReservation($this->reservation);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Estrazione documenti fallita: '.$e->getMessage());
            session()->flash('error', 'Estrazione interrotta: '.$e->getMessage().' — controlla storage/logs/laravel.log');

            return;
        }

        $this->reservation->refresh();
        $this->syncAdminTaxCodesFromReservation();

        $guests = $this->reservation->extracted_guests ?? [];
        $hasNotes = collect($guests)->contains(fn ($g) => ! empty($g['extraction_notes']));
        $hasAnyData = collect($guests)->contains(function ($g) {
            $d = $g['data'] ?? [];

            return filled($d['tax_code'] ?? null)
                || filled($d['first_name'] ?? null)
                || filled($d['last_name'] ?? null);
        });

        $telegramOk = $result['success'] && ($hasAnyData || ! $hasNotes);

        $notifications->extractionDone(
            $this->reservation,
            $telegramOk,
            $result['message']
        );

        if ($result['success']) {
            $msg = $result['message'];
            if ($hasNotes) {
                $msg .= ' Alcuni documenti hanno avvisi — vedi note sotto.';
            }
            session()->flash('message', $msg);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    private function syncAdminTaxCodesFromReservation(): void
    {
        $this->adminTaxCodes = [];

        foreach ($this->reservation->contractGuests() as $guest) {
            if (! ($guest['is_foreigner'] ?? false)) {
                $this->adminTaxCodes[(int) ($guest['slot'] ?? 0)] = $guest['data']['tax_code'] ?? '';
            }
        }
    }

    public function saveAdminTaxCodes(): void
    {
        foreach ($this->adminTaxCodes as $slot => $code) {
            $code = trim((string) $code);
            if ($code !== '') {
                $this->reservation->setGuestTaxCode((int) $slot, $code);
            }
        }
        $this->reservation->refresh();
        session()->flash('message', 'Codici fiscali aggiornati.');
    }

    public function inviaContrattoPerFirma(ContractRenderService $contracts): void
    {
        if (empty($this->reservation->extracted_guests)) {
            session()->flash('error', 'Esegui prima l\'estrazione dei dati dai documenti.');

            return;
        }

        if (! in_array($this->contractLocaleToSend, ['it', 'en'], true)) {
            session()->flash('error', 'Seleziona la lingua del contratto (IT o EN).');

            return;
        }

        $this->reservation->update([
            'contract_locale' => $this->contractLocaleToSend,
            'contract_ready_for_guest' => true,
        ]);
        $this->reservation->refresh();

        $contracts->saveHtmlSnapshot($this->reservation);

        app(AdminNotificationService::class)->contractAuthorized($this->reservation);
        app(GuestNotificationService::class)->contractReady($this->reservation);

        $lang = $this->contractLocaleToSend === 'en' ? 'inglese' : 'italiano';
        session()->flash('message', "Contratto pronto — inviato per la firma ({$lang}).");
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