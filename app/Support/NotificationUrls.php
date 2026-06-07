<?php

namespace App\Support;

use App\Models\Reservation;

class NotificationUrls
{
    public static function guestPortal(Reservation $reservation): string
    {
        return route('checkin.show', ['token' => $reservation->token]);
    }

    public static function guestDocuments(Reservation $reservation): string
    {
        return route('checkin.documents', ['token' => $reservation->token]);
    }

    public static function guestContract(Reservation $reservation): string
    {
        return route('checkin.contract', ['token' => $reservation->token]);
    }

    public static function adminReservation(Reservation $reservation): string
    {
        return route('admin.arrivi.show', $reservation->id);
    }

    public static function absolute(?string $url, Reservation $reservation): string
    {
        if (blank($url)) {
            return static::guestPortal($reservation);
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    public static function appendLinkLine(string $body, string $url, string $label = 'Apri'): string
    {
        $url = trim($url);

        if ($url === '' || str_contains($body, $url)) {
            return $body;
        }

        return rtrim($body)."\n\n{$label}: {$url}";
    }
}
