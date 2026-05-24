<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Checkfront invia timestamp Unix (istante UTC).
 * Il giorno di calendario va letto nel fuso della struttura (es. Europe/Rome).
 */
class CheckfrontDates
{
    public static function timezone(): string
    {
        return (string) config('checkfront.timezone', 'Europe/Rome');
    }

    public static function calendarDate(int|string|null $timestamp): ?string
    {
        if ($timestamp === null || $timestamp === '') {
            return null;
        }

        return Carbon::createFromTimestamp((int) $timestamp, 'UTC')
            ->timezone(self::timezone())
            ->format('Y-m-d');
    }

    public static function toCheckInDatetime(int|string|null $timestamp): ?string
    {
        $date = self::calendarDate($timestamp);

        return $date ? "{$date} 16:00:00" : null;
    }

    public static function toCheckOutDatetime(int|string|null $timestamp): ?string
    {
        $date = self::calendarDate($timestamp);

        return $date ? "{$date} 10:00:00" : null;
    }
}
