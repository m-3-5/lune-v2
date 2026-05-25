<div class="max-w-md mx-auto mt-16">
    <div class="bg-white rounded-3xl shadow-lg border border-slate-200 p-8">
        <h1 class="text-2xl font-black text-slate-900">Area sviluppo</h1>
        <p class="text-sm text-gray-500 mt-2">Solo per il team tecnico. Serenella usa la pagina <strong>Progetto</strong>.</p>
        <form wire:submit="attemptUnlock" class="mt-6 space-y-4">
            <div>
                <label class="block text-xs font-black uppercase text-gray-400 mb-1">Password</label>
                <input type="password" wire:model="devPassword" autocomplete="current-password"
                    class="w-full rounded-xl border-gray-200" />
                @error('devPassword') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full py-3 bg-slate-900 text-white rounded-2xl font-black text-sm uppercase">Accedi</button>
        </form>
        <a href="{{ route('admin.progetto') }}" class="block text-center text-sm text-indigo-600 font-bold mt-6">← Torna a Progetto</a>
    </div>
</div>
