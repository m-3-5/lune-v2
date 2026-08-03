<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email? : Email di accesso} {--password= : Password (se omessa, viene generata)} {--name= : Nome visualizzato}';

    protected $description = 'Crea (o aggiorna la password di) un utente per il login admin';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('Email di accesso');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email non valida.');

            return self::FAILURE;
        }

        $password = $this->option('password');
        $generated = false;

        if (! $password) {
            $password = Str::password(14);
            $generated = true;
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::min(8)]]
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first('password'));

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $this->option('name') ?: Str::before($email, '@'),
                'password' => Hash::make($password),
            ]
        );

        $this->info(($user->wasRecentlyCreated ? 'Utente creato: ' : 'Password aggiornata per: ').$email);

        if ($generated) {
            $this->warn('Password generata (segnala solo qui, non è recuperabile in altro modo):');
            $this->line($password);
        }

        return self::SUCCESS;
    }
}
