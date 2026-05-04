<x-layouts.app title="Gestione JLune">
    <div class="min-h-screen bg-gray-50 pb-20">
        
        <!-- Header stile App -->
        <div class="bg-indigo-700 p-6 rounded-b-[2.5rem] shadow-lg mb-8">
            <div class="max-w-md mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-black text-white uppercase tracking-tight">Area Serenella</h1>
                    <p class="text-indigo-100 text-sm">Gestione appartamenti e documenti</p>
                </div>
                <!-- Icona profilo o stato -->
                <div class="w-10 h-10 bg-indigo-600 rounded-full border-2 border-indigo-400 flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
        </div>

        <div class="max-w-md mx-auto px-4 space-y-6">
            
            <!-- CONTATORE NOTIFICHE RAPIDE (Ora Cliccabile) -->
            <a href="{{ route('admin.arrivi') }}" class="block transform active:scale-95 transition-transform">
                <div class="bg-red-500 rounded-3xl p-5 text-white flex items-center justify-between shadow-lg shadow-red-200">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <svg class="w-8 h-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                            </span>
                        </div>
                        <div>
                            <p class="font-black text-lg leading-none italic">2 ALERT</p>
                            <p class="text-xs opacity-90 uppercase font-bold tracking-wider">Documenti da controllare</p>
                        </div>
                    </div>
                    <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

            <!-- GRIGLIA MENU PRINCIPALE -->
            <div class="grid grid-cols-2 gap-4">
                
                <!-- BOTTONE: ELENCO ARRIVI -->
                <a href="{{ route('admin.arrivi') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center group active:scale-95 transition-all">
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Arrivi e<br>Documenti</span>
                </a>

                <!-- BOTTONE: VIDEO TUTORIAL -->
                <a href="{{ route('admin.video') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center active:scale-95 transition-all group">
                    <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Gestione<br>Video QR</span>
                </a>

                <!-- BOTTONE: CONTRATTI -->
                <a href="{{ route('admin.contratti') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center active:scale-95 transition-all group">
                    <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Archivio<br>Contratti</span>
                </a>

                <!-- BOTTONE: IMPOSTAZIONI -->
                <a href="{{ route('admin.configura') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center active:scale-95 transition-all group">
                    <div class="w-14 h-14 bg-gray-100 text-gray-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-gray-800 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Configura<br>App</span>
                </a>
            </div>

            <!-- MODULO ULTIMI CARICAMENTI -->
            <div class="mt-10">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h2 class="text-xl font-black text-indigo-950 uppercase tracking-tight italic">Ultimi Caricamenti</h2>
                    <a href="{{ route('admin.arrivi') }}" class="text-[10px] font-black text-indigo-600 underline uppercase tracking-widest hover:text-indigo-800">
                        Vedi tutto
                    </a>
                </div>
                
                <!-- Questo componente mostra l'anteprima delle prenotazioni -->
                <div class="bg-white rounded-[2.5rem] p-2 shadow-sm border border-gray-100">
                    @livewire('admin.reservations-module')
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>