<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Reservation;
use Illuminate\Support\Str;

class TestReservationService
{
    /**
     * @param  array<int, string>  $extraSkus
     */
    public function create(array $input, array $extraSkus = [], bool $isTest = true): Reservation
    {
        $apartment = Apartment::findOrFail($input['apartment_id']);
        $checkIn = $input['check_in'].' 16:00:00';
        $checkOut = $input['check_out'].' 10:00:00';
        $suffix = strtoupper(Str::random(6));
        $prefix = $isTest ? 'TEST' : 'MAN';
        $bookingId = "{$prefix}-".now()->format('ymdHis')."-{$suffix}";

        $lineItems = $this->buildLineItems($apartment, $extraSkus);
        $notes = trim((string) ($input['test_notes'] ?? ''));
        $isPaid = (bool) ($input['is_paid'] ?? true);
        $price = (float) ($input['total_price'] ?? 100);

        return Reservation::create([
            'apartment_id' => $apartment->id,
            'checkfront_booking_id' => $bookingId,
            'checkfront_item_id' => $apartment->checkfront_item_id,
            'booking_code' => "{$prefix}-{$suffix}",
            'status' => 'CONFIRMED',
            'checkfront_line_items' => $lineItems,
            'checkfront_fields' => [],
            'guest_name' => $input['guest_name'],
            'guest_cognome' => $input['guest_cognome'] ?? null,
            'guest_email' => $input['guest_email'] ?? null,
            'guest_phone' => $input['guest_phone'] ?? null,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => (int) ($input['adults'] ?? 1),
            'children' => (int) ($input['children'] ?? 0),
            'token' => Str::random(12),
            'checkfront_payment_url' => config('checkfront.payment_url').'?code='."{$prefix}-{$suffix}",
            'is_paid' => $isPaid,
            'total_price' => $price,
            'paid_total' => $isPaid ? $price : 0,
            'balance' => $isPaid ? 0 : $price,
            'documents_validated' => false,
            'is_test' => $isTest,
            'test_notes' => $notes,
            'internal_comment' => $isTest
                ? '[TEST] Prenotazione creata manualmente da Sviluppo.'.($notes ? "\n".$notes : '')
                : 'Prenotazione manuale inserita da admin (non da Checkfront).'.($notes ? "\n".$notes : ''),
        ]);
    }

    public function delete(Reservation $reservation): void
    {
        if (! $reservation->is_test) {
            throw new \InvalidArgumentException('Solo le prenotazioni di test possono essere eliminate da qui.');
        }

        $reservation->delete();
    }

    /**
     * @param  array<int, string>  $extraSkus
     * @return array<int, array<string, mixed>>
     */
    protected function buildLineItems(Apartment $apartment, array $extraSkus): array
    {
        $lines = [[
            'line_type' => 'apartment',
            'sku' => $apartment->sku,
            'name' => $apartment->name ?? $apartment->sku,
            'item_id' => $apartment->checkfront_item_id,
        ]];

        foreach ($extraSkus as $sku) {
            $sku = strtolower(trim($sku));
            if ($sku === '') {
                continue;
            }
            $lines[] = [
                'line_type' => 'extra',
                'sku' => $sku,
                'name' => config('checkfront.extra_item_labels.'.$sku) ?? $sku,
            ];
        }

        return $lines;
    }

    /**
     * @return array<string, string>
     */
    public static function availableExtras(): array
    {
        return config('checkfront.extra_item_labels', []);
    }
}
