<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lune - Gestione Soggiorno' }}</title>
    
    <!-- Caricamento script e stili di Laravel (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased pb-20" x-data="{ menuOpen: false }">

    @php
        // Gestione sicura delle variabili nel layout globale
        $hasReservation = isset($reservation) && $reservation !== null;

        $isPaid = $hasReservation ? ($reservation->is_paid ?? false) : false;
        $docsApproved = $hasReservation ? ($reservation->documents_validated ?? false) : false;
        $contractSigned = $hasReservation ? ($reservation->contract_accepted ?? false) : false;

        $isCheckinTime = false;
        $isNearCheckout = false;

        if ($hasReservation && $reservation->checkin_date) {
            // Fissiamo l'ingresso alle 16:00 del giorno di check-in
            $checkinTime = \Carbon\Carbon::parse($reservation->checkin_date)->setTime(16, 0, 0);
            $isCheckinTime = now()->greaterThanOrEqualTo($checkinTime);
        }

        if ($hasReservation && $reservation->checkout_date) {
            // Fissiamo l'uscita alle 10:00 del giorno di check-out
            $checkoutTime = \Carbon\Carbon::parse($reservation->checkout_date)->setTime(10, 0, 0);
            // Vero se mancano 24 ore o meno all'uscita, ma non siamo ancora oltre l'orario
            $isNearCheckout = now()->diffInHours($checkoutTime, false) <= 24 && now()->lessThan($checkoutTime);
        }

        // Lo sblocco finale della casa avviene solo se il contratto è firmato E sono passate le 16:00
        $isUnlocked = $contractSigned && $isCheckinTime;
    @endphp

    <!-- HEADER FISSO -->
    <header class="fixed top-0 left-0 w-full bg-indigo-700 text-white shadow-md z-50">
        <div class="max-w-md mx-auto flex justify-between items-center p-4">
            <h1 class="text-xl font-extrabold tracking-tight">
                {{ $apartmentName ?? 'Lune App' }}
            </h1>

            <button @click="menuOpen = !menuOpen" class="focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!menuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    <path x-show="menuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- MENU LATERALE (SLIDER) -->
    <nav x-show="menuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-full"
         class="fixed top-[68px] left-0 w-64 h-full bg-white shadow-2xl z-40 flex flex-col p-6 space-y-4 font-medium"
         style="display: none;">
        
        <!-- VOCE 1: Home (Sempre accessibile) -->
        <a href="#" class="flex items-center gap-2 text-indigo-700 hover:text-indigo-900 border-b pb-2">
            🏠 Il tuo soggiorno
        </a>

        <!-- VOCE 2: Documenti (Sbloccata se pagato) -->
        <a href="{{ $isPaid ? route('checkin.documents', ['token' => $reservation->token ?? '']) : '#' }}" 
           class="flex items-center gap-2 border-b pb-2 {{ $isPaid ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
            📄 Inserimento Documenti
            @if(!$isPaid) <span class="text-[10px] uppercase font-bold text-red-500 ml-auto bg-red-50 px-2 py-1 rounded">Attesa Saldo</span> @endif
        </a>

        <!-- VOCE 3: Firma Contratto (Sbloccata se documenti validati) -->
        <a href="{{ $docsApproved ? route('checkin.documents', ['token' => $reservation->token ?? '']) . '#sezione-contratto' : '#' }}" 
           class="flex items-center gap-2 border-b pb-2 {{ $docsApproved ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
            ✍️ Firma Contratto
            @if(!$docsApproved) 
                <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
            @else
                @if($contractSigned)
                    <span class="text-xs text-green-600 font-bold ml-auto">✓</span>
                @endif
            @endif
        </a>

        <!-- VOCE 4: Info Ingresso (Sbloccata se Contratto firmato + check-in time >= 16:00) -->
        <a href="#" 
           class="flex items-center gap-2 border-b pb-2 {{ $isUnlocked ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
            🔑 Info Ingresso & Video
            @if(!$isUnlocked) 
                <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
            @endif
        </a>

        <!-- VOCE 5: Regole e QR Code (Stesso blocco dell'ingresso) -->
        <a href="#" 
           class="flex items-center gap-2 border-b pb-2 {{ $isUnlocked ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
            📱 QR Code Elettrodomestici
            @if(!$isUnlocked) 
                <svg class="w-4 h-4 ml-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
            @endif
        </a>
        
        <!-- VOCE 6: Check-out -->
        <a href="#" class="flex items-center gap-2 border-b pb-2 {{ $isNearCheckout ? 'text-orange-600 font-bold' : 'text-gray-800 hover:text-indigo-600' }}">
            👋 Istruzioni Check-out
            @if($isNearCheckout)
                <span class="text-[10px] uppercase bg-orange-100 text-orange-800 px-2 py-1 rounded-full ml-auto animate-pulse">In uscita</span>
            @endif
        </a>

    </nav>

    <!-- Overlay scuro -->
    <div x-show="menuOpen" @click="menuOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-30" style="display: none;"></div>

    <!-- CONTENUTO PRINCIPALE -->
    <main class="pt-24 max-w-md mx-auto px-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>