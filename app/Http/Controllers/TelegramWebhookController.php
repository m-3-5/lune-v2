<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, ?string $secret = null)
    {
        $expected = config('telegram.webhook_secret');
        if (filled($expected) && $secret !== $expected) {
            abort(403);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;

        if (! is_array($message)) {
            return response('ok');
        }

        $text = trim((string) ($message['text'] ?? ''));
        $chatId = (string) ($message['chat']['id'] ?? '');

        if ($chatId === '' || ! str_starts_with($text, '/start')) {
            return response('ok');
        }

        $payload = trim(substr($text, 6));
        if ($payload === '') {
            app(TelegramNotifier::class)->sendToChat(
                $chatId,
                '👋 Ciao! Per collegare le notifiche apri il link dal tuo check-in (pulsante «Ricevi su Telegram»).'
            );

            return response('ok');
        }

        $reservation = Reservation::query()->where('token', $payload)->first();

        if (! $reservation) {
            app(TelegramNotifier::class)->sendToChat(
                $chatId,
                '❌ Link non valido o scaduto. Apri di nuovo il link dal portale check-in.'
            );

            return response('ok');
        }

        $reservation->update([
            'telegram_chat_id' => $chatId,
            'telegram_linked_at' => now(),
        ]);

        $name = $reservation->guestDisplayName();
        $code = $reservation->booking_code;

        app(TelegramNotifier::class)->sendToChat(
            $chatId,
            "✅ <b>Telegram collegato</b>\n\nPrenotazione <b>{$code}</b> — {$name}.\nRiceverai qui i promemoria su documenti, contratto e soggiorno (se attivi dall'host)."
        );

        Log::info('Telegram collegato a prenotazione', [
            'reservation_id' => $reservation->id,
            'booking_code' => $code,
        ]);

        return response('ok');
    }
}
