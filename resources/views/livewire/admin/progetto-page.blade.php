<div class="max-w-4xl mx-auto space-y-8 pb-16" x-on:guide-ticket-created.window="document.getElementById('task-board')?.scrollIntoView({ behavior: 'smooth', block: 'start' })">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Progetto</h1>
        <p class="text-gray-500 text-sm mt-1">Nome, contatti e notifiche.</p>
    </div>

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6 space-y-3">
        <h2 class="text-lg font-black text-indigo-950">Nome</h2>
        <p class="text-sm text-gray-600">
            Compare in titoli, notifiche, PWA e email in tutta l'app — cambialo una volta sola qui.
        </p>
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model="appName" placeholder="Es. Appartamenti Rossi"
                class="flex-1 min-w-[200px] rounded-xl border-gray-200 text-sm">
            <button type="button" wire:click="saveAppName"
                class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">
                Salva nome
            </button>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-3">
        <h2 class="text-lg font-black text-gray-900">Il tuo account</h2>
        <p class="text-sm text-gray-600">Accesso: <strong>{{ auth()->user()->email }}</strong></p>

        @error('currentPassword')
            <p class="text-red-600 text-xs font-bold">{{ $message }}</p>
        @enderror
        @error('newPassword')
            <p class="text-red-600 text-xs font-bold">{{ $message }}</p>
        @enderror

        <div class="grid sm:grid-cols-3 gap-2">
            <input type="password" wire:model="currentPassword" placeholder="Password attuale"
                class="rounded-xl border-gray-200 text-sm">
            <input type="password" wire:model="newPassword" placeholder="Nuova password"
                class="rounded-xl border-gray-200 text-sm">
            <input type="password" wire:model="newPassword_confirmation" placeholder="Conferma nuova password"
                class="rounded-xl border-gray-200 text-sm">
        </div>
        <button type="button" wire:click="changePassword"
            class="px-5 py-2 bg-gray-800 text-white rounded-xl text-xs font-black uppercase">
            Cambia password
        </button>
    </section>

    <section id="notifiche" class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-emerald-950">Notifiche email e WhatsApp (team admin)</h2>
        <p class="text-sm text-gray-600">
            Avvisi operativi (documenti, contratti, prove TEST) verso i contatti sotto.
            Telegram e push PWA restano attivi separatamente. <strong>Di default tutto è disattivato</strong> finché non attivate e testate.
        </p>

        @if (session()->has('progetto_message'))
            <div class="bg-green-50 text-green-800 p-3 rounded-xl text-sm font-bold border border-green-200">
                {{ session('progetto_message') }}
            </div>
        @endif

        <div class="space-y-1 divide-y divide-gray-100">
            <x-admin.toggle-switch wire:model="adminNotificationsEnabled" label="Invio notifiche attivo" color="emerald" />
            <x-admin.toggle-switch wire:model="adminEmailNotificationsEnabled" label="Email" color="emerald" />
            <x-admin.toggle-switch wire:model="adminWhatsAppNotificationsEnabled" label="WhatsApp" color="emerald" />
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
            SMTP e WhatsApp: configura in
            <a href="{{ route('admin.notifiche.whatsapp') }}" class="text-emerald-700 underline font-bold">Notifiche → WhatsApp</a>.
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

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-indigo-950">Dati locatore (contratti)</h2>
        <p class="text-sm text-gray-600">
            Questi dati compaiono come "Locatore" nei contratti di locazione generati per gli ospiti (PDF e pagina firma).
        </p>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Nome / ragione sociale</label>
                <input type="text" wire:model="landlordName" placeholder="Nome Cognome o Struttura Srl"
                    class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Indirizzo</label>
                <input type="text" wire:model="landlordAddress" placeholder="Via Esempio 1 - Città (Italia)"
                    class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Email</label>
                <input type="email" wire:model="landlordEmail" placeholder="info@tuastruttura.it"
                    class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Telefono</label>
                <input type="text" wire:model="landlordPhone" placeholder="+39 …"
                    class="w-full rounded-xl border-gray-200 text-sm mt-1">
            </div>
        </div>

        <button type="button" wire:click="saveLandlordDetails"
            class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">
            Salva dati locatore
        </button>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-sky-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-sky-950">Notifiche verso gli ospiti</h2>
        <p class="text-sm text-gray-600">
            Email, WhatsApp, <strong>Telegram</strong> e push verso i contatti Checkfront (<code>guest_email</code> / <code>guest_phone</code>) o Telegram collegato dal portale ospite.
            <strong>Restano disattivate di default</strong> per evitare invii ai clienti reali prima di aver provato l'app.
            Le prenotazioni TEST non ricevono mai email/WhatsApp reali (solo il pulsante test sotto).
        </p>

        <div class="space-y-1 divide-y divide-gray-100">
            <x-admin.toggle-switch wire:model="guestNotificationsEnabled" label="Notifiche ospite attive" color="sky" />
            <x-admin.toggle-switch wire:model="guestEmailNotificationsEnabled" label="Email" color="sky" />
            <x-admin.toggle-switch wire:model="guestWhatsAppNotificationsEnabled" label="WhatsApp" hint="Configura account Business in Notifiche → WhatsApp" color="sky" />
            <x-admin.toggle-switch wire:model="guestTelegramNotificationsEnabled" label="Telegram" hint="Ospite collega dal check-in" color="sky" />
            <x-admin.toggle-switch wire:model="guestPushNotificationsEnabled" label="Push PWA ospite" color="sky" />
        </div>

        <p class="text-xs text-gray-500">
            Attivare «Notifiche ospite attive» + almeno un canale.
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
        </p>
        <x-pwa-push-register channel="admin" class="mb-0" />
    </section>

    <section id="task-board" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-2">Task e avanzamenti</h2>
        <p class="text-sm text-gray-600 mb-4">Le domande dalla guida compaiono qui. Aggiungi richieste e segui le risposte del team.</p>
        <livewire:admin.development-tasks-board :developer-mode="false" />
    </section>
</div>
