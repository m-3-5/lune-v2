<?php

namespace App\Http\Controllers;

use App\Services\CheckfrontBookingSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckfrontWebhookController extends Controller
{
    public function handle(Request $request, CheckfrontBookingSync $sync): JsonResponse
    {
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
