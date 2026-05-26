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
                <h2 class="text-3xl font-black mb-1">{{ $reservation->guestDisplayName() }}</h2>
                <p class="text-indigo-200 font-medium italic text-lg">{{ $reservation->apartment->name }}</p>
                <p class="text-indigo-300 text-xs mt-2">
                    Checkfront #{{ $reservation->checkfront_booking_id }} · {{ $reservation->checkfront_status ?? '—' }} · {{ $reservation->paymentLabel() }}
                </p>
                @if($reservation->guest_email || $reservation->guest_phone)
                    <p class="text-indigo-200 text-sm mt-2">
                        @if($reservation->guest_email)<a href="mailto:{{ $reservation->guest_email }}" class="underline">{{ $reservation->guest_email }}</a>@endif
                        @if($reservation->guest_phone) · {{ $reservation->guest_phone }}@endif
                    </p>
                @endif
            </div>
            <svg class="absolute right-[-20px] bottom-[-20px] w-48 h-48 text-white opacity-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2H7a1 1 0 100-2h.01zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
        </div>

        <!-- Dati Checkfront completi -->
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 space-y-4">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-400">Dati Checkfront</h3>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <span class="text-gray-400 text-xs uppercase font-bold">Codice</span>
                    <p class="font-bold">{{ $reservation->booking_code ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-400 text-xs uppercase font-bold">Cliente CF</span>
                    <p class="font-mono text-xs">{{ $reservation->checkfront_customer_code ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-400 text-xs uppercase font-bold">Notti</span>
                    <p class="font-bold">{{ $reservation->nightsCount() }}</p>
                </div>
                <div>
                    <span class="text-gray-400 text-xs uppercase font-bold">Ospiti</span>
                    <p class="font-bold">{{ $reservation->checkfrontField('numpax', $reservation->adults) }}</p>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-400 text-xs uppercase font-bold">Letti</span>
                    <p class="font-bold">{{ $reservation->checkfrontField('queen', '—') }}</p>
                </div>
            </div>

            @if($reservation->checkfrontField('note'))
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-sm">
                    <span class="text-[10px] font-black uppercase text-amber-800">Note ospite</span>
                    <p>{{ $reservation->checkfrontField('note') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3 text-sm border-t pt-3">
                <div><span class="text-gray-400 text-xs uppercase font-bold">Subtotale</span><p>€ {{ number_format($reservation->sub_total ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Tasse</span><p>€ {{ number_format($reservation->tax_total ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Totale</span><p class="font-bold">€ {{ number_format($reservation->total_price ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Pagato</span><p class="font-bold text-green-700">€ {{ number_format($reservation->paid_total ?? 0, 2, ',', '.') }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase font-bold">Saldo</span><p class="font-bold text-amber-700">€ {{ number_format($reservation->balance ?? 0, 2, ',', '.') }}</p></div>
            </div>

            @if(count($reservation->extraLineItems()) > 0)
                <div class="border-t pt-3">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Extra e servizi</p>
                    <ul class="text-sm space-y-2">
                        @foreach($reservation->extraLineItems() as $line)
                            <li class="flex justify-between bg-gray-50 rounded-lg px-3 py-2">
                                <span>{{ $line['label'] ?? $line['sku'] }}</span>
                                <span class="font-bold">€ {{ number_format((float) ($line['total'] ?? 0), 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(is_array($reservation->checkfront_taxes) && count($reservation->checkfront_taxes))
                <div class="border-t pt-3">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Dettaglio tasse / pulizie</p>
                    <ul class="text-xs text-gray-600 space-y-1">
                        @foreach($reservation->checkfront_taxes as $tax)
                            @if((float) ($tax['amount'] ?? 0) > 0)
                                <li>{{ $tax['name'] }} — € {{ $tax['amount'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="border-t pt-3 space-y-2">
                <div class="bg-gray-50 p-3 rounded-xl">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Link area ospite (Jlune App)</p>
                    <code class="text-xs break-all text-indigo-800">{{ $reservation->guest_portal_url }}</code>
                </div>
                @if($reservation->checkfront_payment_url)
                    <a href="{{ $reservation->checkfront_payment_url }}" target="_blank" rel="noopener"
                       class="inline-block text-xs font-bold text-indigo-600 underline">
                        Pagamento su Checkfront →
                    </a>
                @endif
            </div>
        </div>

        <!-- Contratto: estrazione IA + invio firma -->
        @if($reservation->documents_validated)
            @php $contracts = app(\App\Services\ContractRenderService::class); @endphp
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-indigo-100 space-y-4">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Contratto</h3>

                @if($reservation->contract_ready_for_guest)
                    <p class="text-sm text-green-700 font-bold">✓ Inviato per la firma ({{ $reservation->contract_locale === 'en' ? 'Inglese' : 'Italiano' }})</p>
                @elseif($reservation->extracted_guests)
                    <p class="text-sm text-amber-700">Anteprima IT/EN — scegli la lingua e invia all'ospite.</p>
                @else
                    <p class="text-sm text-gray-600">Estrai i dati dai documenti approvati, poi invia il contratto.</p>
                @endif

                @if (session()->has('message'))
                    <div class="bg-green-50 text-green-800 p-3 rounded-xl text-sm font-bold border border-green-200">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="flex flex-wrap gap-2 items-center">
                    <button wire:click="estraiDatiDocumenti" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest disabled:opacity-50">
                        <span wire:loading.remove wire:target="estraiDatiDocumenti">Estrai dati (Document AI)</span>
                        <span wire:loading wire:target="estraiDatiDocumenti">Analisi in corso (1–2 min)…</span>
                    </button>
                    <span class="text-[10px] text-gray-500">In locale: <code class="bg-gray-100 px-1 rounded">php artisan jlune:test-extraction {{ $reservation->id }}</code></span>
                </div>

                @if(is_array($reservation->extracted_guests))
                    @foreach($reservation->extracted_guests as $g)
                        @if(!empty($g['extraction_notes']))
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900">
                                <p class="font-black uppercase text-[9px] mb-1">Avvisi estrazione — Ospite {{ $g['slot'] ?? '?' }}</p>
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($g['extraction_notes'] as $note)
                                        <li>{{ $note }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach

                    <div class="bg-slate-50 rounded-xl p-3 text-xs space-y-2">
                        <p class="font-black uppercase text-slate-500 text-[9px]">Dati per contratto (fonte: tessera + documenti)</p>
                        @foreach($reservation->extracted_guests as $g)
                            <p>
                                <strong>#{{ $g['slot'] }}</strong>:
                                {{ $g['data']['last_name'] ?? '—' }}
                                {{ $g['data']['first_name'] ?? '' }}
                                @if(!empty($g['data']['birth_date'])) · nato {{ $g['data']['birth_date'] }} @endif
                                @if(!empty($g['data']['tax_code'])) · CF {{ $g['data']['tax_code'] }} @endif
                                @if(!empty($g['data']['document_number'])) · doc {{ $g['data']['document_number'] }} @endif
                            </p>
                        @endforeach
                        <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-200">
                            <a href="{{ route('admin.arrivo.export', ['id' => $reservation->id, 'format' => 'json']) }}"
                               class="text-[10px] font-black uppercase text-indigo-600 underline">Esporta JSON</a>
                            <a href="{{ route('admin.arrivo.export', ['id' => $reservation->id, 'format' => 'csv']) }}"
                               class="text-[10px] font-black uppercase text-indigo-600 underline">Esporta CSV (Excel)</a>
                            <a href="{{ route('admin.arrivo.export', ['id' => $reservation->id, 'format' => 'xml']) }}"
                               class="text-[10px] font-black uppercase text-indigo-600 underline">Esporta XML</a>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-xs space-y-3">
                        <p class="font-black uppercase text-gray-500 text-[9px]">Codici fiscali (correzione manuale)</p>
                        @foreach($reservation->extracted_guests as $g)
                            @if(!($g['is_foreigner'] ?? false))
                                @php $slot = (int)($g['slot'] ?? 0); @endphp
                                <div class="flex items-center gap-2">
                                    <span class="w-16 font-bold">#{{ $slot }}</span>
                                    <input type="text" wire:model.blur="adminTaxCodes.{{ $slot }}"
                                        class="flex-1 font-mono uppercase text-xs border-gray-200 rounded-lg"
                                        placeholder="CF">
                                </div>
                            @endif
                        @endforeach
                        <button wire:click="saveAdminTaxCodes" type="button"
                            class="text-[10px] font-black uppercase text-indigo-600 underline">
                            Salva CF
                        </button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div class="border rounded-xl overflow-hidden">
                            <p class="bg-indigo-100 text-indigo-900 text-[10px] font-black uppercase px-3 py-2">Anteprima italiano</p>
                            <div class="p-3 max-h-48 overflow-y-auto text-xs bg-white">
                                {!! $contracts->html($reservation, 'it') !!}
                            </div>
                        </div>
                        <div class="border rounded-xl overflow-hidden">
                            <p class="bg-slate-100 text-slate-800 text-[10px] font-black uppercase px-3 py-2">Anteprima inglese</p>
                            <div class="p-3 max-h-48 overflow-y-auto text-xs bg-white">
                                {!! $contracts->html($reservation, 'en') !!}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 border-t pt-4">
                        <span class="text-[10px] font-black uppercase text-gray-500">Lingua per la firma:</span>
                        <label class="inline-flex items-center gap-1 text-sm font-bold">
                            <input type="radio" wire:model.live="contractLocaleToSend" value="it"> Italiano
                        </label>
                        <label class="inline-flex items-center gap-1 text-sm font-bold">
                            <input type="radio" wire:model.live="contractLocaleToSend" value="en"> English
                        </label>
                        <button wire:click="inviaContrattoPerFirma" wire:confirm="Inviare il contratto all'ospite per la firma?"
                            class="ml-auto px-5 py-3 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-green-200">
                            Contratto pronto — invia per la firma
                        </button>
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

                    @if($doc->ai_raw_response)
                        @php $ai = is_array($doc->ai_raw_response) ? $doc->ai_raw_response : json_decode($doc->ai_raw_response, true); @endphp
                        <div class="mx-2 mb-2 p-2 rounded-lg text-[10px] overflow-hidden {{ ($ai['status'] ?? '') === 'success' ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-900' }}">
                            <strong>IA:</strong> {{ $ai['status'] ?? '?' }} — {{ $ai['message'] ?? '' }}
                            @if(!empty($ai['extracted_data']))
                                <span class="block font-mono mt-1 break-all whitespace-pre-wrap max-w-full">{{ json_encode($ai['extracted_data'], JSON_UNESCAPED_UNICODE) }}</span>
                            @endif
                            @if(!empty($ai['ocr_preview']))
                                <details class="mt-2">
                                    <summary class="cursor-pointer font-bold text-[9px] uppercase">Testo OCR (come in log)</summary>
                                    <pre class="mt-1 whitespace-pre-wrap text-[9px] opacity-80">{{ $ai['ocr_preview'] }}</pre>
                                </details>
                            @endif
                        </div>
                    @endif

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