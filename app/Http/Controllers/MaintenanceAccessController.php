<?php

namespace App\Http\Controllers;

use App\Support\AppSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class MaintenanceAccessController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! AppSettings::maintenanceLoginValid($data['email'], $data['password'])) {
            return back()->withErrors(['password' => 'Email o password non corretti.']);
        }

        Cookie::queue(Cookie::make('jlune_bypass', AppSettings::serenellaAccessToken(), 60 * 24 * 30));
        $request->session()->put('jlune_entered', true);

        return redirect('/');
    }
}
