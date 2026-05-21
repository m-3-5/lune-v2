<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;

class ContractRenderService
{
    public function html(Reservation $reservation): string
    {
        $locale = $reservation->contract_locale === 'en' ? 'en' : 'it';
        $guests = $reservation->extracted_guests ?? [];

        return view("contracts.{$locale}", [
            'reservation' => $reservation,
            'guests' => $guests,
            'apartment' => $reservation->apartment,
        ])->render();
    }

    public function saveHtmlSnapshot(Reservation $reservation): string
    {
        $html = $this->html($reservation);
        $path = "contracts/{$reservation->id}/contratto-{$reservation->booking_code}-".now()->format('Ymd-His').'.html';
        Storage::disk('local')->put($path, $html);

        return $path;
    }
}
