<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Reservation;
use App\Models\GuestDocument;

new class extends Component
{
    use WithFileUploads; // Necessario per gestire i file

    public Reservation $reservation;
    public $photo;
    public $status = 'missing';

    public function mount(Reservation $reservation)
    {
        $this->reservation = $reservation;
        
        // Controlliamo se l'ospite ha già caricato qualcosa
        $latestDocument = $this->reservation->guestDocuments()->latest()->first();
        if ($latestDocument) {
            $this->status = $latestDocument->status;
        }
    }

    public function save()
    {
        // 1. Validazione: massimo 10MB, accetta immagini o PDF
        $this->validate([
            'photo' => 'required|mimes:jpg,jpeg,png,pdf|max:10240', 
        ]);

        // 2. Salvataggio sicuro nella cartella privata (storage/app/private/documents)
        $filePath = $this->photo->store('private/documents', 'local');

        // 3. Creiamo la riga nel database in attesa della validazione IA/Serenella
        GuestDocument::create([
            'reservation_id' => $this->reservation->id,
            'file_path' => $filePath,
            'status' => 'pending', 
            'first_name' => 'Da verificare',
            'last_name' => 'Da verificare',
            'date_of_birth' => '1990-01-01',
        ]);

        // 4. Aggiorniamo la UI e puliamo il form
        $this->status = 'pending';
        $this->reset('photo');
    }
};
?>

<div class="bg-blue-50 border border-blue-200 p-4 rounded-xl text-center mb-3">
    <p class="text-blue-800 font-bold text-sm mb-2">📄 Documenti d'identità</p>
    
    @if($status === 'pending')
        <div class="animate-pulse">
            <p class="text-blue-700 text-xs mb-3">L'IA sta verificando la leggibilità del documento...</p>
            <span class="bg-blue-200 text-blue-800 py-1 px-3 rounded-full text-xs font-bold">In elaborazione 🤖</span>
        </div>
    @elseif($status === 'approved')
        <p class="text-green-700 text-xs mb-3 font-bold">✅ Documento Verificato e Approvato</p>
    @elseif($status === 'rejected')
        <p class="text-red-700 text-xs mb-2 font-bold">❌ Immagine non valida</p>
        <p class="text-red-600 text-xs mb-3">Assicurati che il documento sia ben illuminato e leggibile, poi riprova.</p>
        
        <form wire:submit="save">
            <input type="file" wire:model="photo" accept="image/jpeg,image/png,application/pdf" capture="environment" class="mb-3 text-xs w-full bg-white p-2 rounded border border-red-300">
            @error('photo') <span class="text-red-500 text-xs block mb-2 font-bold">{{ $message }}</span> @enderror
            
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm w-full transition shadow-md" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Ricarica Documento</span>
                <span wire:loading wire:target="save">Caricamento in corso...</span>
            </button>
        </form>
    @else
        <p class="text-blue-700 text-xs mb-3">Scatta una foto o carica il tuo documento per sbloccare l'ingresso.</p>
        
        <form wire:submit="save">
            {{-- accept="image/*" e capture="environment" forzano l'apertura della fotocamera su mobile --}}
            <input type="file" wire:model="photo" accept="image/jpeg,image/png,application/pdf" capture="environment" class="mb-3 text-xs w-full bg-white p-2 rounded border border-blue-300">
            @error('photo') <span class="text-red-500 text-xs block mb-2 font-bold">{{ $message }}</span> @enderror
            
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm w-full transition shadow-md" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Carica Documento</span>
                <span wire:loading wire:target="save">Attendere prego...</span>
            </button>
        </form>
    @endif
</div>