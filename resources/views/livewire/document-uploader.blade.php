<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Reservation;
use App\Models\GuestDocument;

new class extends Component {
    use WithFileUploads;

    public Reservation $reservation;
    public $photo;
    public $guestName;
    public $documents = [];
    public $totalGuests = 1;

    public function mount(Reservation $reservation)
    {
        $this->reservation = $reservation;
        // Calcoliamo i passeggeri totali (se i campi sono vuoti, default 1)
        $this->totalGuests = ($reservation->adults ?? 1) + ($reservation->children ?? 0);
        $this->loadDocuments();
    }

    public function loadDocuments()
    {
        $this->documents = $this->reservation->guestDocuments()->get();
    }

    public function save(\App\Services\GeminiService $gemini)
{
    $this->validate([
        'photo' => 'required|image|max:10240',
        'guestName' => 'required|string|max:255',
    ]);

    $path = $this->photo->store('documents', 'public');

    // Creiamo il documento nel database
    $doc = GuestDocument::create([
        'reservation_id' => $this->reservation->id,
        'file_path' => $path,
        'guest_name' => $this->guestName,
        'status' => 'pending', 
    ]);

    // CHIAMATA ALL'IA: Analizziamo il documento in tempo reale!
    try {
        $analysis = $gemini->scanDocument($path);
        // Qui aggiungeremo la logica per salvare i dati estratti nel prossimo step
    } catch (\Exception $e) {
        // Se l'IA fallisce, Serenella lo vedrà comunque come 'pending'
    }

    $this->reset(['photo', 'guestName']);
    $this->loadDocuments();
    session()->flash('message', 'Documento ricevuto! L\'intelligenza artificiale lo sta controllando...');
}

    // Funzione per eliminare un documento rifiutato o sbagliato
    public function deleteDocument($id)
    {
        $doc = GuestDocument::find($id);
        if($doc) {
            $doc->delete();
            $this->loadDocuments();
        }
    }
}; ?>

<div>
    {{-- Contatore Ospiti --}}
    <div class="mb-6 flex items-center justify-between bg-indigo-50 p-4 rounded-xl border border-indigo-100">
        <div>
            <p class="text-sm text-indigo-800 font-medium">Ospiti previsti</p>
            <p class="text-2xl font-bold text-indigo-900">{{ count($documents) }} / {{ $totalGuests }}</p>
        </div>
        <div class="text-right">
            @if(count($documents) >= $totalGuests)
                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Tutti inseriti</span>
            @else
                <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Mancano documenti</span>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-blue-800 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-2">
            <span>🤖</span> {{ session('message') }}
        </div>
    @endif

    <!-- Form di Upload (Nascosto se abbiamo raggiunto il numero di ospiti) -->
    @if(count($documents) < $totalGuests)
        <form wire:submit="save" class="space-y-4 mb-8 p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nome e Cognome Ospite</label>
                <input type="text" wire:model="guestName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3 border" placeholder="Es. Giulia Bianchi">
                @error('guestName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Foto Documento</label>
                <input type="file" wire:model="photo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                <div wire:loading wire:target="photo" class="text-sm text-indigo-600 mt-2 font-medium flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Caricamento immagine...
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                Carica Documento
            </button>
        </form>
    @endif

    <!-- Lista Documenti e Stati IA -->
    @if(count($documents) > 0)
        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Stato Verifiche</h3>
        <ul class="space-y-3">
            @foreach($documents as $doc)
                <li class="p-4 bg-white border border-gray-100 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 bg-gray-50 p-2 rounded-lg">
                            👤
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $doc->guest_name }}</p>
                            
                            {{-- LA MAGIA DELLA UX: I SEMAFORI --}}
                            @if($doc->status === 'pending')
                                <p class="text-xs font-medium text-yellow-600 flex items-center gap-1 mt-1">
                                    <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span> In analisi (IA)
                                </p>
                            @elseif($doc->status === 'approved')
                                <p class="text-xs font-medium text-green-600 flex items-center gap-1 mt-1">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Valido e Confermato
                                </p>
                            @else
                                <p class="text-xs font-medium text-red-600 flex items-center gap-1 mt-1">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span> Non valido / Illeggibile
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Se rifiutato, permettiamo di cancellarlo e ricaricarlo --}}
                    @if($doc->status === 'rejected')
                        <button wire:click="deleteDocument({{ $doc->id }})" class="text-xs bg-red-50 text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-100 font-medium transition-colors">
                            Elimina e Ricarica
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>