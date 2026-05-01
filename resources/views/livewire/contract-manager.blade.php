<?php

use function Livewire\Volt\{state, mount};
use App\Models\Reservation;

state([
    'reservation' => null,
    'guests' => [], // <-- AGGIUNTO: Riceve l'array con i dati estratti dall'IA
    'termsAccepted' => false,
    'privacyAccepted' => false,
    'isContractSigned' => false,
]);

// Aggiungiamo $guests alla funzione mount
mount(function (Reservation $reservation, $guests = []) {
    $this->reservation = $reservation;
    $this->guests = $guests;
    
    // Se il contratto era già stato accettato in precedenza, impostiamo lo stato
    $this->isContractSigned = $this->reservation->contract_accepted ?? false;
    
    if ($this->isContractSigned) {
        $this->termsAccepted = true;
        $this->privacyAccepted = true;
    }
});

$signContract = function () {
    // Validazione base
    if (!$this->termsAccepted || !$this->privacyAccepted) {
        return; 
    }

    // TODO: In futuro, qui potremmo generare un PDF con i log (IP, Timestamp) per prova legale

    // Aggiorniamo il database
    $this->reservation->contract_accepted = true;
    $this->reservation->save();

    $this->isContractSigned = true;

    // Sblocchiamo le informazioni del soggiorno
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
            <p class="text-green-700 mb-6">Grazie per aver completato la procedura legale. Il tuo check-in è terminato.</p>
        </div>
    @else
        <div class="p-6">
            {{-- Testo del contratto (scorrevole) --}}
            <div class="h-64 overflow-y-auto p-5 bg-gray-50 border border-gray-200 rounded-xl mb-6 text-sm text-gray-700 space-y-4">
                <div class="text-center mb-6">
                    <p class="text-lg font-black text-gray-900 uppercase">Contratto di Locazione Turistica</p>
                    <p class="text-xs text-gray-500">(Bozza Fac-simile in attesa di validazione)</p>
                </div>

                <p>Tra la struttura ricettiva <strong>JLune</strong> (di seguito "Locatore") e i seguenti ospiti (di seguito "Conduttori"):</p>
                
                {{-- RIQUADRO DATI ESTRATTI DALL'INTELLIGENZA ARTIFICIALE --}}
                <div class="bg-white p-4 border border-indigo-100 rounded-lg my-4 shadow-sm">
                    <ul class="space-y-3">
                        @forelse($guests as $index => $guest)
                            <li class="border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-indigo-900">Ospite {{ $index }}:</span> 
                                        <span class="text-gray-900">
                                            {{ data_get($guest, 'data.first_name', data_get($guest, 'name', 'N.D.')) }} 
                                            {{ data_get($guest, 'data.last_name', '') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs mt-1">
                                    @if(!data_get($guest, 'is_foreigner', false))
                                        <span class="text-gray-600">Codice Fiscale: </span>
                                        <span class="font-mono text-gray-900">{{ data_get($guest, 'data.tax_code', 'Acquisito a sistema') }}</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-semibold">Cittadino Straniero (CF Non Richiesto)</span>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 italic">Dati ospiti in fase di elaborazione...</li>
                        @endforelse
                    </ul>
                </div>

                <p>Si conviene e si stipula quanto segue:</p>
                
                <p><strong>1. Oggetto e Uso dell'Immobile:</strong> Il Locatore concede in locazione per esclusive finalità turistiche l'immobile prenotato. È severamente vietato l'uso per scopi diversi o da parte di un numero di persone superiore a quello indicato nei documenti caricati a sistema.</p>
                <p><strong>2. Check-in e Check-out:</strong> Il check-in è consentito a partire dalle ore 16:00. Il check-out deve avvenire tassativamente entro le ore 10:00 del giorno di partenza, salvo accordi scritti con l'Host.</p>
                <p><strong>3. Regole di Comportamento:</strong> L'ospite si impegna a mantenere un comportamento rispettoso del riposo altrui e delle norme di civile convivenza. Non è consentito organizzare feste o eventi all'interno dell'appartamento.</p>
                <p><strong>4. Danni e Responsabilità:</strong> Il Conduttore è responsabile per qualsiasi danno arrecato all'immobile, agli arredi o agli elettrodomestici durante il soggiorno, e ne risponderà economicamente.</p>
            </div>

            <form wire:submit="signContract" class="space-y-4">
                {{-- Checkbox 1: Termini Generali --}}
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" wire:model.live="termsAccepted" required class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">Accetto i Termini e le Condizioni</span>
                        <p class="text-gray-500">Dichiaro di aver letto, compreso e di accettare integralmente le condizioni generali di locazione riportate sopra per tutti gli occupanti.</p>
                    </div>
                </label>

                {{-- Checkbox 2: Privacy (Essenziale) --}}
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center h-5">
                        <input type="checkbox" wire:model.live="privacyAccepted" required class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">Informativa Privacy</span>
                        <p class="text-gray-500">Acconsento al trattamento dei miei dati e dei miei documenti d'identità in conformità al GDPR per le sole finalità di Pubblica Sicurezza e gestione della prenotazione.</p>
                    </div>
                </label>

                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            @if(!$termsAccepted || !$privacyAccepted) disabled @endif>
                        Firma Elettronica e Accetta
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-3">Cliccando su "Firma Elettronica e Accetta" l'azione assume valore legale di firma vincolante.</p>
                </div>
            </form>
        </div>
    @endif
</div>