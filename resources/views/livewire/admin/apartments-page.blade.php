<div class="max-w-3xl mx-auto space-y-6 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Appartamenti</h1>
        <p class="text-gray-500 text-sm mt-1">Gestisci gli appartamenti della struttura.</p>
    </div>

    @if (session()->has('appartamenti_message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold">{{ session('appartamenti_message') }}</div>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-indigo-950">{{ $editingId ? 'Modifica appartamento' : 'Nuovo appartamento' }}</h2>

        <div class="grid sm:grid-cols-2 gap-3">
            <div class="sm:col-span-2">
                <label class="text-[10px] font-black uppercase text-gray-400">Nome</label>
                <input type="text" wire:model="name" placeholder="Es. Trilocale Vista Mare" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="text-[10px] font-black uppercase text-gray-400">Indirizzo (opzionale)</label>
                <input type="text" wire:model="address" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">WhatsApp (opzionale)</label>
                <input type="text" wire:model="whatsappNumber" placeholder="+39..." class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Orario check-in</label>
                <input type="time" wire:model="defaultCheckinHour" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('defaultCheckinHour') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Codice accesso (opzionale)</label>
                <input type="text" wire:model="accessCode" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Ordine visualizzazione</label>
                <input type="number" min="0" wire:model="displayOrder" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="button" wire:click="save"
                class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">
                {{ $editingId ? 'Salva modifiche' : 'Crea appartamento' }}
            </button>
            @if ($editingId)
                <button type="button" wire:click="cancelEdit"
                    class="px-5 py-2 bg-gray-100 text-gray-600 rounded-xl text-xs font-black uppercase">
                    Annulla
                </button>
            @endif
        </div>
    </section>

    <section class="space-y-3">
        @forelse ($apartments as $apartment)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-black text-gray-900">{{ $apartment->name }}</p>
                    <p class="text-xs text-gray-500">
                        sku: {{ $apartment->sku }}
                        @if ($apartment->reservations_count > 0)
                            · {{ $apartment->reservations_count }} prenotazioni collegate
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="edit({{ $apartment->id }})"
                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">Modifica</button>

                    @if ($confirmingDeleteId === $apartment->id)
                        <button type="button" wire:click="delete({{ $apartment->id }})"
                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-black">Conferma eliminazione</button>
                        <button type="button" wire:click="cancelDelete"
                            class="px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold">Annulla</button>
                    @else
                        <button type="button" wire:click="confirmDelete({{ $apartment->id }})"
                            class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold">Elimina</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-[0.2em] bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                Nessun appartamento — creane uno qui sopra.
            </div>
        @endforelse
    </section>
</div>
