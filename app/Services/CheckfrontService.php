<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckfrontService
{
    protected string $host;

    protected ?string $apiKey;

    protected ?string $apiSecret;

    public function __construct()
    {
        $this->host = config('checkfront.host');
        $this->apiKey = config('checkfront.api_key');
        $this->apiSecret = config('checkfront.api_secret');
    }

    public function isConfigured(): bool
    {
        return filled($this->host) && filled($this->apiKey) && filled($this->apiSecret);
    }

    /**
     * GET sicuro verso API Checkfront 3.0 (credenziali solo server-side).
     */
    public function apiGet(string $path, array $query = []): ?Response
    {
        if (! $this->isConfigured()) {
            Log::warning('Checkfront API: credenziali mancanti in .env');

            return null;
        }

        $path = ltrim($path, '/');

        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->acceptJson()
                ->timeout(30)
                ->get("https://{$this->host}/api/3.0/{$path}", $query);

            if (! $response->successful()) {
                Log::error("Checkfront API {$path} HTTP {$response->status()}", [
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error("Checkfront API {$path} eccezione: ".$e->getMessage());

            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchItems(): array
    {
        $response = $this->apiGet('item');

        if (! $response?->successful()) {
            return [];
        }

        $items = $response->json('items') ?? $response->json('item') ?? [];

        if (isset($items['item_id']) || isset($items['sku'])) {
            return [$items];
        }

        return is_array($items) ? array_values($items) : [];
    }

    public function fetchBooking(string $bookingId): ?array
    {
        $response = $this->apiGet("booking/{$bookingId}");

        if (! $response?->successful()) {
            return null;
        }

        return $response->json('booking');
    }

    /**
     * Elenco prenotazioni (sola lettura). Pagina dell'endpoint booking/index.
     *
     * @return array{entries: array<int, array<string, mixed>>, pages: int, page: int}
     */
    public function fetchBookingIndexPage(int $page = 1, array $query = []): array
    {
        $response = $this->apiGet('booking/index', array_merge([
            'limit' => 100,
            'page' => $page,
        ], $query));

        if (! $response?->successful()) {
            return ['entries' => [], 'pages' => 0, 'page' => $page];
        }

        $raw = $response->json('booking/index') ?? [];
        $entries = is_array($raw) ? array_values($raw) : [];

        return [
            'entries' => $entries,
            'pages' => max(1, (int) ($response->json('request.pages') ?? 1)),
            'page' => $page,
        ];
    }

    /**
     * Scarica tutte le pagine di booking/index (solo GET, non modifica Checkfront).
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllBookingIndex(array $query = [], int $maxPages = 50): array
    {
        $all = [];
        $page = 1;

        do {
            $result = $this->fetchBookingIndexPage($page, $query);
            $all = array_merge($all, $result['entries']);
            $pages = $result['pages'];
            $page++;
        } while ($page <= $pages && $page <= $maxPages);

        return $all;
    }

    /**
     * Acconto o saldo completo: paid_total > 0 (regola sblocco documenti).
     */
    public function hasDepositOrPaid(float $paidTotal, float $total = 0): bool
    {
        if ($paidTotal > 0) {
            return true;
        }

        return $total > 0 && $paidTotal >= $total;
    }

    /**
     * Saldo a zero (pagamento completo).
     */
    public function isBookingFullyPaid(string $bookingId): bool
    {
        $booking = $this->fetchBooking($bookingId);

        if (! $booking) {
            return false;
        }

        $order = $booking['order'] ?? [];
        $total = (float) ($order['total'] ?? 0);
        $paid = (float) ($order['paid_total'] ?? 0);
        $balance = $total - $paid;

        Log::info("Booking {$bookingId}: Totale {$total}, Pagato {$paid}, Saldo {$balance}");

        return $total > 0 && $balance <= 0;
    }

    public function paymentUrlForCode(?string $bookingCode): ?string
    {
        if (! $bookingCode) {
            return null;
        }

        $base = rtrim(config('checkfront.payment_url'), '/');

        return "{$base}?code={$bookingCode}";
    }
}
