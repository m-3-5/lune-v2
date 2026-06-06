<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Prova flusso</h1>
        <p class="text-gray-500 text-sm mt-1 max-w-xl">
            Simula un ospite senza Checkfront: link personale, documenti, contratto.
            Le prove hanno etichetta <strong>TEST</strong> e non sono prenotazioni reali.
        </p>
    </div>

    @if (session()->has('prova_error'))
        <div class="bg-red-50 text-red-800 p-4 rounded-2xl text-sm font-bold border border-red-200">
            {{ session('prova_error') }}
        </div>
    @endif

    @if (session()->has('prova_message'))
        <div class="bg-green-50 text-green-800 p-4 rounded-2xl text-sm font-bold border border-green-200">
            {{ session('prova_message') }}
        </div>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-amber-100 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-gray-900">Creazione prove</h2>
            <p class="text-xs text-gray-600 mt-1">
                Attiva solo quando provi. Disattiva in produzione quando non serve.
            </p>
        </div>
        <button type="button" wire:click="toggleEnabled"
            class="px-6 py-3 rounded-2xl font-black text-sm uppercase {{ $enabled ? 'bg-amber-500 text-amber-950' : 'bg-indigo-600 text-white' }}">
            {{ $enabled ? 'Disattiva prove' : 'Attiva prove' }}
        </button>
    </section>

    @if ($enabled)
        <section class="bg-white rounded-3xl shadow-sm border-2 border-indigo-200 p-6 space-y-4">
            <h2 class="text-lg font-black text-indigo-950">Nuova prova</h2>

            <div class="grid sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black uppercase text-gray-400">Appartamento</label>
                    <select wire:model="testApartmentId" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                        @foreach ($apartments as $apt)
                            <option value="{{ $apt->id }}">{{ $apt->name }} ({{ $apt->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Nome</label>
                    <input wire:model="testGuestName" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Cognome</label>
                    <input wire:model="testGuestCognome" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Email (opz.)</label>
                    <input type="email" wire:model="testGuestEmail" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Cellulare (opz.)</label>
                    <input wire:model="testGuestPhone" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Check-in</label>
                    <input type="date" wire:model="testCheckIn" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Check-out</label>
                    <input type="date" wire:model="testCheckOut" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Adulti</label>
                    <input type="number" wire:model="testAdults" min="1" max="20" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 text-sm font-bold">
                        <input type="checkbox" wire:model="testIsPaid" class="rounded border-gray-300 text-indigo-600" />
                        Già pagato (sblocca documenti)
                    </label>
                </div>
            </div>

            <button type="button" wire:click="createTestReservation" wire:loading.attr="disabled"
                class="w-full py-4 rounded-2xl text-sm font-black uppercase tracking-wider shadow-lg border-0"
                style="background-color: #4f46e5; color: #ffffff;">
                <span wire:loading.remove wire:target="createTestReservation">Crea prenotazione di prova</span>
                <span wire:loading wire:target="createTestReservation">Creazione…</span>
            </button>

            @if ($lastTestPortalUrl)
                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-sm">
                    <p class="font-black text-indigo-900 mb-2">Link area ospite (apri come cliente):</p>
                    <a href="{{ $lastTestPortalUrl }}" target="_blank" rel="noopener"
                       class="text-indigo-700 font-bold break-all underline">{{ $lastTestPortalUrl }}</a>
                </div>
            @endif
        </section>
    @else
        <section class="bg-gray-50 rounded-3xl border border-gray-200 p-8 text-center text-sm text-gray-600">
            <p class="font-bold text-gray-800 mb-2">Prove disattivate</p>
            <p>Attiva l'interruttore sopra per creare link di prova. In agenda le prove compaiono con badge <span class="font-black text-indigo-700">TEST</span>.</p>
        </section>
    @endif

    @if ($testReservations->isNotEmpty())
        <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-black text-gray-900 mb-4">Prove recenti</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($testReservations as $tr)
                    <li class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 rounded-xl px-4 py-3">
                        <span>
                            <span class="font-black text-indigo-700 text-xs uppercase">TEST</span>
                            {{ $tr->guestDisplayName() }}
                            · {{ $tr->apartment->name ?? '—' }}
                            · {{ $tr->check_in?->format('d/m') }}–{{ $tr->check_out?->format('d/m') }}
                        </span>
                        <span class="flex flex-wrap gap-3 text-xs font-bold">
                            <a href="{{ $tr->guest_portal_url }}" target="_blank" rel="noopener" class="text-indigo-600 underline">Ospite</a>
                            <a href="{{ route('admin.arrivi.show', $tr->id) }}" class="text-indigo-600 underline">Admin</a>
                            @if ($enabled)
                                <button type="button" wire:click="deleteTestReservation({{ $tr->id }})"
                                    wire:confirm="Eliminare questa prova?"
                                    class="text-red-600">Elimina</button>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="bg-slate-50 rounded-2xl border border-slate-200 p-4 text-xs text-slate-600">
        <p class="font-black uppercase text-slate-500 mb-1">Come usarla</p>
        <ol class="list-decimal pl-4 space-y-1">
            <li>Attiva le prove → compila → <strong>Crea prenotazione di prova</strong></li>
            <li>Apri il link <strong>Ospite</strong> (meglio da telefono o finestra privata)</li>
            <li>In admin: <strong>Arrivi e documenti</strong> → approva → estrai dati → contratto</li>
            <li>Quando hai finito, elimina la prova e disattiva l'interruttore</li>
        </ol>
    </section>
</div>
