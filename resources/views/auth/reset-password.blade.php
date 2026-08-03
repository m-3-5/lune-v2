<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimposta password — {{ \App\Support\AppSettings::appName() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-sm w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="text-4xl text-center">🔑</div>
        <h1 class="text-2xl font-black text-slate-900 mt-4 text-center">Nuova password</h1>

        @if ($errors->any())
            <p class="text-red-600 text-xs mt-4 text-center font-bold">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="mt-6 space-y-3">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3 mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Nuova password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3 mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Conferma nuova password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3 mt-1">
            </div>
            <button type="submit"
                class="w-full px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
                Aggiorna password
            </button>
        </form>
    </div>
</body>
</html>
