<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReflectCustomerVisits extends Command
{
    protected $signature = 'customers:reflect-visits';
    protected $description = '終了済みの未キャンセル予約を顧客来店実績へ反映する';

    public function handle(): int
    {
        Reservation::query()
            ->where('status', 'reserved')
            ->whereNotNull('customer_id')
            ->where('end_at', '<=', now())
            ->whereNull('visit_reflected_at')
            ->orderBy('id')
            ->chunkById(100, function ($reservations) {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation) {
                        $customer = Customer::find($reservation->customer_id);

                        if (!$customer) {
                            $reservation->visit_reflected_at = now();
                            $reservation->save();
                            return;
                        }

                        $customer->visit_count = (int) $customer->visit_count + 1;

                        if (is_null($customer->last_visit) || $reservation->start_at->gt($customer->last_visit)) {
                            $customer->last_visit = $reservation->start_at;
                        }

                        $customer->save();

                        $reservation->visit_reflected_at = now();
                        $reservation->save();
                    });
                }
            });

        $this->info('顧客来店実績の反映が完了しました。');

        return self::SUCCESS;
    }
}