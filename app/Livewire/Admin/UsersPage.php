<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class UsersPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $role = User::ROLE_ADMIN;

    public ?int $confirmingDeleteId = null;

    public ?string $lastCreatedPassword = null;

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:'.User::ROLE_ADMIN.','.User::ROLE_SUPER_ADMIN,
        ]);

        $password = Str::password(14);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($password),
            'role' => $this->role,
        ]);

        $this->lastCreatedPassword = $password;
        $this->reset(['name', 'email', 'role']);
        $this->role = User::ROLE_ADMIN;

        session()->flash('users_message', "Utente creato. Password generata (segnala ora, non sarà più visibile): {$password}");
    }

    public function changeRole(int $id, string $role): void
    {
        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            return;
        }

        $user = User::findOrFail($id);

        if ($user->isSuperAdmin() && $role === User::ROLE_ADMIN && $this->isLastSuperAdmin($user)) {
            session()->flash('users_error', 'Non puoi togliere il ruolo super admin all\'ultimo rimasto.');

            return;
        }

        $user->update(['role' => $role]);
        session()->flash('users_message', 'Ruolo aggiornato.');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('users_error', 'Non puoi eliminare il tuo stesso account da qui.');
            $this->confirmingDeleteId = null;

            return;
        }

        if ($user->isSuperAdmin() && $this->isLastSuperAdmin($user)) {
            session()->flash('users_error', 'Non puoi eliminare l\'ultimo super admin.');
            $this->confirmingDeleteId = null;

            return;
        }

        $user->delete();
        $this->confirmingDeleteId = null;
        session()->flash('users_message', 'Utente eliminato.');
    }

    protected function isLastSuperAdmin(User $user): bool
    {
        return User::where('role', User::ROLE_SUPER_ADMIN)->where('id', '!=', $user->id)->doesntExist();
    }

    public function render()
    {
        return view('livewire.admin.users-page', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }
}
