<?php

namespace App\Http\Middleware;

use App\Support\AppSettings;
use Closure;
use Illuminate\Http\Request;

/**
 * Pagina di manutenzione "nostra": a differenza del down-file nativo di Laravel,
 * è resa dinamicamente a ogni richiesta, così può mostrare un pulsante extra
 * "Visita il sito" solo a chi ha il cookie di accesso valido (Serenella).
 */
class EnforceSiteMaintenance
{
    /** @var array<int, string> */
    protected array $except = [
        'admin',
        'admin/*',
        'assistenza',
        'assistenza/*',
        'accesso/*',
        'entra',
        'webhook/*',
        'push/*',
        'up',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! AppSettings::siteMaintenanceOn() || $request->is($this->except)) {
            return $next($request);
        }

        $hasAccess = AppSettings::serenellaAccessValid($request->cookie('jlune_bypass'));

        if ($hasAccess && $request->session()->get('jlune_entered')) {
            return $next($request);
        }

        return response()->view('maintenance', ['hasAccess' => $hasAccess], 503);
    }
}
