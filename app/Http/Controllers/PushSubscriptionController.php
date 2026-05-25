<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel' => 'required|in:admin,guest',
            'endpoint' => 'required|string|max:512',
            'keys' => 'required|array',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
            'encoding' => 'nullable|string|max:32',
            'reservation_id' => 'nullable|integer|exists:reservations,id',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'channel' => $validated['channel'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['encoding'] ?? 'aesgcm',
                'reservation_id' => $validated['reservation_id'] ?? null,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = $request->validate(['endpoint' => 'required|string'])['endpoint'];
        PushSubscription::query()->where('endpoint', $endpoint)->delete();

        return response()->json(['ok' => true]);
    }
}
