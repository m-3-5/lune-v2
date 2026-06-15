<?php

namespace App\Livewire\Admin\Notifiche;

use App\Support\AppSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CanalePushPage extends Component
{
    public function render()
    {
        $webPushEnabled = (bool) config('webpush.enabled');
        $vapidReady = $webPushEnabled
            && filled(config('webpush.vapid.public_key'))
            && filled(config('webpush.vapid.private_key'));

        return view('livewire.admin.notifiche.canale-push-page', [
            'webPushEnabled' => $webPushEnabled,
            'vapidReady' => $vapidReady,
            'vapidPublicKey' => config('webpush.vapid.public_key'),
            'vapidSubject' => config('webpush.vapid.subject'),
            'guestPushOn' => AppSettings::guestPushNotificationsEnabled(),
            'guestNotificationsOn' => AppSettings::guestNotificationsEnabled(),
        ]);
    }
}
