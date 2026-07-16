<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <div class="rounded-[2rem] p-6 text-white shadow-lg mb-5" style="background:linear-gradient(135deg,#059669,#065f46);">
        <p class="text-emerald-100 text-[10px] font-black uppercase tracking-widest mb-1">✍️ Contratto di locazione</p>
        <h1 class="text-xl font-black">
            @if($reservation->contract_accepted)
                Firmato, tutto ok
            @else
                Puoi aprirlo quando vuoi
            @endif
        </h1>
        <p class="text-emerald-100 text-sm mt-2">
            La firma è richiesta prima dell'accesso alle info di ingresso.
            @if($reservation->contract_locale)
                Lingua: <strong>{{ $reservation->contract_locale === 'en' ? 'English' : 'Italiano' }}</strong>.
            @endif
        </p>
    </div>

    @livewire('guest-contract', ['reservation' => $reservation], key('guest-contract-'.$reservation->id))

</x-layouts.app>
