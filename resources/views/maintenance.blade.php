<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sito in manutenzione</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-lg w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="text-4xl">🚧</div>
        <h1 class="text-2xl font-black text-slate-900 mt-4">Stiamo aggiornando l'app</h1>
        <p class="text-gray-500 text-sm mt-3">
            Torniamo online a breve. Se hai una richiesta urgente o vuoi maggiori dettagli,
            scrivici un ticket di assistenza: lo riceviamo subito e ti rispondiamo appena possibile.
        </p>

        <a href="/assistenza"
            class="inline-block mt-6 px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
            Scrivi un ticket di assistenza
        </a>

        @if ($hasAccess ?? false)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-2">Sei arrivata qui con il tuo link personale.</p>
                <a href="/entra"
                    class="inline-block px-6 py-3 rounded-2xl font-black text-sm uppercase bg-emerald-600 text-white hover:bg-emerald-700">
                    Visita il sito
                </a>
            </div>
        @endif

        <details class="mt-6 pt-4 border-t border-gray-100 text-left">
            <summary class="text-xs font-black uppercase text-gray-400 cursor-pointer select-none">Accesso team</summary>
            <div class="mt-3">
                @if (session('requested'))
                    <p class="text-green-700 text-xs font-bold bg-green-50 rounded-lg p-3">
                        ✅ Email inviata a {{ session('requested') }}. Apri il link entro 30 minuti per confermare.
                    </p>
                @else
                    <p class="text-gray-400 text-xs mb-2">Inserisci la tua email registrata: riceverai un link per confermare l'accesso.</p>
                    @if ($errors->any())
                        <p class="text-red-600 text-xs mb-2">{{ $errors->first() }}</p>
                    @endif
                    <form method="POST" action="{{ route('team.access.request.general') }}" class="space-y-2">
                        @csrf
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="La tua email" required
                            class="w-full rounded-lg border-2 border-gray-300 text-sm p-3">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">
                            Invia link di accesso
                        </button>
                    </form>
                @endif
            </div>
        </details>
    </div>
</body>
</html>
