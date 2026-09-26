<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class ReservationCalendarWindow
{
    /**
     * Return the leftmost date for the seven-day company calendar.
     * Past requested dates are always clamped to today.
     */
    public function start(?string $requestedDate, CarbonInterface $today): Carbon
    {
        $today = Carbon::instance($today)->startOfDay();
        $requested = filled($requestedDate)
            ? Carbon::parse($requestedDate)->startOfDay()
            : $today->copy();

        return $requested->lt($today) ? $today->copy() : $requested;
    }

    public function dates(CarbonInterface $start): array
    {
        return collect(range(0, 6))
            ->map(fn (int $offset) => Carbon::instance($start)->addDays($offset)->toDateString())
            ->all();
    }
}
