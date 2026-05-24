<x-layouts.admin title="Gestione Jlune">
    @php
        $docsPending = \App\Models\AdminNotification::unreadCount();
    @endphp
    <div class="min-h-screen bg-gray-50 pb-20">

        <div class="bg-indigo-700 p-6 rounded-b-[2.5rem] shadow-lg mb-8">
            <div class="max-w-md mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-black text-white uppercase tracking-tight">Area Serenella</h1>
                    <p class="text-indigo-100 text-sm">Gestione appartamenti e documenti</p>
                </div>
            </div>
        </div>

        <div class="max-w-md mx-auto px-4 space-y-6">

            <div class="grid grid-cols-2 gap-4">

                <a href="{{ route('admin.arrivi') }}" class="relative bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center group active:scale-95 transition-all">
                    @if ($docsPending > 0)
                        <span class="absolute top-3 right-3 min-w-[1.25rem] h-5 px-1.5 rounded-full bg-amber-500 text-white text-[10px] font-black flex items-center justify-center">
                            {{ $docsPending }}
                        </span>
                    @endif
                    <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Arrivi e<br>Documenti</span>
                </a>

                <a href="{{ route('admin.video') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center active:scale-95 transition-all group">
                    <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Gestione<br>Video QR</span>
                </a>

                <a href="{{ route('admin.contratti') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center active:scale-95 transition-all group">
                    <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Archivio<br>Contratti</span>
                </a>

                <a href="{{ route('admin.configura') }}" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col items-center text-center active:scale-95 transition-all group">
                    <div class="w-14 h-14 bg-gray-100 text-gray-600 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-gray-800 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                    </div>
                    <span class="font-black text-gray-900 text-[11px] uppercase tracking-widest leading-tight">Configura<br>App</span>
                </a>
            </div>

            <div class="mt-6">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h2 class="text-xl font-black text-indigo-950 uppercase tracking-tight italic">Agenda</h2>
                    <a href="{{ route('admin.arrivi') }}" class="text-[10px] font-black text-indigo-600 underline uppercase tracking-widest hover:text-indigo-800">
                        Tutte le prenotazioni
                    </a>
                </div>

                <div class="bg-white rounded-[2.5rem] p-3 shadow-sm border border-gray-100">
                    @livewire('admin.reservations-module', ['context' => 'dashboard'])
                </div>
            </div>

        </div>
    </div>
</x-layouts.admin>
