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
        'documents_submitted_at' => 'datetime',
        'is_paid' => 'boolean',
        'documents_validated' => 'boolean',
        'total_price' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance' => 'decimal:2',
        'checkfront_line_items' => 'array',
        'extracted_guests' => 'array',
        'contract_ready_for_guest' => 'boolean',
        'contract_extracted_at' => 'datetime',
    ];

    protected $appends = [
        'guest_portal_url',
    ];

    public function getGuestPortalUrlAttribute(): string
    {
        return url('/checkin/'.$this->token);
    }

    public function hasDocumentsPendingReview(): bool
    {
        return $this->documents_submitted_at !== null && ! $this->documents_validated;
    }

    public function paymentLabel(): string
    {
        if (! $this->is_paid) {
            return 'Non pagato';
        }
        if ($this->balance > 0) {
            return 'Acconto';
        }

        return 'Saldo OK';
    }

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