<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Apartment;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\CheckfrontService;

class CheckfrontWebhookController extends Controller
{
    public function handle(Request $request, CheckfrontService $checkfrontService)
    {
        // 1. Logghiamo tutto il contenuto per sicurezza
        Log::info('🔔 Webhook Checkfront Ricevuto:', $request->all());

        $data = $request->all();

        // 2. Estrazione dati dal Payload (mappati sulla struttura reale del tuo log)
        $checkfrontBookingId = $data['booking']['@attributes']['booking_id'] ?? null;
        $bookingCode = $request->input('booking.code'); // Es: XCSD-060526
        
        // Estraiamo lo SKU e il Nome dell'articolo (appartamento)
        $sku = $request->input('booking.order.items.item.sku');
        $itemName = $request->input('booking.order.items.item.name'); // Se presente nel sotto-oggetto

        // Dati Cliente
        $guestName = $request->input('booking.customer.name') ?? $request->input('booking.fields.customer_name', 'Ospite Sconosciuto');
        $guestEmail = $request->input('booking.customer.email');
        if (empty($guestEmail)) {
            $guestEmail = $request->input('booking.fields.customer_email');
        }

        // Date (Checkfront invia timestamp, li convertiamo in formato Database Y-m-d)
        $startTimestamp = $request->input('booking.start_date');
        $endTimestamp = $request->input('booking.end_date');
        
        $checkIn = $startTimestamp ? Carbon::createFromTimestamp($startTimestamp)->format('Y-m-d 16:00:00') : Carbon::today()->format('Y-m-d 16:00:00');
        $checkOut = $endTimestamp ? Carbon::createFromTimestamp($endTimestamp)->format('Y-m-d 10:00:00') : Carbon::tomorrow()->format('Y-m-d 10:00:00');

        // Dati Economici
        $totalPrice = $request->input('booking.order.total', 0);
        $paidAmount = $request->input('booking.order.paid_total', 0);
        $bookingStatus = $request->input('booking.status'); // Es: PEND, PAID

        // 3. RICERCA APPARTAMENTO (Doppia Sicurezza: SKU prima, Nome poi)
        $apartment = Apartment::where('sku', $sku)
                    ->orWhere('name', $sku) // A volte lo SKU viene inviato nel campo name
                    ->orWhere('checkfront_name', $sku)
                    ->first();

        if (!$apartment) {
            Log::error("❌ Errore: Appartamento non trovato. SKU ricevuto: " . $sku);
            return response()->json(['status' => 'error', 'message' => 'Appartamento non riconosciuto'], 404);
        }

        // 4. VERIFICA SALDO (Tramite il tuo CheckfrontService)
        $isPaid = ($bookingStatus === 'PAID'); // Controllo veloce dal webhook
        
        if (!$isPaid && $checkfrontBookingId) {
            // Se il webhook dice PEND, facciamo un controllo extra via API per sicurezza
            $isPaid = $checkfrontService->isBookingFullyPaid($checkfrontBookingId);
        }

        // 5. SALVATAGGIO O AGGIORNAMENTO
        // Usiamo il booking_id di Checkfront come chiave per non creare duplicati
        $reservation = Reservation::updateOrCreate(
            ['checkfront_booking_id' => $checkfrontBookingId],
            [
                'apartment_id' => $apartment->id,
                'guest_name'   => $guestName,
                'guest_email'  => is_array($guestEmail) ? null : $guestEmail, // Evita errori se Checkfront manda []
                'check_in'     => $checkIn,
                'check_out'    => $checkOut,
                // Generiamo il token solo se è una nuova prenotazione
                'token'        => (Reservation::where('checkfront_booking_id', $checkfrontBookingId)->exists()) 
                                  ? Reservation::where('checkfront_booking_id', $checkfrontBookingId)->first()->token 
                                  : Str::random(10),
                
                // Costruiamo il link di pagamento dinamico usando il Codice
                'checkfront_payment_url' => "https://jlune.checkfront.com/reserve/payment/?code={$bookingCode}",
                'is_paid'      => $isPaid,
                
                // Se hai aggiunto queste colonne al database, verranno salvate:
                'total_price'  => $totalPrice,
                'booking_code' => $bookingCode,
                'status'       => $bookingStatus,
            ]
        );

        Log::info("✅ Sincronizzazione Completata: Prenotazione {$bookingCode} salvata per {$guestName}.");

        return response()->json([
            'status' => 'success', 
            'message' => 'Dati ricevuti e sincronizzati',
            'reservation_id' => $reservation->id
        ]);
    }
}