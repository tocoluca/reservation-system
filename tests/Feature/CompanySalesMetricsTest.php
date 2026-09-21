<?php

namespace Tests\Feature;

use App\Services\CompanySalesMetrics;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompanySalesMetricsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->timestamp('start_at');
            $table->string('status');
            $table->integer('total_price')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_completed_amount_and_future_booking_forecast_are_separate(): void
    {
        $now = Carbon::parse('2026-09-21 12:00:00');
        $this->reservation(1, '2026-09-20 10:00:00', 'completed', 12000);
        $this->reservation(1, '2026-09-21 10:00:00', 'reserved', 9000);
        $this->reservation(1, '2026-09-21 13:00:00', 'reserved', 11000);
        $this->reservation(1, '2026-09-22 10:00:00', 'reserved', 13000);
        $this->reservation(1, '2026-09-22 11:00:00', 'cancelled', 8000);
        $this->reservation(1, '2026-09-22 12:00:00', 'no_show', 7000);
        $this->reservation(2, '2026-09-20 10:00:00', 'completed', 99999);
        $this->reservation(1, '2026-10-01 10:00:00', 'completed', 5000);
        $this->reservation(1, '2026-09-19 10:00:00', 'completed', 4000, true);

        $metrics = app(CompanySalesMetrics::class)->summarize(
            1,
            Carbon::parse('2026-09-01 00:00:00'),
            Carbon::parse('2026-10-01 00:00:00'),
            $now
        );

        $this->assertSame(6, $metrics['reservation_count']);
        $this->assertSame(1, $metrics['completed_count']);
        $this->assertSame(12000, $metrics['completed_amount']);
        $this->assertSame(2, $metrics['forecast_count']);
        $this->assertSame(24000, $metrics['forecast_amount']);
        $this->assertSame(1, $metrics['cancelled_count']);
        $this->assertSame(16.7, $metrics['cancelled_rate']);
        $this->assertSame(1, $metrics['no_show_count']);
        $this->assertSame(16.7, $metrics['no_show_rate']);
    }

    public function test_empty_period_has_zero_rates(): void
    {
        $metrics = app(CompanySalesMetrics::class)->summarize(
            1,
            Carbon::parse('2026-08-01 00:00:00'),
            Carbon::parse('2026-09-01 00:00:00'),
            Carbon::parse('2026-09-21 12:00:00')
        );

        $this->assertSame(0, $metrics['completed_amount']);
        $this->assertSame(0, $metrics['forecast_amount']);
        $this->assertSame(0, $metrics['cancelled_rate']);
        $this->assertSame(0, $metrics['no_show_rate']);
    }

    public function test_current_period_comparisons_use_the_same_elapsed_date_and_time(): void
    {
        $now = Carbon::parse('2026-09-21 12:00:00');
        $this->reservation(1, '2026-09-20 10:00:00', 'completed', 10000);
        $this->reservation(1, '2026-08-20 10:00:00', 'completed', 7000);
        $this->reservation(1, '2026-08-22 10:00:00', 'completed', 3000);
        $this->reservation(1, '2025-09-20 10:00:00', 'completed', 5000);
        $this->reservation(1, '2025-09-22 10:00:00', 'completed', 2000);
        $service = app(CompanySalesMetrics::class);

        $monthComparisons = $service->comparisons(
            1, 'month', Carbon::parse('2026-09-01'), Carbon::parse('2026-10-01'), $now, 10000
        );

        $this->assertSame('前月同日まで', $monthComparisons[0]['label']);
        $this->assertSame(7000, $monthComparisons[0]['amount']);
        $this->assertSame(3000, $monthComparisons[0]['change_amount']);
        $this->assertSame(42.9, $monthComparisons[0]['change_rate']);
        $this->assertSame('前年同月同日まで', $monthComparisons[1]['label']);
        $this->assertSame(5000, $monthComparisons[1]['amount']);
        $this->assertSame(100.0, $monthComparisons[1]['change_rate']);

        $yearComparisons = $service->comparisons(
            1, 'year', Carbon::parse('2026-01-01'), Carbon::parse('2027-01-01'), $now, 10000
        );

        $this->assertSame('前年同日まで', $yearComparisons[0]['label']);
        $this->assertSame(5000, $yearComparisons[0]['amount']);
    }

    private function reservation(int $companyId, string $startAt, string $status, int $amount, bool $deleted = false): void
    {
        DB::table('reservations')->insert([
            'company_id' => $companyId,
            'start_at' => $startAt,
            'status' => $status,
            'total_price' => $amount,
            'created_at' => '2026-09-01 00:00:00',
            'updated_at' => '2026-09-01 00:00:00',
            'deleted_at' => $deleted ? '2026-09-02 00:00:00' : null,
        ]);
    }
}
