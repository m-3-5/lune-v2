<?php

namespace App\Support;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationMovements
{
    /**
     * @return Collection<int, array{reservation: Reservation, type: string}>
     */
    public static function forDate(Carbon $date, bool $includeCancelled = false): Collection
    {
        $day = $date->copy()->startOfDay();

        $base = Reservation::query()
            ->with('apartment')
            ->when(! $includeCancelled, fn ($q) => $q->notCancelled());

        $arrivals = (clone $base)
            ->arrivingOn($day)
            ->get()
            ->map(fn (Reservation $r) => ['reservation' => $r, 'type' => 'arrival']);

        $departures = (clone $base)
            ->departingOn($day)
            ->get()
            ->map(fn (Reservation $r) => ['reservation' => $r, 'type' => 'departure']);

        return $departures
            ->concat($arrivals)
            ->sortBy(fn ($row) => $row['reservation']->apartment?->name ?? 'zzz')
            ->values();
    }

    /**
     * @return array<int, array{date: string, label: string, arrivals: int, departures: int}>
     */
    public static function agendaDaySummaries(int $startOffsetDays = 2, int $days = 7): array
    {
        $summaries = [];

        for ($i = 0; $i < $days; $i++) {
            $day = today()->addDays($startOffsetDays + $i);
            $summaries[] = [
                'date' => $day->format('Y-m-d'),
                'label' => $day->locale('it')->isoFormat('ddd D/M'),
                'arrivals' => Reservation::query()->notCancelled()->arrivingOn($day)->count(),
                'departures' => Reservation::query()->notCancelled()->departingOn($day)->count(),
            ];
        }

        return $summaries;
    }
}
