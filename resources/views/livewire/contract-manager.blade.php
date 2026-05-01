<?php

use function Livewire\Volt\{state, mount};
use App\Models\Reservation;

state([
    'reservation' => null,
    'termsAccepted' => false,
    'privacyAccepted' => false,
    'isContractSigned' => false,
]);

mount(function (Reservation $reservation) {
    $this->reservation = $reservation;
    // Se il contratto era già stato accettato in precedenza, impostiamo lo stato
    $this->isContractSigned = $this->reservation->contract_accepted ?? false;
    
    if ($this->isContractSigned) {
        $this->termsAccepted = true;
        $this->privacyAccepted = true;
    }
});

$signContract = function () {
    // Validazione base (anche se HTML5 previene il submit se non spuntate)
    if (!$this->termsAccepted || !$this->privacyAccepted) {
        return; 
    }

    // TODO: In futuro, qui potremmo generare un PDF con i log (IP, Timestamp) per prova legale

    // Aggiorniamo il database
    $this->reservation->contract_accepted = true;
    $this->reservation->save();

    $this->isContractSigned = true;

    // Qui potremmo emettere un evento per sbloccare le informazioni del soggiorno
    $this->dispatch('contract-signed'); 
};

?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="bg-indigo-50 border-b border-indigo-100 p-5">
        <h3 class="text-xl font-bold text-indigo-900 flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Contratto di Locazione Turistica
        </h3>
    </div>

    @if($isContractSigned)
        <div class="p-8 text-center bg-green-50">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h4 class="text-2xl font-bold text-green-900 mb-2">Contratto Accettato</h4>
            <p class="text-green-700 mb-6">Grazie per aver completato la procedura legale. Il tuo check-in è quasi terminato.</p>
            {{-- Qui potremo mettere un bottone "Vai alle Info Soggiorno" --}}
        </div>
    @else
        <div class="p-6">
            {{-- Testo del contratto (scorrevole) --}}
            <div class="h-64 overflow-y-auto p-4 bg-gray-50 border border-gray-200 rounded-xl mb-6 text-sm text-gray-700 space-y-4">
                <p><strong>CONDIZIONI GENERALI DI CONTRATTO</strong></p>
                <p>Il presente documento regola il soggiorno turistico presso le nostre strutture. L'ospite, accettando il presente contratto, dichiara di aver preso visione delle regole della casa e si impegna a rispettarle.</p>
                <p><strong>1. Uso dell'Immobile:</strong> L'immobile è concesso in locazione esclusivamente per finalità turistica. Non è consentito l'uso per scopi diversi o da parte di un numero di persone superiore a quello indicato in prenotazione.</p>
                <p><strong>2. Check-in e Check-out:</strong> (Dettagli sugli orari da definire con Serenella).</p>
                <p><strong>3. Regole di Comportamento:</strong> Si raccomanda il rispetto del riposo altrui e delle norme di civile convivenza. Non è consentito organizzare feste (salvo eccezioni concordate).</p>
                <p><em>(Qui andrà inserito il testo legale completo fornito da Serenella)</em></p>
            </div>

            <form wire:submit="signContract" class="space-y-4">
                {{-- Checkbox 1: Termini Generali --}}
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" wire:model.live="termsAccepted" required class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">Accetto i Termini e le Condizioni</span>
                        <p class="text-gray-500">Dichiaro di aver letto, compreso e di accettare integralmente le condizioni generali di locazione riportate sopra.</p>
                    </div>
                </label>

                {{-- Checkbox 2: Privacy (Essenziale) --}}
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" wire:model.live="privacyAccepted" required class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">Informativa Privacy</span>
                        <p class="text-gray-500">Acconsento al trattamento dei miei dati personali in conformità al GDPR per le finalità legate alla gestione della prenotazione.</p>
                    </div>
                </label>

                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            @if(!$termsAccepted || !$privacyAccepted) disabled @endif>
                        Conferma e Accetta
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-3">Cliccando su "Conferma e Accetta" l'azione avrà valore di firma elettronica.</p>
                </div>
            </form>
        </div>
    @endif
</div>