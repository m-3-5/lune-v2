<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Carbon\Carbon;

class CheckinController extends Controller
{
    public function show($token)
    {
        // 1. Cerca la prenotazione tramite il token univoco (o restituisci errore 404 se non esiste)
        $reservation = Reservation::with('apartment')->where('token', $token)->firstOrFail();
        $apartment = $reservation->apartment;

        // 2. Calcoliamo i "Semafori" per la vista
        $is_paid = $reservation->is_paid;
        
        // Contiamo se ci sono documenti in attesa di validazione da parte di Serenella
        $pending_docs = $reservation->guestDocuments()->where('status', 'pending')->count();
        $docs_validated = $reservation->documents_validated;

        // 3. Controllo dell'orario (es. l'ingresso è consentito solo dopo le 16:00 del giorno del check-in)
        $checkinDateStr = $reservation->check_in->format('Y-m-d');
        // Uniamo la data di check-in con l'orario di default dell'appartamento
        $checkinTime = Carbon::parse($checkinDateStr . ' ' . $apartment->default_checkin_hour);
        $is_early = now()->isBefore($checkinTime);

        // 4. Il Super-Lucchetto: si sblocca SOLO se tutto è in regola!
        $is_unlocked = $is_paid && $docs_validated && !$is_early;

        // Passiamo tutte queste variabili alla pagina Blade che hai già creato
        return view('checkin.show', compact(
            'reservation', 
            'apartment', 
            'is_paid', 
            'pending_docs', 
            'is_early', 
            'is_unlocked'
        ));
    }
}
