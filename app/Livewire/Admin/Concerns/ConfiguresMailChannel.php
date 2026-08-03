<?php

namespace App\Livewire\Admin\Concerns;

use App\Mail\AdminTeamAlertMail;
use App\Support\AppSettings;
use App\Support\MailConfigurator;
use Illuminate\Support\Facades\Mail;

trait ConfiguresMailChannel
{
    public bool $mailSmtpEnabled = false;

    public string $mailHost = '';

    public int $mailPort = 465;

    public string $mailEncryption = 'ssl';

    public string $mailUsername = '';

    public string $mailPassword = '';

    public string $mailFromAddress = '';

    public string $mailFromName = '';

    public string $testEmail = '';

    protected function mountMailChannel(): void
    {
        $this->mailSmtpEnabled = AppSettings::mailSmtpEnabled();
        $this->mailHost = AppSettings::mailSmtpHost();
        $this->mailPort = AppSettings::mailSmtpPort();
        $this->mailEncryption = AppSettings::mailSmtpEncryption();
        $this->mailUsername = AppSettings::mailSmtpUsername();
        $this->mailFromAddress = AppSettings::mailFromAddress();
        $this->mailFromName = AppSettings::mailFromName();
        $this->testEmail = AppSettings::adminEmails()[0] ?? '';
    }

    public function saveMailSettings(): void
    {
        AppSettings::setMailSmtpEnabled($this->mailSmtpEnabled);
        AppSettings::set('mail_smtp_host', trim($this->mailHost));
        AppSettings::set('mail_smtp_port', max(1, (int) $this->mailPort));
        AppSettings::set('mail_smtp_encryption', $this->mailEncryption);
        AppSettings::set('mail_smtp_username', trim($this->mailUsername));
        AppSettings::set('mail_from_address', trim($this->mailFromAddress));
        AppSettings::set('mail_from_name', trim($this->mailFromName) ?: 'Gestione Appartamenti');

        if (trim($this->mailPassword) !== '') {
            AppSettings::setMailSmtpPassword(trim($this->mailPassword));
            $this->mailPassword = '';
        }

        MailConfigurator::apply();

        session()->flash('channel_message', 'Configurazione email salvata.');
    }

    public function sendTestEmail(): void
    {
        MailConfigurator::apply();

        if (! AppSettings::mailSmtpReady()) {
            session()->flash('channel_message', 'SMTP non pronto: attiva l\'invio e inserisci la password della casella.');

            return;
        }

        $to = trim($this->testEmail);

        if ($to === '') {
            session()->flash('channel_message', 'Inserisci un indirizzo per il test.');

            return;
        }

        try {
            Mail::to($to)->send(new AdminTeamAlertMail(
                'Test email',
                "Messaggio di prova dal canale Email.\nMittente: ".AppSettings::mailFromAddress(),
                url('/admin/notifiche/email'),
            ));

            session()->flash('channel_message', 'Email di test inviata a '.$to);
        } catch (\Throwable $e) {
            session()->flash('channel_message', 'Invio fallito: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function mailChannelViewData(): array
    {
        return [
            'mailReady' => AppSettings::mailSmtpReady(),
            'mailPasswordSet' => AppSettings::mailPasswordIsSet(),
            'effectiveMailDriver' => config('mail.default'),
        ];
    }
}
