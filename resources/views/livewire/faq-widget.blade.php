<div>
    @if ($open)
        <div class="fixed bottom-24 {{ $position === 'left' ? 'left-5' : 'right-5' }} z-[80] w-[92vw] max-w-sm h-[70vh] max-h-[520px] bg-white rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-gray-100">
            <div class="bg-emerald-600 text-white px-5 py-4 flex items-center justify-between shrink-0">
                <div>
                    <p class="font-black text-sm">Assistente Jlune</p>
                    <p class="text-emerald-100 text-xs">Domande frequenti</p>
                </div>
                <button type="button" wire:click="toggle" class="text-white/80 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2 bg-gray-50">
                @forelse ($suggestions as $entry)
                    <button type="button" wire:click="select({{ $entry->id }})"
                        class="w-full text-left px-4 py-3 rounded-2xl text-sm font-bold shadow-sm {{ $selected && $selected->id === $entry->id ? 'bg-emerald-600 text-white' : 'bg-white text-gray-800 hover:bg-emerald-50' }}">
                        {{ $entry->question }}
                    </button>

                    @if ($selected && $selected->id === $entry->id)
                        <div class="bg-white border border-emerald-100 rounded-2xl p-4 text-sm text-gray-700 shadow-sm">
                            <p class="whitespace-pre-wrap">{{ $selected->answer }}</p>
                            @if ($link = $this->resolveLink($selected))
                                <a href="{{ $link }}" class="inline-block mt-3 text-emerald-700 font-black underline">
                                    {{ $selected->link_label ?? 'Apri' }}
                                </a>
                            @endif
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-gray-400 italic text-center py-6">
                        {{ trim($query) === '' ? 'Scrivi qui sotto la tua domanda…' : 'Nessuna domanda simile trovata — prova a scrivere in modo diverso, oppure scrivici un ticket.' }}
                    </p>
                @endforelse
            </div>

            <div class="p-3 border-t border-gray-100 bg-white shrink-0">
                <input type="text" wire:model.live.debounce.300ms="query" placeholder="Scrivi la tua domanda…"
                    class="w-full rounded-full border-2 border-gray-200 text-sm px-4 py-3 focus:border-emerald-500 focus:ring-0">
            </div>
        </div>
    @endif

    <button type="button" wire:click="toggle"
        class="fixed bottom-5 {{ $position === 'left' ? 'left-5' : 'right-5' }} z-[70] w-14 h-14 rounded-full bg-emerald-600 text-white shadow-lg shadow-emerald-900/30 hover:bg-emerald-700 flex items-center justify-center text-2xl leading-none">
        {{ $open ? '×' : '💬' }}
    </button>
</div>
