<div>
    <button type="button" wire:click="toggle"
        class="fixed bottom-5 {{ $position === 'left' ? 'left-5' : 'right-5' }} z-[70] inline-flex items-center gap-2 px-5 py-3 rounded-full bg-emerald-600 text-white text-xs font-black uppercase tracking-wide shadow-lg shadow-emerald-900/30 hover:bg-emerald-700">
        ❓ Aiuto
    </button>

    @if ($open)
        <div class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center bg-black/40 p-4" wire:click.self="toggle">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[80vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-black text-slate-900">Domande frequenti</h2>
                    <button type="button" wire:click="toggle" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>

                <input type="text" wire:model.live.debounce.300ms="query" placeholder="Scrivi la tua domanda…"
                    class="w-full rounded-lg border-2 border-gray-300 text-sm p-3 mb-4">

                <div class="space-y-2">
                    @forelse ($suggestions as $entry)
                        <button type="button" wire:click="select({{ $entry->id }})"
                            class="w-full text-left px-4 py-3 rounded-xl text-sm font-bold {{ $selected && $selected->id === $entry->id ? 'bg-emerald-600 text-white' : 'bg-gray-50 text-gray-800 hover:bg-gray-100' }}">
                            {{ $entry->question }}
                        </button>

                        @if ($selected && $selected->id === $entry->id)
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-sm text-emerald-900">
                                <p class="whitespace-pre-wrap">{{ $selected->answer }}</p>
                                @if ($link = $this->resolveLink($selected))
                                    <a href="{{ $link }}" class="inline-block mt-3 text-emerald-700 font-black underline">
                                        {{ $selected->link_label ?? 'Apri' }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-400 italic text-center py-4">Nessuna domanda simile trovata — prova a scrivere in modo diverso, oppure scrivici un ticket.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
