<?php

namespace App\Livewire\Guest;

use App\Models\GuestNotification;
use App\Models\Reservation;
use Livewire\Component;

class NotificationsBell extends Component
{
    public int $reservationId;

    public bool $open = false;

    public function mount(int $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markRead(int $id): void
    {
        GuestNotification::query()
            ->forReservation($this->reservationId)
            ->whereKey($id)
            ->first()
            ?->markRead();
    }

    public function markAllRead(): void
    {
        GuestNotification::query()
            ->forReservation($this->reservationId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $reservation = Reservation::find($this->reservationId);

        return view('livewire.guest.notifications-bell', [
            'reservation' => $reservation,
            'unreadCount' => GuestNotification::unreadCountFor($this->reservationId),
            'notifications' => GuestNotification::query()
                ->forReservation($this->reservationId)
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
