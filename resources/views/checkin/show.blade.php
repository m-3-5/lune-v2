<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">
    
    <div class="bg-white rounded-2xl shadow-sm p-6 border-t-4 border-indigo-600">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-900">
            👋 Benvenuto, {{ $reservation->guest_name }}!
        </h2>
        
        <p class="text-gray-600 mb-4">
            Stai visualizzando i dettagli del tuo soggiorno presso <strong>{{ $apartment->name }}</strong>.
        </p>

        @if(!$reservation->is_paid)
            <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-center mb-4">
                <p class="text-red-800 font-bold mb-2">⚠️ Acconto/Saldo Richiesto</p>
                <p class="text-sm text-red-600 mb-3">Per sbloccare il caricamento dei documenti, completa il pagamento.</p>
                <a href="{{ $reservation->checkfront_payment_url }}" target="_blank" class="inline-block bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow hover:bg-red-700">
                    Paga su Checkfront
                </a>
            </div>
        @else
            <div class="bg-green-50 p-4 rounded-xl border border-green-200 text-center mb-4">
                <p class="text-green-800 font-bold">✅ Pagamento confermato!</p>
                <p class="text-sm text-green-700">Ora puoi procedere con il caricamento dei documenti nel menu.</p>
            </div>
        @endif

    </div>

</x-layouts.app>