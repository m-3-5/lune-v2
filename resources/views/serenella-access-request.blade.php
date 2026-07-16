<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conferma accesso — Jlune</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="text-4xl text-center">🔐</div>
        <h1 class="text-2xl font-black text-slate-900 mt-4 text-center">Richiedi accesso</h1>

        @if (session('requested'))
            <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold mt-6 text-center">
                ✅ Email inviata a {{ session('requested') }}. Apri il link entro 30 minuti per confermare.
            </div>
        @else
            <p class="text-gray-500 text-sm mt-3 text-center">
                Per attivare il tuo accesso permanente, inserisci la tua email registrata:
                riceverai un link per confermare.
            </p>

            @if ($errors->any())
                <p class="text-red-600 text-xs mt-4 text-center font-bold">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('serenella.access.request', $token) }}" class="mt-6 space-y-3">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="latua@email.it"
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3">
                <button type="submit"
                    class="w-full px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
                    Invia link di conferma
                </button>
            </form>
        @endif
    </div>
</body>
</html>
