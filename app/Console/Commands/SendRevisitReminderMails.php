<?php

namespace App\Console\Commands;

use App\Mail\RevisitReminderMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerFollowupMailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRevisitReminderMails extends Command
{
    protected $signature = 'mail:send-revisit-reminders {--company_id=}';
    protected $description = '最終来店日から会社ごとの設定日数が経過した顧客へ再来店促進メールを送る';

    public function handle(): int
    {
        $companyId = $this->option('company_id');

        $companyQuery = Company::query()
            ->where('is_active', 1);

        if (!empty($companyId)) {
            $companyQuery->where('id', $companyId);
        }

        $companyQuery->chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $reminderDays = (int) ($company->revisit_reminder_days ?? 45);
                $targetDate = now()->subDays($reminderDays)->startOfDay();

                $this->info("会社ID {$company->id} / {$company->name} の処理を開始します（{$reminderDays}日設定）");

                $customers = Customer::query()
                    ->where('company_id', $company->id)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->whereNotNull('last_visit')
                    ->whereDate('last_visit', '<=', $targetDate)
                    ->whereDoesntHave('reservations', function ($q) {
                        $q->where('status', 'reserved')
                          ->where('start_at', '>', now());
                    })
                    ->whereDoesntHave('followupMailLogs', function ($q) {
                        $q->where('mail_type', 'revisit_reminder')
                          ->where('sent_at', '>=', now()->subDays(30));
                    })
                    ->orderBy('last_visit')
                    ->get();

                foreach ($customers as $customer) {
                    try {
                        Mail::to($customer->email)->send(
                            new RevisitReminderMail($company, $customer)
                        );

                        CustomerFollowupMailLog::create([
                            'company_id' => $company->id,
                            'customer_id' => $customer->id,
                            'mail_type' => 'revisit_reminder',
                            'sent_at' => now(),
                        ]);

                        $this->info("送信完了: company={$company->id} customer={$customer->id} email={$customer->email}");
                    } catch (\Throwable $e) {
                        Log::error('再来店促進メール送信失敗', [
                            'company_id' => $company->id,
                            'customer_id' => $customer->id,
                            'email' => $customer->email,
                            'error' => $e->getMessage(),
                        ]);

                        $this->error("送信失敗: company={$company->id} customer={$customer->id} email={$customer->email}");
                    }
                }
            }
        });

        return self::SUCCESS;
    }
}