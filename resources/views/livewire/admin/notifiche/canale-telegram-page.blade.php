<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.notifiche') }}" class="text-indigo-600 font-bold text-sm">← Notifiche</a>
        <h1 class="text-3xl font-black text-violet-950 mt-2">✈️ Telegram</h1>
        <p class="text-gray-500 text-sm mt-1">
            @if ($telegramBotUsername)
                Bot unico <strong>@{{ $telegramBotUsername }}</strong> per admin e ospiti.
            @else
                Nessun bot configurato — crealo in pochi minuti (istruzioni qui sotto).
            @endif
        </p>
    </div>

    <section class="bg-amber-50 rounded-3xl border border-amber-100 p-6 space-y-3">
        <h2 class="text-lg font-black text-amber-950">Crea il tuo bot (una tantum, gratis)</h2>
        <ol class="text-sm text-gray-700 space-y-2 list-decimal list-inside">
            <li>Su Telegram cerca <strong>@BotFather</strong> e apri la chat.</li>
            <li>Scrivi <code class="bg-white px-1 rounded">/newbot</code> e segui i passi: nome a piacere, username che deve finire in <code class="bg-white px-1 rounded">bot</code> (es. <code class="bg-white px-1 rounded">appartamentirossi_bot</code>).</li>
            <li>BotFather ti restituisce un <strong>token</strong> — copialo, serve al passo dopo.</li>
            <li>Apri la chat col tuo nuovo bot e premi <strong>Avvia</strong> (serve per ottenere il tuo Chat ID: scrivi qualsiasi messaggio, poi visita <code class="bg-white px-1 rounded break-all">https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code> e cerca <code class="bg-white px-1 rounded">"chat":{"id":...}</code>).</li>
            <li>Inserisci le variabili qui sotto nel <code class="bg-white px-1 rounded">.env</code> del server.</li>
        </ol>
    </section>

    <section class="bg-violet-50 rounded-3xl border border-violet-100 p-6 space-y-3">
        <h2 class="text-lg font-black text-violet-950">A cosa serve</h2>
        <div class="grid sm:grid-cols-2 gap-3 text-sm">
            <div class="bg-white rounded-xl border border-violet-100 p-4">
                <p class="font-bold">Admin</p>
                <p class="text-xs text-gray-600 mt-1">Messaggi istantanei al telefono. Chat ID in <code>TELEGRAM_NOTIFY_CHAT_IDS</code>.</p>
                <p class="text-xs mt-2 {{ $telegramAdminReady ? 'text-emerald-700 font-bold' : 'text-gray-500' }}">
                    {{ $telegramAdminReady ? '✅ Bot + chat ID configurati' : '⬜ Completa .env e /start al bot' }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-violet-100 p-4">
                <p class="font-bold">Ospiti</p>
                <p class="text-xs text-gray-600 mt-1">Pulsante «Collega Telegram» nel portale check-in. {{ $telegramLinkedCount }} prenotazioni collegate.</p>
                <p class="text-xs mt-2 {{ ($guestTelegramOn && $telegramBotReady) ? 'text-emerald-700 font-bold' : 'text-gray-500' }}">
                    {{ ($guestTelegramOn && $telegramBotReady) ? '✅ Toggle ospite ON' : '⬜ Attiva in Progetto' }}
                </p>
            </div>
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-3">Stima costi</h2>
        <p class="text-sm text-gray-700">
            <strong>Gratuito.</strong> Telegram non addebita l'uso del Bot API. Costo operativo: zero euro, solo setup una tantum del webhook su Plesk.
        </p>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-violet-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-violet-950">Configurazione server (.env)</h2>
        <p class="text-sm text-gray-600">Variabili da impostare su Plesk (non in Git):</p>
        <ul class="text-xs font-mono bg-gray-50 rounded-xl p-4 space-y-1 text-gray-800">
            <li>TELEGRAM_ENABLED=true</li>
            <li>TELEGRAM_BOT_TOKEN=…</li>
            <li>TELEGRAM_BOT_USERNAME=iltuobot_bot</li>
            <li>TELEGRAM_NOTIFY_CHAT_IDS=123456789</li>
            <li>TELEGRAM_WEBHOOK_SECRET=stringa-casuale-lunga</li>
        </ul>
        <p class="text-sm text-gray-600">
            Test locale: <code class="bg-gray-100 px-1 rounded">php artisan jlune:telegram-test</code>
        </p>

        @if ($webhookUrl)
            <div class="bg-violet-50 border border-violet-100 rounded-xl p-4 text-xs">
                <p class="font-bold text-violet-900 mb-1">Webhook (una volta in produzione)</p>
                <code class="block bg-white border border-violet-200 rounded p-2 break-all text-[11px]">{{ $webhookUrl }}</code>
                <p class="text-violet-700/80 mt-2">
                    <code>https://api.telegram.org/botTOKEN/setWebhook?url=URL</code>
                </p>
            </div>
        @endif

        @if (count($notifyChatIds))
            <p class="text-xs text-gray-500">Chat ID admin attivi: {{ implode(', ', $notifyChatIds) }}</p>
        @endif
    </section>

    <section class="bg-gray-50 rounded-2xl border border-gray-200 p-4 text-sm text-gray-700">
        <p class="font-bold mb-1">Toggle in app</p>
        <p>
            Attiva Telegram admin/ospite in
            <a href="{{ route('admin.progetto') }}#notifiche" class="text-indigo-600 underline font-bold">Progetto → Notifiche</a>.
        </p>
    </section>
</div>
