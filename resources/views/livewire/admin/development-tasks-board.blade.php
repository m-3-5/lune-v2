<div>
    @if (session()->has('tasks_message'))
        <div class="mb-4 text-sm font-bold text-green-700 bg-green-50 rounded-xl px-4 py-2">{{ session('tasks_message') }}</div>
    @endif

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ([
            'active' => 'In corso',
            'serenella' => 'Per Serenella',
            'production' => 'Produzione',
            'done' => 'Completati',
            'all' => 'Tutti',
        ] as $key => $label)
            <button type="button" wire:click="setFilter('{{ $key }}')"
                class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="bg-gray-50 rounded-2xl p-4 mb-6 border border-gray-100">
        <p class="text-xs font-black uppercase text-gray-400 mb-2">
            {{ $developerMode ? 'Nuova voce (team)' : 'Nuova richiesta per il team' }}
        </p>
        @if ($developerMode)
            <select wire:model="newType" class="mb-2 rounded-lg border-gray-200 text-sm w-full max-w-xs">
                <option value="production_task">Task produzione</option>
                <option value="question_for_serenella">Domanda per Serenella</option>
                <option value="serenella_request">Richiesta (come Serenella)</option>
            </select>
        @endif
        <input type="text" wire:model="newTitle" placeholder="Titolo breve" class="w-full rounded-xl border-gray-200 text-sm mb-2" />
        <textarea wire:model="newBody" rows="2" placeholder="Dettaglio (opzionale)" class="w-full rounded-xl border-gray-200 text-sm mb-2"></textarea>
        <button type="button" wire:click="createItem"
            class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">Aggiungi</button>
    </div>

    <ul class="space-y-3">
        @forelse ($items as $item)
            <li class="border border-gray-100 rounded-2xl overflow-hidden bg-white shadow-sm">
                <button type="button" wire:click="toggleExpanded({{ $item->id }})" class="w-full text-left px-4 py-3 flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $item->statusColor() }}">{{ $item->statusLabel() }}</span>
                    <span class="text-[10px] font-bold text-indigo-600 uppercase">{{ $item->typeLabel() }}</span>
                    <span class="font-bold text-sm flex-1 min-w-0">{{ $item->title }}</span>
                </button>
                @if ($expandedId === $item->id)
                    <div class="px-4 pb-4 border-t border-gray-50 text-sm space-y-3">
                        @if ($item->body)
                            <p class="text-gray-600 whitespace-pre-wrap">{{ $item->body }}</p>
                        @endif
                        @if ($item->test_instructions)
                            <p class="text-xs text-green-800 bg-green-50 rounded-xl p-3"><span class="font-black">Test:</span> {{ $item->test_instructions }}</p>
                        @endif
                        @if ($developerMode)
                            <label class="block text-[10px] font-black uppercase text-gray-400">Istruzioni test (Telegram + notifica telefono)</label>
                            <textarea wire:model="testInstructions" rows="3" class="w-full rounded-xl border-gray-200 text-sm"
                                placeholder="Es: Apri /admin/progetto → verifica task…"></textarea>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="setStatus({{ $item->id }}, 'open')"
                                    class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $item->status === 'open' ? 'bg-indigo-600 text-white' : 'bg-gray-100' }}">Da fare</button>
                                <button type="button" wire:click="setStatus({{ $item->id }}, 'in_progress')"
                                    class="px-2 py-1 rounded-lg text-[10px] font-bold {{ $item->status === 'in_progress' ? 'bg-indigo-600 text-white' : 'bg-gray-100' }}">In corso</button>
                                <button type="button" wire:click="completeItem({{ $item->id }})"
                                    class="px-3 py-1 rounded-lg text-[10px] font-black bg-green-600 text-white">Completata → invia notifica</button>
                                <button type="button" wire:click="deleteItem({{ $item->id }})" wire:confirm="Eliminare definitivamente questa voce?"
                                    class="px-3 py-1 rounded-lg text-[10px] font-black bg-red-50 text-red-600">Elimina</button>
                            </div>
                        @endif
                        @foreach ($item->replies as $reply)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">{{ $reply->author === 'team' ? 'Team' : 'Serenella' }}</p>
                                <p class="whitespace-pre-wrap">{{ $reply->body }}</p>
                            </div>
                        @endforeach
                        <div>
                            <textarea wire:model="replyBody" rows="2" class="w-full rounded-xl border-gray-200 text-sm" placeholder="Scrivi una risposta…"></textarea>
                            <button type="button" wire:click="addReply({{ $item->id }})"
                                class="mt-2 px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold">Rispondi</button>
                        </div>
                    </div>
                @endif
            </li>
        @empty
            <li class="text-sm text-gray-400 italic py-6 text-center">Nessuna voce in questo filtro.</li>
        @endforelse
    </ul>
</div>
