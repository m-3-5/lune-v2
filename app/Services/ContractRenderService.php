<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;

class ContractRenderService
{
    public function html(Reservation $reservation, ?string $locale = null): string
    {
        $locale = $this->resolveLocale($reservation, $locale);
        $guests = $reservation->extracted_guests ?? [];

        return view("contracts.{$locale}", [
            'reservation' => $reservation,
            'guests' => $guests,
            'apartment' => $reservation->apartment,
        ])->render();
    }

    /**
     * @return array{it: string, en: string}
     */
    public function htmlBoth(Reservation $reservation): array
    {
        return [
            'it' => $this->html($reservation, 'it'),
            'en' => $this->html($reservation, 'en'),
        ];
    }

    public function resolveLocale(Reservation $reservation, ?string $locale = null): string
    {
        if ($locale !== null && in_array($locale, ['it', 'en'], true)) {
            return $locale;
        }

        return $reservation->contract_locale === 'en' ? 'en' : 'it';
    }

    public function saveHtmlSnapshot(Reservation $reservation): string
    {
        $html = $this->html($reservation);
        $path = "contracts/{$reservation->id}/contratto-{$reservation->booking_code}-".now()->format('Ymd-His').'.html';
        Storage::disk('local')->put($path, $html);

        return $path;
    }
}
