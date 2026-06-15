<div class="max-w-3xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.notifiche') }}" class="text-indigo-600 font-bold text-sm">← Notifiche</a>
        <h1 class="text-3xl font-black text-sky-950 mt-2">📲 Web Push (PWA)</h1>
        <p class="text-gray-500 text-sm mt-1">
            Notifiche sul telefono quando l'app Jlune è installata («Aggiungi a schermata Home»). Funziona per admin e ospiti.
        </p>
    </div>

    <section class="bg-sky-50 rounded-3xl border border-sky-100 p-6 space-y-3">
        <h2 class="text-lg font-black text-sky-950">A cosa serve</h2>
        <p class="text-sm text-sky-900/90">
            Complemento a email/WhatsApp/Telegram: avvisi leggeri quando il browser ha installato la PWA e l'utente ha accettato le notifiche.
            Gli ospiti attivano dal portale check-in; gli admin dall'area admin.
        </p>
        <p class="text-xs {{ ($guestPushOn && $guestNotificationsOn && $vapidReady) ? 'text-emerald-700 font-bold' : 'text-gray-600' }}">
            Ospiti: {{ ($guestPushOn && $guestNotificationsOn && $vapidReady) ? '✅ toggle ON + chiavi VAPID' : '⬜ attiva toggle in Progetto e VAPID nel .env' }}
        </p>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-3">Stima costi</h2>
        <p class="text-sm text-gray-700">
            <strong>Gratuito.</strong> Nessun provider esterno a pagamento. Le chiavi VAPID sono generate una volta sul server.
        </p>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-sky-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-sky-950">Configurazione server (.env)</h2>
        <ol class="text-sm text-gray-700 list-decimal list-inside space-y-2">
            <li>Sul server: <code class="bg-gray-100 px-1 rounded">php artisan jlune:vapid-keys</code></li>
            <li>Copia le righe nel <code>.env</code> di Plesk</li>
            <li>Imposta <code>WEBPUSH_ENABLED=true</code></li>
            <li><code>php artisan config:clear</code></li>
        </ol>

        <div class="rounded-xl border p-3 text-sm {{ $vapidReady ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
            @if ($vapidReady)
                <p class="font-bold text-emerald-800">✅ Web Push attivo</p>
                <p class="text-xs text-emerald-700 mt-1 font-mono break-all">Public key: {{ $vapidPublicKey ? substr($vapidPublicKey, 0, 48).'…' : '' }}</p>
            @else
                <p class="font-bold text-amber-800">⬜ Non pronto</p>
                <p class="text-xs text-amber-700 mt-1">
                    @if (! $webPushEnabled)
                        <code>WEBPUSH_ENABLED=false</code> — abilita nel .env
                    @else
                        Chiavi VAPID mancanti — esegui <code>jlune:vapid-keys</code>
                    @endif
                </p>
            @endif
        </div>

        <p class="text-sm text-gray-600">
            Test admin: <code class="bg-gray-100 px-1 rounded">php artisan jlune:push-test</code>
        </p>
        <p class="text-xs text-gray-500">Subject VAPID: {{ $vapidSubject ?: 'non impostato' }}</p>
    </section>

    <section class="bg-gray-50 rounded-2xl border border-gray-200 p-4 text-sm text-gray-700">
        <p class="font-bold mb-1">Toggle ospiti</p>
        <p>
            «Push» in
            <a href="{{ route('admin.progetto') }}#notifiche" class="text-indigo-600 underline font-bold">Progetto → Notifiche</a>.
            L'ospite deve installare la PWA e premere «Attiva notifiche».
        </p>
    </section>
</div>
