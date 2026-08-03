<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ \App\Support\AppSettings::appName() }} Admin">
    <link rel="manifest" href="{{ route('admin.manifest') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="/pwa-icons/admin-512.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/pwa-icons/admin-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/pwa-icons/admin-180.png">
    <title>{{ $title ?? \App\Support\AppSettings::appName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="/js/jlune-pwa-install.js?v=5" defer></script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw-admin.js', { scope: '/admin' }).catch(function () {});
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <x-construction-banner />

    <!-- OVERLAY E MENU A SCOMPARSA (SOLO MOBILE) -->
    <div x-show="sidebarOpen" class="relative z-50 md:hidden" style="display: none;">
        <!-- Sfondo scuro -->
        <div x-show="sidebarOpen" 
             x-transition.opacity.duration.300ms 
             class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false"></div>

        <div class="fixed inset-0 flex">
            <!-- Sidebar mobile che slitta -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition ease-in-out duration-300 transform" 
                 x-transition:enter-start="-translate-x-full" 
                 x-transition:enter-end="translate-x-0" 
                 x-transition:leave="transition ease-in-out duration-300 transform" 
                 x-transition:leave-start="translate-x-0" 
                 x-transition:leave-end="-translate-x-full" 
                 class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-slate-900 text-white pt-5 pb-4 shadow-xl">
                
                <div class="absolute right-0 top-0 -mr-12 pt-2">
                    <button type="button" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none" @click="sidebarOpen = false">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex px-4 items-center h-12">
                    <h1 class="text-xl font-black tracking-tight text-white">{{ \App\Support\AppSettings::appName() }} <span class="text-indigo-400">Admin</span></h1>
                </div>
                <nav class="mt-5 h-full overflow-y-auto px-4 space-y-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📊</span> Dashboard</a>
                    <a href="{{ route('admin.arrivi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📇</span> Arrivi e Documenti</a>
                    <a href="{{ route('admin.appartamenti') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🏠</span> Appartamenti</a>
                    <a href="{{ route('admin.video') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🎥</span> Gestione Video</a>
                    <a href="{{ route('admin.contratti') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📄</span> Contratti</a>
                    <a href="{{ route('admin.testo-contratto') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📝</span> Testo contratto</a>
                    <a href="{{ route('admin.prova') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🧪</span> Prova flusso</a>
                    <a href="{{ route('admin.progetto') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors mt-4 border-t border-slate-800 pt-4"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📋</span> Progetto e task</a>
                    <a href="{{ route('admin.notifiche') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🔔</span> Notifiche e canali</a>
                    @if (auth()->user()?->isSuperAdmin())
                        <a href="{{ route('admin.utenti') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">👥</span> Utenti</a>
                    @endif
                    <button type="button" onclick="window.jlunePwaInstall && window.jlunePwaInstall.open('admin'); sidebarOpen = false" class="flex items-center gap-3 w-full text-left px-3 py-2.5 rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors text-teal-300"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📲</span> Scarica l'app</button>
                    @if (auth()->user()?->isSuperAdmin())
                        <a href="{{ route('admin.sviluppo') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl text-xs font-bold hover:bg-slate-800 transition-colors {{ \App\Support\JluneDeveloperAccess::isGranted() ? 'text-slate-300' : 'text-slate-500' }}">
                            <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🔧</span> Sviluppo{{ ! \App\Support\JluneDeveloperAccess::isGranted() ? ' 🔒' : '' }}
                        </a>
                    @endif
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full text-left px-3 py-2.5 rounded-2xl text-xs font-bold hover:bg-slate-800 transition-colors text-slate-500">
                            <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🚪</span> Esci ({{ auth()->user()->email }})
                        </button>
                    </form>
                </nav>
            </div>
        </div>
    </div>

    <!-- SIDEBAR FISSA (SOLO DESKTOP) -->
    <div class="hidden md:fixed md:inset-y-0 md:z-50 md:flex md:w-64 md:flex-col">
        <div class="flex grow flex-col overflow-y-auto bg-slate-900 px-6 pb-4 shadow-2xl text-white">
            <div class="flex h-20 shrink-0 items-center border-b border-slate-800 mb-6">
                <h1 class="text-xl font-black tracking-tight text-white">{{ \App\Support\AppSettings::appName() }} <span class="text-indigo-400">Admin</span></h1>
            </div>
            <nav class="flex flex-1 flex-col">
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul role="list" class="-mx-2 space-y-1.5">
                            <li><a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📊</span> Dashboard</a></li>
                            <li><a href="{{ route('admin.arrivi') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📇</span> Arrivi e Documenti</a></li>
                            <li><a href="{{ route('admin.appartamenti') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🏠</span> Appartamenti</a></li>
                            <li><a href="{{ route('admin.video') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🎥</span> Gestione Video</a></li>
                            <li><a href="{{ route('admin.contratti') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📄</span> Contratti</a></li>
                            <li><a href="{{ route('admin.testo-contratto') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📝</span> Testo contratto</a></li>
                            <li><a href="{{ route('admin.prova') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🧪</span> Prova flusso</a></li>
                            <li><a href="{{ route('admin.progetto') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 border-t border-slate-800 pt-4 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📋</span> Progetto e task</a></li>
                            <li><a href="{{ route('admin.notifiche') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🔔</span> Notifiche e canali</a></li>
                            @if (auth()->user()?->isSuperAdmin())
                                <li><a href="{{ route('admin.utenti') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors"><span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">👥</span> Utenti</a></li>
                            @endif
                            <li>
                                <button type="button" onclick="window.jlunePwaInstall && window.jlunePwaInstall.open('admin')" class="flex items-center gap-3 w-full text-left rounded-2xl px-3 py-2.5 text-sm font-bold hover:bg-slate-800 transition-colors text-teal-300">
                                    <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">📲</span> Scarica l'app
                                </button>
                            </li>
                            @if (auth()->user()?->isSuperAdmin())
                                <li>
                                    <a href="{{ route('admin.sviluppo') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-xs font-bold {{ \App\Support\JluneDeveloperAccess::isGranted() ? 'text-slate-300' : 'text-slate-500' }} hover:bg-slate-800 transition-colors">
                                        <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🔧</span> Sviluppo{{ ! \App\Support\JluneDeveloperAccess::isGranted() ? ' 🔒' : '' }}
                                    </a>
                                </li>
                            @endif
                            <li>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full text-left rounded-2xl px-3 py-2.5 text-xs font-bold hover:bg-slate-800 transition-colors text-slate-500">
                                        <span class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center">🚪</span> Esci ({{ auth()->user()->email }})
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- CONTENUTO PRINCIPALE (Si sposta a destra su PC) -->
    <div class="md:pl-64 flex flex-col h-screen">
        
        <!-- TOPBAR BIANCA (Visibile solo su MOBILE per aprire il menu) -->
        <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm md:hidden">
            <button type="button" class="-m-2.5 p-2.5 text-gray-700" @click="sidebarOpen = true">
                <span class="sr-only">Apri sidebar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <div class="flex-1 text-lg font-black text-slate-900">{{ \App\Support\AppSettings::appName() }} <span class="text-indigo-600">Admin</span></div>
            <button type="button"
                    onclick="window.jlunePwaInstall && window.jlunePwaInstall.open('admin')"
                    class="shrink-0 rounded-full bg-slate-900 px-3 py-1.5 text-[10px] font-black uppercase text-white tracking-wide">
                📲 App
            </button>
        </div>

        <!-- AREA DOVE VENGONO CARICATE LE PAGINE (Dinamica) -->
        <main class="flex-1 overflow-y-auto bg-gray-50">
            <div class="sticky top-0 z-30 flex h-14 shrink-0 items-center justify-end border-b border-gray-200 bg-white/95 backdrop-blur px-4 md:px-8">
                @livewire('admin.notifications-bell')
            </div>
            <div class="p-4 sm:p-6 lg:p-8 pb-24 max-w-7xl mx-auto w-full">
                {{ $slot }}
            </div>
        </main>
    </div>

    <a href="{{ route('assistenza') }}"
       class="fixed bottom-5 right-5 z-[70] inline-flex items-center gap-2 px-5 py-3 rounded-full bg-indigo-600 text-white text-xs font-black uppercase tracking-wide shadow-lg shadow-indigo-900/30 hover:bg-indigo-700">
        🎫 Ticket
    </a>
    @livewire('faq-widget', ['audience' => 'admin', 'position' => 'left'])

    @livewireScripts
    <x-pwa-install-modal channel="admin" />
</body>
</html>