<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevelopmentItem extends Model
{
    public const TYPE_SERENELLA_REQUEST = 'serenella_request';

    public const TYPE_PRODUCTION_TASK = 'production_task';

    public const TYPE_QUESTION_FOR_SERENELLA = 'question_for_serenella';

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    protected $fillable = [
        'type',
        'status',
        'title',
        'body',
        'test_instructions',
        'author',
    ];

    public function replies(): HasMany
    {
        return $this->hasMany(DevelopmentReply::class)->orderBy('created_at');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_SERENELLA_REQUEST => 'Richiesta Serenella',
            self::TYPE_PRODUCTION_TASK => 'Produzione',
            self::TYPE_QUESTION_FOR_SERENELLA => 'Domanda per Serenella',
            default => $this->type,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Da fare',
            self::STATUS_IN_PROGRESS => 'In corso',
            self::STATUS_DONE => 'Completata',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'bg-gray-100 text-gray-700',
            self::STATUS_IN_PROGRESS => 'bg-amber-100 text-amber-900',
            self::STATUS_DONE => 'bg-green-100 text-green-800',
            default => 'bg-gray-100',
        };
    }
}
