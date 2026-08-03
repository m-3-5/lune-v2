<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.progetto') }}" class="text-indigo-600 font-bold text-sm">← Progetto</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Canali di invio</h1>
        <p class="text-gray-500 text-sm mt-1">
            Configura SMTP email e WhatsApp (Twilio). I destinatari e i toggle on/off restano in
            <a href="{{ route('admin.progetto') }}" class="text-indigo-600 underline">Progetto → Notifiche</a>.
        </p>
    </div>

    @if (session()->has('canali_message'))
        <div class="bg-green-50 text-green-800 p-3 rounded-xl text-sm font-bold border border-green-200">
            {{ session('canali_message') }}
        </div>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-indigo-950">Email SMTP (uscita)</h2>
        <p class="text-sm text-gray-600">
            Casella <strong>appjlune@inm35.net</strong> su <code class="bg-gray-100 px-1 rounded">out.postassl.it:465</code> SSL.
        </p>

        <div class="flex flex-wrap gap-4 text-sm items-center">
            <label class="flex items-center gap-2 font-bold">
                <input type="checkbox" wire:model="mailSmtpEnabled" class="rounded text-indigo-600" />
                Invio SMTP attivo
            </label>
            @if ($mailReady)
                <span class="text-xs font-black uppercase text-green-700 bg-green-50 px-2 py-1 rounded-lg">Pronto</span>
            @else
                <span class="text-xs font-black uppercase text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">Non pronto</span>
            @endif
            <span class="text-xs text-gray-500">Driver: <code>{{ $effectiveMailDriver }}</code></span>
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
            <button type="button" wire:click="saveMailSettings" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">Salva email</button>
            <button type="button" wire:click="sendTestEmail" class="px-5 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">Test email</button>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-lg font-black text-emerald-950">WhatsApp</h2>
            @if ($whatsappChannelReady)
                <span class="text-xs font-black uppercase text-green-700 bg-green-50 px-2 py-1 rounded-lg">Provider pronto</span>
            @else
                <span class="text-xs font-black uppercase text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">Provider non pronto</span>
            @endif
        </div>

        <p class="text-sm text-gray-600">
            Consigliato: <strong>Twilio</strong> (sandbox per test, account del gestore in produzione).
            CallMeBot resta disponibile come alternativa leggera.
        </p>

        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div class="md:col-span-2">
                <label class="text-[10px] font-black uppercase text-gray-400">Provider</label>
                <select wire:model.live="whatsappProvider" class="w-full rounded-xl border-gray-200 mt-1">
                    <option value="log">Solo log (sicuro, default)</option>
                    <option value="twilio">Twilio WhatsApp API</option>
                    <option value="callmebot">CallMeBot (legacy)</option>
                </select>
            </div>

            @if ($whatsappProvider === 'twilio')
                <div class="md:col-span-2 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-sm text-emerald-950 space-y-2">
                    <p class="font-bold">Setup Twilio (sandbox — test)</p>
                    <ol class="list-decimal list-inside space-y-1 text-emerald-900/90">
                        <li>Crea account su <a href="https://www.twilio.com/try-twilio" target="_blank" rel="noopener" class="underline">twilio.com</a></li>
                        <li>Console → <strong>Account Info</strong>: copia Account SID e Auth Token</li>
                        <li>Console → <strong>Messaging → Try WhatsApp</strong> (Sandbox)</li>
                        <li>Da WhatsApp invia al numero sandbox il messaggio <code class="bg-white px-1 rounded">join …</code> indicato in console</li>
                        <li>Numero From sandbox: di solito <code class="bg-white px-1 rounded">+14155238886</code></li>
                        <li>Salva qui → test al tuo <code>+393487564418</code></li>
                    </ol>
                    <p class="text-xs text-emerald-800">Alla pubblicazione: account Twilio del gestore + numero WhatsApp Business approvato — cambi solo SID, token e From qui.</p>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Account SID</label>
                    <input type="text" wire:model="twilioAccountSid" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                        class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase text-gray-400">Numero From (WhatsApp)</label>
                    <input type="text" wire:model="twilioWhatsAppFrom" placeholder="+14155238886"
                        class="w-full rounded-xl border-gray-200 font-mono text-sm mt-1" />
                    <p class="text-[10px] text-gray-500 mt-1">Sandbox Twilio o numero business approvato</p>
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
            @endif

            @if ($whatsappProvider === 'callmebot')
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black uppercase text-gray-400">Apikey CallMeBot (una per riga)</label>
                    <textarea wire:model="whatsappCallMeBotKeysText" rows="3" placeholder="5458750"
                        class="w-full rounded-xl border-gray-200 text-sm font-mono mt-1"></textarea>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-gray-100">
            @if ($whatsappProvider === 'twilio')
                <div class="flex-1 min-w-[200px]">
                    <label class="text-[10px] font-black uppercase text-gray-400">Cellulare test</label>
                    <input type="text" wire:model="testWhatsAppPhone" placeholder="+393487564418"
                        class="w-full rounded-xl border-gray-200 text-sm mt-1" />
                </div>
            @endif
            <button type="button" wire:click="saveWhatsAppSettings" class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase">Salva WhatsApp</button>
            <button type="button" wire:click="sendTestWhatsApp" class="px-5 py-2 bg-slate-800 text-white rounded-xl text-xs font-black uppercase">Test WhatsApp</button>
        </div>
    </section>

    <section class="bg-indigo-50 rounded-2xl border border-indigo-100 p-4 text-sm text-indigo-900">
        <p class="font-bold mb-1">Ordine consigliato</p>
        <ol class="list-decimal list-inside space-y-1 text-indigo-800/90">
            <li>Email: password → Salva → Test email</li>
            <li>Twilio: join sandbox → SID + token + From → Salva → Test WhatsApp</li>
            <li><a href="{{ route('admin.progetto') }}" class="underline font-bold">Progetto</a>: attiva toggle notifiche admin/ospite</li>
        </ol>
    </section>
</div>
