<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class MarkReservationNoShows extends Command
{
    protected $signature = 'reservations:mark-no-show';
    protected $description = '予約時間から1時間過ぎた予約中データを無断キャンセルに変更する';

    public function handle(): int
    {
        $count = Reservation::query()
            ->where('status', Reservation::STATUS_RESERVED)
            ->where('start_at', '<=', now()->subHour())
            ->update([
                'status' => Reservation::STATUS_NO_SHOW,
                'updated_at' => now(),
            ]);

        if ($count > 0) {
            $this->info("Marked {$count} reservations as no_show.");
        }

        return self::SUCCESS;
    }
}
