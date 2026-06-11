<?php

namespace App\Console\Commands;

use App\Services\TwilioWhatsAppService;
use App\Support\AppSettings;
use Illuminate\Console\Command;

class TwilioWhatsAppTest extends Command
{
    protected $signature = 'jlune:twilio-test {phone? : Numero destinatario es. +393487564418}';

    protected $description = 'Diagnostica invio WhatsApp via Twilio (sandbox)';

    public function handle(TwilioWhatsAppService $twilio): int
    {
        $this->table(['Impostazione', 'Valore'], [
            ['Provider', AppSettings::whatsappProvider()],
            ['Twilio pronto', AppSettings::twilioReady() ? 'sì' : 'NO'],
            ['Account SID', AppSettings::twilioAccountSid() ?: '(vuoto)'],
            ['From', AppSettings::twilioWhatsAppFrom()],
            ['Auth token salvato', AppSettings::twilioAuthTokenIsSet() ? 'sì' : 'NO'],
        ]);

        if (AppSettings::whatsappProvider() !== 'twilio') {
            $this->error('Provider non è twilio. Imposta Twilio in Admin → Canali di invio.');

            return self::FAILURE;
        }

        if (! $twilio->isReady()) {
            $this->error('Twilio non pronto: SID, Auth Token e From mancanti.');

            return self::FAILURE;
        }

        $phone = $this->argument('phone') ?? (AppSettings::adminPhones()[0] ?? null);

        if (! $phone) {
            $this->error('Specifica un numero: php artisan jlune:twilio-test +393487564418');

            return self::FAILURE;
        }

        $this->warn('Sandbox: dal tuo WhatsApp devi aver inviato join <codice> al +14155238886');
        $this->line('Invio test a: '.$phone);

        $result = $twilio->send(
            $phone,
            "Test Jlune CLI\n\nSe leggi questo, Twilio sandbox OK.\n".now()->format('d/m/Y H:i'),
        );

        if ($result['ok']) {
            $this->info('Twilio accettato. SID: '.($result['sid'] ?? '—'));
            $this->line('Controlla WhatsApp sul telefono. Se non arriva, apri Twilio Console → Monitor → Logs → Messaging.');

            return self::SUCCESS;
        }

        $this->error('Twilio rifiutato: '.($result['error'] ?? 'errore sconosciuto'));
        $this->line('Cerca "Twilio WhatsApp" in storage/logs/laravel.log');

        return self::FAILURE;
    }
}
