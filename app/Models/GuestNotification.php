<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestNotification extends Model
{
    protected $fillable = [
        'reservation_id',
        'type',
        'title',
        'body',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForReservation($query, int $reservationId)
    {
        return $query->where('reservation_id', $reservationId);
    }

    public static function unreadCountFor(int $reservationId): int
    {
        return static::query()
            ->forReservation($reservationId)
            ->unread()
            ->count();
    }
}
