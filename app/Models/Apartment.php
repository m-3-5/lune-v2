<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'checkfront_item_id',
        'checkfront_category_id',
        'display_order',
        'address',
        'pre_booking_info',
        'house_rules',
        'stay_info',
        'checkout_info',
        'checkin_video_url',
        'whatsapp_number',
        'default_checkin_hour',
        'access_code',
        'checkfront_name',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
