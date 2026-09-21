<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class CompanySalesMetrics
{
    /**
     * Amounts are reservation prices, not settled payment amounts.
     * The end of the period is exclusive.
     */
    public function summarize(int $companyId, CarbonInterface $start, CarbonInterface $end, CarbonInterface $now): array
    {
        $row = Reservation::query()
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->where('start_at', '>=', $start)
            ->where('start_at', '<', $end)
            ->selectRaw(
                'COUNT(*) AS reservation_count,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS completed_count,
                 SUM(CASE WHEN status = ? THEN COALESCE(total_price, 0) ELSE 0 END) AS completed_amount,
                 SUM(CASE WHEN status = ? AND start_at >= ? THEN 1 ELSE 0 END) AS forecast_count,
                 SUM(CASE WHEN status = ? AND start_at >= ? THEN COALESCE(total_price, 0) ELSE 0 END) AS forecast_amount,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS cancelled_count,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS no_show_count',
                [
                    Reservation::STATUS_COMPLETED,
                    Reservation::STATUS_COMPLETED,
                    Reservation::STATUS_RESERVED,
                    $now->toDateTimeString(),
                    Reservation::STATUS_RESERVED,
                    $now->toDateTimeString(),
                    Reservation::STATUS_CANCELLED,
                    Reservation::STATUS_NO_SHOW,
                ]
            )
            ->first();

        $count = (int) ($row->reservation_count ?? 0);
        $cancelledCount = (int) ($row->cancelled_count ?? 0);
        $noShowCount = (int) ($row->no_show_count ?? 0);

        return [
            'reservation_count' => $count,
            'completed_count' => (int) ($row->completed_count ?? 0),
            'completed_amount' => (int) ($row->completed_amount ?? 0),
            'forecast_count' => (int) ($row->forecast_count ?? 0),
            'forecast_amount' => (int) ($row->forecast_amount ?? 0),
            'cancelled_count' => $cancelledCount,
            'cancelled_rate' => $count ? round($cancelledCount / $count * 100, 1) : 0,
            'no_show_count' => $noShowCount,
            'no_show_rate' => $count ? round($noShowCount / $count * 100, 1) : 0,
        ];
    }

    public function comparisons(
        int $companyId,
        string $period,
        Carbon $periodStart,
        Carbon $periodEnd,
        Carbon $now,
        int $currentAmount
    ): array {
        $isCurrentPeriod = $periodStart->lte($now) && $periodEnd->gt($now);
        $ranges = $period === 'month'
            ? [
                ['label' => $isCurrentPeriod ? '前月同日まで' : '前月', 'start' => $periodStart->copy()->subMonthNoOverflow()],
                ['label' => $isCurrentPeriod ? '前年同月同日まで' : '前年同月', 'start' => $periodStart->copy()->subYearNoOverflow()],
            ]
            : [
                ['label' => $isCurrentPeriod ? '前年同日まで' : '前年', 'start' => $periodStart->copy()->subYearNoOverflow()],
            ];

        return array_map(function (array $range) use ($companyId, $period, $isCurrentPeriod, $now, $currentAmount) {
            $comparisonStart = $range['start'];
            $comparisonEnd = $period === 'month'
                ? $comparisonStart->copy()->addMonth()
                : $comparisonStart->copy()->addYear();

            if ($isCurrentPeriod) {
                $sameDay = $period === 'month'
                    ? $comparisonStart->copy()
                    : $comparisonStart->copy()->month($now->month);
                $comparisonEnd = $sameDay
                    ->day(min($now->day, $sameDay->daysInMonth))
                    ->setTime($now->hour, $now->minute, $now->second);
            }

            $previousAmount = $this->summarize($companyId, $comparisonStart, $comparisonEnd, $now)['completed_amount'];
            $changeAmount = $currentAmount - $previousAmount;

            return [
                'label' => $range['label'],
                'amount' => $previousAmount,
                'change_amount' => $changeAmount,
                'change_rate' => $previousAmount > 0 ? round($changeAmount / $previousAmount * 100, 1) : null,
            ];
        }, $ranges);
    }
}
