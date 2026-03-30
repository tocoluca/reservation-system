<?php

namespace App\Console\Commands;

use App\Mail\ReservationReminderMail;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReservationReminders extends Command
{
    protected $signature = 'mail:send-reservation-reminders';
    protected $description = '前日の予約リマインドメールを送信します';

    public function handle(): int
    {
        $tomorrowStart = Carbon::tomorrow()->startOfDay();
        $tomorrowEnd = Carbon::tomorrow()->endOfDay();

        $reservations = Reservation::with([
                'company',
                'customer',
                'staff',
                'menus',
            ])
            ->where('status', 'reserved')
            ->whereBetween('start_at', [$tomorrowStart, $tomorrowEnd])
            ->whereNull('reminder_sent_at')
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('送信対象の予約はありません。');
            return self::SUCCESS;
        }

        $sentCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        foreach ($reservations as $reservation) {
            try {
                if (!$reservation->customer) {
                    $skipCount++;
                    Log::warning('リマインド送信スキップ: customer が存在しない', [
                        'reservation_id' => $reservation->id,
                    ]);
                    continue;
                }

                if (empty($reservation->customer->email)) {
                    $skipCount++;
                    Log::warning('リマインド送信スキップ: customer email が空', [
                        'reservation_id' => $reservation->id,
                        'customer_id' => $reservation->customer->id ?? null,
                    ]);
                    continue;
                }

                Mail::to($reservation->customer->email)
                    ->send(new ReservationReminderMail($reservation));

                $reservation->update([
                    'reminder_sent_at' => now(),
                ]);

                $sentCount++;

                $this->info("送信完了 reservation_id={$reservation->id}");
            } catch (\Throwable $e) {
                $errorCount++;

                Log::error('リマインド送信エラー', [
                    'reservation_id' => $reservation->id,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->error("送信失敗 reservation_id={$reservation->id} : {$e->getMessage()}");
            }
        }

        $this->info("完了: sent={$sentCount}, skip={$skipCount}, error={$errorCount}");

        return self::SUCCESS;
    }
}