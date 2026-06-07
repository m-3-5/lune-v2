<?php

namespace App\Livewire\Admin;

use App\Models\Reservation;
use App\Services\AdminOutboundNotifier;
use App\Services\GuestOutboundNotifier;
use App\Support\AppSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ProgettoPage extends Component
{
    public bool $adminNotificationsEnabled = false;

    public bool $adminEmailNotificationsEnabled = false;

    public bool $adminWhatsAppNotificationsEnabled = false;

    public string $adminEmailsText = '';

    public string $adminPhonesText = '';

    public bool $guestNotificationsEnabled = false;

    public bool $guestEmailNotificationsEnabled = false;

    public bool $guestWhatsAppNotificationsEnabled = false;

    public bool $guestPushNotificationsEnabled = false;

    public string $guestTestEmail = '';

    public function mount(): void
    {
        $this->adminNotificationsEnabled = AppSettings::adminNotificationsEnabled();
        $this->adminEmailNotificationsEnabled = AppSettings::adminEmailNotificationsEnabled();
        $this->adminWhatsAppNotificationsEnabled = AppSettings::adminWhatsAppNotificationsEnabled();
        $this->adminEmailsText = implode("\n", AppSettings::get('admin_emails', []));
        $this->adminPhonesText = implode("\n", AppSettings::get('admin_phones', []));

        $this->guestNotificationsEnabled = AppSettings::guestNotificationsEnabled();
        $this->guestEmailNotificationsEnabled = AppSettings::guestEmailNotificationsEnabled();
        $this->guestWhatsAppNotificationsEnabled = AppSettings::guestWhatsAppNotificationsEnabled();
        $this->guestPushNotificationsEnabled = AppSettings::guestPushNotificationsEnabled();
    }

    public function saveNotificationSettings(): void
    {
        AppSettings::setAdminNotificationsEnabled($this->adminNotificationsEnabled);
        AppSettings::setAdminEmailNotificationsEnabled($this->adminEmailNotificationsEnabled);
        AppSettings::setAdminWhatsAppNotificationsEnabled($this->adminWhatsAppNotificationsEnabled);
        AppSettings::set('admin_emails', $this->linesToArray($this->adminEmailsText));
        AppSettings::set('admin_phones', $this->linesToArray($this->adminPhonesText));

        AppSettings::setGuestNotificationsEnabled($this->guestNotificationsEnabled);
        AppSettings::setGuestEmailNotificationsEnabled($this->guestEmailNotificationsEnabled);
        AppSettings::setGuestWhatsAppNotificationsEnabled($this->guestWhatsAppNotificationsEnabled);
        AppSettings::setGuestPushNotificationsEnabled($this->guestPushNotificationsEnabled);

        session()->flash('progetto_message', 'Impostazioni notifiche salvate.');
    }

    public function sendTestNotification(AdminOutboundNotifier $notifier): void
    {
        $notifier->notify(
            'Test notifiche Jlune (admin)',
            "Messaggio di prova inviato da Progetto.\nSe ricevi questa email/WhatsApp, la configurazione è ok.",
            url('/admin/progetto'),
            force: true,
        );

        session()->flash('progetto_message', 'Test admin inviato (ignora i toggle disattivati). Controlla email e log WhatsApp.');
    }

    public function sendGuestTestNotification(GuestOutboundNotifier $notifier): void
    {
        $email = trim($this->guestTestEmail);

        if ($email === '') {
            session()->flash('progetto_message', 'Inserisci un\'email di test ospite.');

            return;
        }

        $reservation = new Reservation([
            'guest_email' => $email,
            'guest_phone' => null,
            'guest_name' => 'Ospite di test',
            'is_test' => true,
        ]);
        $reservation->id = 0;

        $notifier->notify(
            $reservation,
            'Anteprima notifica ospite',
            "Questo è il testo che un ospite riceverebbe.\nPrenotazione di prova — nessun invio reale ai clienti Checkfront.",
            url('/admin/progetto'),
            force: true,
        );

        session()->flash('progetto_message', 'Test ospite inviato a '.$email.' (ignora toggle e blocco TEST).');
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
        $entries = AppSettings::projectCostEntries();
        $base = AppSettings::projectBaseCost();
        $extra = collect($entries)->sum(fn ($e) => (float) ($e['amount'] ?? 0));

        return view('livewire.admin.progetto-page', [
            'appGuide' => AppSettings::appGuide(),
            'projectBaseCost' => $base,
            'costEntries' => $entries,
            'totalCost' => $base + $extra,
            'extraSum' => $extra,
            'parsedEmails' => AppSettings::adminEmails(),
            'parsedPhones' => AppSettings::adminPhones(),
            'mailSmtpReady' => AppSettings::mailSmtpReady(),
            'effectiveMailDriver' => config('mail.default'),
            'underConstruction' => AppSettings::underConstruction(),
        ]);
    }
}
