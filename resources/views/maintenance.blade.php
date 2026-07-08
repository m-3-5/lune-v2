<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jlune — Sito in manutenzione</title>
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
    </div>
</body>
</html>
