<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Log;

class AdminPushNotifier
{
    public function notifyAdmins(string $title, string $type = 'general', ?string $url = null): void
    {
        $subscriptions = PushSubscription::query()
            ->where('channel', PushSubscription::CHANNEL_ADMIN)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => 'Gestione Appartamenti',
            'body' => $title,
            'url' => $url ?? '/admin/progetto',
            'tag' => $type,
            'vibrate' => [200, 100, 200],
        ]);

        foreach ($subscriptions as $sub) {
            $this->sendToSubscription($sub, $payload);
        }
    }

    public function notifyGuest(?int $reservationId, string $title, ?string $url = null, bool $force = false): void
    {
        if (! $force && (! AppSettings::guestNotificationsEnabled() || ! AppSettings::guestPushNotificationsEnabled())) {
            return;
        }

        $query = PushSubscription::query()->where('channel', PushSubscription::CHANNEL_GUEST);
        if ($reservationId) {
            $query->where(function ($q) use ($reservationId) {
                $q->whereNull('reservation_id')->orWhere('reservation_id', $reservationId);
            });
        }

        $payload = json_encode([
            'title' => 'Check-in',
            'body' => $title,
            'url' => $url ?? '/',
            'vibrate' => [200, 100, 200],
        ]);

        foreach ($query->get() as $sub) {
            $this->sendToSubscription($sub, $payload);
        }
    }

    protected function sendToSubscription(PushSubscription $sub, string $payload): void
    {
        if (! config('webpush.enabled') || ! class_exists(\Minishlink\WebPush\WebPush::class)) {
            return;
        }

        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');

        if (! filled($publicKey) || ! filled($privateKey)) {
            return;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => config('webpush.vapid.subject'),
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);
            $webPush->queueNotification(
                \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
                ]),
                $payload,
                ['TTL' => 86400, 'urgency' => 'high']
            );

            foreach ($webPush->flush() as $report) {
                if (! $report->isSuccess()) {
                    Log::warning('Web Push fallito', [
                        'reason' => $report->getReason(),
                        'endpoint' => $sub->endpoint,
                    ]);
                    if ($report->isSubscriptionExpired()) {
                        $sub->delete();
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Web Push eccezione: '.$e->getMessage());
        }
    }
}
