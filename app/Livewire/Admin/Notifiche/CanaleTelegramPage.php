<?php

namespace App\Livewire\Admin\Notifiche;

use App\Models\Reservation;
use App\Support\AppSettings;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CanaleTelegramPage extends Component
{
    public function render()
    {
        $telegramBotReady = config('telegram.enabled') && filled(config('telegram.bot_token'));
        $telegramAdminReady = $telegramBotReady && config('telegram.notify_chat_ids') !== [];
        $guestTelegramOn = AppSettings::guestTelegramNotificationsEnabled();

        $telegramLinkedCount = Reservation::query()
            ->whereNotNull('telegram_chat_id')
            ->notCancelled()
            ->notPast()
            ->count();

        $webhookUrl = $telegramBotReady
            ? URL::to('/webhook/telegram/'.(config('telegram.webhook_secret') ?: 'IMPOSTA_TELEGRAM_WEBHOOK_SECRET'))
            : null;

        return view('livewire.admin.notifiche.canale-telegram-page', [
            'telegramBotReady' => $telegramBotReady,
            'telegramAdminReady' => $telegramAdminReady,
            'guestTelegramOn' => $guestTelegramOn,
            'adminTelegramOn' => AppSettings::adminNotificationsEnabled(),
            'telegramBotUsername' => ltrim((string) config('telegram.bot_username', ''), '@'),
            'webhookUrl' => $webhookUrl,
            'telegramLinkedCount' => $telegramLinkedCount,
            'notifyChatIds' => config('telegram.notify_chat_ids', []),
        ]);
    }
}
