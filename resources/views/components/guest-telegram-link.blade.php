@props(['reservation'])

@if (config('telegram.enabled') && filled(config('telegram.bot_token')))
    @php
        $linked = $reservation->telegramLinked();
        $link = $reservation->telegramDeepLink();
    @endphp
    @if ($link)
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm mb-4">
            <p class="font-black text-sky-950 mb-1">📲 Ricevi avvisi su Telegram</p>
            @if ($linked)
                <p class="text-sky-800 text-xs">
                    ✅ Telegram collegato il {{ $reservation->telegram_linked_at?->format('d/m/Y H:i') ?? '—' }}.
                    Riceverai qui i promemoria (documenti, contratto, soggiorno) se attivi dall'host.
                </p>
            @else
                <p class="text-sky-800 text-xs mb-3">
                    Apri Telegram e premi <strong>Avvia</strong> sul bot Jlune per collegare questa prenotazione.
                    Più comodo di email e WhatsApp sul telefono.
                </p>
                <a href="{{ $link }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-xl text-xs font-black uppercase tracking-wide hover:bg-sky-700">
                    Collega Telegram
                </a>
            @endif
        </div>
    @endif
@endif
