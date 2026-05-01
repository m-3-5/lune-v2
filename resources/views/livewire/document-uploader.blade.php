<?php

use function Livewire\Volt\{state, mount, usesFileUploads};
use App\Models\Reservation;
use App\Services\DocumentAIService;
use Illuminate\Support\Facades\Log;

usesFileUploads();

state([
    'reservation' => null,
    'totalGuests' => 1,
    'guestSlots' => [], // Conterrà la griglia dei documenti per ogni ospite
    'uploads' => [],    // Array temporaneo per i file caricati
    'isLocked' => true, // Il lucchetto che blocca l'accesso al contratto
]);

mount(function (Reservation $reservation) {
    $this->reservation = $reservation;
    // Calcoliamo i passeggeri totali (Adulti + Bambini, default 1)
    $this->totalGuests = ($reservation->adults ?? 1) + ($reservation->children ?? 0);
    $this->initializeSlots();
});

// Costruisce la struttura dati per tutti gli ospiti
$initializeSlots = function () {
    $slots = [];
    for ($i = 1; $i <= $this->totalGuests; $i++) {
        $slots[$i] = [
            'name' => '', // L'utente potrà digitare il nome dell'ospite
            'is_foreigner' => false, // <-- Aggiunto questo per funzione straniero
            'documents' => [
                'id_front'  => ['status' => 'empty', 'file_path' => null],
                'id_back'   => ['status' => 'empty', 'file_path' => null],
                'tax_front' => ['status' => 'empty', 'file_path' => null],
                'tax_back'  => ['status' => 'empty', 'file_path' => null],
            ],
            'is_approved' => false // Diventa true solo quando tutti e 4 i documenti sono validi
        ];
    }
    $this->guestSlots = $slots;
};

// Metodo chiamato automaticamente al caricamento di un file
$processUpload = function (DocumentAIService $aiService, $guestIndex, $docType) {
    $file = data_get($this->uploads, "{$guestIndex}.{$docType}");
    
    if (!$file) return;

    // 1. Impostiamo lo stato su "in analisi" per mostrare il semaforo giallo
    $this->guestSlots[$guestIndex]['documents'][$docType]['status'] = 'analyzing';

    // 2. Salviamo fisicamente il file sul disco
    $path = $file->store('documents', 'public');
    $this->guestSlots[$guestIndex]['documents'][$docType]['file_path'] = $path;

    // 3. CHIAMATA AL SERVIZIO AI (Ora Reale e connessa a Google Cloud!)
    try {
        if (str_contains($docType, 'id_')) {
            $result = $aiService->analyzeIdentityDocument($file);
        } else {
            $result = $aiService->analyzeTaxCode($file);
        }

        // Se l'AI restituisce successo, aggiorniamo il semaforo a verde
        if ($result['status'] === 'success') {
            $this->guestSlots[$guestIndex]['documents'][$docType]['status'] = 'approved';
            
            // AGGIUNTA FONDAMENTALE: Salviamo i dati estratti per iniettarli nel contratto!
            if (isset($result['extracted_data'])) {
                // Inizializziamo l'array 'data' se non esiste ancora per questo ospite
                if (!isset($this->guestSlots[$guestIndex]['data'])) {
                    $this->guestSlots[$guestIndex]['data'] = [];
                }
                
                foreach ($result['extracted_data'] as $key => $val) {
                    $this->guestSlots[$guestIndex]['data'][$key] = $val;
                }
            }
        } else {
            $this->guestSlots[$guestIndex]['documents'][$docType]['status'] = 'error';
        }
    } catch (\Exception $e) {
        // In caso di errore (es. Google Cloud irraggiungibile) mettiamo in errore
        $this->guestSlots[$guestIndex]['documents'][$docType]['status'] = 'error';
    }

    $this->checkCompletion();
};

// Modifica la funzione $initializeSlots dentro il blocco PHP
$initializeSlots = function () {
    $slots = [];
    for ($i = 1; $i <= $this->totalGuests; $i++) {
        $slots[$i] = [
            'name' => '',
            'is_foreigner' => false, // <-- Aggiunto questo
            'documents' => [
                'id_front'  => ['status' => 'empty', 'file_path' => null],
                'id_back'   => ['status' => 'empty', 'file_path' => null],
                'tax_front' => ['status' => 'empty', 'file_path' => null],
                'tax_back'  => ['status' => 'empty', 'file_path' => null],
            ],
            'is_approved' => false
        ];
    }
    $this->guestSlots = $slots;
};

// Modifica la funzione $checkCompletion per ignorare il CF se straniero
$checkCompletion = function () {
    $allApproved = true;
    
    foreach ($this->guestSlots as $index => $slot) {
        $guestApproved = true;
        
        // Controlliamo i documenti dell'identità (Sempre obbligatori)
        if ($slot['documents']['id_front']['status'] !== 'approved' || 
            $slot['documents']['id_back']['status'] !== 'approved') {
            $guestApproved = false;
        }

        // Controlliamo il Codice Fiscale SOLO se NON è straniero
        if (!$slot['is_foreigner']) {
            if ($slot['documents']['tax_front']['status'] !== 'approved' || 
                $slot['documents']['tax_back']['status'] !== 'approved') {
                $guestApproved = false;
            }
        }

        $this->guestSlots[$index]['is_approved'] = $guestApproved;
        if (!$guestApproved) $allApproved = false;
    }

    if ($allApproved) {
        $this->isLocked = false;
    }
};

?>

<div class="space-y-8">
    {{-- Header e Contatore --}}
    <div class="flex items-center justify-between bg-indigo-50 p-5 rounded-2xl border border-indigo-100 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-indigo-900">Documenti Ospiti</h2>
            <p class="text-sm text-indigo-700">Carica i documenti per le {{ $totalGuests }} persone previste</p>
        </div>
        <div>
            @if(!$isLocked)
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800 ring-1 ring-inset ring-green-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Sbloccato
                </span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Bloccato
                </span>
            @endif
        </div>
    </div>

    {{-- Griglia degli Ospiti --}}
    <div class="space-y-6">
        @foreach($guestSlots as $index => $slot)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-indigo-100 text-indigo-600 rounded-full w-8 h-8 flex items-center justify-center font-bold">
                            {{ $index }}
                        </div>
                        <input type="text" wire:model="guestSlots.{{ $index }}.name" placeholder="Nome Ospite {{ $index }}" class="bg-transparent border-none focus:ring-0 text-gray-900 font-semibold placeholder-gray-400 p-0">
                    </div>
                    @if($slot['is_approved'])
                        <span class="text-green-600 text-sm font-bold flex items-center gap-1">✓ Completato</span>
                    @endif
                </div>

                {{-- AGGIUNGI DA QUI: Toggle per Ospite Straniero --}}
    <div class="flex items-center gap-2 bg-amber-50 p-2 rounded-lg border border-amber-100">
        <input type="checkbox" 
               wire:model.live="guestSlots.{{ $index }}.is_foreigner" 
               wire:change="checkCompletion"
               id="foreigner-{{ $index }}" 
               class="rounded text-indigo-600 focus:ring-indigo-500">
        <label for="foreigner-{{ $index }}" class="text-xs font-bold text-amber-800 uppercase tracking-wide cursor-pointer">
            Ospite Straniero (Senza Codice Fiscale)
        </label>
    </div>
    {{-- FINE AGGIUNTA --}}

                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    {{-- CARTA D'IDENTITA' --}}
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-gray-700">Carta d'Identità / Passaporto</h4>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- Fronte ID --}}
                            <label class="relative flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ $slot['documents']['id_front']['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                                <span class="text-xs font-semibold text-gray-500 mb-2">Fronte</span>
                                @if($slot['documents']['id_front']['status'] === 'empty')
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                @elseif($slot['documents']['id_front']['status'] === 'analyzing')
                                    <svg class="w-6 h-6 text-yellow-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                @elseif($slot['documents']['id_front']['status'] === 'approved')
                                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                                <input type="file" wire:model="uploads.{{ $index }}.id_front" wire:change="processUpload({{ $index }}, 'id_front')" class="hidden" accept="image/*">
                            </label>

                            {{-- Retro ID --}}
                            <label class="relative flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ $slot['documents']['id_back']['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                                <span class="text-xs font-semibold text-gray-500 mb-2">Retro</span>
                                @if($slot['documents']['id_back']['status'] === 'empty')
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                @elseif($slot['documents']['id_back']['status'] === 'analyzing')
                                    <svg class="w-6 h-6 text-yellow-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                @elseif($slot['documents']['id_back']['status'] === 'approved')
                                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                                <input type="file" wire:model="uploads.{{ $index }}.id_back" wire:change="processUpload({{ $index }}, 'id_back')" class="hidden" accept="image/*">
                            </label>
                        </div>
                    </div>

                 {{-- CODICE FISCALE --}}
    <div class="space-y-3 {{ $slot['is_foreigner'] ? 'opacity-40 grayscale pointer-events-none' : '' }}">
        <h4 class="text-sm font-bold text-gray-700 flex items-center justify-between">
            Codice Fiscale
            @if($slot['is_foreigner'])
                <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded">NON RICHIESTO</span>
            @endif
        </h4>
        
        @if(!$slot['is_foreigner'])
            <div class="grid grid-cols-2 gap-3">
                {{-- Fronte CF --}}
                <label class="relative flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ $slot['documents']['tax_front']['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                    <span class="text-xs font-semibold text-gray-500 mb-2">Fronte</span>
                    @if($slot['documents']['tax_front']['status'] === 'empty')
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    @elseif($slot['documents']['tax_front']['status'] === 'analyzing')
                        <svg class="w-6 h-6 text-yellow-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    @elseif($slot['documents']['tax_front']['status'] === 'approved')
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @endif
                    <input type="file" wire:model="uploads.{{ $index }}.tax_front" wire:change="processUpload({{ $index }}, 'tax_front')" class="hidden" accept="image/*">
                </label>

                {{-- Retro CF --}}
                <label class="relative flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ $slot['documents']['tax_back']['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-300' }}">
                    <span class="text-xs font-semibold text-gray-500 mb-2">Retro</span>
                    @if($slot['documents']['tax_back']['status'] === 'empty')
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    @elseif($slot['documents']['tax_back']['status'] === 'analyzing')
                        <svg class="w-6 h-6 text-yellow-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    @elseif($slot['documents']['tax_back']['status'] === 'approved')
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @endif
                    <input type="file" wire:model="uploads.{{ $index }}.tax_back" wire:change="processUpload({{ $index }}, 'tax_back')" class="hidden" accept="image/*">
                </label>
            </div>
        @else
            <div class="p-4 bg-gray-50 border border-dashed border-gray-200 rounded-xl text-center text-xs text-gray-400 italic">
                Esonerato in quanto cittadino straniero
            </div>
        @endif
    </div>
</div> {{-- CHIUSURA DEL GRID P-5 --}}
</div>
@endforeach

    {{-- Sezione Contratto (Si sblocca solo quando isLocked è false) --}}
    {{-- Sezione Contratto --}}
    @if(!$isLocked)
        <div class="mt-8 transition-all duration-500 transform translate-y-0 opacity-100">
            {{-- Passiamo sia la reservation che i dati degli ospiti ($guestSlots) --}}
            <livewire:contract-manager :reservation="$reservation" :guests="$guestSlots" />
        </div>
    @else
        <div class="mt-8 p-6 bg-gray-50 border border-gray-200 rounded-2xl text-center text-gray-500 opacity-75">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
            <p>Il contratto sarà disponibile per l'accettazione non appena tutti i documenti saranno validati.</p>
        </div>
    @endif
</div>