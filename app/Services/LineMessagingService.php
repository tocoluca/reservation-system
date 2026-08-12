<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessagingService
{
    public function pushText(Company $company, string $lineUserId, string $text): bool
    {
        if (!$company->sendsCustomerLine()) {
            Log::info('LINE push skipped: customer notification channel excludes LINE', [
                'company_id' => $company->id ?? null,
                'plan_code' => $company->plan_code,
                'customer_notification_channel' => $company->customerNotificationChannel(),
                'line_user_id' => $lineUserId,
            ]);
            return false;
        }

        $channelAccessToken = $company->line_channel_access_token ?? null;

        if (blank($channelAccessToken) || blank($lineUserId) || blank($text)) {
            Log::warning('LINE push skipped: token or userId or text missing', [
                'company_id' => $company->id ?? null,
                'line_user_id' => $lineUserId,
            ]);
            return false;
        }

        $response = Http::withToken($channelAccessToken)
            ->acceptJson()
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => mb_substr($text, 0, 5000),
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('LINE push failed', [
                'company_id' => $company->id ?? null,
                'line_user_id' => $lineUserId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        Log::info('LINE push success', [
            'company_id' => $company->id ?? null,
            'line_user_id' => $lineUserId,
        ]);

        return true;
    }
}
