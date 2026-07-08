<?php

namespace App\Http\Controllers;

use App\Support\AppSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SerenellaAccessController extends Controller
{
    /**
     * Link personale ricevuto via email a ogni nuova prenotazione: conferma il token
     * (che scade e viene rinnovato a ogni prenotazione) e imposta il cookie di accesso.
     * Non entra subito nel sito: atterra sulla pagina di manutenzione come tutti,
     * ma lì le compare il pulsante "Visita il sito" (vedi EnforceSiteMaintenance).
     */
    public function confirm(Request $request, string $token): RedirectResponse
    {
        abort_unless(AppSettings::serenellaAccessValid($token), 404);

        $minutes = max(1, now()->diffInMinutes(AppSettings::serenellaAccessExpiresAt() ?? now()->addDays(30)));

        Cookie::queue(Cookie::make('jlune_bypass', $token, $minutes));

        return redirect('/');
    }

    /** Lei clicca "Visita il sito" sulla pagina di manutenzione: da qui in poi naviga il sito vero. */
    public function enter(Request $request): RedirectResponse
    {
        abort_unless(AppSettings::serenellaAccessValid($request->cookie('jlune_bypass')), 404);

        $request->session()->put('jlune_entered', true);

        return redirect('/');
    }
}
