<div class="max-w-2xl mx-auto space-y-6 pb-16">
    <div>
        <a href="{{ route('admin.arrivi') }}" class="text-indigo-600 font-bold text-sm">← Arrivi e documenti</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Nuova prenotazione</h1>
        <p class="text-gray-500 text-sm mt-1">Per prenotazioni prese a mano (telefono, di persona) — non collegate a Checkfront.</p>
    </div>

    @if (session()->has('manual_message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold">{{ session('manual_message') }}</div>
    @endif

    @if ($lastPortalUrl)
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4">
            <p class="text-xs font-black uppercase text-indigo-400 mb-1">Link ospite (check-in)</p>
            <a href="{{ $lastPortalUrl }}" target="_blank" class="text-indigo-700 font-bold text-sm break-all underline">{{ $lastPortalUrl }}</a>
        </div>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div>
            <label class="text-[10px] font-black uppercase text-gray-400">Appartamento</label>
            <select wire:model="apartmentId" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @foreach ($apartments as $apartment)
                    <option value="{{ $apartment->id }}">{{ $apartment->name }}</option>
                @endforeach
            </select>
            @error('apartmentId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Nome ospite</label>
                <input type="text" wire:model="guestName" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('guestName') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Cognome</label>
                <input type="text" wire:model="guestCognome" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Email (opzionale)</label>
                <input type="email" wire:model="guestEmail" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Telefono (opzionale)</label>
                <input type="text" wire:model="guestPhone" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Check-in</label>
                <input type="date" wire:model="checkIn" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('checkIn') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Check-out</label>
                <input type="date" wire:model="checkOut" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('checkOut') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Adulti</label>
                <input type="number" min="1" wire:model="adults" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Bambini</label>
                <input type="number" min="0" wire:model="children" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Prezzo totale (€)</label>
                <input type="number" step="0.01" min="0" wire:model="totalPrice" placeholder="100" class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div class="flex items-center gap-2 mt-5">
                <input type="checkbox" wire:model="isPaid" id="isPaid" class="rounded border-gray-300">
                <label for="isPaid" class="text-sm font-bold text-gray-700">Già pagata</label>
            </div>
        </div>

        <div>
            <label class="text-[10px] font-black uppercase text-gray-400">Note interne (opzionale)</label>
            <textarea wire:model="notes" rows="2" class="w-full rounded-xl border-gray-200 text-sm mt-1"></textarea>
        </div>

        <button type="button" wire:click="create"
            class="w-full py-3 bg-indigo-600 text-white font-black rounded-2xl text-sm uppercase">
            Crea prenotazione
        </button>
    </section>
</div>
