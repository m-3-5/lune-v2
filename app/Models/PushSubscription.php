<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    public const CHANNEL_ADMIN = 'admin';

    public const CHANNEL_GUEST = 'guest';

    protected $fillable = [
        'channel',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'reservation_id',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
