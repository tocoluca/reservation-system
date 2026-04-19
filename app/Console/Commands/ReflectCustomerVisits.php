<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReflectCustomerVisits extends Command
{
    protected $signature = 'customers:reflect-visits';
    protected $description = '来店済み予約を顧客テーブルへ反映する';

    public function handle(): int
    {
        $query = Reservation::with('customer')
            ->where('status', 'completed')
            ->whereNotNull('customer_id')
            ->whereNull('visit_reflected_at')
            ->orderBy('id');

        $count = 0;

        $query->chunkById(200, function ($reservations) use (&$count) {
            foreach ($reservations as $reservation) {
                try {
                    DB::transaction(function () use ($reservation, &$count) {
                        $reservation->refresh();

                        if ($reservation->status !== 'completed') {
                            return;
                        }

                        if ($reservation->visit_reflected_at !== null) {
                            return;
                        }

                        $customer = $reservation->customer;

                        if (!$customer) {
                            return;
                        }

                        $visitDate = $reservation->start_at;

                        $currentVisitCount = (int) ($customer->visit_count ?? 0);
                        $lastVisit = $customer->last_visit;

                        $customer->visit_count = $currentVisitCount + 1;

                        if (empty($lastVisit) || $visitDate > $lastVisit) {
                            $customer->last_visit = $visitDate;
                        }

                        $customer->save();

                        $reservation->visit_reflected_at = now();
                        $reservation->save();

                        $count++;
/*
                        $this->info("反映完了: reservation_id={$reservation->id} customer_id={$customer->id}");

                        Log::info('顧客来店実績反映成功', [
                            'reservation_id' => $reservation->id,
                            'customer_id' => $customer->id,
                            'company_id' => $reservation->company_id,
                            'visit_count' => $customer->visit_count,
                            'last_visit' => optional($customer->last_visit)->format('Y-m-d H:i:s'),
                        ]);
*/
                    });
                } catch (\Throwable $e) {
                    Log::error('顧客来店実績反映失敗', [
                        'reservation_id' => $reservation->id,
                        'customer_id' => $reservation->customer_id,
                        'company_id' => $reservation->company_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
/*
        $this->info("完了: {$count}件反映しました。");
*/
        return self::SUCCESS;
    }
}