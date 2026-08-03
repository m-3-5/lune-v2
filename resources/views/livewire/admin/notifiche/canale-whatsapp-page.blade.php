<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.notifiche') }}" class="text-indigo-600 font-bold text-sm">← Notifiche</a>
        <h1 class="text-3xl font-black text-emerald-950 mt-2">💬 WhatsApp</h1>
        <p class="text-gray-500 text-sm mt-1">
            Account Twilio ufficiale + canale Meta. Configura, accendi/spendi e testa tutto da qui — senza codice.
        </p>
    </div>

    <x-admin.desktop-only title="WhatsApp — apri da desktop">
    <x-admin.channel-flash />

    {{-- Interruttori principali --}}
    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-1 divide-y divide-gray-100">
        <h2 class="text-lg font-black text-emerald-950 mb-3 pb-0">Accendi / spegni</h2>

        <x-admin.toggle-switch
            wire:model.live="whatsappTwilioEnabled"
            label="Canale Twilio attivo"
            hint="Spento = messaggi solo nel log (sicuro). Acceso = invio reale via API."
            color="emerald"
        />

        <x-admin.toggle-switch
            wire:model.live="twilioBusinessMode"
            label="Account ufficiale Business (Meta)"
            hint="Spento = Sandbox Twilio (solo test con join). Acceso = numero WhatsApp Business collegato a Meta."
            color="emerald"
        />

        <x-admin.toggle-switch
            wire:model.live="adminNotificationsEnabled"
            label="Notifiche admin (generale)"
            hint="Master switch avvisi al team."
            color="emerald"
        />

        <x-admin.toggle-switch
            wire:model.live="adminWhatsAppNotificationsEnabled"
            label="WhatsApp verso admin"
            hint="Team — usa i cellulari sotto."
            color="emerald"
        />

        <x-admin.toggle-switch
            wire:model.live="guestNotificationsEnabled"
            label="Notifiche ospiti (generale)"
            hint="Master switch promemoria check-in."
            color="sky"
        />

        <x-admin.toggle-switch
            wire:model.live="guestWhatsAppNotificationsEnabled"
            label="WhatsApp verso ospiti"
            hint="Cellulare da anagrafica Checkfront. Richiede Business + template Meta per invii automatici."
            color="sky"
        />
    </section>

    @if ($underConstruction)
        <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 text-sm">
            <strong>Work in progress attivo.</strong> Gli ospiti reali non ricevono WhatsApp finché non spegni WIP o usi «Prova notifiche» su una prenotazione.
        </div>
    @endif

    {{-- Setup Business Meta + Twilio --}}
    @if ($twilioBusinessMode)
        <section class="bg-emerald-50 rounded-3xl border border-emerald-200 p-6 space-y-3">
            <h2 class="text-lg font-black text-emerald-950">Setup account ufficiale (Meta + Twilio)</h2>
            <p class="text-sm text-emerald-900/90">Segui questi passi una volta sola. Poi cambi solo i campi sotto se passi a un altro account.</p>
            <ol class="list-decimal list-inside space-y-2 text-sm text-emerald-900/90">
                <li><strong>Meta Business Suite</strong> → WhatsApp Manager → collega il numero ufficiale del canale.</li>
                <li><strong>Twilio Console</strong> → Messaging → WhatsApp Senders → «Connect with Facebook» e completa l’onboarding Meta.</li>
                <li>Attendi approvazione del numero (può richiedere 1–3 giorni).</li>
                <li>In Twilio copia il numero approvato (formato <code class="bg-white px-1 rounded">+39…</code>) nel campo <strong>Numero From</strong> sotto.</li>
                <li>Per messaggi automatici agli ospiti: crea un <strong>Content Template</strong> in Twilio → approvazione Meta → incolla il Content SID.</li>
                <li><strong>Test interno:</strong> da WhatsApp invia un messaggio al numero business, poi usa «Test admin» — oppure usa un template approvato.</li>
            </ol>
            <p class="text-xs text-emerald-800/80">
                Stima costi: ~0,05–0,10 € a messaggio · volume previsto ~5–15 €/mese in alta stagione.
            </p>
        </section>
    @else
        <section class="bg-gray-50 rounded-3xl border border-gray-200 p-6 space-y-2">
            <h2 class="text-lg font-black text-gray-900">Modalità Sandbox (solo test)</h2>
            <p class="text-sm text-gray-600">Utile per prove rapide. Ogni numero deve inviare <code class="bg-white px-1 rounded">join …</code> al sandbox Twilio. Non adatto agli ospiti reali.</p>
        </section>
    @endif

    {{-- Credenziali API --}}
    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-black text-emerald-950">Credenziali Twilio</h2>
            @if ($whatsappChannelReady)
                <span class="text-xs font-black uppercase text-green-700 bg-green-50 px-2 py-1 rounded-lg">Pronto</span>
            @else
                <span class="text-xs font-black uppercase text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">Incomplete</span>
            @endif
            <span class="text-xs text-gray-500">Modalità: <strong>{{ $twilioMode === 'business' ? 'Business ufficiale' : 'Sandbox' }}</strong></span>
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Account SID</label>
                <input type="text" wire:model="twilioAccountSid" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                    class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Numero From (WhatsApp)</label>
                <input type="text" wire:model="twilioWhatsAppFrom"
                    placeholder="{{ $twilioBusinessMode ? '+39xxxxxxxxxx (numero business approvato)' : '+14155238886 (sandbox)' }}"
                    class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
            </div>
            <div class="md:col-span-2">
                <label class="text-[10px] font-black uppercase text-gray-400">
                    Auth Token
                    @if ($twilioAuthTokenSet)<span class="text-green-600">(salvato)</span>@endif
                </label>
                <input type="password" wire:model="twilioAuthToken" autocomplete="new-password"
                    placeholder="{{ $twilioAuthTokenSet ? '••••••••' : 'Auth Token Twilio' }}"
                    class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
            </div>
            @if ($twilioBusinessMode)
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Meta Business ID (riferimento)</label>
                    <input type="text" wire:model="twilioMetaBusinessId" placeholder="Opzionale — per documentazione interna"
                        class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Content Template SID</label>
                    <input type="text" wire:model="twilioContentTemplateSid" placeholder="HX… (template Meta approvato)"
                        class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
                    <p class="text-[10px] text-gray-500 mt-1">Per promemoria automatici ospiti (prossimo passo codice).</p>
                </div>
            @endif
        </div>

        <div class="border-t border-gray-100 pt-4">
            <label class="text-[10px] font-black uppercase text-gray-400">Cellulari admin WhatsApp (uno per riga)</label>
            <textarea wire:model="adminPhonesText" rows="3" placeholder="+393487564418"
                class="w-full rounded-xl border-gray-200 text-sm font-mono mt-1"></textarea>
            @if (count($parsedAdminPhones))
                <p class="text-[10px] text-gray-500 mt-1">Attivi: {{ implode(', ', $parsedAdminPhones) }}</p>
            @endif
        </div>

        <button type="button" wire:click="saveWhatsAppSettings"
            class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase">
            Salva credenziali e numeri
        </button>
    </section>

    {{-- Test --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-gray-900">Test interno</h2>
        <p class="text-sm text-gray-600">
            @if ($twilioBusinessMode)
                Con account Business: preferisci scrivere prima al numero ufficiale da WhatsApp, poi «Test admin». Per numeri mai contattati serve un template approvato.
            @else
                Sandbox: il numero di test deve aver fatto <code class="bg-gray-100 px-1 rounded">join</code> al sandbox Twilio.
            @endif
        </p>

        @if ($whatsappTwilioEnabled)
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Test admin (tuo numero)</label>
                    <input type="text" wire:model="testWhatsAppPhone" placeholder="+39..."
                        class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                    <button type="button" wire:click="sendTestWhatsApp"
                        class="mt-2 px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">
                        Invia test admin
                    </button>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Test ospite (simulazione)</label>
                    <input type="text" wire:model="testGuestWhatsAppPhone" placeholder="+39..."
                        class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                    <button type="button" wire:click="sendTestGuestWhatsApp"
                        class="mt-2 px-4 py-2 bg-sky-700 text-white rounded-xl text-xs font-black uppercase">
                        Invia test ospite
                    </button>
                </div>
            </div>
        @else
            <p class="text-sm text-amber-700">Accendi «Canale Twilio attivo» per inviare test reali.</p>
        @endif
    </section>
    </x-admin.desktop-only>
</div>
