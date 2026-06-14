<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Jlune Admin">
    <link rel="manifest" href="/manifest-admin.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/admin-192.png">
    <link rel="apple-touch-icon" href="/icons/admin-192.png">
    <title>{{ $title ?? 'Jlune Gestione' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
                    <h1 class="text-2xl font-black tracking-widest uppercase text-indigo-400">Jlune Admin</h1>
                </div>
                <nav class="mt-5 h-full overflow-y-auto px-4 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">📊 Dashboard</a>
                    <a href="{{ route('admin.arrivi') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">📇 Arrivi e Documenti</a>
                    <a href="{{ route('admin.video') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">🎥 Gestione Video</a>
                    <a href="{{ route('admin.contratti') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">📄 Contratti</a>
                    <a href="{{ route('admin.testo-contratto') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">📝 Testo contratto</a>
                    <a href="{{ route('admin.prova') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">🧪 Prova flusso</a>
                    <a href="{{ route('admin.progetto') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800 mt-4 border-t border-slate-700 pt-4">📋 Progetto e task</a>
                    <a href="{{ route('admin.canali') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">📧 Canali di invio</a>
                    <a href="{{ route('admin.notifiche') }}" class="block px-3 py-2 rounded-md font-medium hover:bg-slate-800">🔔 Notifiche</a>
                    <a href="{{ route('admin.sviluppo') }}" class="block px-3 py-2 rounded-md text-xs font-medium {{ \App\Support\JluneDeveloperAccess::isGranted() ? 'text-slate-300' : 'text-slate-500' }} hover:bg-slate-800">
                        🔧 Sviluppo (team)@if (! \App\Support\JluneDeveloperAccess::isGranted()) 🔒@endif
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- SIDEBAR FISSA (SOLO DESKTOP) -->
    <div class="hidden md:fixed md:inset-y-0 md:z-50 md:flex md:w-64 md:flex-col">
        <div class="flex grow flex-col overflow-y-auto bg-slate-900 px-6 pb-4 shadow-2xl text-white">
            <div class="flex h-20 shrink-0 items-center border-b border-slate-700 mb-6">
                <h1 class="text-2xl font-black tracking-widest uppercase text-indigo-400">Jlune Admin</h1>
            </div>
            <nav class="flex flex-1 flex-col">
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul role="list" class="-mx-2 space-y-2">
                            <li><a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">📊 Dashboard</a></li>
                            <li><a href="{{ route('admin.arrivi') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">📇 Arrivi e Documenti</a></li>
                            <li><a href="{{ route('admin.video') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">🎥 Gestione Video</a></li>
                            <li><a href="{{ route('admin.contratti') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">📄 Contratti</a></li>
                            <li><a href="{{ route('admin.testo-contratto') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">📝 Testo contratto</a></li>
                            <li><a href="{{ route('admin.prova') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">🧪 Prova flusso</a></li>
                            <li><a href="{{ route('admin.progetto') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 border-t border-slate-700 pt-4 transition-colors">📋 Progetto e task</a></li>
                            <li><a href="{{ route('admin.canali') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">📧 Canali di invio</a></li>
                            <li><a href="{{ route('admin.notifiche') }}" class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-slate-800 transition-colors">🔔 Notifiche</a></li>
                            <li>
                                <a href="{{ route('admin.sviluppo') }}" class="block rounded-md px-3 py-2 text-xs font-semibold {{ \App\Support\JluneDeveloperAccess::isGranted() ? 'text-slate-300' : 'text-slate-500' }} hover:bg-slate-800 transition-colors">
                                    🔧 Sviluppo (team)@if (! \App\Support\JluneDeveloperAccess::isGranted()) 🔒@endif
                                </a>
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
            <div class="flex-1 text-lg font-bold text-slate-800">Pannello di Controllo</div>
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

    @livewireScripts
    <x-pwa-install-modal channel="admin" />
</body>
</html>