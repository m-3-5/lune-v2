<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">
    
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

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
                <p class="text-green-800 font-bold">✅ {{ $reservation->paymentLabel() }}</p>
                <p class="text-sm text-green-700">Puoi caricare i documenti dal menu.</p>
                @if($reservation->balance > 0)
                    <p class="text-xs text-green-600 mt-2">Saldo residuo: € {{ number_format($reservation->balance, 2, ',', '.') }}</p>
                @endif
            </div>
        @endif

        @if($reservation->hasDocumentsPendingReview())
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center mb-4">
                <p class="text-amber-800 font-bold">📋 Documenti in verifica</p>
                <p class="text-sm text-amber-700">Serenella sta controllando i file. Il contratto si sbloccherà dopo l'approvazione.</p>
            </div>
        @elseif($reservation->documents_validated && $reservation->contract_ready_for_guest)
            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-200 text-center mb-4">
                <p class="text-indigo-800 font-bold">✅ Documenti approvati</p>
                <p class="text-sm text-indigo-700">Puoi firmare il contratto dal menu.</p>
            </div>
        @elseif($reservation->documents_validated)
            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-200 text-center mb-4">
                <p class="text-indigo-800 font-bold">✅ Documenti approvati</p>
                <p class="text-sm text-indigo-700">Il contratto sarà disponibile dopo la verifica finale di Serenella.</p>
            </div>
        @endif

    </div>

</x-layouts.app>