<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Services\AdminNotificationService;
use App\Services\GuestNotificationService;
use App\Support\CheckfrontDates;
use App\Support\CheckfrontPayload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckfrontBookingSync
{
    public function __construct(
        protected CheckfrontService $checkfront
    ) {}

    /**
     * @return array{reservation: Reservation}|array{error: string, status: int}
     */
    public function syncFromWebhook(array $data): array
    {
        return $this->syncFromBooking($data['booking'] ?? []);
    }

    /**
     * @return array{reservation: Reservation}|array{error: string, status: int}
     */
    public function syncFromBooking(array $booking): array
    {
        $checkfrontBookingId = $booking['@attributes']['booking_id']
            ?? $booking['booking_id']
            ?? null;

        if (! $checkfrontBookingId) {
            return ['error' => 'booking_id mancante', 'status' => 422];
        }

        $bookingCode = $booking['code'] ?? null;
        $checkfrontStatus = $booking['status'] ?? $booking['status_id'] ?? null;
        $status = $checkfrontStatus === 'STOP' ? 'CANCELLED' : $checkfrontStatus;

        $rawItems = $booking['order']['items']['item'] ?? null;
        $allLineItems = $this->normalizeOrderItems($rawItems);
        $lineItem = $this->resolveApartmentLineItem($rawItems);

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

        $customer = $this->parseCustomer($booking);
        $checkfrontFields = $this->parseCheckfrontFields($booking);
        $guestCounts = $this->parseGuestCount($booking);
        $bookingLanguage = CheckfrontPayload::scalar($booking['meta']['booking_language'] ?? null);

        $startTimestamp = $booking['start_date'] ?? null;
        $endTimestamp = $booking['end_date'] ?? null;

        $checkIn = CheckfrontDates::toCheckInDatetime($startTimestamp)
            ?? now()->timezone(CheckfrontDates::timezone())->format('Y-m-d 16:00:00');

        $checkOut = CheckfrontDates::toCheckOutDatetime($endTimestamp)
            ?? now()->timezone(CheckfrontDates::timezone())->addDay()->format('Y-m-d 10:00:00');

        $order = $booking['order'] ?? [];
        $financials = $this->parseFinancials($order);

        $existing = Reservation::where('checkfront_booking_id', $checkfrontBookingId)->first();
        $financials = $this->mergeFinancials($financials, $existing);

        $isPaid = $this->checkfront->hasDepositOrPaid($financials['paid_total'], $financials['total_price']);

        if (! $isPaid && $checkfrontBookingId && $financials['paid_total'] <= 0) {
            $isPaid = $this->checkfront->isBookingFullyPaid((string) $checkfrontBookingId);
            if ($isPaid && $financials['total_price'] > 0) {
                $financials['paid_total'] = $financials['total_price'];
                $financials['balance'] = 0;
            }
        }

        $reservation = Reservation::updateOrCreate(
            ['checkfront_booking_id' => $checkfrontBookingId],
            [
                'apartment_id' => $apartment->id,
                'checkfront_item_id' => $lineItem['item_id'] ?? null,
                'checkfront_customer_code' => $customer['code'],
                'checkfront_line_items' => $allLineItems,
                'checkfront_fields' => $checkfrontFields,
                'checkfront_taxes' => CheckfrontPayload::normalizeTaxes($order['taxes'] ?? []),
                'guest_name' => $customer['name'],
                'guest_cognome' => $customer['cognome'],
                'guest_email' => $customer['email'],
                'guest_phone' => $customer['phone'],
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $guestCounts['adults'],
                'children' => $guestCounts['children'],
                'token' => $existing?->token ?? Str::random(10),
                'checkfront_payment_url' => $this->checkfront->paymentUrlForCode($bookingCode),
                'is_paid' => $isPaid,
                'total_price' => $financials['total_price'],
                'sub_total' => $financials['sub_total'],
                'tax_total' => $financials['tax_total'],
                'paid_total' => $financials['paid_total'],
                'balance' => $financials['balance'],
                'booking_code' => $bookingCode,
                'checkfront_status' => $checkfrontStatus,
                'status' => $status,
                'checkfront_language' => $bookingLanguage,
            ]
        );

        Log::info("✅ Sincronizzazione Completata: Prenotazione {$bookingCode} → {$customer['name']} (apt: {$apartment->sku})");

        if ($reservation->wasRecentlyCreated) {
            app(AdminNotificationService::class)->bookingSynced($reservation);
        }

        $guestNotifications = app(GuestNotificationService::class);
        if ($existing && ! $existing->is_paid && $isPaid) {
            $guestNotifications->notify(
                $reservation,
                GuestNotificationService::TYPE_DOCUMENTS_REQUIRED,
                'Pagamento ricevuto',
                'Ora puoi caricare i documenti dal menu Inserimento Documenti.',
                route('checkin.documents', ['token' => $reservation->token]),
                dedupeHours: 0,
            );
        } elseif (! $isPaid) {
            $guestNotifications->paymentReminder($reservation);
        }

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
        $apartmentCategories = config('checkfront.apartment_category_ids', []);

        foreach ($lines as $line) {
            if (($line['line_type'] ?? '') === 'apartment') {
                return $line;
            }
        }

        foreach ($lines as $line) {
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
        $sku = strtolower((string) ($item['sku'] ?? ''));
        $categoryId = (string) ($item['category_id'] ?? '');
        $apartmentCategories = config('checkfront.apartment_category_ids', []);
        $extraSkus = config('checkfront.excluded_item_skus', []);
        $labels = config('checkfront.extra_item_labels', []);

        $lineType = 'other';
        if (in_array($categoryId, $apartmentCategories, true) || Apartment::where('sku', $sku)->exists()) {
            $lineType = 'apartment';
        } elseif (in_array($sku, $extraSkus, true) || $categoryId === '5') {
            $lineType = 'extra';
        }

        $label = $labels[$sku] ?? ucfirst(str_replace('_', ' ', $sku));

        return [
            'item_id' => (string) ($attrs['item_id'] ?? $item['item_id'] ?? ''),
            'sku' => (string) ($item['sku'] ?? ''),
            'label' => $label,
            'line_type' => $lineType,
            'category_id' => $categoryId,
            'line_id' => (string) ($attrs['line_id'] ?? ''),
            'total' => (string) ($item['total'] ?? '0'),
            'tax_total' => (string) ($item['tax_total'] ?? '0'),
            'status' => (string) ($item['status'] ?? ''),
            'qty' => (string) ($item['qty'] ?? '1'),
        ];
    }

    /**
     * @return array{name: string, cognome: ?string, email: ?string, phone: ?string, code: ?string}
     */
    protected function parseCustomer(array $booking): array
    {
        $fields = $booking['fields'] ?? [];
        $customer = $booking['customer'] ?? [];
        $meta = $booking['meta'] ?? [];

        return [
            'name' => CheckfrontPayload::scalar($customer['name'])
                ?? CheckfrontPayload::scalar($fields['customer_name'])
                ?? CheckfrontPayload::scalar($meta['customer_name'])
                ?? 'Ospite Sconosciuto',
            'cognome' => CheckfrontPayload::scalar($fields['cognome'])
                ?? CheckfrontPayload::scalar($meta['cognome']),
            'email' => CheckfrontPayload::scalar($customer['email'])
                ?? CheckfrontPayload::scalar($fields['customer_email'])
                ?? CheckfrontPayload::scalar($meta['customer_email']),
            'phone' => CheckfrontPayload::scalar($customer['phone'])
                ?? CheckfrontPayload::scalar($fields['customer_phone'])
                ?? CheckfrontPayload::scalar($meta['customer_phone']),
            'code' => CheckfrontPayload::scalar($customer['code'] ?? null),
        ];
    }

    /**
     * Campi custom Checkfront utili in reception.
     *
     * @return array<string, string|null>
     */
    protected function parseCheckfrontFields(array $booking): array
    {
        $fields = $booking['fields'] ?? [];
        $meta = $booking['meta'] ?? [];

        $keys = [
            'numpax',
            'queen',
            'note',
            'oradiarrivo',
            'customer_gender',
            'cognome',
        ];

        $out = [];
        foreach ($keys as $key) {
            $value = CheckfrontPayload::scalar($fields[$key] ?? null)
                ?? CheckfrontPayload::scalar($meta[$key] ?? null);
            if ($value !== null) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * @return array{adults: int, children: int}
     */
    protected function parseGuestCount(array $booking): array
    {
        $numpax = (int) (
            CheckfrontPayload::scalar($booking['fields']['numpax'] ?? null)
            ?? CheckfrontPayload::scalar($booking['meta']['numpax'] ?? null)
            ?? 1
        );

        return [
            'adults' => max(1, $numpax),
            'children' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $order
     * @return array{total_price: float, sub_total: float, tax_total: float, paid_total: float, balance: float}
     */
    protected function parseFinancials(array $order): array
    {
        $total = CheckfrontPayload::float($order['total'] ?? 0);
        $subTotal = CheckfrontPayload::float($order['sub_total'] ?? 0);
        $taxTotal = CheckfrontPayload::float($order['tax_total'] ?? 0);
        $paid = CheckfrontPayload::float($order['paid_total'] ?? 0);

        return [
            'total_price' => $total,
            'sub_total' => $subTotal,
            'tax_total' => $taxTotal,
            'paid_total' => $paid,
            'balance' => max(0, $total - $paid),
        ];
    }

    /**
     * Evita di azzerare totali validi quando Checkfront manda aggiornamenti parziali (es. solo pulizia).
     *
     * @param  array{total_price: float, sub_total: float, tax_total: float, paid_total: float, balance: float}  $incoming
     * @return array{total_price: float, sub_total: float, tax_total: float, paid_total: float, balance: float}
     */
    protected function mergeFinancials(array $incoming, ?Reservation $existing): array
    {
        if (! $existing) {
            return $incoming;
        }

        foreach (['total_price', 'sub_total', 'tax_total', 'paid_total'] as $field) {
            if ($incoming[$field] <= 0 && ($existing->{$field} ?? 0) > 0) {
                $incoming[$field] = (float) $existing->{$field};
            }
        }

        $incoming['balance'] = max(0, $incoming['total_price'] - $incoming['paid_total']);

        return $incoming;
    }
}
