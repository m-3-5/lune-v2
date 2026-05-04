<div class="min-h-screen bg-gray-50 pb-32">
    <!-- Header: Più largo su Desktop -->
    <div class="bg-white p-4 shadow-sm border-b sticky top-0 z-10">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.arrivi') }}" class="text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-lg font-black text-indigo-900 uppercase tracking-tighter">Controllo Documenti</h1>
            </div>
            @if($reservation->documents_validated)
                <span class="bg-green-500 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase shadow-lg shadow-green-200">Validata ✓</span>
            @endif
        </div>
    </div>

    <div class="p-6 max-w-4xl mx-auto space-y-8">
        
        <!-- Card Info Ospite: Espandibile -->
        <div class="bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-indigo-300 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Prenotazione</p>
                <h2 class="text-3xl font-black mb-1">{{ $reservation->guest_name }}</h2>
                <p class="text-indigo-200 font-medium italic text-lg">{{ $reservation->apartment->name }}</p>
            </div>
            <svg class="absolute right-[-20px] bottom-[-20px] w-48 h-48 text-white opacity-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2H7a1 1 0 100-2h.01zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
        </div>

        <!-- LISTA DOCUMENTI -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($reservation->guestDocuments as $doc)
                <div class="bg-white rounded-[2rem] p-4 shadow-sm border {{ $doc->status === 'approved' ? 'border-green-500' : ($doc->status === 'rejected' ? 'border-red-500' : 'border-gray-100') }} transition-all">
                    <div class="flex justify-between items-center mb-4 px-2">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                        
                        <!-- Pulsanti di Stato Singoli -->
                        <div class="flex gap-2">
                            <button wire:click="setDocumentStatus({{ $doc->id }}, 'rejected')" class="p-2 rounded-full {{ $doc->status === 'rejected' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <button wire:click="setDocumentStatus({{ $doc->id }}, 'approved')" class="p-2 rounded-full {{ $doc->status === 'approved' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="rounded-2xl overflow-hidden bg-gray-100 shadow-inner">
                        <img src="{{ asset('storage/' . $doc->file_path) }}" class="w-full h-64 object-cover cursor-pointer hover:scale-105 transition-transform" onclick="window.open(this.src)">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Toolbar in basso: ora centrata e "fluttuante" su desktop -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-md px-6 z-20">
        <div class="bg-white/80 backdrop-blur-xl border p-4 rounded-[2.5rem] shadow-2xl flex gap-3">
            <button class="flex-1 bg-gray-100 text-gray-400 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest">Rifiuta Tutto</button>
            <button wire:click="approvaTutto" class="flex-[2] bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg shadow-indigo-200">Approva Tutto</button>
        </div>
    </div>
</div>