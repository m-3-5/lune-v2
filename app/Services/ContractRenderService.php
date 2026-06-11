<?php

namespace App\Services;

use App\Models\Reservation;
use App\Support\AppSettings;
use App\Support\ContractTemplates;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'body' => $this->body($reservation, $locale),
        ])->render();
    }

    /**
     * Corpo del contratto (clausole): testo personalizzato dall'admin
     * oppure predefinito, con i segnaposto sostituiti dai dati reali.
     */
    public function body(Reservation $reservation, string $locale): string
    {
        $template = AppSettings::contractBody($locale) ?? ContractTemplates::defaultBody($locale);

        return strtr($template, $this->placeholderValues($reservation));
    }

    /**
     * @return array<string, string>
     */
    protected function placeholderValues(Reservation $reservation): array
    {
        return [
            '[APPARTAMENTO]' => e($reservation->apartment->name ?? 'Alloggio'),
            '[CHECK_IN]' => $reservation->check_in?->format('d/m/Y') ?? '—',
            '[CHECK_OUT]' => $reservation->check_out?->format('d/m/Y') ?? '—',
            '[NOTTI]' => (string) $reservation->nightsCount(),
            '[PREZZO_TOTALE]' => number_format((float) $reservation->total_price, 2, ',', '.'),
            '[CAPARRA]' => number_format((float) $reservation->total_price * 0.30, 2, ',', '.'),
            '[CODICE_PRENOTAZIONE]' => e((string) $reservation->booking_code),
        ];
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

    /** Genera il PDF del contratto (contenuto binario). */
    public function pdf(Reservation $reservation, ?string $locale = null): string
    {
        $locale = $this->resolveLocale($reservation, $locale);

        return Pdf::loadView('contracts.pdf', [
            'reservation' => $reservation,
            'guests' => $reservation->extracted_guests ?? [],
            'locale' => $locale,
            'body' => $this->body($reservation, $locale),
        ])->output();
    }

    /** Salva il PDF del contratto e memorizza il percorso sulla prenotazione. */
    public function savePdfSnapshot(Reservation $reservation): string
    {
        $path = "contracts/{$reservation->id}/contratto-{$reservation->booking_code}-".now()->format('Ymd-His').'.pdf';
        Storage::disk('local')->put($path, $this->pdf($reservation));
        $reservation->update(['contract_pdf_path' => $path]);

        return $path;
    }
}
