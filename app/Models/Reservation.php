<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        'checkfront_fields' => 'array',
        'checkfront_taxes' => 'array',
        'sub_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'extracted_guests' => 'array',
        'contract_ready_for_guest' => 'boolean',
        'contract_accepted' => 'boolean',
        'contract_accepted_at' => 'datetime',
        'contract_extracted_at' => 'datetime',
        'is_test' => 'boolean',
        'notifications_pilot' => 'boolean',
        'telegram_linked_at' => 'datetime',
    ];

    /** In fase costruzione: solo questa prenotazione può ricevere notifiche reali all'ospite. */
    public function allowsGuestNotificationsDelivery(): bool
    {
        if (! \App\Support\AppSettings::underConstruction()) {
            return true;
        }

        return (bool) $this->notifications_pilot;
    }

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

    public static function pendingDocumentReviewCount(): int
    {
        return static::query()
            ->notCancelled()
            ->notPast()
            ->whereNotNull('documents_submitted_at')
            ->where('documents_validated', false)
            ->count();
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

    public function nightsCount(): int
    {
        if (! $this->check_in || ! $this->check_out) {
            return 0;
        }

        return max(1, (int) $this->check_in->copy()->startOfDay()->diffInDays(
            $this->check_out->copy()->startOfDay()
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function apartmentLineItems(): array
    {
        return array_values(array_filter(
            $this->checkfront_line_items ?? [],
            fn ($line) => ($line['line_type'] ?? '') === 'apartment'
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function extraLineItems(): array
    {
        return array_values(array_filter(
            $this->checkfront_line_items ?? [],
            fn ($line) => ($line['line_type'] ?? '') === 'extra'
        ));
    }

    public function guestDisplayName(): string
    {
        $parts = array_filter([$this->guest_name, $this->guest_cognome]);

        return implode(' ', $parts) ?: ($this->guest_name ?? 'Ospite');
    }

    public function checkfrontField(string $key, ?string $default = null): ?string
    {
        $fields = $this->checkfront_fields ?? [];

        return $fields[$key] ?? $default;
    }

    public function guestCountDisplay(): string
    {
        $numpax = $this->checkfrontField('numpax');
        if ($numpax !== null && $numpax !== '') {
            return $numpax.' ospiti';
        }

        $total = (int) ($this->adults ?? 0) + (int) ($this->children ?? 0);

        return $total > 0 ? $total.' ospiti' : '—';
    }

    /**
     * @return array<int, string>
     */
    public function operationalExtrasLabels(): array
    {
        $labels = [];
        $skip = ['tassadisoggiorno', 'fraisbancaires'];

        foreach ($this->extraLineItems() as $line) {
            $sku = strtolower((string) ($line['sku'] ?? ''));
            if ($sku === '' || in_array($sku, $skip, true)) {
                continue;
            }
            $labels[] = config('checkfront.extra_item_labels.'.$sku)
                ?? ($line['name'] ?? $line['label'] ?? $sku);
        }

        $queen = $this->checkfrontField('queen');
        if ($queen !== null && $queen !== '') {
            $labels[] = 'Letto: '.$queen;
        }

        return array_values(array_unique($labels));
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', 'CANCELLED');
    }

    public function scopeNotPast(Builder $query): Builder
    {
        return $query->whereDate('check_out', '>=', today());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('check_out', '<', today());
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'CANCELLED');
    }

    public function scopeArrivingOn(Builder $query, Carbon|string $date): Builder
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query->whereDate('check_in', $d->toDateString());
    }

    public function scopeDepartingOn(Builder $query, Carbon|string $date): Builder
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query->whereDate('check_out', $d->toDateString());
    }

    public function scopeTest(Builder $query): Builder
    {
        return $query->where('is_test', true);
    }

    public function scopeInHouseOn(Builder $query, Carbon|string $date): Builder
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query
            ->notCancelled()
            ->whereDate('check_in', '<=', $d->toDateString())
            ->whereDate('check_out', '>', $d->toDateString());
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

    public function guestNotifications()
    {
        return $this->hasMany(GuestNotification::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function contractGuests(): array
    {
        return $this->extracted_guests ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function italianGuestsMissingTaxCode(): array
    {
        return array_values(array_filter(
            $this->contractGuests(),
            fn ($g) => ! ($g['is_foreigner'] ?? false) && blank($g['data']['tax_code'] ?? null)
        ));
    }

    public function requiresTaxCodeForContract(): bool
    {
        return count($this->italianGuestsMissingTaxCode()) > 0;
    }

    public function setGuestTaxCode(int $slot, string $taxCode): void
    {
        $taxCode = strtoupper(preg_replace('/\s+/', '', $taxCode));
        $guests = $this->extracted_guests ?? [];

        $updated = false;
        foreach ($guests as &$guest) {
            if ((int) ($guest['slot'] ?? 0) === $slot) {
                $guest['data']['tax_code'] = $taxCode;
                $updated = true;
            }
        }

        if (! $updated) {
            $guests[] = [
                'slot' => $slot,
                'name' => "Ospite {$slot}",
                'is_foreigner' => false,
                'data' => ['tax_code' => $taxCode],
            ];
        }

        $this->update(['extracted_guests' => array_values($guests)]);
    }

    public function telegramLinked(): bool
    {
        return filled($this->telegram_chat_id);
    }

    public function telegramDeepLink(): ?string
    {
        $telegram = app(\App\Services\TelegramNotifier::class);

        if (! config('telegram.enabled') || blank($this->token) || blank($telegram->botUsername())) {
            return null;
        }

        return $telegram->deepLink($this->token);
    }
}