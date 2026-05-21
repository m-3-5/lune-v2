<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckfrontBookingSync
{
    public function __construct(
        protected CheckfrontService $checkfront
    ) {}

    /**
     * Sincronizza una prenotazione dal payload webhook Checkfront.
     *
     * @return array{reservation: Reservation}|array{error: string, status: int}
     */
    public function syncFromWebhook(array $data): array
    {
        $booking = $data['booking'] ?? [];
        $checkfrontBookingId = $booking['@attributes']['booking_id'] ?? null;

        if (! $checkfrontBookingId) {
            return ['error' => 'booking_id mancante', 'status' => 422];
        }

        $bookingCode = $booking['code'] ?? null;
        $checkfrontStatus = $booking['status'] ?? null;
        $status = $checkfrontStatus === 'STOP' ? 'CANCELLED' : $checkfrontStatus;

        $lineItem = $this->resolveApartmentLineItem($booking['order']['items']['item'] ?? null);

        if (! $lineItem) {
            Log::error('Checkfront webhook: nessuna riga appartamento trovata', [
                'booking_id' => $checkfrontBookingId,
            ]);

            return ['error' => 'Appartamento non riconosciuto nel payload', 'status' => 404];
        }

        $apartment = $this->findApartment($lineItem);

        if (! $apartment) {
            Log::error('Checkfront webhook: appartamento non in anagrafica', [
                'booking_id' => $checkfrontBookingId,
                'item_id' => $lineItem['item_id'] ?? null,
                'sku' => $lineItem['sku'] ?? null,
            ]);

            return ['error' => 'Appartamento non censito in apartments', 'status' => 404];
        }

        $guestName = $booking['customer']['name']
            ?? $booking['fields']['customer_name']
            ?? 'Ospite Sconosciuto';

        $guestEmail = $this->scalarOrNull($booking['customer']['email'] ?? null)
            ?? $this->scalarOrNull($booking['fields']['customer_email'] ?? null);

        $startTimestamp = $booking['start_date'] ?? null;
        $endTimestamp = $booking['end_date'] ?? null;

        $checkIn = $startTimestamp
            ? Carbon::createFromTimestamp((int) $startTimestamp)->format('Y-m-d 16:00:00')
            : now()->format('Y-m-d 16:00:00');

        $checkOut = $endTimestamp
            ? Carbon::createFromTimestamp((int) $endTimestamp)->format('Y-m-d 10:00:00')
            : now()->addDay()->format('Y-m-d 10:00:00');

        $order = $booking['order'] ?? [];
        $totalPrice = (float) ($order['total'] ?? 0);
        $paidTotal = (float) ($order['paid_total'] ?? 0);
        $balance = max(0, $totalPrice - $paidTotal);

        $isPaid = $this->checkfront->hasDepositOrPaid($paidTotal, $totalPrice);

        if (! $isPaid && $checkfrontBookingId) {
            $isPaid = $this->checkfront->isBookingFullyPaid((string) $checkfrontBookingId);
            if ($isPaid) {
                $paidTotal = $totalPrice;
                $balance = 0;
            }
        }

        $allLineItems = $this->normalizeOrderItems($booking['order']['items']['item'] ?? null);
        $guestCounts = $this->parseGuestCount($booking);
        $bookingLanguage = $booking['meta']['booking_language'] ?? null;

        $existing = Reservation::where('checkfront_booking_id', $checkfrontBookingId)->first();

        $reservation = Reservation::updateOrCreate(
            ['checkfront_booking_id' => $checkfrontBookingId],
            [
                'apartment_id' => $apartment->id,
                'checkfront_item_id' => $lineItem['item_id'] ?? null,
                'checkfront_line_items' => $allLineItems,
                'guest_name' => $guestName,
                'guest_email' => $guestEmail,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $guestCounts['adults'],
                'children' => $guestCounts['children'],
                'token' => $existing?->token ?? Str::random(10),
                'checkfront_payment_url' => $this->checkfront->paymentUrlForCode($bookingCode),
                'is_paid' => $isPaid,
                'total_price' => $totalPrice,
                'paid_total' => $paidTotal,
                'balance' => $balance,
                'booking_code' => $bookingCode,
                'checkfront_status' => $checkfrontStatus,
                'status' => $status,
                'checkfront_language' => $bookingLanguage,
            ]
        );

        Log::info("✅ Sincronizzazione Completata: Prenotazione {$bookingCode} → {$guestName} (apt: {$apartment->sku})");

        return ['reservation' => $reservation];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function normalizeOrderItems(mixed $items): array
    {
        if ($items === null || $items === []) {
            return [];
        }

        if (isset($items['sku']) || isset($items['@attributes'])) {
            return [$this->normalizeLineItem($items)];
        }

        $lines = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $lines[] = $this->normalizeLineItem($item);
            }
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolveApartmentLineItem(mixed $items): ?array
    {
        $lines = $this->normalizeOrderItems($items);
        $excludedSkus = config('checkfront.excluded_item_skus', []);
        $apartmentCategories = config('checkfront.apartment_category_ids', []);

        foreach ($lines as $line) {
            $sku = strtolower((string) ($line['sku'] ?? ''));
            if (in_array($sku, $excludedSkus, true)) {
                continue;
            }
            $categoryId = (string) ($line['category_id'] ?? '');
            if (in_array($categoryId, $apartmentCategories, true)) {
                return $line;
            }
        }

        foreach ($lines as $line) {
            $sku = strtolower((string) ($line['sku'] ?? ''));
            if ($sku !== '' && Apartment::where('sku', $sku)->exists()) {
                return $line;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $lineItem
     */
    public function findApartment(array $lineItem): ?Apartment
    {
        $itemId = $lineItem['item_id'] ?? null;
        $sku = $lineItem['sku'] ?? null;

        if ($itemId) {
            $byId = Apartment::where('checkfront_item_id', (string) $itemId)->first();
            if ($byId) {
                return $byId;
            }
        }

        if ($sku) {
            return Apartment::where('sku', $sku)->first();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeLineItem(array $item): array
    {
        $attrs = $item['@attributes'] ?? [];

        return [
            'item_id' => (string) ($attrs['item_id'] ?? $item['item_id'] ?? ''),
            'sku' => (string) ($item['sku'] ?? ''),
            'category_id' => (string) ($item['category_id'] ?? ''),
            'line_id' => (string) ($attrs['line_id'] ?? ''),
            'total' => (string) ($item['total'] ?? ''),
            'status' => (string) ($item['status'] ?? ''),
            'qty' => (string) ($item['qty'] ?? '1'),
        ];
    }

    /**
     * @return array{adults: int, children: int}
     */
    protected function parseGuestCount(array $booking): array
    {
        $numpax = (int) ($booking['fields']['numpax'] ?? $booking['meta']['numpax'] ?? 1);

        return [
            'adults' => max(1, $numpax),
            'children' => 0,
        ];
    }

    protected function scalarOrNull(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }
}
