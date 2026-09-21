<?php

namespace Tests\Feature;

use App\Http\Controllers\Company\ReservationController;
use App\Models\Company;
use App\Models\Reservation;
use App\Models\Staff;
use App\Services\ReservationChangeNoticeService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShopCancellationNoticeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code');
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('staff_code');
            $table->string('name');
            $table->string('password');
            $table->string('role');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->string('status')->default('reserved');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_type')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reservation_change_notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('title');
            $table->date('target_date')->nullable();
            $table->string('reason_type')->nullable();
            $table->text('reason_text')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('reservation_change_notice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notice_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('contact_type')->nullable();
            $table->string('contact_status')->default('pending');
            $table->string('response_status')->default('waiting');
            $table->string('response_token', 100)->nullable()->unique();
            $table->timestamp('mail_sent_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->unsignedInteger('reminder_send_count')->default(0);
            $table->timestamp('mail_opened_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason_type')->nullable();
            $table->unsignedBigInteger('cancel_processed_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function test_shop_cancellation_from_calendar_creates_change_notice(): void
    {
        [$company, $master, $reservation] = $this->createReservation();
        Auth::guard('company')->setUser($master);

        $request = Request::create('/company/reservation/' . $reservation->id . '/cancel', 'POST', [
            'cancel_kind' => 'shop',
        ]);

        $response = $this->app->make(ReservationController::class)
            ->cancel($request, $reservation->id);

        $this->app->make(ReservationController::class)
            ->cancel($request, $reservation->id);

        $this->assertTrue($response->getData(true)['success']);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancelled_type' => 'shop',
        ]);
        $this->assertDatabaseHas('reservation_change_notices', [
            'company_id' => $company->id,
            'reason_type' => 'shop_cancel',
            'status' => 'in_progress',
            'created_by' => $master->id,
        ]);
        $this->assertDatabaseHas('reservation_change_notice_items', [
            'company_id' => $company->id,
            'reservation_id' => $reservation->id,
            'contact_status' => 'pending',
            'response_status' => 'waiting',
            'cancel_reason_type' => 'shop',
            'cancel_processed_by' => $master->id,
        ]);
        $this->assertDatabaseCount('reservation_change_notices', 1);
        $this->assertDatabaseCount('reservation_change_notice_items', 1);
    }

    public function test_customer_cancellation_does_not_create_change_notice(): void
    {
        [, $master, $reservation] = $this->createReservation();
        Auth::guard('company')->setUser($master);

        $request = Request::create('/company/reservation/' . $reservation->id . '/cancel', 'POST', [
            'cancel_kind' => 'customer',
        ]);

        $this->app->make(ReservationController::class)
            ->cancel($request, $reservation->id);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
            'cancelled_type' => 'customer',
        ]);
        $this->assertDatabaseCount('reservation_change_notices', 0);
        $this->assertDatabaseCount('reservation_change_notice_items', 0);
    }

    public function test_calendar_closure_creates_notices_only_for_today_and_future(): void
    {
        $this->travelTo(Carbon::parse('2026-09-21 12:00:00'));
        [$company, , $pastReservation] = $this->createReservation('2026-09-20');
        $todayReservation = $this->createReservationForDate($company, '2026-09-21');
        $futureReservation = $this->createReservationForDate($company, '2026-09-22');
        $service = $this->app->make(ReservationChangeNoticeService::class);

        $this->assertNull($service->createForClosedDate($company, '2026-09-20'));
        $this->assertNotNull($service->createForClosedDate($company, '2026-09-21'));
        $this->assertNotNull($service->createForClosedDate($company, '2026-09-22'));

        $this->assertDatabaseCount('reservation_change_notices', 2);
        $this->assertDatabaseMissing('reservation_change_notice_items', ['reservation_id' => $pastReservation->id]);
        $this->assertDatabaseHas('reservation_change_notice_items', ['reservation_id' => $todayReservation->id]);
        $this->assertDatabaseHas('reservation_change_notice_items', ['reservation_id' => $futureReservation->id]);
    }

    public function test_calendar_time_change_creates_notices_only_for_today_and_future(): void
    {
        $this->travelTo(Carbon::parse('2026-09-21 12:00:00'));
        [$company, , $pastReservation] = $this->createReservation('2026-09-20');
        $todayReservation = $this->createReservationForDate($company, '2026-09-21');
        $futureReservation = $this->createReservationForDate($company, '2026-09-22');
        $service = $this->app->make(ReservationChangeNoticeService::class);

        $this->assertNull($service->createForTimeChange($company, '2026-09-20', '12:00', '18:00'));
        $this->assertNotNull($service->createForTimeChange($company, '2026-09-21', '12:00', '18:00'));
        $this->assertNotNull($service->createForTimeChange($company, '2026-09-22', '12:00', '18:00'));

        $this->assertDatabaseCount('reservation_change_notices', 2);
        $this->assertDatabaseMissing('reservation_change_notice_items', ['reservation_id' => $pastReservation->id]);
        $this->assertDatabaseHas('reservation_change_notice_items', ['reservation_id' => $todayReservation->id]);
        $this->assertDatabaseHas('reservation_change_notice_items', ['reservation_id' => $futureReservation->id]);
    }

    private function createReservation(string $date = '2026-09-21'): array
    {
        $company = Company::create([
            'name' => 'Company A',
            'company_code' => 'COMP0001',
        ]);

        $master = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MASTER01',
            'name' => 'Master A',
            'password' => 'unused',
            'role' => 'master',
        ]);

        return [$company, $master, $this->createReservationForDate($company, $date)];
    }

    private function createReservationForDate(Company $company, string $date): Reservation
    {
        $reservationId = DB::table('reservations')->insertGetId([
            'company_id' => $company->id,
            'customer_name' => '予約 太郎',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '09012345678',
            'start_at' => $date . ' 10:00:00',
            'end_at' => $date . ' 11:00:00',
            'status' => 'reserved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Reservation::findOrFail($reservationId);
    }
}
