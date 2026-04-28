<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    // Permette a Laravel di scrivere in tutte le colonne della tabella
    protected $guarded = [];

    // Converte automaticamente le date del database in oggetti Carbon (per poter usare ->format() ecc.)
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'is_paid' => 'boolean',
    ];

    /**
     * Relazione: Ogni prenotazione appartiene a un Appartamento
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Relazione: Una prenotazione può avere molti documenti caricati
     */
    public function guestDocuments()
    {
        return $this->hasMany(GuestDocument::class);
    }

    // Nota: Abbiamo rimosso temporaneamente l'invio automatico delle email
    // per evitare errori finché non configureremo il server di posta.
}