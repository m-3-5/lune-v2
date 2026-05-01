<?php

use function Livewire\Volt\{state, mount};
use App\Models\Reservation;

state([
    'reservation' => null,
    'apartmentName' => 'Appartamento',
    'guestName' => 'Ospite',
    
    // Stati di sblocco
    'isPaid' => false,
    'docsApproved' => false,
    'contractSigned' => false,
    'isCheckinTime' => false,
    'isUnlocked' => false, // Il vero via libera
]);

mount(function (Reservation $reservation) {
    $this->reservation = $reservation;
    
    // Preleviamo i dati base
    // NOTA: Assicurati di avere la relazione $reservation->apartment configurata nel modello
    $this->apartmentName = $reservation->apartment->name ?? 'Appartamento';
    $this->guestName = $reservation->guest_name ?? 'Ospite';

    // Ricalcoliamo gli stati di sblocco (come fatto nella sidebar)
    $this->isPaid = $reservation->is_paid ?? false;
    $this->docsApproved = $reservation->documents_validated ?? false;
    $this->contractSigned = $reservation->contract_accepted ?? false;

    if ($reservation->checkin_date) {
        $checkinTime = \Carbon\Carbon::parse($reservation->checkin_date)->setTime(16, 0, 0);
        $this->isCheckinTime = now()->greaterThanOrEqualTo($checkinTime);
    }

    $this->isUnlocked = $this->contractSigned && $this->isCheckinTime;
});

?>

<div class="space-y-6">
    {{-- Hero Section (Pianificata per la foto dell'appartamento) --}}
    <div class="relative bg-indigo-900 rounded-3xl overflow-hidden shadow-lg h-48 flex items-end">
        {{-- In futuro qui metteremo l'immagine di copertina dell'appartamento --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
        <div class="relative p-6 text-white w-full">
            <h2 class="text-2xl font-bold mb-1">Benvenuto, {{ explode(' ', $guestName)[0] }}!</h2>
            <p class="text-indigo-100">{{ $apartmentName }} • JLune</p>
        </div>
    </div>

    {{-- Griglia Welcome Kit --}}
    <div class="grid grid-cols-2 gap-4">
        
        {{-- 1. Check-In / Documenti (Sempre visibile, porta alla pagina documenti) --}}
        <a href="{{ route('checkin.documents', ['token' => $reservation->token ?? '']) }}" 
           class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow aspect-square text-center relative overflow-hidden">
            @if($contractSigned)
                <div class="absolute top-3 right-3 bg-green-100 text-green-600 rounded-full p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            @endif
            <svg class="w-10 h-10 text-indigo-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span class="font-bold text-gray-800 text-sm">Check-in</span>
            <span class="text-xs text-gray-500 mt-1">Doc & Contratto</span>
        </a>

        {{-- 2. Credenziali WiFi (Sbloccato solo alla fine) --}}
        <button onclick="{{ $isUnlocked ? 'alert(\'Mostra modale WiFi\')' : '' }}" 
           class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow aspect-square text-center relative {{ !$isUnlocked ? 'opacity-60 cursor-not-allowed' : '' }}">
            @if(!$isUnlocked)
                <div class="absolute top-3 right-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            @endif
            <svg class="w-10 h-10 text-indigo-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
            <span class="font-bold text-gray-800 text-sm">Rete WiFi</span>
        </button>

        {{-- 3. Info Ingresso (Sbloccato solo alla fine) --}}
        <a href="{{ $isUnlocked ? '#' : 'javascript:void(0)' }}" 
           class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow aspect-square text-center relative {{ !$isUnlocked ? 'opacity-60 cursor-not-allowed' : '' }}">
            @if(!$isUnlocked)
                <div class="absolute top-3 right-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            @endif
            <svg class="w-10 h-10 text-indigo-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            <span class="font-bold text-gray-800 text-sm">Info Ingresso</span>
        </a>

        {{-- 4. Regole Generali (Sbloccate solo alla fine) --}}
        <a href="{{ $isUnlocked ? '#' : 'javascript:void(0)' }}" 
           class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow aspect-square text-center relative {{ !$isUnlocked ? 'opacity-60 cursor-not-allowed' : '' }}">
            @if(!$isUnlocked)
                <div class="absolute top-3 right-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            @endif
            <svg class="w-10 h-10 text-indigo-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <span class="font-bold text-gray-800 text-sm">Regole Casa</span>
        </a>

        {{-- 5. Locali Consigliati (Sempre libero) --}}
        <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow aspect-square text-center">
            <svg class="w-10 h-10 text-indigo-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path></svg>
            <span class="font-bold text-gray-800 text-sm">Locali<br>Consigliati</span>
        </a>

        {{-- 6. Galleria Foto (Sempre libero) --}}
        <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow aspect-square text-center">
            <svg class="w-10 h-10 text-indigo-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="font-bold text-gray-800 text-sm">Esplora<br>L'Appartamento</span>
        </a>

    </div>

    {{-- Avviso se bloccato --}}
    @if(!$isUnlocked)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex gap-3 text-yellow-800 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p>Per sbloccare il WiFi e l'accesso alla casa, completa il Check-in e attendi le ore 16:00 del giorno di arrivo.</p>
    </div>
    @endif
</div>