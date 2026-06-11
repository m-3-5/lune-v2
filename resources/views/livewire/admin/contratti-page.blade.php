<div>
    <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold mb-4 inline-block">← Torna alla Home</a>

    <div class="mb-6">
        <h1 class="text-3xl font-black text-indigo-950">📄 Contratti firmati</h1>
        <p class="text-gray-500 mt-1 text-sm">Archivio dei contratti firmati elettronicamente dagli ospiti, con copia PDF scaricabile.</p>
    </div>

    @if ($statusMessage)
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-semibold">
            {{ $statusMessage }}
        </div>
    @endif

    @if ($signed->isEmpty())
        <div class="bg-white rounded-xl shadow border border-gray-200 p-10 text-center">
            <p class="text-gray-500 italic">Nessun contratto firmato finora. Quando un ospite firma, il contratto comparirà qui automaticamente.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($signed as $reservation)
                <div class="bg-white rounded-xl shadow border border-gray-200 p-4 flex flex-col md:flex-row md:items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-black text-indigo-950">{{ $reservation->guestDisplayName() }}</span>
                            <span class="text-xs font-mono bg-gray-100 border border-gray-200 rounded px-1.5 py-0.5">{{ $reservation->booking_code }}</span>
                            @if ($reservation->is_test)
                                <span class="text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 rounded px-1.5 py-0.5">TEST</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $reservation->apartment?->name ?? 'Appartamento' }}
                            · {{ $reservation->check_in?->format('d/m/Y') }} → {{ $reservation->check_out?->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-emerald-700 font-semibold mt-1">
                            ✍️ Firmato il {{ $reservation->contract_accepted_at?->format('d/m/Y H:i') ?? '—' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($reservation->contract_pdf_path)
                            <a href="{{ route('admin.contratti.pdf', $reservation) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-indigo-700 transition-colors">
                                ⬇️ Scarica PDF
                            </a>
                        @else
                            <button type="button" wire:click="regeneratePdf({{ $reservation->id }})"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                                🔄 Genera PDF
                            </button>
                        @endif
                        <a href="{{ route('admin.arrivi.show', $reservation->id) }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                            👁️ Dettaglio
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
