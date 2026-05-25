<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <div class="flex flex-wrap justify-between items-start gap-4">
        <div>
            <a href="{{ route('admin.progetto') }}" class="text-indigo-600 font-bold text-sm">← Progetto</a>
            <h1 class="text-3xl font-black text-slate-900 mt-2">Sviluppo (team)</h1>
            <p class="text-gray-500 text-sm mt-1">Costruzione, contatti, guida modificabile, costi.</p>
        </div>
        <button type="button" wire:click="lock" class="text-xs font-bold text-gray-400 hover:text-red-600 uppercase">Esci</button>
    </div>

    @if (session()->has('dev_message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold">{{ session('dev_message') }}</div>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-amber-100 p-6">
        <h2 class="text-lg font-black mb-2">App in costruzione</h2>
        <button type="button" wire:click="toggleConstruction"
            class="px-6 py-3 rounded-2xl font-black text-sm uppercase {{ $underConstruction ? 'bg-amber-500 text-amber-950' : 'bg-indigo-600 text-white' }}">
            {{ $underConstruction ? 'Disattiva' : 'Attiva' }}
        </button>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black mb-2">Contatti admin (anteprime)</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <textarea wire:model="adminEmailsText" rows="3" class="w-full rounded-xl border-gray-200 text-sm font-mono"></textarea>
            <textarea wire:model="adminPhonesText" rows="3" class="w-full rounded-xl border-gray-200 text-sm font-mono"></textarea>
        </div>
        <button type="button" wire:click="saveContacts" class="mt-3 px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">Salva</button>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black mb-2">Guida (testo in Progetto)</h2>
        <textarea wire:model="appGuide" rows="14" class="w-full rounded-xl border-gray-200 text-sm font-mono"></textarea>
        <button type="button" wire:click="saveGuide" class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">Salva guida</button>
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
        <h2 class="text-lg font-black text-emerald-950">Notifiche Serenella ↔ Team</h2>
        <div class="text-sm text-gray-600 space-y-2">
            <p><strong>Telegram</strong> (consigliato): BotFather → token → <code>TELEGRAM_ENABLED=true</code>, chat ID in <code>TELEGRAM_NOTIFY_CHAT_IDS</code>. Ogni persona avvia il bot con <code>/start</code>, poi leggi gli ID con <code>getUpdates</code> o <code>php artisan jlune:telegram-test</code>.</p>
            <p><strong>Web Push</strong>: <code>php artisan jlune:vapid-keys</code> → incolla nel .env → <code>WEBPUSH_ENABLED=true</code> → su Plesk <code>composer install</code> → ogni telefono: installa PWA + «Attiva notifiche» su Progetto.</p>
        </div>
        <x-pwa-push-register channel="admin" />
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-emerald-950">Notifiche Serenella ↔ Team</h2>
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
</div>
