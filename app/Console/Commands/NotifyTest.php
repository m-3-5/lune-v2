<?php

namespace App\Console\Commands;

use App\Services\AdminOutboundNotifier;
use App\Support\AppSettings;
use App\Support\MailConfigurator;
use Illuminate\Console\Command;

class NotifyTest extends Command
{
    protected $signature = 'jlune:notify-test {--force : Invia anche se i toggle admin sono disattivati}';

    protected $description = 'Invia una notifica di test email/WhatsApp ai contatti admin configurati';

    public function handle(AdminOutboundNotifier $notifier): int
    {
        MailConfigurator::apply();
        $force = (bool) $this->option('force');

        $this->table(
            ['Impostazione', 'Valore'],
            [
                ['SMTP attivo', AppSettings::mailSmtpEnabled() ? 'sì' : 'NO'],
                ['SMTP pronto', AppSettings::mailSmtpReady() ? 'sì' : 'NO'],
                ['Mittente', AppSettings::mailFromAddress()],
                ['Host', AppSettings::mailSmtpHost().':'.AppSettings::mailSmtpPort()],
                ['Admin invio attivo', AppSettings::adminNotificationsEnabled() ? 'sì' : 'NO'],
                ['Admin email attiva', AppSettings::adminEmailNotificationsEnabled() ? 'sì' : 'no'],
                ['Admin WhatsApp attiva', AppSettings::adminWhatsAppNotificationsEnabled() ? 'sì' : 'no'],
                ['Ospite invio attivo', AppSettings::guestNotificationsEnabled() ? 'sì' : 'NO'],
                ['Email admin', implode(', ', AppSettings::adminEmails()) ?: '(nessuna)'],
                ['Telefoni admin', implode(', ', AppSettings::adminPhones()) ?: '(nessuno)'],
                ['Mail driver', config('mail.default')],
                ['WhatsApp provider', AppSettings::whatsappProvider()],
                ['WhatsApp pronto', AppSettings::whatsAppChannelReady() ? 'sì' : 'NO'],
                ['Twilio SID', AppSettings::twilioAccountSid() ?: '(vuoto)'],
                ['Force', $force ? 'sì' : 'no'],
            ]
        );

        if (! AppSettings::mailSmtpReady()) {
            $this->warn('SMTP non pronto: configura password in Admin → Canali di invio.');
        }

        if (! $force && ! AppSettings::adminNotificationsEnabled()) {
            $this->warn('Invio admin disattivato. Usa --force per inviare comunque.');
        }

        $notifier->notify(
            'Test (CLI)',
            'Messaggio di prova da php artisan jlune:notify-test',
            url('/admin/progetto'),
            force: $force,
        );

        $this->info('Notifica di test inviata (controlla email, log WhatsApp, o CallMeBot).');

        return self::SUCCESS;
    }
}
