<x-layouts.app :apartmentName="$apartment->name" :reservation="$reservation" :hasDocuments="false" :isCheckinTime="false">

    <div class="rounded-[2rem] p-6 text-white shadow-lg mb-5" style="background:linear-gradient(135deg,#4f46e5,#4338ca);">
        <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest mb-1">🔌 Elettrodomestici</p>
        <h1 class="text-xl font-black">Come si usano</h1>
        <p class="text-indigo-100 text-sm mt-2">Inquadra il QR sull'elettrodomestico, oppure scegli dalla lista qui sotto.</p>
    </div>

    @forelse ($applianceVideos as $video)
        <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden mb-4">
            <x-video-player :video="$video" />
            <p class="px-4 py-3 font-black text-sm text-gray-800">{{ $video->title }}</p>
        </div>
    @empty
        <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
            Nessun video ancora disponibile per questo appartamento.
        </div>
    @endforelse

</x-layouts.app>
