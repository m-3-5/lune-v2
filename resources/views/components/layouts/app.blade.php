<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lune - Gestione Soggiorno' }}</title>
    
    <!-- Caricamento script e stili di Laravel (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<!-- Inizializziamo Alpine.js per il menu: x-data="{ menuOpen: false }" -->
<body class="bg-gray-50 text-gray-800 font-sans antialiased pb-20" x-data="{ menuOpen: false }">

    <!-- HEADER FISSO -->
    <header class="fixed top-0 left-0 w-full bg-indigo-700 text-white shadow-md z-50">
        <div class="max-w-md mx-auto flex justify-between items-center p-4">
            <!-- Nome Appartamento o Logo -->
            <h1 class="text-xl font-extrabold tracking-tight">
                {{ $apartmentName ?? 'Lune App' }}
            </h1>

            <!-- Pulsante Menu Hamburger -->
            <button @click="menuOpen = !menuOpen" class="focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <!-- Cambia icona se il menu è aperto o chiuso -->
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
        
        <!-- VOCE 1: Home/Dettagli (Sempre accessibile) -->
        <a href="#" class="flex items-center gap-2 text-indigo-700 hover:text-indigo-900 border-b pb-2">
            🏠 Il tuo soggiorno
        </a>

        <!-- VOCE 2: Documenti (Sbloccata se pagato) -->
        @php $isPaid = $reservation->is_paid ?? false; @endphp
        <a href="{{ $isPaid ? route('checkin.documents', ['token' => $reservation->token]) : '#' }}" 
        class="flex items-center gap-2 border-b pb-2 {{ $isPaid ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
        📄 Inserimento Documenti
        @if(!$isPaid) <span class="text-xs text-red-500 ml-auto">Attesa Saldo</span> @endif
        </a>

        <!-- VOCE 3: Info Ingresso (Sbloccata se pagato + documenti OK + check-in time) -->
        @php $isUnlocked = $isPaid && ($hasDocuments ?? false) && ($isCheckinTime ?? false); @endphp
        <a href="#" 
           class="flex items-center gap-2 border-b pb-2 {{ $isUnlocked ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
            🔑 Info Ingresso & Video
            @if(!$isUnlocked) <span class="text-xs text-gray-400 ml-auto">Bloccato</span> @endif
        </a>

        <!-- VOCE 4: Regole e QR Code (Stesso blocco dell'ingresso) -->
        <a href="#" 
           class="flex items-center gap-2 border-b pb-2 {{ $isUnlocked ? 'text-gray-800 hover:text-indigo-600' : 'text-gray-300 cursor-not-allowed pointer-events-none' }}">
            📱 QR Code Elettrodomestici
        </a>
        
        <!-- VOCE 5: Check-out (Sempre visibile, ma magari cliccabile solo dopo l'ingresso) -->
        <a href="#" class="flex items-center gap-2 border-b pb-2 text-gray-800 hover:text-indigo-600">
            👋 Istruzioni Check-out
        </a>

    </nav>

    <!-- Overlay scuro per chiudere il menu cliccando fuori -->
    <div x-show="menuOpen" @click="menuOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-30" style="display: none;"></div>

    <!-- CONTENUTO PRINCIPALE DELLA PAGINA -->
    <!-- Diamo un padding-top (pt-24) per non far coprire il contenuto dall'header fisso -->
    <main class="pt-24 max-w-md mx-auto px-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>