<div>
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl mb-6 text-sm font-bold border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex gap-3 mb-6">
        <button wire:click="setViewMode('active')" 
            class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $viewMode === 'active' ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600' }}">
            Arrivi
        </button>
        <button wire:click="setViewMode('cancelled')" 
            class="px-5 py-2.5 rounded-full text-[11px] font-black uppercase tracking-widest transition-all duration-300 {{ $viewMode === 'cancelled' ? 'bg-red-500 text-white shadow-md' : 'bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600' }}">
            Cancellate
        </button>
    </div>

    <div class="space-y-4">
        @forelse($reservations as $res)
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between transition-all hover:shadow-md">
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-xl 
                        {{ $viewMode === 'cancelled' ? 'bg-red-400' : 'bg-indigo-600' }}">
                        {{ substr($res->guest_name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-black leading-none {{ $viewMode === 'cancelled' ? 'text-gray-500 line-through' : 'text-indigo-950' }}">
                            {{ $res->guest_name }}
                        </h4>
                        <p class="text-xs font-bold uppercase mt-1 {{ $viewMode === 'cancelled' ? 'text-gray-400' : 'text-indigo-500' }}">
                            {{ $res->apartment->name ?? 'Appartamento' }}
                        </p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">
                            {{ $res->check_in->format('d/m/Y') }} → {{ $res->check_out->format('d/m/Y') }}
                        </p>
                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $res->booking_code }} · {{ $res->checkfront_status }}</p>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    
                    @if($viewMode === 'cancelled')
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                            Cancellata
                        </span>
                        
                        <button wire:click="deleteReservation({{ $res->id }})" 
                                wire:confirm="Sei sicuro di voler eliminare DEFINITIVAMENTE questa prenotazione dal sistema?"
                                class="text-red-500 font-black text-sm uppercase underline tracking-widest hover:text-red-700 mt-1">
                            Elimina
                        </button>
                    @else
                        @if($res->is_paid)
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                {{ $res->paymentLabel() }}
                            </span>
                        @else
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                Non pagato
                            </span>
                        @endif
                        @if($res->documents_validated)
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                Documenti OK
                            </span>
                        @elseif($res->hasDocumentsPendingReview())
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                Doc. da verificare
                            </span>
                        @elseif($res->is_paid)
                            <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter">
                                Attesa documenti
                            </span>
                        @endif
                        
                        <a href="{{ route('admin.arrivi.show', $res->id) }}" 
                           class="text-indigo-600 font-black text-sm uppercase underline tracking-widest hover:text-indigo-800 mt-1">
                            Apri
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-400 font-bold uppercase text-xs tracking-[0.2em] bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                @if($viewMode === 'cancelled')
                    Nessuna prenotazione cancellata
                @else
                    Nessuna prenotazione attiva
                @endif
            </div>
        @endforelse
    </div>
</div>