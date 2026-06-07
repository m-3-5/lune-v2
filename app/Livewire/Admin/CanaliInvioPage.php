<?php

namespace App\Livewire\Admin;

use App\Mail\AdminTeamAlertMail;
use App\Services\AdminWhatsAppNotifier;
use App\Services\TwilioWhatsAppService;
use App\Support\AppSettings;
use App\Support\MailConfigurator;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CanaliInvioPage extends Component
{
    public bool $mailSmtpEnabled = false;

    public string $mailHost = '';

    public int $mailPort = 465;

    public string $mailEncryption = 'ssl';

    public string $mailUsername = '';

    public string $mailPassword = '';

    public string $mailFromAddress = '';

    public string $mailFromName = '';

    public string $whatsappProvider = 'log';

    public string $whatsappCallMeBotKeysText = '';

    public string $twilioAccountSid = '';

    public string $twilioAuthToken = '';

    public string $twilioWhatsAppFrom = '';

    public string $testEmail = '';

    public string $testWhatsAppPhone = '';

    public function mount(): void
    {
        $this->mailSmtpEnabled = AppSettings::mailSmtpEnabled();
        $this->mailHost = AppSettings::mailSmtpHost();
        $this->mailPort = AppSettings::mailSmtpPort();
        $this->mailEncryption = AppSettings::mailSmtpEncryption();
        $this->mailUsername = AppSettings::mailSmtpUsername();
        $this->mailFromAddress = AppSettings::mailFromAddress();
        $this->mailFromName = AppSettings::mailFromName();
        $this->whatsappProvider = AppSettings::whatsappProvider();
        $this->whatsappCallMeBotKeysText = implode("\n", AppSettings::whatsappCallMeBotKeys());
        $this->twilioAccountSid = AppSettings::twilioAccountSid();
        $this->twilioWhatsAppFrom = AppSettings::twilioWhatsAppFrom();
        $this->testEmail = AppSettings::adminEmails()[0] ?? '';
        $this->testWhatsAppPhone = AppSettings::adminPhones()[0] ?? '';
    }

    public function saveMailSettings(): void
    {
        AppSettings::setMailSmtpEnabled($this->mailSmtpEnabled);
        AppSettings::set('mail_smtp_host', trim($this->mailHost));
        AppSettings::set('mail_smtp_port', max(1, (int) $this->mailPort));
        AppSettings::set('mail_smtp_encryption', $this->mailEncryption);
        AppSettings::set('mail_smtp_username', trim($this->mailUsername));
        AppSettings::set('mail_from_address', trim($this->mailFromAddress));
        AppSettings::set('mail_from_name', trim($this->mailFromName) ?: 'Jlune');

        if (trim($this->mailPassword) !== '') {
            AppSettings::setMailSmtpPassword(trim($this->mailPassword));
            $this->mailPassword = '';
        }

        MailConfigurator::apply();

        session()->flash('canali_message', 'Configurazione email salvata.');
    }

    public function saveWhatsAppSettings(): void
    {
        $provider = in_array($this->whatsappProvider, ['log', 'callmebot', 'twilio'], true)
            ? $this->whatsappProvider
            : 'log';

        AppSettings::setWhatsAppProvider($provider);
        AppSettings::setWhatsAppCallMeBotKeys($this->linesToArray($this->whatsappCallMeBotKeysText));
        AppSettings::set('twilio_account_sid', trim($this->twilioAccountSid));
        AppSettings::set('twilio_whatsapp_from', trim($this->twilioWhatsAppFrom) ?: '+14155238886');

        if (trim($this->twilioAuthToken) !== '') {
            AppSettings::setTwilioAuthToken(trim($this->twilioAuthToken));
            $this->twilioAuthToken = '';
        }

        session()->flash('canali_message', 'Configurazione WhatsApp salvata.');
    }

    public function sendTestEmail(): void
    {
        MailConfigurator::apply();

        if (! AppSettings::mailSmtpReady()) {
            session()->flash('canali_message', 'SMTP non pronto: attiva l\'invio e inserisci la password della casella.');

            return;
        }

        $to = trim($this->testEmail);

        if ($to === '') {
            session()->flash('canali_message', 'Inserisci un indirizzo per il test.');

            return;
        }

        try {
            Mail::to($to)->send(new AdminTeamAlertMail(
                'Test email Jlune',
                "Messaggio di prova da Canali di invio.\nMittente: ".AppSettings::mailFromAddress(),
                url('/admin/canali'),
            ));

            session()->flash('canali_message', 'Email di test inviata a '.$to);
        } catch (\Throwable $e) {
            session()->flash('canali_message', 'Invio fallito: '.$e->getMessage());
        }
    }

    public function sendTestWhatsApp(AdminWhatsAppNotifier $notifier, TwilioWhatsAppService $twilio): void
    {
        $provider = AppSettings::whatsappProvider();

        if ($provider === 'twilio') {
            if (! $twilio->isReady()) {
                session()->flash('canali_message', 'Twilio non pronto: inserisci Account SID, Auth Token e numero From, poi Salva.');

                return;
            }

            $phone = trim($this->testWhatsAppPhone);

            if ($phone === '') {
                session()->flash('canali_message', 'Inserisci un numero per il test WhatsApp.');

                return;
            }

            $result = $twilio->send(
                $phone,
                "Test WhatsApp Jlune\n\nMessaggio di prova da Canali di invio.\n".url('/admin/canali'),
            );

            if ($result['ok']) {
                session()->flash('canali_message', 'WhatsApp Twilio inviato a '.$phone.($result['sid'] ? ' (SID: '.$result['sid'].')' : ''));
            } else {
                session()->flash('canali_message', 'Twilio errore: '.($result['error'] ?? 'sconosciuto'));
            }

            return;
        }

        $notifier->send(
            'Test WhatsApp Jlune',
            'Messaggio di prova da Canali di invio (admin).',
            url('/admin/canali'),
            force: true,
        );

        session()->flash('canali_message', $provider === 'callmebot'
            ? 'Test CallMeBot inviato (controlla WhatsApp).'
            : 'Provider log: messaggio scritto nel log Laravel.');
    }

    /**
     * @return array<int, string>
     */
    protected function linesToArray(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
    }

    public function render()
    {
        return view('livewire.admin.canali-invio-page', [
            'mailReady' => AppSettings::mailSmtpReady(),
            'mailPasswordSet' => AppSettings::mailPasswordIsSet(),
            'effectiveMailDriver' => config('mail.default'),
            'twilioReady' => AppSettings::twilioReady(),
            'twilioAuthTokenSet' => AppSettings::twilioAuthTokenIsSet(),
            'whatsappChannelReady' => AppSettings::whatsAppChannelReady(),
        ]);
    }
}
