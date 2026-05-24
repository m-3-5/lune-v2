<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-green-600 mb-4">
        <h2 class="text-xl font-bold text-gray-900 mb-2">✍️ Contratto di locazione</h2>
        <p class="text-sm text-gray-600">
            Puoi aprire questa sezione in qualsiasi momento. La firma è richiesta prima dell'accesso alle info di ingresso.
            @if($reservation->contract_locale)
                Lingua inviata da Serenella:
                <strong>{{ $reservation->contract_locale === 'en' ? 'English' : 'Italiano' }}</strong>.
            @endif
        </p>
    </div>

    @livewire('guest-contract', ['reservation' => $reservation], key('guest-contract-'.$reservation->id))

</x-layouts.app>
