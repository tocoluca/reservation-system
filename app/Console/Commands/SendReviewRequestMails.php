<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequestMail;
use App\Models\Reservation;
use App\Services\LineMessagingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestMails extends Command
{
    protected $signature = 'mail:send-review-requests {--company_id=}';
    protected $description = '来店後の口コミ依頼メール・LINEを送信する';

    public function handle(): int
    {
        $query = Reservation::with(['company', 'review', 'customer'])
            ->where('status', 'reserved')
            ->whereNotNull('review_token')
            ->whereNull('review_requested_at')
            ->whereNull('review_submitted_at')
            ->where('start_at', '<=', now()->subHours(3))
            ->whereHas('company', function ($q) {
                $q->where('review_enabled', true);
            });

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

                if (!(bool) ($reservation->company->review_enabled ?? false)) {
                    continue;
                }

                if ($reservation->review) {
                    $reservation->update([
                        'review_requested_at' => now(),
                    ]);
                    continue;
                }

                $reviewUrl = route('reviews.create', $reservation->review_token);

                $sentAny = false;

                if (!empty($reservation->customer_email)) {
                    Mail::to($reservation->customer_email)->send(
                        new ReviewRequestMail($reservation->company, $reservation, $reviewUrl)
                    );
                    $sentAny = true;
                }

                if (
                    $reservation->customer &&
                    !empty($reservation->customer->line_user_id) &&
                    (bool) ($reservation->customer->line_notifications_enabled ?? true) &&
                    (bool) ($reservation->customer->line_review_opt_in ?? true)
                ) {
                    $text = "【{$reservation->company->name}】ご来店ありがとうございました。\n"
                        . "よろしければ口コミのご協力をお願いいたします。\n"
                        . $reviewUrl;

                    $lineSent = app(LineMessagingService::class)->pushText(
                        $reservation->company,
                        $reservation->customer->line_user_id,
                        $text
                    );

                    if ($lineSent) {
                        $reservation->customer->forceFill([
                            'last_line_sent_at' => now(),
                        ])->save();

                        $sentAny = true;
                    }
                }

                if (!$sentAny) {
/*
                    Log::warning('口コミ依頼送信スキップ: メールもLINEも送れない', [
                        'reservation_id' => $reservation->id,
                        'company_id'     => $reservation->company_id,
                    ]);
*/
                    continue;
                }

                $reservation->update([
                    'review_requested_at' => now(),
                ]);

                $this->info("送信完了: reservation={$reservation->id}");
/*
                Log::info('口コミ依頼送信成功', [
                    'reservation_id' => $reservation->id,
                    'company_id'     => $reservation->company_id,
                    'email'          => $reservation->customer_email,
                    'line_user_id'   => optional($reservation->customer)->line_user_id,
                ]);
*/
            } catch (\Throwable $e) {
                Log::error('口コミ依頼送信失敗', [
                    'reservation_id' => $reservation->id,
                    'company_id'     => $reservation->company_id,
                    'email'          => $reservation->customer_email,
                    'line_user_id'   => optional($reservation->customer)->line_user_id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}