<?php

namespace App\Livewire\Admin;

use App\Models\AdminNotification;
use Livewire\Component;

class NotificationsBell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markRead(int $id): void
    {
        AdminNotification::find($id)?->markRead();
    }

    public function markAllRead(): void
    {
        AdminNotification::query()->unread()->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.admin.notifications-bell', [
            'unreadCount' => AdminNotification::unreadCount(),
            'notifications' => AdminNotification::query()
                ->with('reservation.apartment')
                ->latest()
                ->limit(30)
                ->get(),
        ]);
    }
}
