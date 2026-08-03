<?php

namespace App\Http\Controllers;

use App\Mail\AdminTeamAlertMail;
use App\Support\AppSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class TeamAccessController extends Controller
{
    /**
     * Link personale ricevuto via email a ogni nuova prenotazione. Non dà accesso
     * subito: chiede di confermare l'email registrata, per legare l'accesso permanente
     * a chi la possiede e non a chiunque intercetti il link.
     */
    public function confirm(Request $request, string $token): View
    {
        abort_unless(AppSettings::teamAccessValid($token), 404);

        return view('team-access-request', ['token' => $token]);
    }

    /** Invia il link di verifica vero all'email inserita, se è tra i contatti admin registrati. */
    public function requestAccess(Request $request, string $token): RedirectResponse
    {
        abort_unless(AppSettings::teamAccessValid($token), 404);

        return $this->sendVerificationLink($request);
    }

    /**
     * Stesso meccanismo di requestAccess(), ma raggiungibile direttamente dalla pagina
     * di manutenzione (senza un link personale di prenotazione già in mano): basta che
     * l'email inserita sia una di quelle registrate come contatto admin.
     */
    public function requestGeneralAccess(Request $request): RedirectResponse
    {
        return $this->sendVerificationLink($request);
    }

    protected function sendVerificationLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($data['email']));

        if (! in_array($email, AppSettings::adminEmails(), true)) {
            return back()->withErrors(['email' => 'Email non riconosciuta tra i contatti admin registrati (Progetto → Contatti).']);
        }

        if (! AppSettings::mailSmtpReady()) {
            return back()->withErrors(['email' => 'Email non configurata sul server al momento — riprova più tardi.']);
        }

        $verifyUrl = URL::temporarySignedRoute('team.access.verify', now()->addMinutes(30), ['email' => $email]);

        Mail::to($email)->send(new AdminTeamAlertMail(
            'Conferma il tuo accesso',
            "Ciao,\n\nclicca qui per confermare e attivare il tuo accesso permanente all'app (valido 30 minuti):\n{$verifyUrl}\n\nSe non hai richiesto tu l'accesso, ignora questa email.",
            $verifyUrl
        ));

        return back()->with('requested', $email);
    }

    /** Link firmato ricevuto via email: conferma l'identità e attiva l'accesso permanente. */
    public function verify(Request $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->query('email')));

        abort_unless(in_array($email, AppSettings::adminEmails(), true), 404);

        $minutes = max(1, now()->diffInMinutes(AppSettings::teamAccessExpiresAt() ?? now()->addDays(30)));

        Cookie::queue(Cookie::make('jlune_bypass', AppSettings::teamAccessToken(), $minutes));
        $request->session()->put('jlune_entered', true);

        return redirect('/')->with('access_confirmed', true);
    }

    /** Clic su "Visita il sito" nella pagina di manutenzione: da qui in poi naviga il sito vero. */
    public function enter(Request $request): RedirectResponse
    {
        abort_unless(AppSettings::teamAccessValid($request->cookie('jlune_bypass')), 404);

        $request->session()->put('jlune_entered', true);

        return redirect('/');
    }
}
