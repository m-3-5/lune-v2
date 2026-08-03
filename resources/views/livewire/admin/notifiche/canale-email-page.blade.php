<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.notifiche') }}" class="text-indigo-600 font-bold text-sm">← Notifiche</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">📧 Email (SMTP)</h1>
        <p class="text-gray-500 text-sm mt-1">
            Casella di uscita per promemoria admin e ospiti. I toggle on/off restano in
            <a href="{{ route('admin.progetto') }}#notifiche" class="text-indigo-600 underline">Progetto → Notifiche</a>.
        </p>
    </div>

    <x-admin.desktop-only title="Email (SMTP) — apri da desktop">
    <x-admin.channel-flash />

    {{-- Spiegazione e costi --}}
    <section class="bg-indigo-50 rounded-3xl border border-indigo-100 p-6 space-y-3">
        <h2 class="text-lg font-black text-indigo-950">A cosa serve</h2>
        <p class="text-sm text-indigo-900/90">
            Invia email transazionali: promemoria check-in agli ospiti, avvisi al gestore (firma contratto, documenti caricati, pagamento mancante).
            Usa la casella dedicata <strong>appjlune@inm35.net</strong> — separata dalla posta personale.
        </p>
        <div class="grid sm:grid-cols-2 gap-3 text-sm">
            <div class="bg-white rounded-xl border border-indigo-100 p-3">
                <p class="font-bold text-indigo-950">Stima costi</p>
                <p class="text-indigo-800/80 mt-1 text-xs">Incluso nel hosting casella INM35. Volume atteso: poche centinaia di email/mese → costo trascurabile.</p>
            </div>
            <div class="bg-white rounded-xl border border-indigo-100 p-3">
                <p class="font-bold text-indigo-950">Stato</p>
                @if ($mailReady)
                    <p class="text-emerald-700 font-bold text-xs mt-1">✅ Pronto — driver {{ $effectiveMailDriver }}</p>
                @else
                    <p class="text-amber-700 font-bold text-xs mt-1">⬜ Non pronto — password o toggle mancante</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Configurazione --}}
    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-indigo-950">Configurazione (senza codice)</h2>
        <p class="text-sm text-gray-600">
            Server consigliato: <code class="bg-gray-100 px-1 rounded">out.postassl.it:465</code> SSL.
        </p>

        <div class="flex flex-wrap gap-4 text-sm items-center">
            <x-admin.toggle-switch wire:model="mailSmtpEnabled" label="Invio SMTP attivo" color="indigo" class="flex-1" />
            @if ($mailReady)
                <span class="text-xs font-black uppercase text-green-700 bg-green-50 px-2 py-1 rounded-lg">Pronto</span>
            @else
                <span class="text-xs font-black uppercase text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">Non pronto</span>
            @endif
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Host SMTP</label>
                <input type="text" wire:model="mailHost" class="w-full rounded-xl border-gray-200 mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Porta</label>
                <input type="number" wire:model="mailPort" class="w-full rounded-xl border-gray-200 mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Crittografia</label>
                <select wire:model="mailEncryption" class="w-full rounded-xl border-gray-200 mt-1">
                    <option value="ssl">SSL (465)</option>
                    <option value="tls">TLS (587)</option>
                    <option value="none">Nessuna</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Utente</label>
                <input type="email" wire:model="mailUsername" class="w-full rounded-xl border-gray-200 mt-1" />
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-black uppercase text-gray-400">
                    Password
                    @if ($mailPasswordSet)<span class="text-green-600">(salvata)</span>@endif
                </label>
                <input type="password" wire:model="mailPassword" autocomplete="new-password"
                    placeholder="{{ $mailPasswordSet ? '••••••••' : 'Password casella' }}"
                    class="w-full rounded-xl border-gray-200 mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Mittente (From)</label>
                <input type="email" wire:model="mailFromAddress" class="w-full rounded-xl border-gray-200 mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Nome mittente</label>
                <input type="text" wire:model="mailFromName" class="w-full rounded-xl border-gray-200 mt-1" />
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-gray-100">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[10px] font-black uppercase text-gray-400">Email test</label>
                <input type="email" wire:model="testEmail" class="w-full rounded-xl border-gray-200 text-sm mt-1" />
            </div>
            <button type="button" wire:click="saveMailSettings" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">Salva</button>
            <button type="button" wire:click="sendTestEmail" class="px-5 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">Test email</button>
        </div>
    </section>

    <section class="bg-gray-50 rounded-2xl border border-gray-200 p-4 text-sm text-gray-700">
        <p class="font-bold mb-1">Prossimo passo</p>
        <p>Attiva «Email» in <a href="{{ route('admin.progetto') }}#notifiche" class="text-indigo-600 underline font-bold">Progetto → Notifiche</a> (sezione admin e/o ospiti).</p>
    </section>
    </x-admin.desktop-only>
</div>
