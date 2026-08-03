<?php

namespace App\Http\Controllers;

use App\Services\CheckfrontBookingSync;
use App\Services\CheckfrontService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckfrontWebhookController extends Controller
{
    public function handle(Request $request, CheckfrontBookingSync $sync, CheckfrontService $checkfront): JsonResponse
    {
        // Nessuna credenziale Checkfront configurata: ignoriamo il webhook invece di
        // processarlo a metà (nessuna prenotazione creata, nessuna notifica inviata).
        if (! $checkfront->isConfigured()) {
            Log::info('Webhook Checkfront ricevuto ma ignorato: nessuna credenziale configurata in .env.');

            return response()->json(['status' => 'ignored', 'message' => 'Checkfront non configurato su questo ambiente.']);
        }

        Log::info('🔔 Webhook Checkfront Ricevuto:', $request->all());

        $result = $sync->syncFromWebhook($request->all());

        if (isset($result['error'])) {
            return response()->json(
                ['status' => 'error', 'message' => $result['error']],
                $result['status']
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Dati ricevuti e sincronizzati',
            'reservation_id' => $result['reservation']->id,
        ]);
    }
}
