<x-layouts.app title="Arrivi e Documenti">
    <div class="p-6">
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold mb-4 inline-block">← Torna alla Home</a>
        <h1 class="text-3xl font-black text-indigo-950 mb-6">Arrivi e Documenti</h1>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-2">
            @livewire('admin.reservations-module')
        </div>
    </div>
</x-layouts.app>