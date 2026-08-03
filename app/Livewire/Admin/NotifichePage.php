<?php

namespace App\Livewire\Admin;

use App\Models\Reservation;
use App\Support\AppSettings;
use App\Support\JluneDeveloperAccess;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class NotifichePage extends Component
{
    public function render()
    {
        $underConstruction = AppSettings::underConstruction();
        $adminOn = AppSettings::adminNotificationsEnabled();
        $guestOn = AppSettings::guestNotificationsEnabled();
        $mailReady = AppSettings::mailSmtpReady();
        $whatsappReady = AppSettings::whatsAppChannelReady();
        $telegramBotReady = config('telegram.enabled') && filled(config('telegram.bot_token'));
        $telegramAdminReady = $telegramBotReady && config('telegram.notify_chat_ids') !== [];
        $guestTelegramOn = AppSettings::guestTelegramNotificationsEnabled();

        $guestCanReceive = ! $underConstruction && $guestOn && (
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
            'underConstruction' => $underConstruction,
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
            'canToggleConstruction' => JluneDeveloperAccess::isGranted(),
            'notificationMatrix' => self::notificationMatrix(),
            'liveChecklist' => self::liveChecklist(
                $underConstruction,
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
     * @return array<int, array{event: string, admin: string, guest: string, when: string}>
     */
    protected static function notificationMatrix(): array
    {
        return [
            [
                'event' => 'Nuova prenotazione Checkfront',
                'admin' => 'Campanella + email/WhatsApp (se attivi)',
                'guest' => '—',
                'when' => 'All\'arrivo webhook',
            ],
            [
                'event' => 'Ospite carica documenti',
                'admin' => 'Campanella + email/WhatsApp',
                'guest' => 'Conferma ricezione + «in verifica»',
                'when' => 'Subito dopo l\'invio',
            ],
            [
                'event' => 'Documenti approvati / rifiutati',
                'admin' => 'Campanella',
                'guest' => 'Email/WhatsApp/push (se attivi)',
                'when' => 'Quando il gestore approva o rifiuta',
            ],
            [
                'event' => 'Contratto pronto per la firma',
                'admin' => 'Campanella',
                'guest' => '«Firma il contratto» (link diretto)',
                'when' => 'Dopo «Contratto pronto» in admin',
            ],
            [
                'event' => 'Codice fiscale mancante',
                'admin' => '—',
                'guest' => 'Promemoria «Inserisci il CF»',
                'when' => 'Contratto pronto ma CF assente',
            ],
            [
                'event' => 'Contratto firmato',
                'admin' => 'Campanella + email/WhatsApp',
                'guest' => 'Email con PDF allegato',
                'when' => 'Alla firma elettronica',
            ],
            [
                'event' => 'Pagamento / documenti in sospeso',
                'admin' => '—',
                'guest' => 'Promemoria automatici (cron 10:00)',
                'when' => 'Arrivo entro 14 giorni',
            ],
            [
                'event' => 'Promemoria arrivo / check-out',
                'admin' => '—',
                'guest' => 'Email/WhatsApp/push (se attivi)',
                'when' => 'Cron giornaliero + visita portale',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, done: bool, hint: string}>
     */
    protected static function liveChecklist(
        bool $underConstruction,
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
                'label' => 'Work in progress disattivato',
                'done' => ! $underConstruction,
                'hint' => 'Solo team sviluppo può spegnerlo (Sviluppo).',
            ],
            [
                'label' => 'Pronto a inviare agli ospiti reali',
                'done' => $guestCanReceive,
                'hint' => 'Richiede ospite ON + canali ok + work in progress OFF.',
            ],
        ];
    }
}
