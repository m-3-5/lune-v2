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
        @if (session()->has('error'))
            <div class="bg-red-100 text-red-800 p-4 rounded-2xl text-sm font-bold border border-red-200">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Card Info Ospite -->
        <div class="bg-indigo-900 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-indigo-300 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Prenotazione {{ $reservation->booking_code ?? '—' }}</p>
                <h2 class="text-3xl font-black mb-1">{{ $reservation->guest_name }}</h2>
                <p class="text-indigo-200 font-medium italic text-lg">{{ $reservation->apartment->name }}</p>
                <p class="text-indigo-300 text-xs mt-2">Checkfront: {{ $reservation->checkfront_status ?? '—' }} · {{ $reservation->paymentLabel() }}</p>
            </div>
            <svg class="absolute right-[-20px] bottom-[-20px] w-48 h-48 text-white opacity-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2H7a1 1 0 100-2h.01zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
        </div>

        <!-- Economico + link ospite -->
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 space-y-4">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Checkfront & area ospite</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-400 text-xs uppercase font-bold">Totale</span><p class="font-bold">€ {{ number_format($reservation->total_price ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Pagato</span><p class="font-bold text-green-700">€ {{ number_format($reservation->paid_total ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Saldo</span><p class="font-bold text-amber-700">€ {{ number_format($reservation->balance ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Ospiti</span><p class="font-bold">{{ $reservation->adults }} adulti</p></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl">
                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Link segreto cliente</p>
                <code class="text-xs break-all text-indigo-800">{{ $reservation->guest_portal_url }}</code>
            </div>
            @if($reservation->checkfront_payment_url)
                <a href="{{ $reservation->checkfront_payment_url }}" target="_blank" class="text-xs font-bold text-indigo-600 underline">Apri pagamento Checkfront</a>
            @endif
            @if(is_array($reservation->checkfront_line_items) && count($reservation->checkfront_line_items))
                <div class="border-t pt-3">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Righe ordine</p>
                    <ul class="text-xs space-y-1">
                        @foreach($reservation->checkfront_line_items as $line)
                            <li class="flex justify-between gap-2">
                                <span>{{ $line['sku'] }} <span class="text-gray-400">(cat. {{ $line['category_id'] }})</span></span>
                                <span class="font-bold">€ {{ $line['total'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Contratto: estrazione IA + autorizzazione -->
        @if($reservation->documents_validated)
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-indigo-100 space-y-4">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Contratto (IA + approvazione)</h3>

                @if($reservation->contract_ready_for_guest)
                    <p class="text-sm text-green-700 font-bold">✓ Contratto visibile all'ospite ({{ $reservation->contract_locale === 'en' ? 'Inglese' : 'Italiano' }})</p>
                @elseif($reservation->extracted_guests)
                    <p class="text-sm text-amber-700">Dati estratti — in attesa della tua autorizzazione per l'ospite.</p>
                @else
                    <p class="text-sm text-gray-600">Estrai nome, data di nascita e codice fiscale dai documenti approvati.</p>
                @endif

                <div class="flex flex-wrap gap-2">
                    <button wire:click="estraiDatiDocumenti" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">
                        <span wire:loading.remove wire:target="estraiDatiDocumenti">Estrai dati (Google Document AI)</span>
                        <span wire:loading wire:target="estraiDatiDocumenti">Analisi in corso…</span>
                    </button>
                    @if($reservation->extracted_guests)
                        <button wire:click="autorizzaContrattoOspite"
                            class="px-4 py-2 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest">
                            Autorizza contratto per ospite
                        </button>
                    @endif
                </div>

                @if(is_array($reservation->extracted_guests))
                    <div class="bg-gray-50 rounded-xl p-4 text-xs space-y-2 max-h-48 overflow-y-auto">
                        @foreach($reservation->extracted_guests as $g)
                            <p>
                                <strong>#{{ $g['slot'] }}</strong>
                                {{ $g['data']['first_name'] ?? '?' }} {{ $g['data']['last_name'] ?? '' }}
                                @if($g['is_foreigner'])
                                    <span class="text-amber-700">(straniero)</span>
                                @else
                                    — CF: <span class="font-mono">{{ $g['data']['tax_code'] ?? '—' }}</span>
                                @endif
                            </p>
                            @if(!empty($g['extraction_notes']))
                                <p class="text-red-600">{{ implode(' · ', $g['extraction_notes']) }}</p>
                            @endif
                        @endforeach
                    </div>
                    <div class="border rounded-xl p-3 max-h-64 overflow-y-auto text-sm bg-white">
                        {!! app(\App\Services\ContractRenderService::class)->html($reservation) !!}
                    </div>
                @endif
            </div>
        @endif

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
            <button wire:click="rifiutaTutto" wire:confirm="Rifiutare tutti i documenti? L'ospite dovrà ricaricarli."
                class="flex-1 bg-gray-100 text-gray-600 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-gray-200">Rifiuta Tutto</button>
            <button wire:click="approvaTutto" class="flex-[2] bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg shadow-indigo-200">Approva Tutto</button>
        </div>
    </div>
</div>