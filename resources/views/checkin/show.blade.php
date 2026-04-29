<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soggiorno a {{ $apartment->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased pb-20">

    <header class="bg-indigo-700 text-white shadow-md p-6 rounded-b-3xl text-center">
        <h1 class="text-3xl font-extrabold tracking-tight">{{ $apartment->name }}</h1>
        <p class="mt-3 text-indigo-100 text-lg font-medium">Ciao {{ $reservation->guest_name }}!</p>
    </header>

    <main class="max-w-md mx-auto mt-6 px-4 space-y-6">
        
        {{-- IL SUPER-LUCCHETTO --}}
        <div class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-indigo-600">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-900">
                🗝️ Il tuo Ingresso
            </h2>

            @if($is_unlocked)
                <div class="bg-green-50 p-4 rounded-xl text-center border border-green-200">
                    <p class="text-green-800 font-bold">Tutto pronto! Ecco le istruzioni.</p>
                    {{-- Qui andranno i video e le foto per l'ingresso --}}
                </div>
            @else
                {{-- STATI DI BLOCCO --}}
                @if(!$is_paid)
                    <div class="bg-red-50 p-4 rounded-xl text-center mb-3">
                        <p class="text-red-800 font-bold mb-1">⚠️ Saldo Richiesto</p>
                    </div>
                @endif

                @if($is_early)
                    <div class="bg-amber-50 p-4 rounded-xl text-center mb-3">
                        <p class="text-amber-800 font-bold text-sm">⏱️ Ingresso dalle ore 16:00</p>
                    </div>
                @endif

                {{-- IL COMPONENTE LIVEWIRE PER I DOCUMENTI --}}
                <div class="mt-4">
                    <livewire:document-uploader :reservation="$reservation" />
                </div>
            @endif
        </div>

    </main>

    @livewireScripts
</body>
</html>