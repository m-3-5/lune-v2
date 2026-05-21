<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use App\Services\CheckfrontService;
use Illuminate\Console\Command;

class SyncCheckfrontItems extends Command
{
    protected $signature = 'checkfront:sync-items';

    protected $description = 'Sincronizza gli appartamenti dall\'inventario Checkfront (API item)';

    public function handle(CheckfrontService $checkfront): int
    {
        if (! $checkfront->isConfigured()) {
            $this->error('Configura CHECKFRONT_HOST, CHECKFRONT_API_KEY e CHECKFRONT_API_SECRET nel file .env');

            return self::FAILURE;
        }

        $this->info('Interrogazione API Checkfront /item ...');

        $items = $checkfront->fetchItems();

        if ($items === []) {
            $this->warn('Nessun item ricevuto. Verifica credenziali e permessi API.');

            return self::FAILURE;
        }

        $knownSkus = Apartment::pluck('sku')->map(fn ($s) => strtolower($s))->all();
        $synced = 0;

        foreach ($items as $item) {
            $sku = strtolower((string) ($item['sku'] ?? ''));
            $itemId = (string) ($item['item_id'] ?? $item['id'] ?? '');

            if ($sku === '' || ! in_array($sku, $knownSkus, true)) {
                continue;
            }

            $apartment = Apartment::where('sku', $sku)->first();

            if (! $apartment) {
                continue;
            }

            $apartment->update([
                'checkfront_item_id' => $itemId ?: $apartment->checkfront_item_id,
                'name' => $item['name'] ?? $apartment->name,
                'checkfront_name' => $item['name'] ?? $apartment->checkfront_name,
            ]);

            $this->line("  ✓ {$sku} → item_id {$itemId}");
            $synced++;
        }

        $this->info("Completato: {$synced} appartamenti aggiornati da Checkfront.");

        $missing = Apartment::whereNull('checkfront_item_id')->get();
        if ($missing->isNotEmpty()) {
            $this->warn('Senza checkfront_item_id: '.$missing->pluck('sku')->join(', '));
        }

        return self::SUCCESS;
    }
}
