<?php

namespace App\Console\Commands;

use App\Mail\ReservationChangeReminderMail;
use App\Models\ReservationChangeNoticeItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReservationChangeReminderCommand extends Command
{
    protected $signature = 'mail:send-reservation-change-reminders';
    protected $description = '予約変更確認メールの自動リマインドを送信する';

    public function handle(): int
    {
        $today = Carbon::today();

        $items = ReservationChangeNoticeItem::query()
            ->with(['reservation', 'notice'])
            ->whereNotNull('customer_email')
            ->whereNotNull('response_token')
            ->whereIn('response_status', ['waiting', 'mail_sent', 'no_response'])
            ->whereNull('confirmed_at')
            ->get();

        $sentCount = 0;
        $skipCount = 0;

        foreach ($items as $item) {
            $reservation = $item->reservation;

            if (!$reservation || empty($reservation->start_at)) {
                $skipCount++;
                continue;
            }

            $reservationDate = Carbon::parse($reservation->start_at)->startOfDay();
            $daysBefore = $today->diffInDays($reservationDate, false);

            if ($daysBefore <= 0) {
                $skipCount++;
                continue;
            }

            if (!$this->shouldSendReminder($daysBefore)) {
                $skipCount++;
                continue;
            }

            if ($item->last_reminder_sent_at && Carbon::parse($item->last_reminder_sent_at)->isSameDay($today)) {
                $skipCount++;
                continue;
            }

            try {
                $confirmUrl = route('reservation.notice.response.show', ['token' => $item->response_token]);

                Mail::to($item->customer_email)->send(
                    new ReservationChangeReminderMail($item, $confirmUrl)
                );

                $item->update([
                    'contact_status' => 'mail_sent',
                    'response_status' => 'mail_sent',
                    'last_reminder_sent_at' => now(),
                    'reminder_send_count' => (int) $item->reminder_send_count + 1,
                ]);

                $sentCount++;

                Log::info('予約変更リマインド送信成功', [
                    'item_id' => $item->id,
                    'reservation_id' => $item->reservation_id,
                    'email' => $item->customer_email,
                    'days_before' => $daysBefore,
                ]);
            } catch (\Throwable $e) {
                Log::error('予約変更リマインド送信失敗', [
                    'item_id' => $item->id,
                    'reservation_id' => $item->reservation_id,
                    'email' => $item->customer_email,
                    'days_before' => $daysBefore,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("送信件数: {$sentCount}");
        $this->info("スキップ件数: {$skipCount}");

        return self::SUCCESS;
    }

    private function shouldSendReminder(int $daysBefore): bool
    {
        return in_array($daysBefore, [12, 9, 6, 5, 4, 3, 2, 1], true);
    }
}