<div class="space-y-5">
    <div class="rounded-[2rem] p-5 text-white shadow-sm" style="background:linear-gradient(135deg,#4f46e5,#4338ca);">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black">Documenti</h2>
                <p class="text-indigo-100 text-xs mt-1">Un documento per ognuno dei {{ $totalGuests }} ospiti</p>
            </div>
            @if(!$isLocked)
                <span class="text-[10px] font-black uppercase bg-white/20 rounded-full px-3 py-1.5">✅ Pronto</span>
            @else
                <span class="text-[10px] font-black uppercase bg-white/20 rounded-full px-3 py-1.5">In corso</span>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        @foreach($guestSlots as $index => $slot)
            <div class="bg-white rounded-[1.75rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 flex items-center gap-3 border-b border-gray-50">
                    <div class="bg-indigo-100 text-indigo-700 rounded-full w-9 h-9 flex items-center justify-center font-black flex-shrink-0">{{ $index }}</div>
                    <input type="text" wire:model.blur="guestSlots.{{ $index }}.name" placeholder="Nome ospite {{ $index }}"
                        class="bg-transparent border-none focus:ring-0 text-gray-900 font-bold p-0 w-full text-sm" required>
                    @if($slot['is_approved'])
                        <span class="text-green-600 text-lg flex-shrink-0">✓</span>
                    @endif
                </div>

                <div class="px-4 pt-3">
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-500">
                        <input type="checkbox" wire:model.live="guestSlots.{{ $index }}.is_foreigner" class="rounded text-indigo-600 focus:ring-indigo-500">
                        Ospite straniero (niente codice fiscale)
                    </label>
                </div>

                <div class="p-4">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Carta d'identità o passaporto</p>
                    <div class="grid grid-cols-2 gap-3">
                        {{-- ID FRONTE --}}
                        <label class="relative flex flex-col items-center justify-center h-28 border-2 border-dashed rounded-2xl cursor-pointer overflow-hidden transition-colors {{ $slot['documents']['id_front']['status'] === 'approved' ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50 hover:bg-gray-100' }}">
                            @if(isset($uploads[$index]['id_front']))
                                @if(strtolower($uploads[$index]['id_front']->extension()) === 'pdf')
                                    <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-xl opacity-80">PDF</div>
                                @else
                                    <img src="{{ $uploads[$index]['id_front']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                @endif
                            @endif
                            <div class="relative z-10 flex flex-col items-center gap-1 text-center">
                                @if($slot['documents']['id_front']['status'] === 'approved')
                                    <span class="text-2xl">✅</span>
                                    <span class="text-[11px] font-black text-green-700 bg-white/80 px-2 rounded">Fronte caricato</span>
                                @else
                                    <span class="text-2xl">📷</span>
                                    <span class="text-[11px] font-bold text-gray-600">Tocca per il fronte</span>
                                @endif
                            </div>
                            <input type="file" wire:model.live="uploads.{{ $index }}.id_front" class="hidden" accept="image/*,.pdf">
                        </label>

                        {{-- ID RETRO --}}
                        @php $isFrontLoaded = isset($uploads[$index]['id_front']); @endphp
                        <label class="relative flex flex-col items-center justify-center h-28 border-2 border-dashed rounded-2xl transition-colors {{ $isFrontLoaded ? 'cursor-pointer border-gray-200 bg-gray-50 hover:bg-gray-100' : 'cursor-not-allowed border-gray-100 bg-gray-50 opacity-50' }} {{ $slot['documents']['id_back']['status'] === 'approved' ? 'border-green-400 bg-green-50' : '' }}">
                            @if(isset($uploads[$index]['id_back']))
                                @if(strtolower($uploads[$index]['id_back']->extension()) === 'pdf')
                                    <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-xl opacity-80">PDF</div>
                                @else
                                    <img src="{{ $uploads[$index]['id_back']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                @endif
                            @endif
                            <div class="relative z-10 flex flex-col items-center gap-1 text-center">
                                @if($slot['documents']['id_back']['status'] === 'approved')
                                    <span class="text-2xl">✅</span>
                                    <span class="text-[11px] font-black text-green-700 bg-white/80 px-2 rounded">Retro caricato</span>
                                @else
                                    <span class="text-2xl">📷</span>
                                    <span class="text-[11px] font-bold text-gray-600">{{ $isFrontLoaded ? 'Tocca per il retro' : 'Prima il fronte' }}</span>
                                @endif
                            </div>
                            <input type="file" wire:model.live="uploads.{{ $index }}.id_back" class="hidden" accept="image/*,.pdf" @if(!$isFrontLoaded) disabled @endif>
                        </label>
                    </div>
                </div>

                <div class="px-4 pb-4 {{ $slot['is_foreigner'] ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Tessera sanitaria <span class="normal-case font-normal text-gray-400">(facoltativa ora)</span></p>
                    <div class="grid grid-cols-2 gap-3">
                        {{-- CF FRONTE --}}
                        <label class="relative flex flex-col items-center justify-center h-24 border-2 border-dashed rounded-2xl cursor-pointer overflow-hidden transition-colors {{ $slot['documents']['tax_front']['status'] === 'approved' ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-gray-50 hover:bg-gray-100' }}">
                            @if(isset($uploads[$index]['tax_front']))
                                @if(strtolower($uploads[$index]['tax_front']->extension()) === 'pdf')
                                    <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-lg opacity-80">PDF</div>
                                @else
                                    <img src="{{ $uploads[$index]['tax_front']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                @endif
                            @endif
                            <div class="relative z-10 flex flex-col items-center text-center">
                                <span class="text-lg">{{ $slot['documents']['tax_front']['status'] === 'approved' ? '✅' : '📷' }}</span>
                                <span class="text-[10px] font-bold text-gray-600 bg-white/80 px-1 rounded">Fronte</span>
                            </div>
                            <input type="file" wire:model.live="uploads.{{ $index }}.tax_front" class="hidden" accept="image/*,.pdf">
                        </label>

                        {{-- CF RETRO --}}
                        <label class="relative flex flex-col items-center justify-center h-24 border-2 border-dashed rounded-2xl transition-colors {{ isset($uploads[$index]['tax_front']) ? 'cursor-pointer border-gray-200 bg-gray-50 hover:bg-gray-100' : 'cursor-not-allowed opacity-50' }} {{ $slot['documents']['tax_back']['status'] === 'approved' ? 'border-green-400 bg-green-50' : '' }}">
                            @if(isset($uploads[$index]['tax_back']))
                                @if(strtolower($uploads[$index]['tax_back']->extension()) === 'pdf')
                                    <div class="absolute inset-0 flex items-center justify-center bg-indigo-100 text-indigo-800 font-black text-lg opacity-80">PDF</div>
                                @else
                                    <img src="{{ $uploads[$index]['tax_back']->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80">
                                @endif
                            @endif
                            <div class="relative z-10 flex flex-col items-center text-center">
                                <span class="text-lg">{{ $slot['documents']['tax_back']['status'] === 'approved' ? '✅' : '📷' }}</span>
                                <span class="text-[10px] font-bold text-gray-600 bg-white/80 px-1 rounded">Retro</span>
                            </div>
                            <input type="file" wire:model.live="uploads.{{ $index }}.tax_back" class="hidden" accept="image/*,.pdf" @if(!isset($uploads[$index]['tax_front'])) disabled @endif>
                        </label>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="bg-white rounded-[1.75rem] border {{ !$isLocked ? 'border-green-300' : 'border-gray-100' }} shadow-sm p-6 text-center">
            @if(!$isLocked)
                <p class="text-3xl mb-2">🎉</p>
                <h3 class="text-base font-black text-green-900 mb-1">Tutto pronto</h3>
                <p class="text-xs text-gray-500 mb-5">Invia i documenti al gestore per la verifica.</p>
            @else
                <p class="text-3xl mb-2">📋</p>
                <h3 class="text-base font-black text-gray-700 mb-1">Manca qualche documento</h3>
                <p class="text-xs text-gray-500 mb-5">Carica fronte e retro della carta d'identità per ogni ospite.</p>
            @endif

            <button
                wire:click="salvaEProcedi"
                @if($isLocked) disabled @endif
                class="w-full py-4 rounded-2xl font-black text-sm uppercase tracking-wide transition-all {{ !$isLocked ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-md' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                {{ $isLocked ? 'In attesa dei documenti…' : 'Invia documenti al gestore' }}
            </button>
        </div>
    </div>
</div>
