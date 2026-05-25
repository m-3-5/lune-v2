@props([
    'channel' => 'admin',
    'reservationId' => null,
    'vapidPublicKey' => config('webpush.vapid.public_key'),
])

@if (config('webpush.enabled') && filled($vapidPublicKey))
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-indigo-100 bg-indigo-50/80 p-3 text-xs']) }}>
        <p class="font-black text-indigo-900 mb-2">Notifiche sul telefono</p>
        <p class="text-indigo-700/80 mb-2">Attiva per ricevere avvisi con suono/vibrazione (PWA installata).</p>
        <button type="button" id="jlune-push-enable"
            class="px-3 py-2 bg-indigo-600 text-white rounded-lg font-bold uppercase tracking-wider text-[10px]">
            Attiva notifiche
        </button>
        <p id="jlune-push-status" class="mt-2 text-[10px] text-gray-500"></p>
    </div>
    <script>
        (function () {
            const channel = @json($channel);
            const reservationId = @json($reservationId);
            const vapidKey = @json($vapidPublicKey);
            const swPath = channel === 'admin' ? '/sw-admin.js' : '/sw-guest.js';
            const scope = channel === 'admin' ? '/admin' : '/checkin';
            const btn = document.getElementById('jlune-push-enable');
            const status = document.getElementById('jlune-push-status');
            if (!btn || !('serviceWorker' in navigator) || !('PushManager' in window)) {
                if (status) status.textContent = 'Browser non supporta Web Push.';
                return;
            }
            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw = window.atob(base64);
                const arr = new Uint8Array(raw.length);
                for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
                return arr;
            }
            btn.addEventListener('click', async function () {
                try {
                    status.textContent = 'Registrazione…';
                    const reg = await navigator.serviceWorker.register(swPath, { scope: scope });
                    await navigator.serviceWorker.ready;
                    const perm = await Notification.requestPermission();
                    if (perm !== 'granted') {
                        status.textContent = 'Permesso negato.';
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
                            channel: channel,
                            endpoint: json.endpoint,
                            keys: json.keys,
                            encoding: 'aesgcm',
                            reservation_id: reservationId,
                        }),
                    });
                    status.textContent = '✓ Notifiche attive su questo dispositivo.';
                    btn.disabled = true;
                } catch (e) {
                    status.textContent = 'Errore: ' + (e.message || e);
                }
            });
        })();
    </script>
@endif
