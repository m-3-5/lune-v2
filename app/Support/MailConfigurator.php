<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailConfigurator
{
    public static function apply(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        Config::set('mail.from.address', AppSettings::mailFromAddress());
        Config::set('mail.from.name', AppSettings::mailFromName());

        if (! AppSettings::mailSmtpReady()) {
            Config::set('mail.default', 'log');

            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', AppSettings::mailSmtpHost());
        Config::set('mail.mailers.smtp.port', AppSettings::mailSmtpPort());
        Config::set('mail.mailers.smtp.username', AppSettings::mailSmtpUsername());
        Config::set('mail.mailers.smtp.password', AppSettings::mailSmtpPassword());
        Config::set('mail.mailers.smtp.scheme', AppSettings::mailSmtpScheme());
        Config::set('mail.mailers.smtp.timeout', null);

        if (! AppSettings::mailSmtpVerifySsl()) {
            // Symfony EsmtpTransportFactory legge verify_peer dalle opzioni DSN
            Config::set('mail.mailers.smtp.verify_peer', false);
            Config::set('mail.mailers.smtp.stream', [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);
        }
    }
}
