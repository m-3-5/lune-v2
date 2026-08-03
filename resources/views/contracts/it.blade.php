<div class="contract-document prose prose-sm max-w-none text-gray-800">
    <div class="text-center mb-6">
        <p class="text-lg font-black uppercase tracking-wide">Contratto di Locazione Breve per Finalità Turistica</p>
        <p class="text-xs text-gray-500">(ai sensi della Legge 431/1998, artt. 1571 e seguenti del Codice Civile e D.L. 50/2017)</p>
        <p class="text-sm font-semibold mt-2">Prenotazione {{ $reservation->booking_code }}</p>
    </div>

    <div class="space-y-4 my-4 not-prose text-sm">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <p class="font-bold text-indigo-900 uppercase border-b pb-1 mb-2">Locatore</p>
            <p class="font-semibold">{{ \App\Support\AppSettings::landlordName() ?: '[Nome gestore da configurare in Progetto → Contatti]' }}</p>
            @if (\App\Support\AppSettings::landlordAddress())
                <p>{{ \App\Support\AppSettings::landlordAddress() }}</p>
            @endif
            <p class="text-xs text-gray-600 mt-1">{{ \App\Support\AppSettings::landlordEmail() }} | {{ \App\Support\AppSettings::landlordPhone() }}</p>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
            <p class="font-bold text-indigo-900 uppercase border-b pb-1 mb-2">Conduttore / Ospiti</p>
            <ul class="space-y-2">
                @foreach($guests as $guest)
                    <li>
                        <strong>Ospite {{ $guest['slot'] }}:</strong> 
                        @if(!empty($guest['data']['first_name']) || !empty($guest['data']['last_name']))
                            {{ $guest['data']['first_name'] ?? '' }} {{ $guest['data']['last_name'] ?? '' }}
                        @else
                            <span class="text-amber-700 text-xs font-semibold">Dati non rilevati — da completare manualmente</span>
                        @endif
                        @if(!empty($guest['data']['birth_date']))
                            <span class="text-gray-600 text-xs">(nato/a il {{ $guest['data']['birth_date'] }})</span>
                        @endif
                        <br>
                        @if($guest['is_foreigner'])
                            <span class="text-amber-800 text-xs font-semibold">Cittadino straniero (CF non richiesto)</span>
                        @endif
                        @if(!empty($guest['data']['document_number']))
                            <span class="text-gray-600 text-xs">Doc: {{ $guest['data']['document_number'] }}</span>
                        @endif
                        @if(!$guest['is_foreigner'] && !empty($guest['data']['tax_code']))
                            <span class="font-mono text-xs block">CF: {{ $guest['data']['tax_code'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="contract-body space-y-4 text-justify leading-relaxed text-sm">
        {!! $body !!}
    </div>

    <div class="mt-8 border-t pt-4 grid grid-cols-2 text-xs text-gray-500">
        <div>Contratto generato elettronicamente il {{ now()->format('d/m/Y H:i') }}</div>
        <div class="text-right">Jlune App</div>
    </div>
</div>
