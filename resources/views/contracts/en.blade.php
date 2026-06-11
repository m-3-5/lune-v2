<div class="contract-document prose prose-sm max-w-none text-gray-800">
    <div class="text-center mb-6">
        <p class="text-lg font-black uppercase tracking-wide">Short-Term Rental Agreement - Tourist Use</p>
        <p class="text-xs text-gray-500">(pursuant to Italian Law 431/1998, Civil Code articles 1571 and following, and DL 50/2017)</p>
        <p class="text-sm font-semibold mt-2">Booking {{ $reservation->booking_code }}</p>
    </div>

    <div class="space-y-4 my-4 not-prose text-sm">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <p class="font-bold text-indigo-900 uppercase border-b pb-1 mb-2">Landlord</p>
            <p class="font-semibold">Serenella Marongiu</p>
            <p>Lungomare Palmasera 32 - Cala Gonone (Italy)</p>
            <p class="text-xs text-gray-600 mt-1">info@appartamentijlune.com | +39 349 5377378</p>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
            <p class="font-bold text-indigo-900 uppercase border-b pb-1 mb-2">Guest(s)</p>
            <ul class="space-y-2">
                @foreach($guests as $guest)
                    <li>
                        <strong>Guest {{ $guest['slot'] }}:</strong> 
                        @if(!empty($guest['data']['first_name']) || !empty($guest['data']['last_name']))
                            {{ $guest['data']['first_name'] ?? '' }} {{ $guest['data']['last_name'] ?? '' }}
                        @else
                            <span class="text-amber-700 text-xs font-semibold">Details not detected — to be completed manually</span>
                        @endif
                        @if(!empty($guest['data']['birth_date']))
                            <span class="text-gray-600 text-xs">(born on {{ $guest['data']['birth_date'] }})</span>
                        @endif
                        <br>
                        @if($guest['is_foreigner'])
                            <span class="text-amber-800 text-xs font-semibold">Foreign guest (Tax code not required)</span>
                        @endif
                        @if(!empty($guest['data']['document_number']))
                            <span class="text-gray-600 text-xs block">Doc/Passport: {{ $guest['data']['document_number'] }}</span>
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
        <div>Contract electronically generated on {{ now()->format('d/m/Y H:i') }}</div>
        <div class="text-right">Jlune App</div>
    </div>
</div>
