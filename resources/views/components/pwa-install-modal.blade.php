@props(['channel' => 'guest', 'reservationId' => null])

@php
    $isGuest = $channel === 'guest';
    $icon = url('/pwa-icons/'.($isGuest ? 'guest-192.png' : 'admin-192.png'));
    $appName = $isGuest ? 'Jlune Check-in' : 'Jlune Gestione';
    $tagline = $isGuest
        ? 'Documenti, contratto e soggiorno sempre a portata di mano.'
        : 'Pannello admin sul telefono — notifiche e prenotazioni.';
    $accentBtn = $isGuest ? 'bg-teal-600 hover:bg-teal-700' : 'bg-slate-800 hover:bg-slate-900';
    $iconBg = $isGuest ? 'bg-white' : 'bg-slate-900';
    $storageKey = 'jlune_pwa_install_dismissed_'.$channel;
    $webPushReady = config('webpush.enabled') && filled(config('webpush.vapid.public_key'));
    $sw = $isGuest ? '/sw-guest.js' : '/sw-admin.js';
    $swScope = $isGuest ? '/checkin' : '/admin';
@endphp

<div id="jlune-pwa-modal-{{ $channel }}"
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 pb-[max(1rem,env(safe-area-inset-bottom))]"
     data-channel="{{ $channel }}"
     data-storage-key="{{ $storageKey }}"
     @if ($reservationId) data-reservation-id="{{ $reservationId }}" @endif
     data-webpush="{{ $webPushReady ? '1' : '0' }}"
     data-vapid="{{ $webPushReady ? config('webpush.vapid.public_key') : '' }}"
     data-push-url="{{ route('push.subscribe') }}"
     data-sw="{{ $sw }}"
     data-sw-scope="{{ $swScope }}">
    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" id="jlune-pwa-backdrop-{{ $channel }}"></div>

    <div class="relative w-full max-w-sm max-h-[90dvh] overflow-y-auto rounded-3xl bg-white shadow-2xl">
        <div class="px-5 pt-6 pb-3 text-center">
            <div id="jlune-pwa-icon-wrap-{{ $channel }}"
                 class="w-[5.5rem] h-[5.5rem] mx-auto rounded-[1.25rem] overflow-hidden shadow-lg mb-4 {{ $iconBg }}">
                <div id="jlune-pwa-icon-fallback-{{ $channel }}"
                     class="w-full h-full hidden items-center justify-center text-white text-3xl font-black {{ $isGuest ? 'bg-teal-600' : 'bg-slate-800' }}">
                    J
                </div>
                <img id="jlune-pwa-icon-img-{{ $channel }}"
                     src="{{ $icon }}"
                     alt="{{ $appName }}"
                     class="w-full h-full object-cover"
                     onerror="this.classList.add('hidden'); document.getElementById('jlune-pwa-icon-fallback-{{ $channel }}')?.classList.replace('hidden','flex');">
            </div>
            <h2 class="text-lg font-black text-gray-900 leading-tight">Installa {{ $appName }}</h2>
            <p class="text-sm text-gray-600 mt-1.5 px-1">{{ $tagline }}</p>
        </div>

        <div class="px-5 pb-3 space-y-3">
            <p id="jlune-pwa-wait-{{ $channel }}" class="hidden text-center text-xs text-gray-500 py-2">
                Preparazione installazione…
            </p>

            <button type="button"
                    id="jlune-pwa-native-install-{{ $channel }}"
                    class="hidden w-full py-3.5 px-4 rounded-2xl text-white text-sm font-black uppercase tracking-wide shadow-md {{ $accentBtn }}">
                Installa ora
            </button>

            <div id="jlune-pwa-manual-{{ $channel }}" class="hidden rounded-2xl bg-gray-50 border border-gray-100 p-4 text-left text-sm text-gray-700">
                <p class="font-bold text-gray-900 mb-2">Come installare:</p>
                <ol class="list-decimal list-inside space-y-1.5 text-xs leading-relaxed" id="jlune-pwa-steps-{{ $channel }}"></ol>
            </div>

            @if ($webPushReady)
                <div id="jlune-pwa-push-block-{{ $channel }}" class="hidden rounded-2xl border border-indigo-100 bg-indigo-50 p-4 text-left">
                    <p class="text-xs font-bold text-indigo-950">Notifiche sul telefono</p>
                    <p class="text-[11px] text-indigo-800 mt-1">Dopo l'installazione, attiva i promemoria.</p>
                    <button type="button"
                            id="jlune-pwa-push-btn-{{ $channel }}"
                            class="mt-3 w-full py-2.5 px-3 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase">
                        Attiva notifiche
                    </button>
                    <p id="jlune-pwa-push-status-{{ $channel }}" class="text-[10px] text-indigo-700 mt-1"></p>
                </div>
            @endif
        </div>

        <div class="px-5 py-4 border-t border-gray-100">
            <button type="button"
                    id="jlune-pwa-later-{{ $channel }}"
                    class="w-full py-2 text-sm font-semibold text-gray-500 hover:text-gray-800">
                Più tardi
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const channel = @json($channel);
    const modal = document.getElementById('jlune-pwa-modal-' + channel);
    if (!modal) return;

    const storageKey = modal.dataset.storageKey;
    if (localStorage.getItem(storageKey) === '1') return;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    if (isStandalone) return;

    const ua = navigator.userAgent || '';
    const isIos = /iPad|iPhone|iPod/.test(ua)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isAndroid = /Android/i.test(ua);
    const isMobile = isIos || isAndroid
        || /webOS|BlackBerry|IEMobile|Opera Mini/i.test(ua)
        || (navigator.maxTouchPoints > 1 && window.innerWidth < 1024);

    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        updateInstallUi();
    });

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return Promise.resolve(null);
        return navigator.serviceWorker.register(modal.dataset.sw, { scope: modal.dataset.swScope })
            .catch(function () { return null; });
    }

    function manualStepsHtml() {
        if (isIos) {
            return '<li>Apri in <strong>Safari</strong></li>'
                + '<li>Tocca <strong>Condividi</strong> (quadrato con freccia)</li>'
                + '<li>Scegli <strong>Aggiungi a Home</strong></li>'
                + '<li>Apri l\'icona <strong>Jlune</strong> dalla home</li>';
        }
        return '<li>Tocca il menu <strong>⋮</strong> in alto a destra</li>'
            + '<li>Scegli <strong>Installa app</strong> o <strong>Aggiungi a schermata Home</strong></li>'
            + '<li>Conferma — l\'icona Jlune apparirà sulla home</li>';
    }

    function updateInstallUi() {
        const nativeBtn = document.getElementById('jlune-pwa-native-install-' + channel);
        const manual = document.getElementById('jlune-pwa-manual-' + channel);
        const steps = document.getElementById('jlune-pwa-steps-' + channel);
        const wait = document.getElementById('jlune-pwa-wait-' + channel);
        const push = document.getElementById('jlune-pwa-push-block-' + channel);

        wait?.classList.add('hidden');

        if (deferredPrompt && nativeBtn) {
            nativeBtn.classList.remove('hidden');
            manual?.classList.add('hidden');
            push?.classList.add('hidden');
            return;
        }

        if (!manual || !steps) return;

        if (isIos || isMobile) {
            manual.classList.remove('hidden');
            steps.innerHTML = manualStepsHtml();
        }

        if (modal.dataset.webpush === '1' && push && (isIos || isMobile)) {
            push.classList.remove('hidden');
        }
    }

    function waitForInstallPrompt(maxMs) {
        return new Promise(function (resolve) {
            if (deferredPrompt) {
                resolve(deferredPrompt);
                return;
            }
            const wait = document.getElementById('jlune-pwa-wait-' + channel);
            let done = false;
            const finish = function (value) {
                if (done) return;
                done = true;
                wait?.classList.add('hidden');
                resolve(value);
            };
            const timer = setTimeout(function () { finish(null); }, maxMs);
            window.addEventListener('beforeinstallprompt', function handler(e) {
                e.preventDefault();
                deferredPrompt = e;
                clearTimeout(timer);
                window.removeEventListener('beforeinstallprompt', handler);
                finish(e);
            });
        });
    }

    function showModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateInstallUi();
    }

    function closeModal(persist) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        if (persist) localStorage.setItem(storageKey, '1');
    }

    document.getElementById('jlune-pwa-later-' + channel)?.addEventListener('click', function () {
        closeModal(true);
    });
    document.getElementById('jlune-pwa-backdrop-' + channel)?.addEventListener('click', function () {
        closeModal(true);
    });

    document.getElementById('jlune-pwa-native-install-' + channel)?.addEventListener('click', async function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        deferredPrompt = null;
        if (choice.outcome === 'accepted' && modal.dataset.webpush === '1') {
            document.getElementById('jlune-pwa-push-block-' + channel)?.classList.remove('hidden');
        }
        if (choice.outcome === 'accepted') {
            closeModal(true);
        }
    });

    const pushBtn = document.getElementById('jlune-pwa-push-btn-' + channel);
    const pushStatus = document.getElementById('jlune-pwa-push-status-' + channel);
    const vapidKey = modal.dataset.vapid;

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = window.atob(base64);
        const arr = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
        return arr;
    }

    pushBtn?.addEventListener('click', async function () {
        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !vapidKey) {
            if (pushStatus) pushStatus.textContent = 'Browser non supporta le notifiche push.';
            return;
        }
        try {
            if (pushStatus) pushStatus.textContent = 'Registrazione…';
            const reg = await navigator.serviceWorker.ready;
            const p = await Notification.requestPermission();
            if (p !== 'granted') {
                if (pushStatus) pushStatus.textContent = 'Permesso negato — abilita dalle impostazioni.';
                return;
            }
            const sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });
            const json = sub.toJSON();
            await fetch(modal.dataset.pushUrl, {
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
                    reservation_id: modal.dataset.reservationId ? parseInt(modal.dataset.reservationId, 10) : null,
                }),
            });
            if (pushStatus) pushStatus.textContent = '✓ Notifiche attive.';
            pushBtn.disabled = true;
        } catch (e) {
            if (pushStatus) pushStatus.textContent = 'Errore: ' + (e.message || e);
        }
    });

    registerServiceWorker().then(function () {
        showModal();
        const wait = document.getElementById('jlune-pwa-wait-' + channel);
        if (isAndroid || (!isIos && !isMobile)) {
            wait?.classList.remove('hidden');
        }
        return waitForInstallPrompt(isAndroid ? 4000 : 2500);
    }).then(function () {
        updateInstallUi();
    });
})();
</script>
