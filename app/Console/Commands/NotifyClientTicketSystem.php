<?php

namespace App\Console\Commands;

use App\Mail\AdminTeamAlertMail;
use App\Support\AppSettings;
use App\Support\MailConfigurator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyClientTicketSystem extends Command
{
    protected $signature = 'jlune:notify-client-ticket-system {--to=info@appartamentijlune.com : Indirizzo destinatario}';

    protected $description = 'Invia una tantum l\'email al cliente che annuncia le notifiche attivate e il nuovo canale ticket';

    public function handle(): int
    {
        MailConfigurator::apply();

        if (! AppSettings::mailSmtpReady()) {
            $this->error('SMTP non pronto: configura password in Admin → Notifiche → Email prima di inviare.');

            return self::FAILURE;
        }

        $to = (string) $this->option('to');
        $ticketUrl = url('/assistenza');
        $today = now()->translatedFormat('d/m/Y');

        $body = <<<TEXT
Ciao,

è pianificata da sistema, per oggi ({$today}), l'attivazione automatica delle notifiche via email su questo indirizzo — un passaggio preparatorio in vista degli ultimi aggiornamenti.

Se stai leggendo questo messaggio, l'attivazione automatica ha funzionato correttamente.

Per qualsiasi dettaglio, domanda o richiesta, da ora potete scriverci direttamente un ticket di assistenza qui:
{$ticketUrl}
Arriva subito al team, che vi risponde personalmente.
TEXT;

        Mail::to($to)->send(new AdminTeamAlertMail(
            'Notifiche attivate + nuovo canale ticket',
            $body,
        ));

        $this->info("Email inviata a {$to}.");

        return self::SUCCESS;
    }
}
