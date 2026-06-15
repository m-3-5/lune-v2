<div>
    @if (session()->has('guide_message'))
        <div class="mb-4 text-sm font-bold text-green-700 bg-green-50 rounded-xl px-4 py-3 border border-green-200">
            {{ session('guide_message') }}
        </div>
    @endif

    <p class="text-sm text-gray-600 mb-4">
        Clicca un argomento per aprirlo. In fondo a ogni riquadro puoi inviare una domanda al team — arriva come ticket con notifica admin.
    </p>

    <div class="space-y-2">
        @foreach ($sections as $section)
            @php $open = $expandedId === $section['id']; @endphp
            <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm" wire:key="guide-{{ $section['id'] }}">
                <button type="button" wire:click="toggleSection('{{ $section['id'] }}')"
                    class="w-full text-left px-4 py-3 flex flex-wrap items-center gap-2 hover:bg-gray-50 transition-colors">
                    <span class="text-lg leading-none">{{ $section['icon'] }}</span>
                    <span class="font-bold text-sm text-gray-900 flex-1 min-w-0">{{ $section['title'] }}</span>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 shrink-0">
                        {{ $section['badge'] }}
                    </span>
                    <span class="text-gray-400 text-xs shrink-0">{{ $open ? '▲' : '▼' }}</span>
                </button>

                @if ($open)
                    <div class="px-4 pb-4 border-t border-gray-100">
                        <div class="prose prose-sm prose-indigo max-w-none text-gray-700 leading-relaxed mt-3
                            prose-headings:font-black prose-h3:text-base prose-h3:mt-4 prose-h3:mb-2
                            prose-strong:text-gray-900 prose-table:text-xs prose-li:my-0.5">
                            {!! \Illuminate\Support\Str::markdown($section['body']) !!}
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            @if ($askingSectionId === $section['id'])
                                <p class="text-xs font-black uppercase text-indigo-600 mb-2">Domanda su questo argomento</p>
                                <textarea wire:model="askQuestion" rows="3"
                                    placeholder="Scrivi cosa non ti è chiaro o cosa ti serve…"
                                    class="w-full rounded-xl border-gray-200 text-sm"></textarea>
                                @error('askQuestion')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <button type="button" wire:click="submitAsk"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">
                                        Invia al team
                                    </button>
                                    <button type="button" wire:click="cancelAsk"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold">
                                        Annulla
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-2">Max riceve notifica Telegram + push. Puoi seguire la risposta in «Task e avanzamenti» sotto.</p>
                            @else
                                <button type="button" wire:click="startAsk('{{ $section['id'] }}')"
                                    class="text-xs font-black uppercase text-indigo-600 hover:text-indigo-800 underline">
                                    Chiedi info su questo argomento →
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
