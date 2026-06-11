<div>
    <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold mb-4 inline-block">← Torna alla Home</a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-3xl font-black text-indigo-950">📝 Testo contratto</h1>
            <p class="text-gray-500 mt-1 text-sm">Modifica le clausole del contratto come in un documento Word. Le modifiche valgono per tutti i contratti futuri.</p>
        </div>

        {{-- Selettore lingua --}}
        <div class="inline-flex rounded-lg border border-gray-300 bg-white p-1 shadow-sm self-start">
            <button type="button" wire:click="switchLocale('it')"
                class="px-4 py-1.5 rounded-md text-sm font-bold transition-colors {{ $locale === 'it' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                🇮🇹 Italiano
            </button>
            <button type="button" wire:click="switchLocale('en')"
                class="px-4 py-1.5 rounded-md text-sm font-bold transition-colors {{ $locale === 'en' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                🇬🇧 English
            </button>
        </div>
    </div>

    @if ($statusMessage)
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-semibold">
            ✅ {{ $statusMessage }}
        </div>
    @endif

    <div class="mb-4 rounded-lg border px-4 py-2 text-xs font-semibold inline-block {{ $isCustom ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-gray-50 border-gray-200 text-gray-600' }}">
        {{ $isCustom ? '✏️ Versione personalizzata attiva per ' . strtoupper($locale) : '📄 Testo predefinito attivo per ' . strtoupper($locale) }}
    </div>

    {{-- Legenda segnaposto --}}
    <div class="bg-sky-50 border border-sky-200 rounded-lg p-4 mb-4 text-sm">
        <p class="font-bold text-sky-900 mb-2">Segnaposto automatici</p>
        <p class="text-sky-800 text-xs mb-3">Questi codici vengono sostituiti automaticamente con i dati reali di ogni prenotazione. Puoi spostarli o riutilizzarli nel testo, ma non modificarne la scrittura.</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($placeholders as $code => $description)
                <span class="inline-flex items-center gap-1 bg-white border border-sky-200 rounded px-2 py-1 text-xs">
                    <code class="font-mono font-bold text-sky-700">{{ $code }}</code>
                    <span class="text-gray-500">{{ $description }}</span>
                </span>
            @endforeach
        </div>
    </div>

    {{-- Editor --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden" wire:ignore>
        <div id="contract-editor" class="text-sm">{!! $bodyHtml !!}</div>
    </div>

    <div class="flex flex-wrap items-center gap-3 mt-4">
        <button type="button"
            x-on:click="$wire.save(window.__contractQuill.getSemanticHTML())"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-indigo-700 transition-colors">
            💾 Salva testo ({{ strtoupper($locale) }})
        </button>

        <button type="button"
            wire:click="restoreDefault"
            wire:confirm="Vuoi davvero ripristinare il testo predefinito? Le modifiche per questa lingua andranno perse."
            class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
            ↩️ Ripristina testo predefinito
        </button>

        <button type="button"
            wire:click="$toggle('showPreview')"
            class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
            👁️ {{ $showPreview ? 'Nascondi anteprima' : 'Mostra anteprima' }}
        </button>
    </div>

    <p class="text-xs text-gray-400 mt-2">Ricordati di salvare prima di cambiare lingua o uscire dalla pagina.</p>

    {{-- Anteprima --}}
    @if ($showPreview)
        <div class="mt-6">
            <h2 class="text-lg font-bold text-indigo-950 mb-2">Anteprima con dati reali (ultima prenotazione)</h2>
            @if ($previewHtml)
                <div class="bg-white rounded-xl shadow border border-gray-200 p-6 max-h-[36rem] overflow-y-auto">
                    {!! $previewHtml !!}
                </div>
            @else
                <p class="text-sm text-gray-500 italic">Nessuna prenotazione disponibile per l'anteprima.</p>
            @endif
        </div>
    @endif
</div>

@assets
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<style>
    #contract-editor { min-height: 420px; }
    #contract-editor .ql-editor { min-height: 420px; font-size: 0.875rem; line-height: 1.6; }
    .ql-toolbar.ql-snow { border: none; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
    .ql-container.ql-snow { border: none; }
</style>
@endassets

@script
<script>
    const quill = new Quill('#contract-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['clean'],
            ],
        },
    });

    window.__contractQuill = quill;

    $wire.on('contract-body-loaded', ({ html }) => {
        quill.clipboard.dangerouslyPasteHTML(html ?? '');
    });
</script>
@endscript
