<?php

namespace App\Livewire\Admin;

use App\Models\Reservation;
use App\Support\AppSettings;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class NotifichePage extends Component
{
    public function render()
    {
        $adminOn = AppSettings::adminNotificationsEnabled();
        $guestOn = AppSettings::guestNotificationsEnabled();
        $mailReady = AppSettings::mailSmtpReady();
        $whatsappReady = AppSettings::whatsAppChannelReady();
        $telegramBotReady = config('telegram.enabled') && filled(config('telegram.bot_token'));
        $telegramAdminReady = $telegramBotReady && config('telegram.notify_chat_ids') !== [];
        $guestTelegramOn = AppSettings::guestTelegramNotificationsEnabled();

        $guestCanReceive = $guestOn && (
            (AppSettings::guestEmailNotificationsEnabled() && $mailReady)
            || (AppSettings::guestWhatsAppNotificationsEnabled() && $whatsappReady)
            || (AppSettings::guestTelegramNotificationsEnabled() && $telegramBotReady)
            || AppSettings::guestPushNotificationsEnabled()
        );

        $adminCanReceive = $adminOn && (
            (AppSettings::adminEmailNotificationsEnabled() && $mailReady)
            || (AppSettings::adminWhatsAppNotificationsEnabled() && $whatsappReady)
            || $telegramAdminReady
        );

        $pilotCount = Reservation::query()
            ->where('notifications_pilot', true)
            ->notCancelled()
            ->notPast()
            ->count();

        $telegramLinkedCount = Reservation::query()
            ->whereNotNull('telegram_chat_id')
            ->notCancelled()
            ->notPast()
            ->count();

        $webhookUrl = $telegramBotReady
            ? URL::to('/webhook/telegram/'.(config('telegram.webhook_secret') ?: 'IMPOSTA_TELEGRAM_WEBHOOK_SECRET'))
            : null;

        return view('livewire.admin.notifiche-page', [
            'adminOn' => $adminOn,
            'guestOn' => $guestOn,
            'guestTelegramOn' => $guestTelegramOn,
            'mailReady' => $mailReady,
            'whatsappReady' => $whatsappReady,
            'telegramBotReady' => $telegramBotReady,
            'telegramAdminReady' => $telegramAdminReady,
            'telegramBotUsername' => ltrim((string) config('telegram.bot_username', ''), '@'),
            'webhookUrl' => $webhookUrl,
            'telegramLinkedCount' => $telegramLinkedCount,
            'whatsappProvider' => AppSettings::whatsappProvider(),
            'guestCanReceive' => $guestCanReceive,
            'adminCanReceive' => $adminCanReceive,
            'pilotCount' => $pilotCount,
            'adminEmails' => AppSettings::adminEmails(),
            'adminPhones' => AppSettings::adminPhones(),
            'isSuperAdmin' => auth()->user()?->isSuperAdmin() ?? false,
            'liveChecklist' => self::liveChecklist(
                $mailReady,
                $whatsappReady,
                $adminOn,
                $guestOn,
                $adminCanReceive,
                $guestCanReceive,
            ),
        ])->title('Notifiche');
    }

    /**
     * @return array<int, array{label: string, done: bool, hint: string}>
     */
    protected static function liveChecklist(
        bool $mailReady,
        bool $whatsappReady,
        bool $adminOn,
        bool $guestOn,
        bool $adminCanReceive,
        bool $guestCanReceive,
    ): array {
        return [
            [
                'label' => 'Email SMTP configurata (Notifiche → Email)',
                'done' => $mailReady,
                'hint' => 'Host, password e test email ok.',
            ],
            [
                'label' => 'WhatsApp configurato (Twilio o CallMeBot)',
                'done' => $whatsappReady,
                'hint' => 'Opzionale ma consigliato per avvisi rapidi.',
            ],
            [
                'label' => 'Notifiche admin attive con contatti inseriti',
                'done' => $adminOn && count(AppSettings::adminEmails()) + count(AppSettings::adminPhones()) > 0,
                'hint' => 'Toggle e email/cellulari in Progetto e task.',
            ],
            [
                'label' => 'Test admin inviato con successo',
                'done' => $adminCanReceive,
                'hint' => 'Pulsante «Invia test admin» in Progetto.',
            ],
            [
                'label' => 'Notifiche ospite attive (quando andate live)',
                'done' => $guestOn,
                'hint' => 'Restano spente finché non siete pronti.',
            ],
            [
                'label' => 'Pronto a inviare agli ospiti reali',
                'done' => $guestCanReceive,
                'hint' => 'Richiede ospite ON + almeno un canale pronto.',
            ],
        ];
    }
}
