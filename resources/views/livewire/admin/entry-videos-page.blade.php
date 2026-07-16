<div class="max-w-3xl mx-auto space-y-6 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Gestione Video</h1>
        <p class="text-gray-500 text-sm mt-1">Carica un file oppure incolla un link YouTube/Vimeo. Ogni video ha un QR da stampare e attaccare nel punto giusto.</p>
    </div>

    @if (session()->has('video_message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold">{{ session('video_message') }}</div>
    @endif
    @error('newVideo') <div class="bg-red-100 text-red-800 p-4 rounded-2xl text-sm font-bold">{{ $message }}</div> @enderror
    @error('newVideoUrl') <div class="bg-red-100 text-red-800 p-4 rounded-2xl text-sm font-bold">{{ $message }}</div> @enderror

    <div class="flex gap-2">
        <button type="button" wire:click="$set('category', 'ingresso')"
            class="px-4 py-2 rounded-full text-xs font-black uppercase {{ $category === 'ingresso' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-500' }}">
            🚪 Video di ingresso
        </button>
        <button type="button" wire:click="$set('category', 'elettrodomestico')"
            class="px-4 py-2 rounded-full text-xs font-black uppercase {{ $category === 'elettrodomestico' ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-500' }}">
            🔌 Elettrodomestici
        </button>
    </div>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <label class="text-[10px] font-black uppercase text-gray-400">Appartamento</label>
        <select wire:model.live="apartmentId" class="w-full rounded-xl border-gray-200 text-sm mt-1 mb-4">
            @foreach ($apartments as $apt)
                <option value="{{ $apt->id }}">{{ $apt->name }}</option>
            @endforeach
        </select>

        @forelse ($videos as $video)
            <div class="flex items-center gap-4 bg-gray-50 rounded-2xl p-4 mb-3">
                <img src="{{ $video->qrDataUri() }}" alt="QR" class="w-16 h-16 rounded-lg bg-white border border-gray-200 shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-black text-sm">{{ $loop->iteration }}. {{ $video->title }}</p>
                    @if ($video->video_url)
                        <p class="text-[10px] font-black uppercase text-indigo-500">Link esterno</p>
                    @endif
                    <a href="{{ $video->qrUrl() }}" target="_blank" rel="noopener" class="text-indigo-600 text-xs underline break-all">{{ $video->qrUrl() }}</a>
                </div>
                <div class="flex flex-col gap-1 shrink-0">
                    <button type="button" wire:click="moveUp({{ $video->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">▲</button>
                    <button type="button" wire:click="moveDown({{ $video->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">▼</button>
                </div>
                <button type="button" wire:click="deleteVideo({{ $video->id }})" wire:confirm="Eliminare questo video?" class="text-red-500 text-xs font-bold shrink-0">Elimina</button>
            </div>
        @empty
            <p class="text-sm text-gray-400 italic text-center py-6">
                Nessun video ancora in "{{ $category === 'ingresso' ? 'Video di ingresso' : 'Elettrodomestici' }}" per questo appartamento.
            </p>
        @endforelse
    </section>

    <section class="bg-white rounded-3xl shadow-sm border-2 border-indigo-200 p-6 space-y-3">
        <h2 class="text-lg font-black text-indigo-950">Aggiungi video — {{ $category === 'ingresso' ? 'Video di ingresso' : 'Elettrodomestici' }}</h2>

        <div>
            <label class="text-[10px] font-black uppercase text-gray-400">Titolo (es. "Portone condominiale" o "Lavatrice")</label>
            <input type="text" wire:model="newTitle" class="w-full rounded-xl border-gray-200 text-sm mt-1">
        </div>

        <div class="flex gap-2">
            <button type="button" wire:click="$set('newMode', 'file')"
                class="flex-1 py-2 rounded-xl text-xs font-black uppercase {{ $newMode === 'file' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                📁 Carica file
            </button>
            <button type="button" wire:click="$set('newMode', 'url')"
                class="flex-1 py-2 rounded-xl text-xs font-black uppercase {{ $newMode === 'url' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                🔗 Link YouTube/Vimeo
            </button>
        </div>

        @if ($newMode === 'file')
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">File video (mp4, max 50MB)</label>
                <input type="file" wire:model="newVideo" accept="video/*" class="w-full text-sm mt-1">
                <div wire:loading wire:target="newVideo" class="text-xs text-gray-400 mt-1">Caricamento…</div>
            </div>
        @else
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Link del video</label>
                <input type="url" wire:model="newVideoUrl" placeholder="https://youtu.be/…" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                <p class="text-[11px] text-gray-400 mt-1">Funziona con YouTube e Vimeo (anteprima diretta). Altri siti: l'ospite vede un pulsante "Guarda il video".</p>
            </div>
        @endif

        <button type="button" wire:click="addVideo" wire:loading.attr="disabled"
            class="w-full py-3 rounded-2xl text-sm font-black uppercase bg-indigo-600 text-white">
            Aggiungi
        </button>
    </section>
</div>
