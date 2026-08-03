<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi — {{ \App\Support\AppSettings::appName() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-sm w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="text-4xl text-center">🔐</div>
        <h1 class="text-2xl font-black text-slate-900 mt-4 text-center">{{ \App\Support\AppSettings::appName() }}</h1>
        <p class="text-gray-500 text-sm mt-1 text-center">Accesso team</p>

        @if ($errors->any())
            <p class="text-red-600 text-xs mt-4 text-center font-bold">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6 space-y-3">
            @csrf
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3 mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3 mt-1">
            </div>
            <label class="flex items-center gap-2 text-xs text-gray-500">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                Ricordami su questo dispositivo
            </label>
            <button type="submit"
                class="w-full px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
                Accedi
            </button>
        </form>
    </div>
</body>
</html>
