<div class="space-y-4">
    @forelse($reservations as $res)
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Iniziale Nome -->
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-black text-xl">
                    {{ substr($res->guest_name, 0, 1) }}
                </div>
                <div>
                    <h4 class="font-black text-indigo-950 leading-none">{{ $res->guest_name }}</h4>
                    <p class="text-xs font-bold text-indigo-500 uppercase mt-1">{{ $res->apartment->name ?? 'Appartamento' }}</p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Arrivo: {{ $res->check_in->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="flex flex-col items-end gap-2">
                @if($res->documents_validated)
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">Documenti OK</span>
                @else
                    <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">Da Verificare</span>
                @endif
                
                <!-- Tasto Azione Mobile -->
                <button class="text-indigo-600 font-black text-[10px] uppercase underline tracking-widest">
                   <a href="{{ route('admin.arrivi.show', $res->id) }}" class="text-indigo-600 font-black text-sm uppercase underline tracking-widest hover:text-indigo-800">
    Apri
</a>
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-[0.2em]">
            Nessuna prenotazione trovata
        </div>
    @endforelse
</div>