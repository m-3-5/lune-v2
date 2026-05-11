<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Reservation;
use App\Models\Apartment;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SyncCheckfrontBookings extends Command
{
    // Il nome del comando che digiterai nel terminale
    protected $signature = 'checkfront:sync {--days=30 : Quanti giorni indietro controllare}';
    protected $description = 'Sincronizza le prenotazioni direttamente da Checkfront API';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Inizio sincronizzazione delle prenotazioni degli ultimi $days giorni...");

        // Recuperiamo le credenziali dal file .env
        $host = env('CHECKFRONT_HOST');
        $apiKey = env('CHECKFRONT_API_KEY');
        $apiSecret = env('CHECKFRONT_API_SECRET');

        // Chiamata API per ottenere la lista delle prenotazioni
        $response = Http::withBasicAuth($apiKey, $apiSecret)
            ->get("https://{$host}/api/3.0/booking", [
                'start_date' => now()->subDays($days)->format('Y-m-d'),
                'end_date'   => now()->addMonths(6)->format('Y-m-d'), // Guardiamo anche al futuro
            ]);

        if (!$response->successful()) {
            $this->error("Errore API: " . $response->body());
            return;
        }

        $bookings = $response->json()['bookings'] ?? [];
        $this->info("Trovate " . count($bookings) . " prenotazioni. Elaborazione in corso...");

        foreach ($bookings as $id => $details) {
            // Logica di estrazione SKU (simile al controller)
            // Nota: L'API list potrebbe avere una struttura leggermente diversa dal Webhook
            $sku = $details['item_sku'] ?? ($details['sku'] ?? null);
            
            $apartment = Apartment::where('sku', $sku)->first();

            if (!$apartment) {
                $this->warn("⚠️ Salto prenotazione $id: SKU '$sku' non trovato nel DB.");
                continue;
            }

            // --- INIZIO MODIFICA: Mappatura cancellazione ---
            // Estraiamo lo stato originale
            $status = $details['status_id'] ?? 'UNKNOWN';
            
            // Se Checkfront dice "STOP", noi lo registriamo come "CANCELLED"
            if ($status === 'STOP') {
                $status = 'CANCELLED';
            }
            // --- FINE MODIFICA ---

            // Usiamo updateOrCreate per non duplicare i dati
            Reservation::updateOrCreate(
                ['checkfront_booking_id' => $id],
                [
                    'apartment_id' => $apartment->id,
                    'guest_name'   => $details['customer_name'] ?? 'Ospite',
                    'check_in'     => Carbon::createFromTimestamp($details['start_date'])->format('Y-m-d 16:00:00'),
                    'check_out'    => Carbon::createFromTimestamp($details['end_date'])->format('Y-m-d 10:00:00'),
                    'token'        => Reservation::where('checkfront_booking_id', $id)->first()->token ?? Str::random(10),
                    'is_paid'      => ($details['status_id'] === 'PAID' || ($details['balance'] ?? 1) <= 0),
                    'status'       => $status, // Passiamo la variabile che abbiamo mappato
                ]
            );

            $this->line("✅ Sincronizzata: {$details['customer_name']} ($id)");
        }

        $this->info("Sincronizzazione completata!");
    }
}