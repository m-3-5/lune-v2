<?php

namespace App\Http\Controllers;

use App\Models\EntryVideo;
use App\Models\Reservation;
use App\Services\GuestNotificationService;
use Carbon\Carbon;

class CheckinController extends Controller
{
    public function show($token)
    {
        // 1. Cerca la prenotazione tramite il token univoco (o restituisci errore 404 se non esiste)
        $reservation = Reservation::with('apartment')->where('token', $token)->firstOrFail();
        $apartment = $reservation->apartment;

        app(GuestNotificationService::class)->syncStatusNotifications($reservation);

        // 2. Calcoliamo i "Semafori" per la vista
        $is_paid = $reservation->is_paid;
        
        // Contiamo se ci sono documenti in attesa di validazione da parte del gestore
        $pending_docs = $reservation->guestDocuments()->where('status', 'pending')->count();
        $docs_validated = $reservation->documents_validated;

        // 3. Controllo dell'orario (es. l'ingresso è consentito solo dopo le 16:00 del giorno del check-in)
        $checkinDateStr = $reservation->check_in->format('Y-m-d');
        // Uniamo la data di check-in con l'orario di default dell'appartamento
        $checkinTime = Carbon::parse($checkinDateStr . ' ' . $apartment->default_checkin_hour);
        $is_early = now()->isBefore($checkinTime);

        // 4. Il Super-Lucchetto: si sblocca SOLO se tutto è in regola!
        $is_unlocked = $is_paid && $docs_validated && !$is_early;

        $entryVideos = $is_unlocked
            ? EntryVideo::where('apartment_id', $apartment->id)->where('category', 'ingresso')->orderBy('step_order')->get()
            : collect();

        // Passiamo tutte queste variabili alla pagina Blade che hai già creato
        return view('checkin.show', compact(
            'reservation',
            'apartment',
            'is_paid',
            'pending_docs',
            'is_early',
            'is_unlocked',
            'entryVideos'
        ));
    }

    public function documents($token)
    {
        $reservation = Reservation::with('apartment')->where('token', $token)->firstOrFail();
        $apartment = $reservation->apartment;

        // Se non ha pagato, lo rimandiamo alla home del check-in
        if (! $reservation->is_paid) {
            return redirect()->route('checkin.show', ['token' => $token])->with('error', 'Devi prima completare il pagamento.');
        }

        app(GuestNotificationService::class)->syncStatusNotifications($reservation);

        return view('checkin.documents', compact('reservation', 'apartment'));
    }

    public function contract(string $token)
    {
        $reservation = Reservation::with('apartment')->where('token', $token)->firstOrFail();
        $apartment = $reservation->apartment;

        if (! $reservation->is_paid) {
            return redirect()->route('checkin.show', ['token' => $token])
                ->with('error', 'Completa prima il pagamento.');
        }

        app(GuestNotificationService::class)->syncStatusNotifications($reservation);

        return view('checkin.contract', compact('reservation', 'apartment'));
    }

    public function appliances(string $token)
    {
        $reservation = Reservation::with('apartment')->where('token', $token)->firstOrFail();
        $apartment = $reservation->apartment;

        $applianceVideos = EntryVideo::where('apartment_id', $apartment->id)
            ->where('category', 'elettrodomestico')
            ->orderBy('step_order')
            ->get();

        return view('checkin.appliances', compact('reservation', 'apartment', 'applianceVideos'));
    }
}