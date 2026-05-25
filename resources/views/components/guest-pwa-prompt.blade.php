@props(['reservationId' => null])

@if (config('webpush.enabled') && filled(config('webpush.vapid.public_key')))
    <div id="jlune-guest-pwa-prompt" class="hidden max-w-md mx-auto px-4 mb-4" data-reservation-id="{{ $reservationId }}">
        <div class="rounded-2xl border border-indigo-200 bg-white shadow-lg overflow-hidden text-sm">
            <div class="bg-indigo-600 px-4 py-2 flex justify-between items-center">
                <span class="text-white font-black text-[10px] uppercase tracking-wider">Jlune sul telefono</span>
                <button type="button" id="jlune-guest-pwa-dismiss" class="text-indigo-200 text-xs font-bold">×</button>
            </div>
            <div class="p-4 space-y-3">
                <div id="jlune-guest-step-install" class="hidden">
                    <p class="font-bold text-indigo-950">1. Installa l'app</p>
                    <p class="text-gray-600 text-xs mt-1" id="jlune-guest-install-hint"></p>
                </div>
                <div id="jlune-guest-step-notify" class="hidden">
                    <p class="font-bold text-indigo-950">2. Attiva le notifiche</p>
                    <p class="text-gray-600 text-xs mt-1">Ricevi promemoria su documenti, contratto e check-in.</p>
                    <button type="button" id="jlune-guest-pwa-notify-btn"
                        class="mt-2 w-full px-3 py-2.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase">
                        Attiva notifiche
                    </button>
                    <p id="jlune-guest-notify-status" class="text-[10px] text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const box = document.getElementById('jlune-guest-pwa-prompt');
            if (!box) return;
            const dismissKey = 'jlune_guest_pwa_prompt_dismissed';
            if (localStorage.getItem(dismissKey) === '1') return;

            const isStandalone = window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true;
            const perm = ('Notification' in window) ? Notification.permission : 'denied';
            const needsInstall = !isStandalone;
            const needsNotify = perm !== 'granted';

            if (!needsInstall && !needsNotify) return;

            box.classList.remove('hidden');
            const stepInstall = document.getElementById('jlune-guest-step-install');
            const stepNotify = document.getElementById('jlune-guest-step-notify');
            const hint = document.getElementById('jlune-guest-install-hint');
            const ua = navigator.userAgent || '';
            const isIos = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            if (needsInstall && stepInstall && hint) {
                stepInstall.classList.remove('hidden');
                hint.textContent = isIos
                    ? 'Safari → Condividi → «Aggiungi a Home». Poi riapri l\'icona Jlune.'
                    : 'Chrome → menu ⋮ → «Aggiungi a schermata Home» o «Installa app».';
            }
            if (needsNotify && stepNotify) {
                stepNotify.classList.remove('hidden');
            }

            document.getElementById('jlune-guest-pwa-dismiss')?.addEventListener('click', function () {
                localStorage.setItem(dismissKey, '1');
                box.classList.add('hidden');
            });

            const notifyBtn = document.getElementById('jlune-guest-pwa-notify-btn');
            const status = document.getElementById('jlune-guest-notify-status');
            const vapidKey = @json(config('webpush.vapid.public_key'));
            const reservationId = box.dataset.reservationId ? parseInt(box.dataset.reservationId, 10) : null;

            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw = window.atob(base64);
                const arr = new Uint8Array(raw.length);
                for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
                return arr;
            }

            notifyBtn?.addEventListener('click', async function () {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    if (status) status.textContent = 'Browser non supporta le notifiche.';
                    return;
                }
                try {
                    if (status) status.textContent = 'Registrazione…';
                    const reg = await navigator.serviceWorker.register('/sw-guest.js', { scope: '/checkin' });
                    await navigator.serviceWorker.ready;
                    const p = await Notification.requestPermission();
                    if (p !== 'granted') {
                        if (status) status.textContent = 'Permesso negato nelle impostazioni.';
                        return;
                    }
                    const sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidKey),
                    });
                    const json = sub.toJSON();
                    await fetch('{{ route('push.subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            channel: 'guest',
                            endpoint: json.endpoint,
                            keys: json.keys,
                            encoding: 'aesgcm',
                            reservation_id: reservationId,
                        }),
                    });
                    if (status) status.textContent = '✓ Notifiche attive.';
                    notifyBtn.disabled = true;
                    document.getElementById('jlune-guest-step-notify')?.classList.add('opacity-60');
                } catch (e) {
                    if (status) status.textContent = 'Errore: ' + (e.message || e);
                }
            });
        })();
    </script>
@endif
