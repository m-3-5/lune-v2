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

    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 mt-6 text-center">
        <p class="text-indigo-900 font-bold">Contratto</p>
        <p class="text-sm text-indigo-700 mt-1 mb-4">Il contratto ha una pagina dedicata nel menu — non è bloccato dagli altri passaggi.</p>
        <a href="{{ route('checkin.contract', ['token' => $reservation->token]) }}"
            class="inline-block bg-indigo-600 text-white font-bold py-2 px-6 rounded-xl text-sm">
            Vai al contratto
        </a>
    </div>

</x-layouts.app>