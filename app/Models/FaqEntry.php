<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FaqEntry extends Model
{
    protected $guarded = [];

    public function scopeAudience(Builder $query, string $audience): Builder
    {
        return $query->where('audience', $audience);
    }

    /** Parole troppo comuni per essere utili a distinguere una domanda dall'altra. */
    protected const STOPWORDS = ['come', 'cosa', 'dove', 'quando', 'chi', 'che', 'per', 'gli', 'una', 'del', 'della', 'con', 'non', 'nel', 'nella'];

    /**
     * Punteggio di rilevanza: quante parole del testo digitato (esclusi termini troppo comuni)
     * compaiono in domanda+parole chiave.
     */
    public function relevanceFor(string $typed): int
    {
        $haystack = mb_strtolower($this->question.' '.$this->keywords);
        $words = array_filter(
            preg_split('/\s+/', mb_strtolower(trim($typed))),
            fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, self::STOPWORDS, true)
        );

        $score = 0;
        foreach ($words as $word) {
            if (str_contains($haystack, $word)) {
                $score++;
            }
        }

        return $score;
    }
}
