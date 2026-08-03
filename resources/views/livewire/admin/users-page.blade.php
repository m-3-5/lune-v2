<div class="max-w-3xl mx-auto space-y-6 pb-16">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 font-bold text-sm">← Dashboard</a>
        <h1 class="text-3xl font-black text-indigo-950 mt-2">Utenti</h1>
        <p class="text-gray-500 text-sm mt-1">Crea account per il team e scegli il livello di accesso.</p>
    </div>

    @if (session()->has('users_message'))
        <div class="bg-green-100 text-green-800 p-4 rounded-2xl text-sm font-bold break-all">{{ session('users_message') }}</div>
    @endif
    @if (session()->has('users_error'))
        <div class="bg-red-100 text-red-800 p-4 rounded-2xl text-sm font-bold">{{ session('users_error') }}</div>
    @endif

    <section class="bg-white rounded-3xl shadow-sm border border-indigo-100 p-6 space-y-4">
        <h2 class="text-lg font-black text-indigo-950">Nuovo utente</h2>

        <div class="grid sm:grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Nome</label>
                <input type="text" wire:model="name" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-gray-400">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="text-[10px] font-black uppercase text-gray-400">Livello</label>
                <select wire:model="role" class="w-full rounded-xl border-gray-200 text-sm mt-1">
                    <option value="admin">Admin — accesso operativo (senza sezioni tecniche)</option>
                    <option value="super_admin">Super admin — accesso completo</option>
                </select>
            </div>
        </div>

        <button type="button" wire:click="create"
            class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase">
            Crea utente
        </button>
    </section>

    <section class="space-y-3">
        @foreach ($users as $user)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-black text-gray-900">{{ $user->name }} @if ($user->id === auth()->id()) <span class="text-[10px] text-gray-400 font-bold uppercase">(tu)</span> @endif</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <select wire:change="changeRole({{ $user->id }}, $event.target.value)"
                        class="rounded-xl border-gray-200 text-xs font-bold py-1.5">
                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                        <option value="super_admin" @selected($user->role === 'super_admin')>Super admin</option>
                    </select>

                    @if ($confirmingDeleteId === $user->id)
                        <button type="button" wire:click="delete({{ $user->id }})"
                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-black">Conferma</button>
                        <button type="button" wire:click="cancelDelete"
                            class="px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold">Annulla</button>
                    @else
                        <button type="button" wire:click="confirmDelete({{ $user->id }})"
                            class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold">Elimina</button>
                    @endif
                </div>
            </div>
        @endforeach
    </section>
</div>
