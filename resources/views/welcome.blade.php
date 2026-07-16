<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jlune</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-lg w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="text-4xl">🏠</div>
        <h1 class="text-2xl font-black text-slate-900 mt-4">Jlune</h1>
        <p class="text-gray-500 text-sm mt-3">
            Questa è un'area privata per la gestione degli appartamenti. Se hai una prenotazione,
            trovi il tuo link personale di accesso nell'email di conferma.
        </p>

        <a href="/assistenza"
            class="inline-block mt-6 px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
            Scrivi un ticket di assistenza
        </a>
    </div>
</body>
</html>
