<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function admin(): JsonResponse
    {
        return response()->json([
            'id' => '/admin',
            'name' => 'Jlune Gestione',
            'short_name' => 'Jlune Admin',
            'description' => 'Pannello gestione Appartamenti Jlune per il team',
            'start_url' => '/admin?source=pwa',
            'scope' => '/admin',
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => '#0f172a',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => '/pwa-icons/admin-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/pwa-icons/admin-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/pwa-icons/admin-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function guest(string $token): JsonResponse
    {
        $reservation = Reservation::query()->where('token', $token)->firstOrFail();

        return response()->json([
            'id' => '/checkin/'.$reservation->token,
            'name' => 'Jlune Check-in',
            'short_name' => 'Jlune',
            'description' => 'Area ospite Appartamenti Jlune — documenti, contratto e soggiorno',
            'start_url' => '/checkin/'.$reservation->token.'?source=pwa',
            'scope' => '/checkin',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#00D185',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => '/pwa-icons/guest-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/pwa-icons/guest-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/pwa-icons/guest-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
