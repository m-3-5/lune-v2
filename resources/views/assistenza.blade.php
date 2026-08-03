<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Assistenza</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-lg w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <h1 class="text-2xl font-black text-slate-900">Scrivi un ticket di assistenza</h1>
        <p class="text-gray-500 text-sm mt-2">
            Descrivi la richiesta: la riceviamo subito e ti rispondiamo via email appena possibile.
        </p>

            <form method="POST" action="{{ route('assistenza.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Nome (facoltativo)</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Il tuo nome"
                        class="w-full rounded-lg border-2 border-gray-300 text-sm mt-1 p-3">
                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Email (per la risposta)</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="latua@email.it"
                        class="w-full rounded-lg border-2 border-gray-300 text-sm mt-1 p-3">
                    @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Oggetto</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Es: Problema con il pagamento"
                        class="w-full rounded-lg border-2 border-gray-300 text-sm mt-1 p-3">
                    @error('subject') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Messaggio</label>
                    <textarea name="message" rows="10" required placeholder="Scrivi qui il tuo messaggio…"
                        class="w-full rounded-lg border-2 border-gray-300 text-sm mt-1 p-3">{{ old('message') }}</textarea>
                    @error('message') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    class="w-full px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
                    Invia ticket
                </button>
            </form>
    </div>
</body>
</html>
