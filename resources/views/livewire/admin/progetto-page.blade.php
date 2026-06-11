<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Progetto Jlune</h1>
        <p class="text-gray-500 text-sm mt-1">Guida, costi e richieste di sviluppo.</p>
    </div>

    <section class="bg-indigo-50 rounded-3xl border border-indigo-100 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-indigo-950">Google Document AI</h2>
            <p class="text-sm text-indigo-900/80 mt-1 max-w-xl">
                Scarica le istruzioni per creare il servizio sul vostro account Google e inviarci le credenziali.
            </p>
        </div>
        <a href="{{ route('admin.guide.document-ai') }}"
           class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700">
            Scarica istruzioni Document AI
        </a>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <div class="prose prose-sm prose-indigo max-w-none text-gray-700 leading-relaxed
            prose-headings:font-black prose-h1:text-2xl prose-h2:text-lg prose-h2:mt-8 prose-h2:mb-3
            prose-strong:text-gray-900 prose-table:text-sm">
            {!! \Illuminate\Support\Str::markdown($appGuide) !!}
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-4">Costi progetto</h2>
        <div class="flex flex-wrap gap-6 mb-4 text-sm">
            <div><span class="text-gray-400 text-xs font-black uppercase">Base</span><br><span class="text-xl font-black">€ {{ number_format($projectBaseCost, 2, ',', '.') }}</span></div>
            <div><span class="text-gray-400 text-xs font-black uppercase">Extra</span><br><span class="text-xl font-black">€ {{ number_format($extraSum, 2, ',', '.') }}</span></div>
            <div><span class="text-gray-400 text-xs font-black uppercase">Totale</span><br><span class="text-2xl font-black text-indigo-700">€ {{ number_format($totalCost, 2, ',', '.') }}</span></div>
        </div>
        @if (count($costEntries) > 0)
            <ul class="space-y-2 text-sm">
                @foreach ($costEntries as $entry)
                    <li class="flex justify-between bg-gray-50 rounded-xl px-4 py-2">
                        <span class="font-medium">{{ $entry['label'] }} @if(!empty($entry['date']))<span class="text-gray-400 text-xs">· {{ $entry['date'] }}</span>@endif</span>
                        <span class="font-black">€ {{ number_format((float) $entry['amount'], 2, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section id="notifiche" class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-emerald-950">Notifiche email e WhatsApp (team admin)</h2>
        <p class="text-sm text-gray-600">
            Avvisi operativi (documenti, contratti, prove TEST) verso i contatti sotto.
            Telegram e push PWA restano attivi separatamente. <strong>Di default tutto è disattivato</strong> finché non attivate e testate.
        </p>

        @if ($underConstruction)
            <div class="bg-amber-50 text-amber-900 p-3 rounded-xl text-sm border border-amber-200">
                «App in costruzione» attiva: le notifiche verso gli ospiti restano in anteprima admin, non al cliente.
            </div>
        @endif

        @if (session()->has('progetto_message'))
            <div class="bg-green-50 text-green-800 p-3 rounded-xl text-sm font-bold border border-green-200">
                {{ session('progetto_message') }}
            </div>
        @endif

        <div class="flex flex-wrap gap-4 text-sm">
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="adminNotificationsEnabled" class="rounded text-emerald-600" />
                Invio notifiche attivo
            </label>
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="adminEmailNotificationsEnabled" class="rounded text-emerald-600" />
                Email
            </label>
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="adminWhatsAppNotificationsEnabled" class="rounded text-emerald-600" />
                WhatsApp
            </label>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Email (una per riga)</label>
                <textarea wire:model="adminEmailsText" rows="4" placeholder="startupm3.5@gmail.com"
                    class="w-full rounded-xl border-gray-200 text-sm font-mono mt-1"></textarea>
                @if (count($parsedEmails))
                    <p class="text-[10px] text-gray-500 mt-1">Riconosciute: {{ implode(', ', $parsedEmails) }}</p>
                @endif
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Cellulari WhatsApp (uno per riga)</label>
                <textarea wire:model="adminPhonesText" rows="4" placeholder="+393487564418"
                    class="w-full rounded-xl border-gray-200 text-sm font-mono mt-1"></textarea>
                @if (count($parsedPhones))
                    <p class="text-[10px] text-gray-500 mt-1">Riconosciuti: {{ implode(', ', $parsedPhones) }}</p>
                @endif
            </div>
        </div>

        <p class="text-xs text-gray-500">
            SMTP e WhatsApp API: configura in
            <a href="{{ route('admin.canali') }}" class="text-emerald-700 underline font-bold">Canali di invio</a>.
            Driver attuale: <code class="bg-gray-100 px-1 rounded">{{ $effectiveMailDriver }}</code>
            @if (! $mailSmtpReady)
                — <span class="text-amber-700">SMTP non pronto (manca password o toggle)</span>
            @endif
        </p>

        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="saveNotificationSettings"
                class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase">
                Salva notifiche
            </button>
            <button type="button" wire:click="sendTestNotification"
                class="px-5 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">
                Invia test admin
            </button>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-sky-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-sky-950">Notifiche verso gli ospiti</h2>
        <p class="text-sm text-gray-600">
            Email, WhatsApp e push verso i contatti Checkfront della prenotazione (<code>guest_email</code> / <code>guest_phone</code>).
            <strong>Restano disattivate di default</strong> per evitare invii ai clienti reali prima di aver provato l'app.
            Le prenotazioni TEST non ricevono mai email/WhatsApp reali (solo il pulsante test sotto).
        </p>

        <div class="flex flex-wrap gap-4 text-sm">
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="guestNotificationsEnabled" class="rounded text-sky-600" />
                Notifiche ospite attive
            </label>
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="guestEmailNotificationsEnabled" class="rounded text-sky-600" />
                Email
            </label>
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="guestWhatsAppNotificationsEnabled" class="rounded text-sky-600" />
                WhatsApp (log finché non c'è provider business)
            </label>
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="guestPushNotificationsEnabled" class="rounded text-sky-600" />
                Push PWA ospite
            </label>
        </div>

        <p class="text-xs text-gray-500">
            Attivare «Notifiche ospite attive» + almeno un canale. Con «App in costruzione» le anteprime restano solo agli admin.
        </p>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] font-black uppercase text-gray-400">Email per test ospite</label>
                <input type="email" wire:model="guestTestEmail" placeholder="tua-email@esempio.it"
                    class="w-full rounded-xl border-gray-200 text-sm mt-1" />
            </div>
            <button type="button" wire:click="sendGuestTestNotification"
                class="px-5 py-2 bg-sky-700 text-white rounded-xl text-xs font-black uppercase">
                Invia test ospite
            </button>
            <button type="button" wire:click="saveNotificationSettings"
                class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase">
                Salva notifiche
            </button>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-50 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-2">Notifiche sul telefono</h2>
        <p class="text-sm text-gray-600 mb-3">
            Task e avvisi operativi (documenti, contratti…): installa come app (Aggiungi a schermata Home) e attiva qui.
            Con <strong>app in costruzione</strong> attiva, le anteprime ospite arrivano solo a te/Serenella (Telegram + push), non al cliente.
        </p>
        <x-pwa-push-register channel="admin" class="mb-0" />
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-2">Task e avanzamenti</h2>
        <p class="text-sm text-gray-600 mb-4">Aggiungi una richiesta, segui le attività in corso (tue e del team).</p>
        <livewire:admin.development-tasks-board :developer-mode="false" />
    </section>
</div>
