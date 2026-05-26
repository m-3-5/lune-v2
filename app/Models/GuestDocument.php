<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ai_raw_response' => 'array',
        'date_of_birth' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
