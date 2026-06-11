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
    $this->reservation = $reservation->fresh();
    $this->guests = $guests ?: ($reservation->extracted_guests ?? []);
    
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

    // Firma completa: timestamp, PDF, notifica admin, email con allegato
    $result = app(\App\Services\ContractSigningService::class)->sign($this->reservation->fresh());

    if (! $result['success']) {
        $this->addError('signing', $result['message']);

        return;
    }

    $this->reservation->refresh();
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
            <div class="h-80 overflow-y-auto p-5 bg-gray-50 border border-gray-200 rounded-xl mb-6 text-sm">
                {!! app(\App\Services\ContractRenderService::class)->html($reservation) !!}
            </div>

            @if($reservation->requiresTaxCodeForContract())
                <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm font-semibold">
                    ⚠️ Per firmare il contratto è necessario il codice fiscale di tutti gli ospiti italiani. Inseriscilo nella sezione dedicata prima di procedere.
                </div>
            @endif

            @error('signing')
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm font-semibold">
                    {{ $message }}
                </div>
            @enderror

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
                            @if(!$termsAccepted || !$privacyAccepted || $reservation->requiresTaxCodeForContract()) disabled @endif>
                        Firma Elettronica e Accetta
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-3">Cliccando su "Firma Elettronica e Accetta" l'azione assume valore legale di firma vincolante.</p>
                </div>
            </form>
        </div>
    @endif
</div>