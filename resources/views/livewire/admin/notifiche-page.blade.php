<div class="max-w-4xl mx-auto space-y-6 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">🔔 Notifiche</h1>
    </div>

    {{-- Azioni rapide --}}
    <section class="flex flex-wrap gap-3">
        <a href="{{ route('admin.progetto') }}#notifiche"
           class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wide hover:bg-emerald-700">
            📋 Toggle e contatti
        </a>
        <a href="{{ route('admin.prova') }}"
           class="inline-flex items-center gap-2 px-5 py-3 bg-white border border-gray-300 text-gray-800 rounded-xl text-xs font-black uppercase tracking-wide hover:bg-gray-50">
            🧪 Prova flusso (TEST)
        </a>
    </section>

    {{-- Canali dedicati --}}
    <section class="grid sm:grid-cols-2 gap-4">
        @foreach ([
            ['route' => 'admin.notifiche.email', 'icon' => '📧', 'title' => 'Email', 'ready' => $mailReady, 'hint' => 'SMTP'],
            ['route' => 'admin.notifiche.whatsapp', 'icon' => '💬', 'title' => 'WhatsApp', 'ready' => $whatsappReady, 'hint' => 'Twilio — admin e ospiti'],
            ['route' => 'admin.notifiche.telegram', 'icon' => '✈️', 'title' => 'Telegram', 'ready' => $telegramBotReady, 'hint' => $telegramBotUsername ? '@'.$telegramBotUsername : 'Nessun bot configurato'],
            ['route' => 'admin.notifiche.push', 'icon' => '📲', 'title' => 'Web Push', 'ready' => config('webpush.enabled') && filled(config('webpush.vapid.public_key')), 'hint' => 'PWA installata'],
        ] as $ch)
            @if ($isSuperAdmin)
                <a href="{{ route($ch['route']) }}"
                   class="block bg-white rounded-2xl border p-5 shadow-sm hover:border-indigo-300 hover:shadow-md transition {{ $ch['ready'] ? 'border-emerald-200' : 'border-gray-200' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $ch['icon'] }}</span>
                        <div>
                            <p class="font-black text-indigo-950">{{ $ch['title'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $ch['hint'] }}</p>
                        </div>
                        <span class="ml-auto text-lg">{{ $ch['ready'] ? '✅' : '⬜' }}</span>
                    </div>
                </a>
            @else
                <div class="block bg-gray-50 rounded-2xl border border-gray-200 p-5 opacity-60">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $ch['icon'] }}</span>
                        <div>
                            <p class="font-black text-gray-500">{{ $ch['title'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $ch['hint'] }}</p>
                        </div>
                        <span class="ml-auto text-lg">{{ $ch['ready'] ? '✅' : '⬜' }}</span>
                    </div>
                    <p class="text-[10px] font-black uppercase text-gray-400 mt-3">🔒 Super admin</p>
                </div>
            @endif
        @endforeach
    </section>

    {{-- Stato a colpo d'occhio --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-indigo-950 mb-4">Stato attuale</h2>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ([
                ['Notifiche admin', $adminOn, $adminCanReceive ? 'Invio effettivo possibile' : 'Spento o canali non pronti'],
                ['Notifiche ospiti', $guestOn, $guestCanReceive ? 'Gli ospiti possono ricevere messaggi' : 'Spento o canali non pronti'],
                ['Telegram ospiti', $guestTelegramOn && $telegramBotReady, $guestTelegramOn ? $telegramLinkedCount.' prenotazioni collegate' : 'Toggle spento in Progetto'],
                ['Prova notifiche', $pilotCount > 0, $pilotCount > 0 ? $pilotCount.' prenotazione/i in test' : 'Nessuna prenotazione pilota'],
            ] as [$label, $ok, $hint])
                <div class="rounded-xl border p-3 {{ $ok ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $ok ? '✅' : '⬜' }}</span>
                        <span class="font-bold text-sm text-gray-900">{{ $label }}</span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1 ml-7">{{ $hint }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Checklist live --}}
    <section class="bg-white rounded-3xl shadow-sm border border-emerald-100 p-6">
        <h2 class="text-lg font-black text-emerald-950 mb-4">Pronta per andare live?</h2>
        <ul class="space-y-2">
            @foreach ($liveChecklist as $item)
                <li class="flex gap-3 items-start rounded-xl px-3 py-2 {{ $item['done'] ? 'bg-emerald-50' : 'bg-gray-50' }}">
                    <span class="text-lg leading-none mt-0.5">{{ $item['done'] ? '✅' : '⬜' }}</span>
                    <div>
                        <p class="font-semibold text-sm text-gray-900">{{ $item['label'] }}</p>
                        <p class="text-xs text-gray-500">{{ $item['hint'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Contatti admin configurati --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-black text-gray-900 mb-3">Contatti admin</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Email</p>
                @if (count($adminEmails))
                    <ul class="space-y-1 font-mono text-xs">
                        @foreach ($adminEmails as $email)
                            <li class="bg-gray-50 rounded px-2 py-1">{{ $email }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-400 italic">Nessuna email configurata</p>
                @endif
            </div>
            <div>
                <p class="text-[10px] font-black uppercase text-gray-400 mb-1">WhatsApp</p>
                @if (count($adminPhones))
                    <ul class="space-y-1 font-mono text-xs">
                        @foreach ($adminPhones as $phone)
                            <li class="bg-gray-50 rounded px-2 py-1">{{ $phone }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-400 italic">Nessun cellulare configurato</p>
                @endif
            </div>
        </div>
        <a href="{{ route('admin.progetto') }}" class="inline-block mt-3 text-xs font-black uppercase text-indigo-600 underline">Modifica →</a>
    </section>

    @if ($webhookUrl && $isSuperAdmin)
        <section class="bg-violet-50 rounded-3xl border border-violet-100 p-6 text-xs">
            <p class="font-bold text-violet-900 mb-1">Webhook Telegram (una volta su Plesk)</p>
            <code class="block bg-white border border-violet-200 rounded p-2 break-all text-[11px]">{{ $webhookUrl }}</code>
        </section>
    @endif
</div>
