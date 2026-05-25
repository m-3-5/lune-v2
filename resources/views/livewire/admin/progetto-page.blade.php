<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Progetto Jlune</h1>
        <p class="text-gray-500 text-sm mt-1">Guida, costi e richieste di sviluppo.</p>
    </div>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <div class="prose prose-sm prose-indigo max-w-none text-gray-700 leading-relaxed
            prose-headings:font-black prose-h1:text-2xl prose-h2:text-lg prose-h2:mt-8 prose-h2:mb-3
            prose-strong:text-gray-900 prose-table:text-sm">
            {!! \Illuminate\Support\Str::markdown($appGuide) !!}
        </div>
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-4">Costi progetto</h2>
        <div class="flex flex-wrap gap-6 mb-4 text-sm">
            <div><span class="text-gray-400 text-xs font-black uppercase">Base</span><br><span class="text-xl font-black">€ {{ number_format($projectBaseCost, 2, ',', '.') }}</span></div>
            <div><span class="text-gray-400 text-xs font-black uppercase">Extra</span><br><span class="text-xl font-black">€ {{ number_format($extraSum, 2, ',', '.') }}</span></div>
            <div><span class="text-gray-400 text-xs font-black uppercase">Totale</span><br><span class="text-2xl font-black text-indigo-700">€ {{ number_format($totalCost, 2, ',', '.') }}</span></div>
        </div>
        @if (count($costEntries) > 0)
            <ul class="space-y-2 text-sm">
                @foreach ($costEntries as $entry)
                    <li class="flex justify-between bg-gray-50 rounded-xl px-4 py-2">
                        <span class="font-medium">{{ $entry['label'] }} @if(!empty($entry['date']))<span class="text-gray-400 text-xs">· {{ $entry['date'] }}</span>@endif</span>
                        <span class="font-black">€ {{ number_format((float) $entry['amount'], 2, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-50 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-2">Notifiche sul telefono</h2>
        <p class="text-sm text-gray-600 mb-3">
            Task e avvisi operativi (documenti, contratti…): installa come app (Aggiungi a schermata Home) e attiva qui.
            Con <strong>app in costruzione</strong> attiva, le anteprime ospite arrivano solo a te/Serenella (Telegram + push), non al cliente.
        </p>
        <x-pwa-push-register channel="admin" class="mb-0" />
    </section>

    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-2">Task e avanzamenti</h2>
        <p class="text-sm text-gray-600 mb-4">Aggiungi una richiesta, segui le attività in corso (tue e del team).</p>
        <livewire:admin.development-tasks-board :developer-mode="false" />
    </section>
</div>
