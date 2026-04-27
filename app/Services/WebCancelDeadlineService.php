<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyBusinessCalendar;
use App\Models\Reservation;
use Carbon\Carbon;

class WebCancelDeadlineService
{
    public const TYPE_HOURS = 'hours';
    public const TYPE_BUSINESS_OPEN_MINUS_ONE_HOUR = 'business_open_minus_1_hour';

    public function deadlineFor(Reservation $reservation): Carbon
    {
        $company = $reservation->company;

        if (
            $company
            && ($company->web_cancel_deadline_type ?? self::TYPE_HOURS) === self::TYPE_BUSINESS_OPEN_MINUS_ONE_HOUR
        ) {
            $openingAt = $this->businessOpeningAt($company, Carbon::parse($reservation->start_at));

            if ($openingAt) {
                return $openingAt->subHour();
            }
        }

        $hours = (int) ($company->web_cancel_deadline_hours ?? 24);

        return Carbon::parse($reservation->start_at)->subHours($hours);
    }

    public function descriptionFor(Reservation $reservation): string
    {
        $company = $reservation->company;

        if (
            $company
            && ($company->web_cancel_deadline_type ?? self::TYPE_HOURS) === self::TYPE_BUSINESS_OPEN_MINUS_ONE_HOUR
        ) {
            return '予約当日の営業開始1時間前まで';
        }

        $hours = (int) ($company->web_cancel_deadline_hours ?? 24);

        return "予約時間の{$hours}時間前まで";
    }

    private function businessOpeningAt(Company $company, Carbon $reservationStart): ?Carbon
    {
        $date = $reservationStart->toDateString();

        $calendar = CompanyBusinessCalendar::query()
            ->where('company_id', $company->id)
            ->whereDate('date', $date)
            ->first();

        if ($calendar) {
            if (!(bool) $calendar->is_open) {
                return null;
            }

            if (!empty($calendar->open_time)) {
                return Carbon::parse($date . ' ' . $calendar->open_time);
            }
        }

        $weekday = $reservationStart->dayOfWeek;

        if (in_array((string) $weekday, (array) ($company->regular_holidays ?? []), true)) {
            return null;
        }

        $patterns = (array) (($company->open_patterns ?? [])[$weekday] ?? []);
        $openTimes = collect($patterns)
            ->map(fn ($pattern) => $pattern['open'] ?? $pattern['open_time'] ?? null)
            ->filter()
            ->sort()
            ->values();

        if ($openTimes->isEmpty()) {
            return null;
        }

        return Carbon::parse($date . ' ' . $openTimes->first());
    }
}
