@props(['channel' => 'guest', 'reservationId' => null])

@php
    $isGuest = $channel === 'guest';
    $icon = url('/pwa-icons/'.($isGuest ? 'guest-180.png' : 'admin-180.png'));
    $appName = $isGuest ? 'Jlune Check-in' : 'Jlune Gestione';
    $tagline = $isGuest
        ? 'Documenti, contratto e soggiorno sul telefono.'
        : 'Pannello admin e notifiche sul telefono.';
    $accentBtn = $isGuest ? 'bg-teal-600 hover:bg-teal-700' : 'bg-slate-800 hover:bg-slate-900';
    $iconBg = $isGuest ? '#ffffff' : '#0f172a';
    $storageKey = 'jlune_pwa_install_dismissed_'.$channel;
    $webPushReady = config('webpush.enabled') && filled(config('webpush.vapid.public_key'));
@endphp

<div id="jlune-pwa-modal-{{ $channel }}"
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4"
     style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
    <div class="absolute inset-0 bg-gray-900/70" id="jlune-pwa-backdrop-{{ $channel }}"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-[20rem] max-h-[85vh] overflow-y-auto">
        <div class="px-5 pt-5 pb-3 text-center">
            <div class="mx-auto mb-3 rounded-2xl overflow-hidden shadow-md flex items-center justify-center"
                 style="width:64px;height:64px;background:{{ $iconBg }};">
                <img src="{{ $icon }}"
                     alt="{{ $appName }}"
                     width="64"
                     height="64"
                     style="width:64px;height:64px;object-fit:contain;display:block;">
            </div>
            <h2 class="text-base font-black text-gray-900">Installa {{ $appName }}</h2>
            <p class="text-xs text-gray-600 mt-1">{{ $tagline }}</p>
        </div>

        <div class="px-5 pb-3 space-y-2.5">
            <p id="jlune-pwa-wait-{{ $channel }}" class="hidden text-center text-xs text-gray-500">Preparazione…</p>

            <button type="button"
                    id="jlune-pwa-native-install-{{ $channel }}"
                    class="hidden w-full py-3 rounded-xl text-white text-xs font-black uppercase {{ $accentBtn }}">
                Installa ora
            </button>

            <div id="jlune-pwa-manual-{{ $channel }}" class="hidden rounded-xl bg-gray-50 border border-gray-100 p-3 text-left">
                <p class="font-bold text-gray-900 text-xs mb-1.5">Come installare:</p>
                <ol class="list-decimal list-inside space-y-1 text-[11px] text-gray-700 leading-relaxed" id="jlune-pwa-steps-{{ $channel }}"></ol>
            </div>

            @if ($webPushReady)
                <div id="jlune-pwa-push-block-{{ $channel }}" class="hidden rounded-xl border border-indigo-100 bg-indigo-50 p-3 text-left">
                    <p class="text-[11px] font-bold text-indigo-950">Notifiche</p>
                    <button type="button"
                            id="jlune-pwa-push-btn-{{ $channel }}"
                            class="mt-2 w-full py-2 rounded-lg bg-indigo-600 text-white text-[10px] font-black uppercase">
                        Attiva notifiche
                    </button>
                    <p id="jlune-pwa-push-status-{{ $channel }}" class="text-[10px] text-indigo-700 mt-1"></p>
                </div>
            @endif
        </div>

        <div class="px-5 py-3 border-t border-gray-100">
            <button type="button"
                    id="jlune-pwa-later-{{ $channel }}"
                    class="w-full py-1.5 text-xs font-semibold text-gray-500">
                Più tardi
            </button>
        </div>
    </div>
</div>

@once
    <script src="/js/jlune-pwa-install.js?v=3"></script>
@endonce

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jlunePwaInstall) return;
        window.jlunePwaInstall.init(@json($channel), {
            storageKey: @json($storageKey),
            sw: @json($isGuest ? '/sw-guest.js' : '/sw-admin.js'),
            swScope: @json($isGuest ? '/checkin' : '/admin'),
            webpush: @json($webPushReady),
            vapid: @json($webPushReady ? config('webpush.vapid.public_key') : ''),
            pushUrl: @json(route('push.subscribe')),
            reservationId: @json($reservationId),
        });
    });
</script>
