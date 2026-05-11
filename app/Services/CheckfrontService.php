<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckfrontService
{
    protected $host;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        // Peschiamo le chiavi sicure dal file .env
        $this->host = env('CHECKFRONT_HOST');
        $this->apiKey = env('CHECKFRONT_API_KEY');
        $this->apiSecret = env('CHECKFRONT_API_SECRET');
    }

    /**
     * Interroga Checkfront per sapere se una prenotazione è saldata al 100%
     */
    public function isBookingFullyPaid($bookingId)
    {
        try {
            // Chiamata API a Checkfront
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("https://{$this->host}/api/3.0/booking/{$bookingId}");

            if ($response->successful()) {
                $data = $response->json();
                
                // I dati della prenotazione sono nella chiave 'booking'
                $booking = $data['booking'] ?? null;

                if ($booking) {
                    // Checkfront organizza i dati economici sotto l'oggetto 'order'
                    $order = $booking['order'] ?? [];
                    
                    // Usiamo 'total' e 'paid_total' come confermato dai log del Webhook
                    $total = (float) ($order['total'] ?? 0);
                    $paid = (float) ($order['paid_total'] ?? 0);
                    
                    // Calcoliamo quanto manca (balance)
                    $balance = $total - $paid;

                    // Se il totale è superiore a 0 e il bilancio è 0 o inferiore, è saldata
                    if ($total > 0 && $balance <= 0) {
                        return true; 
                    }
                    
                    // Log di debug opzionale per vedere i numeri in caso di mancato saldo
                    Log::info("Booking {$bookingId}: Totale {$total}, Pagato {$paid}, Saldo {$balance}");
                }
            } else {
                Log::error("Errore API Checkfront per Booking ID {$bookingId}: " . $response->body());
            }
        } catch (\Exception $e) {
            // L'uso di ?? e dei controlli sopra previene l'errore "Undefined array key"
            Log::error("Eccezione durante chiamata API Checkfront per {$bookingId}: " . $e->getMessage());
        }

        return false; // Di default non sblocchiamo il check-in se il calcolo fallisce
    }
}