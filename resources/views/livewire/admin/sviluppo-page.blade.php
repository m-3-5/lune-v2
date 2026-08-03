<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <div class="flex flex-wrap justify-between items-start gap-4">
        <div>
            <a href="{{ route('admin.progetto') }}" class="text-indigo-600 font-bold text-sm">← Progetto</a>
            <h1 class="text-3xl font-black text-slate-900 mt-2">Sviluppo (team)</h1>
            <p class="text-gray-500 text-sm mt-1">Contatti, costi, task.</p>
        </div>
        <button type="button" wire:click="lock" class="text-xs font-bold text-gray-400 hover:text-red-600 uppercase">Esci</button>
    </div>

    @if (session()->has('dev_message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold">{{ session('dev_message') }}</div>
    @endif

    <x-admin.desktop-only title="Sviluppo — apri da desktop">
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black mb-2">Password Sviluppo</h2>
        <p class="text-xs text-gray-500 mb-3">Serve a chiunque nel team debba entrare qui. Impostabile in produzione con la variabile <code class="bg-gray-100 px-1 rounded">JLUNE_DEV_PASSWORD</code> nel <code class="bg-gray-100 px-1 rounded">.env</code>.</p>
        <p class="font-mono text-lg font-black text-slate-900 bg-gray-50 rounded-xl px-4 py-2 inline-block">{{ $devPasswordDisplay }}</p>
    </section>

    @if ($testBookingsEnabled)
    <section class="bg-white rounded-3xl shadow-sm border-2 border-indigo-200 p-6 space-y-4 pb-8">
        <div class="flex flex-wrap justify-between gap-2 items-start">
            <div>
                <h2 class="text-lg font-black text-indigo-950">Prenotazione TEST (team)</h2>
                <p class="text-xs text-gray-600">Form completo (extra, note). Il team usa <a href="{{ route('admin.prova') }}" class="text-indigo-600 font-bold underline">Prova flusso</a>.</p>
            </div>
            <p class="text-[10px] font-black uppercase {{ \App\Support\AppSettings::testBookingsAdminEnabled() ? 'text-green-700' : 'text-gray-400' }}">
                Prova team: {{ \App\Support\AppSettings::testBookingsAdminEnabled() ? 'ON' : 'OFF' }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Appartamento</label>
                <select wire:model="testApartmentId" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                    @foreach ($apartments as $apt)
                        <option value="{{ $apt->id }}">{{ $apt->name }} ({{ $apt->sku }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2 text-sm font-bold">
                    <input type="checkbox" wire:model="testIsPaid" class="rounded border-gray-300 text-indigo-600" />
                    Già pagato (sblocca documenti)
                </label>
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
                <label class="text-[10px] font-black uppercase text-gray-400">Email</label>
                <input type="email" wire:model="testGuestEmail" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Cellulare</label>
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
                <input type="number" wire:model="testAdults" min="1" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Bambini</label>
                <input type="number" wire:model="testChildren" min="0" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
            </div>
        </div>

        @if (count($availableExtras) > 0)
            <div>
                <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Extra (come Checkfront)</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($availableExtras as $sku => $label)
                        <label class="flex items-center gap-2 text-xs font-bold bg-gray-50 px-3 py-2 rounded-xl">
                            <input type="checkbox" wire:model="testExtras" value="{{ $sku }}" class="rounded text-indigo-600" />
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <label class="text-[10px] font-black uppercase text-gray-400">Note test (opzionale)</label>
            <textarea wire:model="testNotes" rows="2" class="w-full rounded-xl border-gray-200 text-sm mt-1"
                placeholder="Es: upload CI ok…"></textarea>
        </div>

        <div class="pt-2 pb-2">
            <button type="button" wire:click="createTestReservation" wire:loading.attr="disabled"
                class="w-full py-4 rounded-2xl text-sm font-black uppercase tracking-wider shadow-lg border-0"
                style="background-color: #4f46e5; color: #ffffff;">
                <span wire:loading.remove wire:target="createTestReservation">Crea prenotazione TEST</span>
                <span wire:loading wire:target="createTestReservation">Creazione…</span>
            </button>
        </div>

        @if ($lastTestPortalUrl)
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-sm">
                <p class="font-black text-indigo-900 mb-2">Link area ospite:</p>
                <a href="{{ $lastTestPortalUrl }}" target="_blank" class="text-indigo-700 font-bold break-all underline">{{ $lastTestPortalUrl }}</a>
            </div>
        @endif

        @if ($testReservations->isNotEmpty())
            <div class="border-t border-indigo-100 pt-4">
                <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Test recenti</p>
                <ul class="space-y-2 text-xs">
                    @foreach ($testReservations as $tr)
                        <li class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 rounded-xl px-3 py-2">
                            <span>
                                <span class="font-black text-indigo-700">TEST</span>
                                {{ $tr->guestDisplayName() }} · {{ $tr->apartment->name ?? '—' }}
                            </span>
                            <span class="flex gap-2">
                                <a href="{{ $tr->guest_portal_url }}" target="_blank" class="font-bold text-indigo-600 underline">Ospite</a>
                                <a href="{{ route('admin.arrivi.show', $tr->id) }}" class="font-bold text-indigo-600 underline">Admin</a>
                                <button type="button" wire:click="deleteTestReservation({{ $tr->id }})"
                                    wire:confirm="Eliminare questa prenotazione TEST?"
                                    class="font-bold text-red-600">Elimina</button>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black mb-2">Contatti admin (anteprime)</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <textarea wire:model="adminEmailsText" rows="3" class="w-full rounded-xl border-gray-200 text-sm font-mono"></textarea>
            <textarea wire:model="adminPhonesText" rows="3" class="w-full rounded-xl border-gray-200 text-sm font-mono"></textarea>
        </div>
        <button type="button" wire:click="saveContacts" class="mt-3 px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">Salva</button>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-4">Modifica costi</h2>
        <div class="flex flex-wrap gap-4 items-end mb-4">
            <div>
                <label class="text-xs font-black uppercase text-gray-400">Base €</label>
                <input type="number" step="0.01" wire:model="projectBaseCost" class="block w-32 rounded-xl border-gray-200 font-bold" />
            </div>
            <p class="text-lg font-black text-indigo-700">Totale: € {{ number_format($totalCost, 2, ',', '.') }}</p>
        </div>
        @foreach ($costEntries as $index => $entry)
            <div class="flex justify-between text-sm bg-gray-50 rounded-xl px-3 py-2 mb-2">
                <span>{{ $entry['label'] }} — € {{ $entry['amount'] }}</span>
                <button type="button" wire:click="removeCostEntry({{ $index }})" class="text-red-600 text-xs font-bold">×</button>
            </div>
        @endforeach
        <div class="grid sm:grid-cols-3 gap-2 mt-4">
            <input wire:model="newCostLabel" placeholder="Descrizione" class="rounded-xl border-gray-200 text-sm" />
            <input type="number" wire:model="newCostAmount" placeholder="€" class="rounded-xl border-gray-200 text-sm" />
            <input type="date" wire:model="newCostDate" class="rounded-xl border-gray-200 text-sm" />
        </div>
        <div class="flex gap-2 mt-2">
            <button type="button" wire:click="addCostEntry" class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-xl text-xs font-black uppercase">+ Voce</button>
            <button type="button" wire:click="saveCosts" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">Salva costi</button>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-emerald-950">Notifiche team</h2>
        <div class="text-sm text-gray-600 space-y-2">
            <p><strong>Telegram</strong> (consigliato): BotFather → token → <code>TELEGRAM_ENABLED=true</code>, chat ID in <code>TELEGRAM_NOTIFY_CHAT_IDS</code>. Ogni persona avvia il bot con <code>/start</code>, poi <code>php artisan jlune:telegram-test</code>.</p>
            <p><strong>Web Push</strong>: <code>php artisan jlune:vapid-keys</code> → .env → <code>WEBPUSH_ENABLED=true</code> → Plesk <code>composer install</code> → PWA + «Attiva notifiche» su Progetto.</p>
        </div>
        <x-pwa-push-register channel="admin" />
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-black mb-2">Task (gestione team)</h2>
        <livewire:admin.development-tasks-board :developer-mode="true" />
    </section>
    </x-admin.desktop-only>
</div>
