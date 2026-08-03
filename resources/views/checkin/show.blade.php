<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-2xl mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl mb-4 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Card di benvenuto --}}
    <div class="rounded-[2rem] p-6 text-white shadow-lg mb-5" style="background:linear-gradient(135deg,#4f46e5,#4338ca);">
        <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest mb-1">{{ $reservation->booking_code ?? $apartment->name }}</p>
        <h1 class="text-2xl font-black">Ciao {{ $reservation->guest_name }} 👋</h1>
        <p class="text-indigo-100 text-sm mt-1">{{ $apartment->name }}</p>

        <div class="flex flex-wrap gap-2 mt-4">
            @if(!$reservation->is_paid)
                <span class="text-[10px] font-black uppercase bg-white/15 rounded-full px-3 py-1.5">⚠️ Acconto da pagare</span>
            @else
                <span class="text-[10px] font-black uppercase bg-white/15 rounded-full px-3 py-1.5">✅ {{ $reservation->paymentLabel() }}</span>
            @endif

            @if($reservation->hasDocumentsPendingReview())
                <span class="text-[10px] font-black uppercase bg-white/15 rounded-full px-3 py-1.5">📋 Documenti in verifica</span>
            @elseif($reservation->documents_validated)
                <span class="text-[10px] font-black uppercase bg-white/15 rounded-full px-3 py-1.5">✅ Documenti ok</span>
            @endif
        </div>
    </div>

    @if(!$reservation->is_paid)
        <a href="{{ $reservation->checkfront_payment_url }}" target="_blank"
            class="block text-center bg-red-600 text-white font-black text-sm uppercase tracking-wide py-4 rounded-2xl shadow-sm mb-5 hover:bg-red-700">
            Paga su Checkfront
        </a>
    @endif

    {{-- Griglia icone di navigazione --}}
    <div class="grid grid-cols-4 gap-3 mb-5">
        <a href="{{ $reservation->is_paid ? route('checkin.documents', $reservation->token) : '#' }}"
            class="bg-white rounded-2xl p-3 text-center shadow-sm border border-gray-100 flex flex-col items-center gap-2 {{ !$reservation->is_paid ? 'opacity-40 pointer-events-none' : 'active:scale-95' }} transition-transform">
            <span class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">📄</span>
            <span class="text-[10px] font-bold text-gray-800">Documenti</span>
        </a>
        <a href="{{ $reservation->is_paid ? route('checkin.contract', $reservation->token) : '#' }}"
            class="bg-white rounded-2xl p-3 text-center shadow-sm border border-gray-100 flex flex-col items-center gap-2 {{ !$reservation->is_paid ? 'opacity-40 pointer-events-none' : 'active:scale-95' }} transition-transform">
            <span class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">✍️</span>
            <span class="text-[10px] font-bold text-gray-800">Contratto</span>
            @if($reservation->contract_accepted)
                <span class="text-[9px] font-black uppercase text-green-600">✓ Firmato</span>
            @endif
        </a>
        <a href="{{ $is_unlocked ? route('checkin.appliances', $reservation->token) : '#' }}"
            class="bg-white rounded-2xl p-3 text-center shadow-sm border border-gray-100 flex flex-col items-center gap-2 {{ !$is_unlocked ? 'opacity-40 pointer-events-none' : 'active:scale-95' }} transition-transform">
            <span class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">🔌</span>
            <span class="text-[10px] font-bold text-gray-800">Elettrodom.</span>
        </a>
        <a href="{{ route('assistenza') }}"
            class="bg-white rounded-2xl p-3 text-center shadow-sm border border-gray-100 flex flex-col items-center gap-2 active:scale-95 transition-transform">
            <span class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">🆘</span>
            <span class="text-[10px] font-bold text-gray-800">Assistenza</span>
        </a>
    </div>

    @if($reservation->hasDocumentsPendingReview())
        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200 text-center mb-4">
            <p class="text-amber-800 font-bold text-sm">📋 Documenti in verifica</p>
            <p class="text-xs text-amber-700 mt-1">Il gestore sta controllando i file. Il contratto si sbloccherà dopo l'approvazione.</p>
        </div>
    @elseif($reservation->documents_validated && $reservation->contract_ready_for_guest)
        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-200 text-center mb-4">
            <p class="text-indigo-800 font-bold text-sm">✅ Documenti approvati</p>
            <p class="text-xs text-indigo-700 mt-1">Puoi firmare il contratto qui sopra.</p>
        </div>
    @elseif($reservation->documents_validated)
        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-200 text-center mb-4">
            <p class="text-indigo-800 font-bold text-sm">✅ Documenti approvati</p>
            <p class="text-xs text-indigo-700 mt-1">Il contratto sarà disponibile dopo la verifica finale del gestore.</p>
        </div>
    @endif

    @if(($entryVideos ?? collect())->isNotEmpty())
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 mb-5">
            <h2 class="text-base font-black text-gray-900 mb-1 flex items-center gap-2">🎥 Video di ingresso</h2>
            <p class="text-xs text-gray-500 mb-4">Guardali in ordine, un passaggio alla volta.</p>
            <div class="space-y-3">
                @foreach ($entryVideos as $video)
                    <div class="border border-gray-100 rounded-2xl overflow-hidden">
                        <x-video-player :video="$video" />
                        <p class="px-3 py-2 text-sm font-bold text-gray-800">{{ $loop->iteration }}. {{ $video->title }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</x-layouts.app>
