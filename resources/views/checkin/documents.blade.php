<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">
    
    <div class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-indigo-600">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-900">
            📄 Inserimento Documenti
        </h2>
        
        <p class="text-gray-600 mb-6">
            Per completare la procedura e sbloccare le informazioni d'ingresso, abbiamo bisogno dei documenti di identità di tutti gli ospiti.
        </p>

        {{-- Usiamo la direttiva @livewire invece del tag HTML --}}
        @livewire('document-uploader', ['reservation' => $reservation])

    </div>

</x-layouts.app>