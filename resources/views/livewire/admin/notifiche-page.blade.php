<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">🔔 Notifiche</h1>
        <p class="text-gray-500 text-sm mt-1">
            Panoramica e hub dei canali. Ogni canale ha una pagina dedicata con spiegazione, stima costi e configurazione senza codice.
        </p>
    </div>

    {{-- Canali dedicati --}}
    <section class="grid sm:grid-cols-2 gap-4">
        @foreach ([
            ['route' => 'admin.notifiche.email', 'icon' => '📧', 'title' => 'Email', 'ready' => $mailReady, 'hint' => 'SMTP appjlune@inm35.net'],
            ['route' => 'admin.notifiche.whatsapp', 'icon' => '💬', 'title' => 'WhatsApp', 'ready' => $whatsappReady, 'hint' => 'Twilio — admin e ospiti'],
            ['route' => 'admin.notifiche.telegram', 'icon' => '✈️', 'title' => 'Telegram', 'ready' => $telegramBotReady, 'hint' => 'Bot @'.$telegramBotUsername],
            ['route' => 'admin.notifiche.push', 'icon' => '📲', 'title' => 'Web Push', 'ready' => config('webpush.enabled') && filled(config('webpush.vapid.public_key')), 'hint' => 'PWA installata'],
        ] as $ch)
            @if ($isSuperAdmin)
                <a href="{{ route($ch['route']) }}"
                   class="block bg-white rounded-2xl border p-5 shadow-sm hover:border-indigo-300 hover:shadow-md transition {{ $ch['ready'] ? 'border-emerald-200' : 'border-gray-200' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $ch['icon'] }}</span>
                        <div>
                            <p class="font-black text-indigo-950">{{ $ch['title'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $ch['hint'] }}</p>
                        </div>
                        <span class="ml-auto text-lg">{{ $ch['ready'] ? '✅' : '⬜' }}</span>
                    </div>
                    <p class="text-[10px] font-black uppercase text-indigo-600 mt-3">Configura →</p>
                </a>
            @else
                <div class="block bg-gray-50 rounded-2xl border border-gray-200 p-5 opacity-60">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $ch['icon'] }}</span>
                        <div>
                            <p class="font-black text-gray-500">{{ $ch['title'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $ch['hint'] }}</p>
                        </div>
                        <span class="ml-auto text-lg">{{ $ch['ready'] ? '✅' : '⬜' }}</span>
                    </div>
                    <p class="text-[10px] font-black uppercase text-gray-400 mt-3">🔒 Riservato al super admin</p>
                </div>
            @endif
        @endforeach
    </section>

    {{-- Stato a colpo d'occhio --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-4">Stato attuale</h2>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ([
                ['Work in progress', $underConstruction, $underConstruction ? 'Gli ospiti reali NON ricevono email/WhatsApp' : 'Modalità live per gli ospiti (se i toggle sono ON)'],
                ['Notifiche admin', $adminOn, $adminCanReceive ? 'Invio effettivo possibile' : 'Spento o canali non pronti'],
                ['Notifiche ospiti', $guestOn, $guestCanReceive ? 'Gli ospiti possono ricevere messaggi' : ($underConstruction ? 'Bloccate da work in progress' : 'Spento o canali non pronti')],
                ['Email SMTP', $mailReady, $mailReady ? 'Pronta per inviare' : 'Configura in Notifiche → Email'],
                ['WhatsApp', $whatsappReady, $whatsappReady ? ucfirst($whatsappProvider).' attivo' : 'Provider: '.$whatsappProvider],
                ['Telegram bot', $telegramBotReady, $telegramBotReady ? '@'.$telegramBotUsername.' attivo' : 'Configura TELEGRAM_BOT_TOKEN nel .env'],
                ['Telegram ospiti', $guestTelegramOn && $telegramBotReady, $guestTelegramOn ? $telegramLinkedCount.' prenotazioni collegate' : 'Toggle spento in Progetto'],
                ['Prova notifiche', $pilotCount > 0, $pilotCount > 0 ? $pilotCount.' prenotazione/i in test' : 'Nessuna prenotazione pilota'],
            ] as [$label, $ok, $hint])
                <div class="rounded-xl border p-3 {{ $ok ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $ok ? '✅' : '⬜' }}</span>
                        <span class="font-bold text-sm text-gray-900">{{ $label }}</span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1 ml-7">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        @if ($underConstruction)
            <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 text-sm">
                <strong>App in costruzione attiva.</strong> I promemoria automatici (cron ore 10:00) girano, ma verso gli ospiti reali restano in <strong>anteprima admin</strong>.
                Per testare su una prenotazione vera: apri il dettaglio arrivo e attiva <strong>«Prova notifiche»</strong>.
            </div>
        @endif
    </section>

    {{-- 3 passi --}}
    <section class="bg-indigo-50 rounded-3xl border border-indigo-100 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-4">Come attivare le notifiche (3 passi)</h2>
        <ol class="space-y-4 text-sm">
            <li class="flex gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-600 text-white font-black flex items-center justify-center text-sm">1</span>
                <div>
                    <p class="font-bold text-indigo-950">Configura i canali di invio</p>
                    <p class="text-indigo-900/80 mt-0.5">Email e WhatsApp: apri la pagina del canale, inserisci credenziali e fai un test.</p>
                    @if ($isSuperAdmin)
                        <div class="flex flex-wrap gap-3 mt-2">
                            <a href="{{ route('admin.notifiche.email') }}" class="text-xs font-black uppercase text-indigo-700 underline">→ Email</a>
                            <a href="{{ route('admin.notifiche.whatsapp') }}" class="text-xs font-black uppercase text-indigo-700 underline">→ WhatsApp</a>
                        </div>
                    @else
                        <p class="text-xs text-indigo-700/60 mt-2">🔒 Riservato al super admin</p>
                    @endif
                </div>
            </li>
            <li class="flex gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-600 text-white font-black flex items-center justify-center text-sm">2</span>
                <div>
                    <p class="font-bold text-indigo-950">Accendi i toggle e inserisci i contatti</p>
                    <p class="text-indigo-900/80 mt-0.5">Sezione verde (admin) e azzurra (ospiti) in Progetto. Di default tutto è <strong>spento</strong>.</p>
                    <a href="{{ route('admin.progetto') }}#notifiche" class="inline-block mt-2 text-xs font-black uppercase text-indigo-700 underline">→ Progetto e task (notifiche)</a>
                </div>
            </li>
            <li class="flex gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-600 text-white font-black flex items-center justify-center text-sm">3</span>
                <div>
                    <p class="font-bold text-indigo-950">Testa, poi vai live</p>
                    <p class="text-indigo-900/80 mt-0.5">
                        Usa «Invia test admin» e una prenotazione TEST con «Prova notifiche».
                        Quando sei pronta, il team disattiva <strong>Work in progress</strong>.
                    </p>
                    @if ($isSuperAdmin)
                        <a href="{{ route('admin.sviluppo') }}" class="inline-block mt-2 text-xs font-black uppercase text-indigo-700 underline">→ Sviluppo (work in progress)</a>
                    @else
                        <p class="text-xs text-indigo-700/70 mt-2">Solo il super admin può spegnere «Work in progress».</p>
                    @endif
                </div>
            </li>
        </ol>
    </section>

    {{-- Checklist live --}}
    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6">
        <h2 class="text-lg font-black text-emerald-950 mb-2">Checklist «Pronta per andare live»</h2>
        <p class="text-sm text-gray-600 mb-4">Spunta mentalmente ogni voce prima di inviare messaggi ai clienti reali.</p>
        <ul class="space-y-2">
            @foreach ($liveChecklist as $item)
                <li class="flex gap-3 items-start rounded-xl px-3 py-2 {{ $item['done'] ? 'bg-emerald-50' : 'bg-gray-50' }}">
                    <span class="text-lg leading-none mt-0.5">{{ $item['done'] ? '✅' : '⬜' }}</span>
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ $item['label'] }}</p>
                        <p class="text-xs text-gray-500">{{ $item['hint'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Matrice chi riceve cosa --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 overflow-x-auto">
        <h2 class="text-lg font-black text-indigo-950 mb-2">Cosa riceve chi</h2>
        <p class="text-sm text-gray-600 mb-4">Riepilogo degli avvisi principali. I canali effettivi dipendono dai toggle (email, WhatsApp, push).</p>
        <table class="w-full text-sm text-left border-collapse min-w-[640px]">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500">
                    <th class="py-2 pr-3 font-black">Evento</th>
                    <th class="py-2 pr-3 font-black">Admin (team)</th>
                    <th class="py-2 pr-3 font-black">Ospite</th>
                    <th class="py-2 font-black">Quando</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach ($notificationMatrix as $row)
                    <tr class="border-b border-gray-100 align-top">
                        <td class="py-2.5 pr-3 font-semibold text-gray-900">{{ $row['event'] }}</td>
                        <td class="py-2.5 pr-3">{{ $row['admin'] }}</td>
                        <td class="py-2.5 pr-3">{{ $row['guest'] }}</td>
                        <td class="py-2.5 text-xs text-gray-500">{{ $row['when'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- Contatti admin configurati --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-3">Contatti admin attuali</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Email</p>
                @if (count($adminEmails))
                    <ul class="space-y-1 font-mono text-xs">
                        @foreach ($adminEmails as $email)
                            <li class="bg-gray-50 rounded px-2 py-1">{{ $email }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-400 italic">Nessuna email configurata</p>
                @endif
            </div>
            <div>
                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">WhatsApp</p>
                @if (count($adminPhones))
                    <ul class="space-y-1 font-mono text-xs">
                        @foreach ($adminPhones as $phone)
                            <li class="bg-gray-50 rounded px-2 py-1">{{ $phone }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-400 italic">Nessun cellulare configurato</p>
                @endif
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3">
            Modifica in <a href="{{ route('admin.progetto') }}" class="text-indigo-600 underline font-bold">Progetto e task</a>.
        </p>
    </section>

    {{-- Telegram --}}
    <section class="bg-white rounded-3xl shadow-sm border border-violet-100 p-6">
        <h2 class="text-lg font-black text-violet-950 mb-2">Telegram</h2>
        <p class="text-sm text-gray-600 mb-4">
            Un solo bot (<strong>@{{ $telegramBotUsername }}</strong>).
            @if ($isSuperAdmin)
                Dettagli, costi e setup in
                <a href="{{ route('admin.notifiche.telegram') }}" class="text-violet-700 underline font-bold">pagina Telegram</a>.
            @else
                Dettagli e setup: 🔒 riservato al super admin.
            @endif
        </p>

        <div class="grid sm:grid-cols-2 gap-3 text-sm mb-4">
            <div class="rounded-xl border p-3 {{ $telegramAdminReady ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                <p class="font-bold">Admin (team)</p>
                <p class="text-xs text-gray-600 mt-1">
                    @if ($telegramAdminReady)
                        ✅ Bot attivo — chat ID in <code>TELEGRAM_NOTIFY_CHAT_IDS</code>
                    @else
                        Imposta <code>TELEGRAM_ENABLED=true</code>, token e chat ID. Test: <code>php artisan jlune:telegram-test</code>
                    @endif
                </p>
            </div>
            <div class="rounded-xl border p-3 {{ ($guestTelegramOn && $telegramBotReady) ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                <p class="font-bold">Ospiti</p>
                <p class="text-xs text-gray-600 mt-1">
                    @if ($guestTelegramOn && $telegramBotReady)
                        ✅ Toggle ON — {{ $telegramLinkedCount }} prenotazioni con Telegram collegato
                    @else
                        Attiva «Telegram» in Progetto → Notifiche ospiti. L'ospite deve premere «Collega Telegram».
                    @endif
                </p>
            </div>
        </div>

        @if ($webhookUrl)
            <div class="bg-violet-50 border border-violet-100 rounded-xl p-4 text-xs">
                <p class="font-bold text-violet-900 mb-1">Webhook (una volta su Plesk)</p>
                <p class="text-violet-800 mb-2">Imposta <code>TELEGRAM_WEBHOOK_SECRET</code> nel .env, poi registra l'URL su Telegram:</p>
                <code class="block bg-white border border-violet-200 rounded p-2 break-all text-[11px]">{{ $webhookUrl }}</code>
                <p class="text-violet-700/80 mt-2">Comando (sostituisci TOKEN e URL):<br>
                    <code>https://api.telegram.org/botTOKEN/setWebhook?url=URL</code></p>
            </div>
        @endif
    </section>

    {{-- Automatismi cron --}}
    <section class="bg-sky-50 rounded-3xl border border-sky-100 p-6">
        <h2 class="text-lg font-black text-sky-950 mb-2">Promemoria automatici (server)</h2>
        <p class="text-sm text-sky-900/90 mb-3">
            Sul server Plesk sono attivi due task giornalieri che lanciano i comandi Laravel:
        </p>
        <ul class="text-sm space-y-2 text-sky-900">
            <li><strong>10:00</strong> — promemoria ospiti (pagamento, documenti, CF, firma) per arrivi entro 14 giorni</li>
            <li><strong>03:30</strong> — cancellazione documenti d'identità dopo il check-out (privacy)</li>
        </ul>
        <p class="text-xs text-sky-800/70 mt-3">
            Rispettano le stesse regole: work in progress, toggle ospite e «Prova notifiche» su singola prenotazione.
        </p>
    </section>

    {{-- Prossimi passi (futuro) --}}
    <section class="bg-white rounded-3xl shadow-sm border border-dashed border-indigo-200 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-2">In arrivo (prossime fasi)</h2>
        <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
            <li><strong>App installabile</strong> — icone separate admin (scura) e ospite (chiara) già configurate; installa da browser «Aggiungi a schermata Home».</li>
            <li><strong>Accesso admin</strong> con utente e password per il team.</li>
            <li><strong>Profilo ospite</strong> opzionale dopo il check-out, per restare in contatto e ricevere sconti per prenotazioni dirette.</li>
        </ul>
        <p class="text-xs text-gray-400 mt-3">Queste funzioni non sono ancora attive; le implementeremo in un passo successivo.</p>
    </section>

    {{-- Link rapidi --}}
    <section class="flex flex-wrap gap-3">
        @if ($isSuperAdmin)
            <a href="{{ route('admin.notifiche.whatsapp') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wide hover:bg-emerald-700">
                💬 WhatsApp
            </a>
            <a href="{{ route('admin.notifiche.email') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-wide hover:bg-indigo-700">
                📧 Email
            </a>
        @endif
        <a href="{{ route('admin.progetto') }}"
           class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wide hover:bg-emerald-700">
            📋 Toggle e test
        </a>
        <a href="{{ route('admin.prova') }}"
           class="inline-flex items-center gap-2 px-5 py-3 bg-white border border-gray-300 text-gray-800 rounded-xl text-xs font-black uppercase tracking-wide hover:bg-gray-50">
            🧪 Prova flusso (TEST)
        </a>
    </section>
</div>
