<?php
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\GeminiService;

new class extends Component {
    use WithFileUploads;

    public $photo;
    public $risposta_ia = null;
    public $loading = false;

    public function testaGemini(GeminiService $gemini)
    {
        $this->validate(['photo' => 'required|image']);
        $this->loading = true;
        
        $path = $this->photo->store('test-ia', 'public');
        $this->risposta_ia = $gemini->scanDocument($path);
        
        $this->loading = false;
    }
}; ?>

<div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-lg border-2 border-indigo-500">
    <h1 class="text-2xl font-bold mb-4 text-indigo-700">🧪 Laboratorio Test Gemini 1.5</h1>
    
    <form wire:submit="testaGemini" class="space-y-4">
        <input type="file" wire:model="photo" class="block w-full border border-gray-300 rounded p-2">
        
        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded">
            Analizza con l'IA
        </button>
        
        <div wire:loading wire:target="testaGemini" class="text-blue-600 font-bold mt-2">
            Stiamo chiamando Google... attendere...
        </div>
    </form>

    @if($risposta_ia)
        <div class="mt-6 p-4 bg-gray-900 text-green-400 rounded overflow-auto">
            <h3 class="font-bold text-white mb-2">Risposta Grezza del Server:</h3>
            <pre>{{ json_encode($risposta_ia, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
</div>