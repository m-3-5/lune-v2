<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Apartment;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\CheckfrontService; // <-- IMPORTIAMO IL NUOVO SERVIZIO

class CheckfrontWebhookController extends Controller
{
    public function handle(Request $request, CheckfrontService $checkfrontService)
    {
        Log::info('🔔 Webhook Checkfront Ricevuto:', $request->all());

        $checkfrontBookingId = $request->input('booking_id'); 
        $apartmentNameFromCheckfront = $request->input('apartment_name'); 
        $guestName = $request->input('customer_name', 'Ospite Sconosciuto');
        
        $checkIn = $request->input('check_in', Carbon::today()->format('Y-m-d 16:00:00'));
        $checkOut = $request->input('check_out', Carbon::tomorrow()->format('Y-m-d 10:00:00'));

        $apartment = Apartment::where('checkfront_name', $apartmentNameFromCheckfront)->first();

        if (!$apartment) {
            Log::error("❌ Errore: Appartamento non trovato per il nome: " . $apartmentNameFromCheckfront);
            return response()->json(['status' => 'error', 'message' => 'Appartamento non riconosciuto'], 404);
        }

        // --- LA VERA MAGIA DELLE API INIZIA QUI ---
        // Chiediamo a Checkfront se la prenotazione è saldata
        $isPaid = false;
        if ($checkfrontBookingId) {
            $isPaid = $checkfrontService->isBookingFullyPaid($checkfrontBookingId);
            if($isPaid) {
                Log::info("💰 La prenotazione {$checkfrontBookingId} risulta interamente SALDATA su Checkfront!");
            } else {
                Log::info("⏳ La prenotazione {$checkfrontBookingId} è in attesa del saldo.");
            }
        }
        // ------------------------------------------

        $reservation = Reservation::updateOrCreate(
            ['checkfront_booking_id' => $checkfrontBookingId],
            [
                'apartment_id' => $apartment->id,
                'guest_name' => $guestName,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'token' => Str::random(10), 
                'checkfront_payment_url' => "https://jlune.checkfront.com/reserve/booking/{$checkfrontBookingId}?view=pay#paynow",
                'is_paid' => $isPaid // <-- AGGIORNIAMO IL SEMAFORO NEL DATABASE AUTOMATICAMENTE!
            ]
        );

        Log::info("✅ Prenotazione creata/aggiornata con successo! Token generato: " . $reservation->token);

        return response()->json(['status' => 'success', 'message' => 'Prenotazione sincronizzata e salvata!']);
    }
}
