<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Apartment;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckfrontWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Salviamo i dati grezzi ricevuti nei log (fondamentale per quando collegheremo il vero Checkfront)
        Log::info('🔔 Webhook Checkfront Ricevuto:', $request->all());

        // 2. Estraiamo i dati (per ora li prepariamo anche per il nostro test manuale)
        $checkfrontBookingId = $request->input('booking_id'); 
        $apartmentNameFromCheckfront = $request->input('apartment_name'); 
        $guestName = $request->input('customer_name', 'Ospite Sconosciuto');
        
        // Se mancano le date nel test, usiamo oggi e domani come esempio
        $checkIn = $request->input('check_in', Carbon::today()->format('Y-m-d 16:00:00'));
        $checkOut = $request->input('check_out', Carbon::tomorrow()->format('Y-m-d 10:00:00'));

        // 3. CERCHIAMO L'APPARTAMENTO TRAMITE IL NOSTRO "PONTE"
        $apartment = Apartment::where('checkfront_name', $apartmentNameFromCheckfront)->first();

        // Se l'appartamento non esiste nel nostro DB, diamo errore
        if (!$apartment) {
            Log::error("❌ Errore: Appartamento non trovato per il nome: " . $apartmentNameFromCheckfront);
            return response()->json(['status' => 'error', 'message' => 'Appartamento non riconosciuto'], 404);
        }

        // 4. CREIAMO (O AGGIORNIAMO) LA PRENOTAZIONE NEL DATABASE
        // updateOrCreate cerca se esiste già una prenotazione con quell'ID, altrimenti la crea nuova!
        $reservation = Reservation::updateOrCreate(
            ['checkfront_booking_id' => $checkfrontBookingId],
            [
                'apartment_id' => $apartment->id,
                'guest_name' => $guestName,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                // Generiamo il token segreto (es. per l'URL app.inm35.it/checkin/aBcD1234Ef)
                'token' => Str::random(10), 
                // Generiamo il link per il pagamento del 30%
                'checkfront_payment_url' => "https://jlune.checkfront.com/reserve/booking/{$checkfrontBookingId}?view=pay#paynow"
            ]
        );

        Log::info("✅ Prenotazione creata con successo! Token generato: " . $reservation->token);

        // 5. Confermiamo a Checkfront che abbiamo fatto tutto
        return response()->json(['status' => 'success', 'message' => 'Prenotazione sincronizzata e salvata!']);
    }
}
