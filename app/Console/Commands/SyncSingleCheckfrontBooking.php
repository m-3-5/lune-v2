<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CheckfrontBookingSync;
use Illuminate\Support\Facades\Http;

class SyncSingleCheckfrontBooking extends Command
{
    // Il nome del comando che useremo su Plesk
    protected $signature = 'checkfront:sync-single {booking_id} {--force}';
    protected $description = 'Scarica e sincronizza una singola prenotazione direttamente dalle API di Checkfront';

    public function handle(CheckfrontBookingSync $service)
    {
        $bookingId = $this->argument('booking_id');
        $this->info("Mi connetto a Checkfront per scaricare la prenotazione: {$bookingId}...");

        // Recuperiamo le chiavi API dal tuo file .env
        $host = env('CHECKFRONT_HOST'); 
        $key = env('CHECKFRONT_API_KEY');
        $secret = env('CHECKFRONT_API_SECRET');

        if (!$host || !$key || !$secret) {
            $this->error("Credenziali Checkfront mancanti nel file .env!");
            return;
        }

        // Assicuriamoci che l'host abbia https://
        if (!str_starts_with($host, 'http')) {
            $host = 'https://' . $host;
        }

        // Facciamo la chiamata diretta ufficiale alle API di Checkfront
        $response = Http::withBasicAuth($key, $secret)
                        ->get("{$host}/api/3.0/booking/{$bookingId}");

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['booking'])) {
                $this->info("Dati ricevuti con successo! Inserimento nel database in corso...");
                
                // Usiamo il metodo di elaborazione che abbiamo testato e sappiamo funzionare
                $service->syncFromWebhook($data);
                
                $this->info("🎉 Prenotazione {$bookingId} importata correttamente!");
            } else {
                $this->error("La risposta API non contiene i dati della prenotazione.");
            }
        } else {
            $this->error("Errore API Checkfront. Codice: " . $response->status());
            $this->error($response->body());
        }
    }
}
