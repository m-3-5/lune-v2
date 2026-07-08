<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket {{ $item->ticketNumber() }} — Jlune</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-lg w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <p class="text-[10px] font-black uppercase text-gray-400">Ticket {{ $item->ticketNumber() }}</p>
        <h1 class="text-2xl font-black text-slate-900 mt-1">{{ str($item->title)->after('Ticket assistenza: ') }}</h1>
        <span class="inline-block mt-2 text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $item->statusColor() }}">{{ $item->statusLabel() }}</span>

        @if (session('ticket_sent'))
            <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold mt-4">
                ✅ Ticket ricevuto. Salva questa pagina per seguirlo e rispondere.
            </div>
        @endif

        @if (session('reply_sent'))
            <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold mt-4">
                ✅ Risposta inviata.
            </div>
        @endif

        <div class="mt-6 space-y-3">
            <div class="bg-gray-50 rounded-xl p-4 text-sm">
                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Tu</p>
                <p class="whitespace-pre-wrap">{{ str($item->body)->after("\n\n") }}</p>
            </div>

            @foreach ($item->replies as $reply)
                <div class="rounded-xl p-4 text-sm {{ $reply->author === 'cliente' ? 'bg-gray-50' : 'bg-indigo-50' }}">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-1">{{ $reply->author === 'cliente' ? 'Tu' : 'Team' }}</p>
                    <p class="whitespace-pre-wrap">{{ $reply->body }}</p>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('ticket.reply', $item->public_token) }}" class="mt-6 space-y-3">
            @csrf
            <label class="text-[10px] font-black uppercase text-gray-400">Rispondi</label>
            <textarea name="message" rows="6" required placeholder="Scrivi qui la tua risposta…"
                class="w-full rounded-lg border-2 border-gray-300 text-sm mt-1 p-3">{{ old('message') }}</textarea>
            @error('message') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

            <button type="submit"
                class="w-full px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
                Invia risposta
            </button>
        </form>
    </div>
</body>
</html>
