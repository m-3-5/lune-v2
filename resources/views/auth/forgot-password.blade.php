<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password dimenticata — {{ \App\Support\AppSettings::appName() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-sm w-full bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="text-4xl text-center">🔑</div>
        <h1 class="text-2xl font-black text-slate-900 mt-4 text-center">Password dimenticata</h1>

        @if (session('status'))
            <p class="text-green-700 text-xs font-bold bg-green-50 rounded-lg p-3 mt-4 text-center">{{ session('status') }}</p>
        @else
            <p class="text-gray-500 text-sm mt-3 text-center">
                Inserisci la tua email: se registrata, riceverai un link per reimpostare la password.
            </p>

            @if ($errors->any())
                <p class="text-red-600 text-xs mt-4 text-center font-bold">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="mt-6 space-y-3">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tua@email.it"
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3">
                <button type="submit"
                    class="w-full px-6 py-3 rounded-2xl font-black text-sm uppercase bg-indigo-600 text-white hover:bg-indigo-700">
                    Invia link di reset
                </button>
            </form>
        @endif

        <a href="{{ route('admin.login') }}" class="block text-center text-xs font-bold text-gray-400 mt-6 hover:text-gray-600">← Torna al login</a>
    </div>
</body>
</html>
