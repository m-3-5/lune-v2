<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier;
use Illuminate\Console\Command;

class TelegramTest extends Command
{
    protected $signature = 'jlune:telegram-test {--message=Test Jlune: notifiche collegate.}';

    protected $description = 'Invia un messaggio di prova ai chat ID Telegram configurati';

    public function handle(TelegramNotifier $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('Telegram non configurato. Imposta TELEGRAM_ENABLED=true, TELEGRAM_BOT_TOKEN e TELEGRAM_NOTIFY_CHAT_IDS nel .env');

            return self::FAILURE;
        }

        $telegram->notifyAdmins('<b>Jlune</b> — '.$this->option('message'));
        $this->info('Messaggio inviato (verifica Telegram sui telefoni configurati).');

        return self::SUCCESS;
    }
}
