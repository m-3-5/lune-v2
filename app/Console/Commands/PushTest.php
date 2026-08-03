<?php

namespace App\Console\Commands;

use App\Services\AdminPushNotifier;
use Illuminate\Console\Command;

class PushTest extends Command
{
    protected $signature = 'jlune:push-test {--message=Test Web Push Admin}';

    protected $description = 'Invia una notifica Web Push di prova ai dispositivi admin registrati';

    public function handle(AdminPushNotifier $push): int
    {
        if (! config('webpush.enabled')) {
            $this->error('WEBPUSH_ENABLED=false nel .env');

            return self::FAILURE;
        }

        $push->notifyAdmins($this->option('message'), 'test', url('/admin/progetto'));
        $this->info('Push in coda (serve almeno un dispositivo con «Attiva notifiche» su /admin/progetto).');

        return self::SUCCESS;
    }
}
