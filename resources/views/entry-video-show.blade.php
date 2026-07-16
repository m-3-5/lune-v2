<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $video->title }} — {{ $video->apartment->name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen px-4 py-8">
    <div class="max-w-md mx-auto">
        <p class="text-indigo-600 font-black text-xs uppercase tracking-widest mb-1">{{ $video->apartment->name }}</p>
        <h1 class="text-2xl font-black text-slate-900 mb-4">{{ $video->title }}</h1>

        @if ($video->videoUrl())
            <video src="{{ $video->videoUrl() }}" controls playsinline class="w-full rounded-2xl shadow-sm border border-gray-100 mb-6"></video>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 mb-6">Video non disponibile</div>
        @endif

        @if ($allSteps->count() > 1)
            <p class="text-xs font-black uppercase text-gray-400 mb-2">Tutti i passaggi</p>
            <div class="space-y-2">
                @foreach ($allSteps as $step)
                    <a href="{{ route('qr.show', $step->qr_token) }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold {{ $step->id === $video->id ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-100 text-gray-800' }}">
                        <span>{{ $loop->iteration }}.</span> {{ $step->title }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
