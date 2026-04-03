<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequestMail;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestMails extends Command
{
    protected $signature = 'mail:send-review-requests {--company_id=}';
    protected $description = '来店後の口コミ依頼メールを送信する';

    public function handle(): int
    {
        $query = Reservation::with(['company', 'review'])
            ->where('status', 'reserved')
            ->whereNotNull('customer_email')
            ->whereNotNull('review_token')
            ->whereNull('review_requested_at')
            ->whereNull('review_submitted_at')
            ->where('start_at', '<=', now()->subHours(3));

        if ($companyId = $this->option('company_id')) {
            $query->where('company_id', $companyId);
        }

        $reservations = $query->get();

        if ($reservations->isEmpty()) {
            $this->info('送信対象はありません。');
            return self::SUCCESS;
        }

        foreach ($reservations as $reservation) {
            try {
                if (!$reservation->company) {
                    continue;
                }

                if ($reservation->review) {
                    $reservation->update([
                        'review_requested_at' => now(),
                    ]);
                    continue;
                }

                $reviewUrl = route('reviews.create', $reservation->review_token);

                Mail::to($reservation->customer_email)->send(
                    new ReviewRequestMail($reservation->company, $reservation, $reviewUrl)
                );

                $reservation->update([
                    'review_requested_at' => now(),
                ]);

                $this->info("送信完了: reservation={$reservation->id} email={$reservation->customer_email}");

                Log::info('口コミ依頼メール送信成功', [
                    'reservation_id' => $reservation->id,
                    'company_id' => $reservation->company_id,
                    'email' => $reservation->customer_email,
                ]);
            } catch (\Throwable $e) {
                Log::error('口コミ依頼メール送信失敗', [
                    'reservation_id' => $reservation->id,
                    'company_id' => $reservation->company_id,
                    'email' => $reservation->customer_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}