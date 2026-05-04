<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class MarkReservationNoShows extends Command
{
    protected $signature = 'reservations:mark-no-show';
    protected $description = '会社設定に従って予約中データを来店済みまたは無断キャンセルに変更する';

    public function handle(): int
    {
        $baseQuery = Reservation::query()
            ->join('companies', 'companies.id', '=', 'reservations.company_id')
            ->where('reservations.status', Reservation::STATUS_RESERVED)
            ->whereIn('companies.reservation_auto_status_mode', ['completed', 'no_show'])
            ->whereRaw(
                'reservations.start_at <= DATE_SUB(?, INTERVAL COALESCE(companies.reservation_auto_status_hours, 1) HOUR)',
                [now()]
            );

        $completedCount = (clone $baseQuery)
            ->where('companies.reservation_auto_status_mode', 'completed')
            ->update([
                'reservations.status' => Reservation::STATUS_COMPLETED,
                'reservations.updated_at' => now(),
            ]);

        $noShowCount = (clone $baseQuery)
            ->where('companies.reservation_auto_status_mode', 'no_show')
            ->update([
                'reservations.status' => Reservation::STATUS_NO_SHOW,
                'reservations.cancelled_at' => now(),
                'reservations.cancelled_type' => 'no_show',
                'reservations.updated_at' => now(),
            ]);

        if ($completedCount > 0 || $noShowCount > 0) {
            $this->info("Marked {$completedCount} reservations as completed, {$noShowCount} reservations as no_show.");
        }

        return self::SUCCESS;
    }
}
