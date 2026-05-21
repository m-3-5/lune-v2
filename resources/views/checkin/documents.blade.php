<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">
    
    <div class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-indigo-600">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-900">
            📄 Inserimento Documenti
        </h2>
        
        <p class="text-gray-600 mb-6">
            Per completare la procedura e sbloccare le informazioni d'ingresso, abbiamo bisogno dei documenti di identità di tutti gli ospiti.
        </p>

        @if(!$reservation->is_paid)
            <p class="text-sm text-gray-500">Il caricamento documenti richiede il pagamento dell'acconto.</p>
        @else
            @livewire('document-uploader', ['reservation' => $reservation])
        @endif

    </div>

    @if($reservation->documents_validated && $reservation->contract_ready_for_guest)
        <div id="sezione-contratto" class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-green-600 mt-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900">✍️ Contratto di locazione</h2>
            <p class="text-sm text-gray-600 mb-4">
                Lingua: <strong>{{ $reservation->contract_locale === 'en' ? 'English' : 'Italiano' }}</strong>
            </p>
            @php
                $guestsForContract = collect($reservation->extracted_guests ?? [])->map(fn ($g) => [
                    'name' => $g['name'] ?? '',
                    'is_foreigner' => $g['is_foreigner'] ?? false,
                    'data' => $g['data'] ?? [],
                ])->all();
            @endphp
            <livewire:contract-manager :reservation="$reservation" :guests="$guestsForContract" />
        </div>
    @elseif($reservation->documents_validated)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mt-6 text-center">
            <p class="text-amber-800 font-bold">Contratto in preparazione</p>
            <p class="text-sm text-amber-700 mt-1">Serenella sta verificando i dati estratti dai documenti. Riceverai una notifica quando potrai firmare.</p>
        </div>
    @endif

</x-layouts.app>