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
            // Facciamo una chiamata protetta (Basic Auth) all'API di Checkfront
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("https://{$this->host}/api/3.0/booking/{$bookingId}");

            if ($response->successful()) {
                $data = $response->json();
                
                // Entriamo nei dati della prenotazione
                $booking = $data['booking'] ?? null;

                if ($booking) {
                    $total = (float) $booking['total'];
                    $paid = (float) $booking['paid'];
                    $balance = (float) $booking['balance']; // Quanto manca da pagare

                    // Se il bilancio rimanente è 0 o minore, è pagata!
                    if ($balance <= 0) {
                        return true; 
                    }
                }
            } else {
                Log::error("Errore API Checkfront per Booking ID {$bookingId}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Eccezione durante chiamata API Checkfront: " . $e->getMessage());
        }

        return false; // Nel dubbio, diciamo che non è pagata per sicurezza
    }
}