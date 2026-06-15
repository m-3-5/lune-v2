<?php

namespace App\Livewire\Admin\Concerns;

use App\Services\AdminWhatsAppNotifier;
use App\Services\TwilioWhatsAppService;
use App\Support\AppSettings;

trait ConfiguresWhatsAppChannel
{
    public bool $whatsappTwilioEnabled = false;

    public bool $twilioBusinessMode = false;

    public string $whatsappProvider = 'log';

    public string $whatsappCallMeBotKeysText = '';

    public string $twilioAccountSid = '';

    public string $twilioAuthToken = '';

    public string $twilioWhatsAppFrom = '';

    public string $twilioContentTemplateSid = '';

    public string $twilioMetaBusinessId = '';

    public bool $adminNotificationsEnabled = false;

    public bool $adminWhatsAppNotificationsEnabled = false;

    public bool $guestNotificationsEnabled = false;

    public bool $guestWhatsAppNotificationsEnabled = false;

    public string $adminPhonesText = '';

    public string $testWhatsAppPhone = '';

    public string $testGuestWhatsAppPhone = '';

    protected function mountWhatsAppChannel(): void
    {
        $this->whatsappProvider = AppSettings::whatsappProvider();
        $this->whatsappTwilioEnabled = $this->whatsappProvider === 'twilio';
        $this->twilioBusinessMode = AppSettings::twilioWhatsAppMode() === 'business';
        $this->whatsappCallMeBotKeysText = implode("\n", AppSettings::whatsappCallMeBotKeys());
        $this->twilioAccountSid = AppSettings::twilioAccountSid();
        $this->twilioWhatsAppFrom = AppSettings::twilioWhatsAppFrom();
        $this->twilioContentTemplateSid = AppSettings::twilioContentTemplateSid();
        $this->twilioMetaBusinessId = AppSettings::twilioMetaBusinessId();
        $this->adminNotificationsEnabled = AppSettings::adminNotificationsEnabled();
        $this->adminWhatsAppNotificationsEnabled = AppSettings::adminWhatsAppNotificationsEnabled();
        $this->guestNotificationsEnabled = AppSettings::guestNotificationsEnabled();
        $this->guestWhatsAppNotificationsEnabled = AppSettings::guestWhatsAppNotificationsEnabled();
        $this->adminPhonesText = implode("\n", AppSettings::adminPhones());
        $this->testWhatsAppPhone = AppSettings::adminPhones()[0] ?? '';
    }

    public function saveWhatsAppSettings(): void
    {
        $this->persistWhatsAppToggles();
        $this->persistWhatsAppProviderAndTwilio();

        session()->flash('channel_message', 'Configurazione WhatsApp salvata.');
    }

    public function updatedWhatsappTwilioEnabled(bool $value): void
    {
        AppSettings::setWhatsAppProvider($value ? 'twilio' : 'log');
        $this->whatsappProvider = $value ? 'twilio' : 'log';
        session()->flash('channel_message', $value ? 'Twilio attivato.' : 'Twilio disattivato (solo log).');
    }

    public function updatedTwilioBusinessMode(bool $value): void
    {
        AppSettings::setTwilioWhatsAppMode($value ? 'business' : 'sandbox');
        session()->flash('channel_message', $value ? 'Modalità Business ufficiale.' : 'Modalità Sandbox test.');
    }

    public function updatedAdminWhatsAppNotificationsEnabled(bool $value): void
    {
        AppSettings::setAdminWhatsAppNotificationsEnabled($value);
        session()->flash('channel_message', $value ? 'WhatsApp admin acceso.' : 'WhatsApp admin spento.');
    }

    public function updatedGuestWhatsAppNotificationsEnabled(bool $value): void
    {
        AppSettings::setGuestWhatsAppNotificationsEnabled($value);
        session()->flash('channel_message', $value ? 'WhatsApp ospiti acceso.' : 'WhatsApp ospiti spento.');
    }

    public function updatedAdminNotificationsEnabled(bool $value): void
    {
        AppSettings::setAdminNotificationsEnabled($value);
    }

    public function updatedGuestNotificationsEnabled(bool $value): void
    {
        AppSettings::setGuestNotificationsEnabled($value);
    }

    protected function persistWhatsAppToggles(): void
    {
        AppSettings::setAdminNotificationsEnabled($this->adminNotificationsEnabled);
        AppSettings::setAdminWhatsAppNotificationsEnabled($this->adminWhatsAppNotificationsEnabled);
        AppSettings::setGuestNotificationsEnabled($this->guestNotificationsEnabled);
        AppSettings::setGuestWhatsAppNotificationsEnabled($this->guestWhatsAppNotificationsEnabled);
        AppSettings::set('admin_phones', $this->linesToArray($this->adminPhonesText));
    }

    protected function persistWhatsAppProviderAndTwilio(): void
    {
        $provider = $this->whatsappTwilioEnabled ? 'twilio' : 'log';

        AppSettings::setWhatsAppProvider($provider);
        AppSettings::setTwilioWhatsAppMode($this->twilioBusinessMode ? 'business' : 'sandbox');
        AppSettings::setWhatsAppCallMeBotKeys($this->linesToArray($this->whatsappCallMeBotKeysText));
        AppSettings::set('twilio_account_sid', trim($this->twilioAccountSid));
        AppSettings::set('twilio_whatsapp_from', trim($this->twilioWhatsAppFrom) ?: ($this->twilioBusinessMode ? '' : '+14155238886'));
        AppSettings::set('twilio_content_template_sid', trim($this->twilioContentTemplateSid));
        AppSettings::set('twilio_meta_business_id', trim($this->twilioMetaBusinessId));

        if (trim($this->twilioAuthToken) !== '') {
            AppSettings::setTwilioAuthToken(trim($this->twilioAuthToken));
            $this->twilioAuthToken = '';
        }
    }

    public function sendTestWhatsApp(AdminWhatsAppNotifier $notifier, TwilioWhatsAppService $twilio): void
    {
        $provider = AppSettings::whatsappProvider();

        if ($provider === 'twilio') {
            if (! $twilio->isReady()) {
                session()->flash('channel_message', 'Twilio non pronto: inserisci Account SID, Auth Token e numero From, poi Salva.');

                return;
            }

            $phone = trim($this->testWhatsAppPhone);

            if ($phone === '') {
                session()->flash('channel_message', 'Inserisci un numero admin per il test.');

                return;
            }

            $mode = AppSettings::twilioWhatsAppMode() === 'business' ? 'Business' : 'Sandbox';
            $result = $twilio->send(
                $phone,
                "Test WhatsApp Jlune ({$mode})\n\nMessaggio interno di prova.\n".url('/admin/notifiche/whatsapp'),
            );

            if ($result['ok']) {
                session()->flash('channel_message', 'WhatsApp inviato a '.$phone.($result['sid'] ? ' (SID: '.$result['sid'].')' : '').". Modalità: {$mode}.");
            } else {
                $hint = AppSettings::twilioWhatsAppMode() === 'business'
                    ? ' Su Business: verifica numero From ufficiale e, se è il primo messaggio, usa un template approvato o scrivi prima al numero business da WhatsApp.'
                    : ' In Sandbox: il numero deve aver inviato join al sandbox Twilio.';
                session()->flash('channel_message', 'Twilio errore: '.($result['error'] ?? 'sconosciuto').$hint);
            }

            return;
        }

        $notifier->send(
            'Test WhatsApp Jlune',
            'Messaggio di prova dal canale WhatsApp (admin).',
            url('/admin/notifiche/whatsapp'),
            force: true,
        );

        session()->flash('channel_message', 'Provider log: messaggio scritto nel log Laravel.');
    }

    public function sendTestGuestWhatsApp(TwilioWhatsAppService $twilio): void
    {
        if (AppSettings::whatsappProvider() !== 'twilio') {
            session()->flash('channel_message', 'Per test ospite serve Twilio acceso.');

            return;
        }

        if (! $twilio->isReady()) {
            session()->flash('channel_message', 'Twilio non pronto: completa la configurazione e Salva.');

            return;
        }

        $phone = trim($this->testGuestWhatsAppPhone);

        if ($phone === '') {
            session()->flash('channel_message', 'Inserisci un numero per il test ospite.');

            return;
        }

        $result = $twilio->send(
            $phone,
            "Test WhatsApp Jlune (ospite)\n\nSimula un promemoria check-in.\n".url('/admin/notifiche/whatsapp'),
        );

        if ($result['ok']) {
            session()->flash('channel_message', 'WhatsApp ospite inviato a '.$phone.($result['sid'] ? ' (SID: '.$result['sid'].')' : ''));
        } else {
            session()->flash('channel_message', 'Twilio errore ospite: '.($result['error'] ?? 'sconosciuto'));
        }
    }

    /**
     * @return array<int, string>
     */
    protected function linesToArray(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
    }

    /**
     * @return array<string, mixed>
     */
    protected function whatsAppChannelViewData(): array
    {
        return [
            'twilioReady' => AppSettings::twilioReady(),
            'twilioAuthTokenSet' => AppSettings::twilioAuthTokenIsSet(),
            'whatsappChannelReady' => AppSettings::whatsAppChannelReady(),
            'twilioMode' => AppSettings::twilioWhatsAppMode(),
            'parsedAdminPhones' => AppSettings::adminPhones(),
        ];
    }
}
