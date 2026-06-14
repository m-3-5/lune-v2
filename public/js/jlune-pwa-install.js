window.jlunePwaInstall = (function () {
    const configs = {};
    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        Object.keys(configs).forEach(function (ch) {
            updateUi(ch);
        });
    });

    function uaFlags() {
        const ua = navigator.userAgent || '';
        const isIos = /iPad|iPhone|iPod/.test(ua)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const isAndroid = /Android/i.test(ua);
        const isMobile = isIos || isAndroid
            || /webOS|BlackBerry|IEMobile|Opera Mini/i.test(ua)
            || (navigator.maxTouchPoints > 1 && window.innerWidth < 1024);
        return { isIos, isAndroid, isMobile };
    }

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
    }

    function manualStepsHtml(flags) {
        if (flags.isIos) {
            return '<li>Apri in <strong>Safari</strong></li>'
                + '<li>Tocca <strong>Condividi</strong></li>'
                + '<li>Scegli <strong>Aggiungi a Home</strong></li>'
                + '<li>Apri l\'icona <strong>Jlune</strong> dalla home</li>';
        }
        return '<li>Tocca il menu <strong>⋮</strong> del browser</li>'
            + '<li>Scegli <strong>Installa app</strong> o <strong>Aggiungi a schermata Home</strong></li>'
            + '<li>Conferma — l\'icona Jlune apparirà sulla home</li>';
    }

    function registerServiceWorker(cfg) {
        if (!('serviceWorker' in navigator)) {
            return Promise.resolve(null);
        }
        return navigator.serviceWorker.register(cfg.sw, { scope: cfg.swScope }).catch(function () {
            return null;
        });
    }

    function updateUi(channel) {
        const cfg = configs[channel];
        if (!cfg) return;
        const flags = uaFlags();
        const nativeBtn = document.getElementById('jlune-pwa-native-install-' + channel);
        const manual = document.getElementById('jlune-pwa-manual-' + channel);
        const steps = document.getElementById('jlune-pwa-steps-' + channel);
        const wait = document.getElementById('jlune-pwa-wait-' + channel);
        const push = document.getElementById('jlune-pwa-push-block-' + channel);

        wait?.classList.add('hidden');

        if (deferredPrompt && nativeBtn) {
            nativeBtn.classList.remove('hidden');
            manual?.classList.add('hidden');
            return;
        }

        if (manual && steps && (flags.isIos || flags.isMobile)) {
            manual.classList.remove('hidden');
            steps.innerHTML = manualStepsHtml(flags);
        }

        if (cfg.webpush && push && (flags.isIos || flags.isMobile) && !deferredPrompt) {
            push.classList.remove('hidden');
        }
    }

    function waitForInstallPrompt(maxMs) {
        return new Promise(function (resolve) {
            if (deferredPrompt) {
                resolve(deferredPrompt);
                return;
            }
            let done = false;
            const finish = function (value) {
                if (done) return;
                done = true;
                resolve(value);
            };
            const timer = setTimeout(function () { finish(null); }, maxMs);
            const handler = function (e) {
                e.preventDefault();
                deferredPrompt = e;
                clearTimeout(timer);
                window.removeEventListener('beforeinstallprompt', handler);
                finish(e);
            };
            window.addEventListener('beforeinstallprompt', handler);
        });
    }

    function bind(channel) {
        const modal = document.getElementById('jlune-pwa-modal-' + channel);
        if (!modal) return;

        document.getElementById('jlune-pwa-later-' + channel)?.addEventListener('click', function () {
            close(channel, true);
        });
        document.getElementById('jlune-pwa-backdrop-' + channel)?.addEventListener('click', function () {
            close(channel, true);
        });

        document.getElementById('jlune-pwa-native-install-' + channel)?.addEventListener('click', async function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const choice = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (choice.outcome === 'accepted') {
                close(channel, true);
            }
        });

        const pushBtn = document.getElementById('jlune-pwa-push-btn-' + channel);
        const pushStatus = document.getElementById('jlune-pwa-push-status-' + channel);
        const cfg = configs[channel];

        pushBtn?.addEventListener('click', async function () {
            if (!cfg || !('serviceWorker' in navigator) || !('PushManager' in window) || !cfg.vapid) {
                if (pushStatus) pushStatus.textContent = 'Browser non supporta le notifiche push.';
                return;
            }
            try {
                if (pushStatus) pushStatus.textContent = 'Registrazione…';
                const reg = await navigator.serviceWorker.ready;
                const p = await Notification.requestPermission();
                if (p !== 'granted') {
                    if (pushStatus) pushStatus.textContent = 'Permesso negato.';
                    return;
                }
                const sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(cfg.vapid),
                });
                const json = sub.toJSON();
                await fetch(cfg.pushUrl, {
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
                        reservation_id: cfg.reservationId || null,
                    }),
                });
                if (pushStatus) pushStatus.textContent = '✓ Notifiche attive.';
                pushBtn.disabled = true;
            } catch (e) {
                if (pushStatus) pushStatus.textContent = 'Errore: ' + (e.message || e);
            }
        });
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = window.atob(base64);
        const arr = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
        return arr;
    }

    function open(channel) {
        if (isStandalone()) return;
        const modal = document.getElementById('jlune-pwa-modal-' + channel);
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        const wait = document.getElementById('jlune-pwa-wait-' + channel);
        const flags = uaFlags();
        if (flags.isAndroid || !flags.isMobile) {
            wait?.classList.remove('hidden');
        }
        waitForInstallPrompt(flags.isAndroid ? 3500 : 2000).then(function () {
            updateUi(channel);
        });
    }

    function close(channel, persist) {
        const modal = document.getElementById('jlune-pwa-modal-' + channel);
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        if (persist && configs[channel]?.storageKey) {
            localStorage.setItem(configs[channel].storageKey, '1');
        }
    }

    function init(channel, options) {
        if (isStandalone()) return;
        configs[channel] = options || {};
        bind(channel);
        registerServiceWorker(configs[channel]);

        const autoKey = 'jlune_pwa_auto_' + channel;
        const dismissed = options.storageKey && localStorage.getItem(options.storageKey) === '1';
        const autoShown = sessionStorage.getItem(autoKey) === '1';

        if (!dismissed && !autoShown) {
            sessionStorage.setItem(autoKey, '1');
            setTimeout(function () { open(channel); }, 800);
        }
    }

    return { init, open, close, isStandalone };
})();
