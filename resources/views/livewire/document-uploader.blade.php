<div class="space-y-8">
    <div class="flex items-center justify-between bg-indigo-50 p-5 rounded-2xl border border-indigo-100 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-indigo-900">Documenti Ospiti</h2>
            <p class="text-sm text-indigo-700">Carica i documenti per le {{ $totalGuests }} persone previste</p>
        </div>
        <div>
            @if(!$isLocked)
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">Sbloccato</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-800">Bloccato</span>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @foreach($guestSlots as $index => $slot)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3 w-full">
                        <div class="bg-indigo-100 text-indigo-600 rounded-full w-8 h-8 flex items-center justify-center font-bold flex-shrink-0">{{ $index }}</div>
                        <input type="text" wire:model.blur="guestSlots.{{ $index }}.name" placeholder="Nome Ospite {{ $index }}" class="bg-transparent border-none focus:ring-0 text-gray-900 font-semibold p-0 w-full" required>
                    </div>
                    <div class="flex items-center gap-2 bg-amber-50 p-2 rounded-lg border border-amber-100 whitespace-nowrap ml-2 flex-shrink-0">
                        <input type="checkbox" wire:model.live="guestSlots.{{ $index }}.is_foreigner" id="foreigner-{{ $index }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <label for="foreigner-{{ $index }}" class="text-[10px] sm:text-xs font-bold text-amber-800 uppercase tracking-wide cursor-pointer">Straniero (No CF)</label>
                    </div>
                </div>

                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-gray-700">Carta d'Identità / Passaporto</h4>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- ID FRONTE --}}
                            <label class="relative flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-xl cursor-pointer overflow-hidden transition-colors {{ $slot['documents']['id_front']['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:bg-gray-50' }}">
                                @if(isset($uploads[$index]['id_front']))
                                    @if(strtolower($uploads[$index]['id_front']->extension()) === 'pdf')
                                        <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-2xl opacity-80">PDF</div>
                                    @else
                                        <img src="{{ $uploads[$index]['id_front']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                    @endif
                                @endif
                                <div class="relative z-10 flex flex-col items-center p-2 text-center bg-white/80 rounded px-2">
                                    <span class="text-xs font-semibold text-gray-800">Fronte</span>
                                    @if($slot['documents']['id_front']['status'] === 'approved')
                                        <span class="text-xs text-green-700 font-bold">✓ Ok</span>
                                    @endif
                                </div>
                                <input type="file" wire:model.live="uploads.{{ $index }}.id_front" class="hidden" accept="image/*,.pdf">
                            </label>

                            {{-- ID RETRO --}}
                            @php $isFrontLoaded = isset($uploads[$index]['id_front']); @endphp
                            <label class="relative flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-xl transition-colors {{ $isFrontLoaded ? 'cursor-pointer border-gray-300 hover:bg-gray-50' : 'cursor-not-allowed border-gray-200 bg-gray-100 opacity-60' }} {{ $slot['documents']['id_back']['status'] === 'approved' ? 'border-green-500 bg-green-50' : '' }}">
                                @if(isset($uploads[$index]['id_back']))
                                    @if(strtolower($uploads[$index]['id_back']->extension()) === 'pdf')
                                        <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-2xl opacity-80">PDF</div>
                                    @else
                                        <img src="{{ $uploads[$index]['id_back']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                    @endif
                                @endif
                                <div class="relative z-10 flex flex-col items-center p-2 text-center bg-white/80 rounded px-2">
                                    <span class="text-xs font-semibold text-gray-800">Retro</span>
                                    @if($slot['documents']['id_back']['status'] === 'approved')
                                        <span class="text-xs text-green-700 font-bold">✓ Ok</span>
                                    @endif
                                </div>
                                <input type="file" wire:model.live="uploads.{{ $index }}.id_back" class="hidden" accept="image/*,.pdf" @if(!$isFrontLoaded) disabled @endif>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-3 {{ $slot['is_foreigner'] ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                        <h4 class="text-sm font-bold text-gray-700">Codice Fiscale</h4>
                        <div class="grid grid-cols-2 gap-3">
                            {{-- CF FRONTE --}}
                            <label class="relative flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-xl cursor-pointer overflow-hidden transition-colors {{ $slot['documents']['tax_front']['status'] === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:bg-gray-50' }}">
                                @if(isset($uploads[$index]['tax_front']))
                                    @if(strtolower($uploads[$index]['tax_front']->extension()) === 'pdf')
                                        <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-2xl opacity-80">PDF</div>
                                    @else
                                        <img src="{{ $uploads[$index]['tax_front']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                    @endif
                                @endif
                                <div class="relative z-10 flex flex-col items-center p-2 bg-white/80 rounded text-center">
                                    <span class="text-xs font-semibold text-gray-800">Fronte</span>
                                    @if($slot['documents']['tax_front']['status'] === 'approved')
                                        <span class="text-xs text-green-700 font-bold">✓ Ok</span>
                                    @endif
                                </div>
                                <input type="file" wire:model.live="uploads.{{ $index }}.tax_front" class="hidden" accept="image/*,.pdf">
                            </label>

                            {{-- CF RETRO --}}
                            <label class="relative flex flex-col items-center justify-center h-32 border-2 border-dashed rounded-xl transition-colors {{ isset($uploads[$index]['tax_front']) ? 'cursor-pointer border-gray-300 hover:bg-gray-50' : 'cursor-not-allowed' }} {{ $slot['documents']['tax_back']['status'] === 'approved' ? 'border-green-500 bg-green-50' : '' }}">
                                @if(isset($uploads[$index]['tax_back']))
                                    @if(strtolower($uploads[$index]['tax_back']->extension()) === 'pdf')
                                        <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-2xl opacity-80">PDF</div>
                                    @else
                                        <img src="{{ $uploads[$index]['tax_back']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                    @endif
                                @endif
                                <div class="relative z-10 flex flex-col items-center p-2 bg-white/80 rounded text-center">
                                    <span class="text-xs font-semibold text-gray-800">Retro</span>
                                    @if($slot['documents']['tax_back']['status'] === 'approved')
                                        <span class="text-xs text-green-700 font-bold">✓ Ok</span>
                                    @endif
                                </div>
                                <input type="file" wire:model.live="uploads.{{ $index }}.tax_back" class="hidden" accept="image/*,.pdf" @if(!isset($uploads[$index]['tax_front'])) disabled @endif>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-8 border-t border-gray-200 pt-8">
            <div class="bg-gray-50 border {{ !$isLocked ? 'border-green-400 bg-green-50' : 'border-gray-200' }} rounded-2xl p-6 text-center transition-colors duration-300">
                
                @if(!$isLocked)
                    <h3 class="text-xl font-bold text-green-900 mb-2">Documentazione Pronta</h3>
                    <p class="text-sm text-green-700 mb-6">Ottimo lavoro. I file verranno inviati in modo sicuro a Serenella.</p>
                @else
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Completamento richiesto</h3>
                    <p class="text-sm text-gray-500 mb-6">Carica tutti i documenti obbligatori (Foto o PDF) per sbloccare il tasto.</p>
                @endif
                
                <button 
                    wire:click="salvaEProcedi" 
                    @if($isLocked) disabled @endif
                    class="inline-flex items-center justify-center px-8 py-4 font-bold text-white rounded-xl transition-all w-full md:w-auto {{ !$isLocked ? 'bg-indigo-600 hover:bg-indigo-700 shadow-md transform hover:scale-105' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
                    
                    @if($isLocked)
                        <svg class="w-5 h-5 mr-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                        In attesa dei documenti...
                    @else
                        Invia a Serenella e vai al Contratto
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>