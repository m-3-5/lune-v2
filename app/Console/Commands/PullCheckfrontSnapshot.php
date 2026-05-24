<?php

namespace App\Console\Commands;

use App\Services\CheckfrontService;
use App\Support\CheckfrontDates;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PullCheckfrontSnapshot extends Command
{
    protected $signature = 'checkfront:pull-snapshot
                            {--output=app/checkfront_data/snapshot.json : Percorso file JSON in uscita}
                            {--days-past=120 : Prenotazioni con fine soggiorno dopo (oggi - N giorni)}
                            {--max-pages=80 : Limite pagine booking/index}
                            {--sleep=150 : Pausa in ms tra una GET booking/{id} e l\'altra}
                            {--index-only : Non scaricare il dettaglio di ogni prenotazione}';

    protected $description = 'Scarica da Checkfront (solo lettura GET) inventario e prenotazioni in un file JSON locale';

    public function handle(CheckfrontService $checkfront): int
    {
        if (! $checkfront->isConfigured()) {
            $this->error('Configura CHECKFRONT_HOST, CHECKFRONT_API_KEY e CHECKFRONT_API_SECRET nel .env');

            return self::FAILURE;
        }

        $outputPath = base_path($this->option('output'));
        $daysPast = max(1, (int) $this->option('days-past'));
        $sleepMs = max(0, (int) $this->option('sleep'));
        $indexOnly = (bool) $this->option('index-only');

        $this->info('Lettura sicura Checkfront (solo GET, nessuna modifica remota)...');

        $this->line('  → /item');
        $items = $checkfront->fetchItems();

        $cutoff = Carbon::now(CheckfrontDates::timezone())
            ->subDays($daysPast)
            ->startOfDay()
            ->timestamp;

        $this->line("  → /booking/index (end_date > {$daysPast} giorni fa)...");
        $indexEntries = $checkfront->fetchAllBookingIndex([
            'end_date' => '>'.$cutoff,
        ], (int) $this->option('max-pages'));

        $this->info('  Index: '.count($indexEntries).' prenotazioni');

        $bookings = [];
        $summaries = [];

        if (! $indexOnly) {
            $bar = $this->output->createProgressBar(count($indexEntries));
            $bar->start();

            foreach ($indexEntries as $row) {
                $id = (string) ($row['booking_id'] ?? '');
                if ($id === '') {
                    $bar->advance();

                    continue;
                }

                $detail = $checkfront->fetchBooking($id);
                if ($detail) {
                    $bookings[$id] = $detail;
                    $summaries[$id] = $this->summarizeBooking($detail);
                }

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        foreach ($indexEntries as $row) {
            $id = (string) ($row['booking_id'] ?? '');
            if ($id !== '' && ! isset($summaries[$id])) {
                $summaries[$id] = [
                    'booking_id' => $id,
                    'code' => $row['code'] ?? null,
                    'status_id' => $row['status_id'] ?? null,
                    'date_desc' => $row['date_desc'] ?? null,
                    'customer_name' => $row['customer_name'] ?? null,
                    'from_index_only' => true,
                ];
            }
        }

        $payload = [
            'meta' => [
                'fetched_at' => now()->timezone(CheckfrontDates::timezone())->toIso8601String(),
                'host' => config('checkfront.host'),
                'timezone' => CheckfrontDates::timezone(),
                'command' => 'checkfront:pull-snapshot',
                'index_count' => count($indexEntries),
                'bookings_with_detail' => count($bookings),
                'index_only' => $indexOnly,
            ],
            'items' => $items,
            'bookings_index' => $indexEntries,
            'booking_summaries' => array_values($summaries),
            'bookings' => $bookings,
        ];

        File::ensureDirectoryExists(dirname($outputPath));
        File::put(
            $outputPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );

        $this->newLine();
        $this->info("Snapshot salvato: {$outputPath}");
        $this->comment('Contiene dati ospiti: non committare su Git. Per aggiornare il DB: php artisan checkfront:sync-from-snapshot');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $booking
     * @return array<string, mixed>
     */
    protected function summarizeBooking(array $booking): array
    {
        $id = $booking['@attributes']['booking_id'] ?? $booking['booking_id'] ?? null;
        $start = $booking['start_date'] ?? null;
        $end = $booking['end_date'] ?? null;

        return [
            'booking_id' => $id,
            'code' => $booking['code'] ?? null,
            'status' => $booking['status'] ?? $booking['status_id'] ?? null,
            'start_date_unix' => $start,
            'end_date_unix' => $end,
            'check_in_local' => CheckfrontDates::toCheckInDatetime($start),
            'check_out_local' => CheckfrontDates::toCheckOutDatetime($end),
            'customer_name' => $booking['customer']['name'] ?? null,
        ];
    }
}
