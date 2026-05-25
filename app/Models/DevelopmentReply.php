<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentReply extends Model
{
    protected $fillable = [
        'development_item_id',
        'author',
        'body',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(DevelopmentItem::class, 'development_item_id');
    }
}
