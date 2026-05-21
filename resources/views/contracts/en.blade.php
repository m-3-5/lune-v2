<div class="contract-document prose prose-sm max-w-none text-gray-800">
    <div class="text-center mb-6">
        <p class="text-lg font-black uppercase tracking-wide">Short-Term Rental Agreement - Tourist Use</p>
        <p class="text-xs text-gray-500">(pursuant to Italian Law 431/1998, Civil Code articles 1571 and following, and DL 50/2017)</p>
        <p class="text-sm font-semibold mt-2">Booking {{ $reservation->booking_code }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4 not-prose text-sm">
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
                        {{ $guest['data']['first_name'] ?? '' }} {{ $guest['data']['last_name'] ?? '' }}
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

    <div class="space-y-4 text-justify leading-relaxed text-sm">
        <p>
            <strong>1. PROPERTY:</strong> The Guest rents the apartment located at Lungomare Palmasera 32, Cala Gonone, named 
            <strong>"{{ $apartment->name ?? 'Property' }}"</strong>. 
            Only registered guests are allowed to stay. If anything changes, please inform us in advance.
        </p>

        <p>
            <strong>2. STAY DETAILS:</strong><br>
            • <strong>Dates:</strong> from {{ $reservation->check_in->format('d/m/Y') }} to {{ $reservation->check_out->format('d/m/Y') }}<br>
            • <strong>Nights:</strong> {{ (int) $reservation->check_in->diffInDays($reservation->check_out) }}<br>
            • <strong>Total price:</strong> € {{ number_format($reservation->total_price, 2, ',', '.') }}<br>
            • <strong>Included in the price:</strong> Final cleaning, Wi-Fi, bed linen & towels, air conditioning, utilities.<br>
            <span class="text-amber-700 font-medium">Tourist tax is not included and must be paid separately.</span>
        </p>

        <p>
            <strong>3. BOOKING CONFIRMATION (DEPOSIT):</strong> A 30% deposit equal to 
            <strong>€ {{ number_format($reservation->total_price * 0.30, 2, ',', '.') }}</strong> 
            is required to confirm the booking. The reservation is confirmed once the payment is received.
        </p>

        <p>
            <strong>4. CANCELLATION POLICY AND FORCE MAJEURE:</strong> The reservation is non-refundable. The Landlord shall not be held responsible for cancellations or changes to the stay due to circumstances beyond their control, such as weather conditions, transport strikes, flight cancellations, or similar events. Such circumstances do not constitute grounds for a refund. However, at the Landlord's discretion, the deposit may be converted into a voucher if: the Guest provides at least 7 days' notice before arrival and the reservation is not a no-show. If these conditions are met, the deposit can be used for a new stay within 12 months, for a reservation of similar value or duration. Any price difference for new dates will be adjusted accordingly. The voucher is non-refundable and cannot be converted into cash.
        </p>

        <p>
            <strong>5. BALANCE PAYMENT:</strong> The balance is normally paid in cash upon arrival. If you prefer to pay by bank transfer, the payment must be completed before arrival and must be received in full. Any bank or transfer fees are at the Guest's expense. The Landlord must receive the full agreed amount without deductions. The tourist tax must always be paid separately in cash upon arrival and should not be included in the bank transfer.
        </p>

        <p>
            <strong>6. CHECK-IN & ACCESS:</strong> Check-in is from 4:00 PM, while check-out must be by 10:00 AM. We offer a self check-in system for flexibility; instructions will be shared after payment and registration. Early check-in or late check-out (up to 1:00 PM) may be available upon request, subject to availability and cleaning schedule.
        </p>

        <p>
            <strong>7. REGISTRATION:</strong> All guests (including children) are required to provide ID documents/Passports before arrival, as required by Italian law.
        </p>

        <p>
            <strong>8. CARE OF THE PROPERTY:</strong> No security deposit is required. We kindly ask you to treat the apartment with care and inform us if anything needs attention within 24 hours of arrival. In case of damage or missing items, the corresponding costs may be charged.
        </p>

        <p>
            <strong>9. HOUSE RULES:</strong> No smoking inside (outdoor areas may be used). Pets are not allowed unless agreed in advance. Please respect neighbors and quiet hours.
        </p>

        <p>
            <strong>CHILD SAFETY:</strong> Parents or guardians are responsible for supervising children at all times during the stay. Children must not be left unattended on balconies, staircases, or in any potentially hazardous areas.
        </p>

        <p>
            <strong>10. OCCUPANCY AND EXTRA BEDS:</strong> The number of guests must match the reservation. Charges are based on the actual use of sleeping spaces. The use of extra beds, even if not previously requested, may result in additional charges. Children up to 3 years old stay free of charge when sharing a bed with their parents. If a separate bed or cot is used, additional charges may apply. If anything changes, please inform us in advance so we can arrange it properly.
        </p>

        <p>
            <strong>11. EARLY DEPARTURE:</strong> If the stay is shortened for any reason, the total agreed amount remains due and no refund will be provided for unused nights.
        </p>

        <p>
            <strong>12. LOCATION:</strong> The property is located on the seafront, in a central and lively area, close to restaurants and the evening promenade. Although equipped with sound-insulated windows, by booking the Guest acknowledges that absolute silence cannot be guaranteed.
        </p>

        <p>
            <strong>13. LIABILITY:</strong> The Landlord is not responsible for loss or theft of personal belongings.
        </p>

        <p>
            <strong>14. JURISDICTION:</strong> Any dispute is subject to the exclusive jurisdiction of the Court of Nuoro (Italy).
        </p>
    </div>

    <div class="mt-8 border-t pt-4 grid grid-cols-2 text-xs text-gray-500">
        <div>Contract electronically generated on {{ now()->format('d/m/Y H:i') }}</div>
        <div class="text-right">JLune Management System</div>
    </div>
</div>