<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffRetirementReservationFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->unique();
            $table->string('name');
            $table->string('industry_type');
            $table->string('plan_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_initialized')->default(true);
            $table->string('subscription_status')->nullable();
            $table->boolean('is_billing_active')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_until')->nullable();
            $table->timestamp('billing_starts_at')->nullable();
            $table->integer('slot_minutes')->default(30);
            $table->integer('max_simultaneous_reservations')->default(1);
            $table->text('open_patterns')->nullable();
            $table->text('regular_holidays')->nullable();
            $table->boolean('holiday_is_closed')->default(false);
            $table->integer('reservation_month_limit')->default(3);
            $table->integer('reservation_open_days')->default(0);
            $table->integer('reservation_close_hours')->default(0);
            $table->boolean('menu_time_priority_flag')->default(false);
            $table->boolean('prefer_less_capable_staff_for_menu_assignment')->default(false);
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('staff_code');
            $table->string('name');
            $table->string('password');
            $table->string('role');
            $table->boolean('is_reservable')->default(true);
            $table->integer('priority_order')->default(0);
            $table->integer('nomination_fee')->default(0);
            $table->string('image_path')->nullable();
            $table->text('comment')->nullable();
            $table->date('retired_at')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->integer('duration');
            $table->integer('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('staff_id');
        });

        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
            $table->boolean('is_work')->default(true);
            $table->timestamps();
        });

        Schema::create('shift_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
        });

        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status');
            $table->boolean('is_full_day')->default(false);
            $table->timestamps();
        });

        Schema::create('company_business_calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->boolean('is_open')->default(true);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->integer('visit_count')->default(0);
            $table->timestamp('last_visit')->nullable();
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->boolean('is_staff_nominated')->default(false);
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('reserved');
            $table->string('source')->nullable();
            $table->integer('price')->default(0);
            $table->integer('nomination_fee')->default(0);
            $table->integer('total_price')->default(0);
            $table->string('cancel_token')->nullable();
            $table->string('review_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reservation_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('menu_id');
            $table->integer('price')->default(0);
            $table->integer('duration')->default(0);
            $table->timestamps();
        });

        Schema::create('reservation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('staff_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('duration')->default(0);
            $table->integer('price')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function test_company_calendar_candidates_allow_retirement_date_and_reject_next_day(): void
    {
        [$company, $staff, $menu, $retirementDate, $nextDate] = $this->fixture();

        $allowedCalendar = $this->actingAs($staff, 'company')->getJson(route('company.reserve.data', [
            'mode' => 'day',
            'date' => $retirementDate,
            'staff_id' => $staff->id,
            'menu_ids' => [$menu->id],
        ]));

        $allowedCalendar->assertOk()->assertJsonCount(1, 'staffs');
        $this->assertSame(1, $allowedCalendar->json("slots.10:00.{$staff->id}.available"));

        $this->actingAs($staff, 'company')->getJson(route('company.reserve.data', [
            'mode' => 'day',
            'date' => $nextDate,
            'staff_id' => $staff->id,
            'menu_ids' => [$menu->id],
        ]))->assertOk()
            ->assertJsonCount(0, 'staffs')
            ->assertJsonCount(0, 'slots');

        $allowed = $this->actingAs($staff, 'company')->getJson(route('company.calendar.assignment-candidates', [
            'datetime' => $retirementDate.' 10:00:00',
            'menu_ids' => [$menu->id],
        ]));

        $allowed->assertOk()
            ->assertJsonCount(1, 'candidates')
            ->assertJsonPath('candidates.0.assignments.0.staff_id', $staff->id);

        $blocked = $this->actingAs($staff, 'company')->getJson(route('company.calendar.assignment-candidates', [
            'datetime' => $nextDate.' 10:00:00',
            'menu_ids' => [$menu->id],
        ]));

        $blocked->assertOk()->assertJsonCount(0, 'candidates');
    }

    public function test_company_direct_request_cannot_create_reservation_after_retirement(): void
    {
        [, $staff, $menu, $retirementDate, $nextDate] = $this->fixture();

        $allowed = $this->actingAs($staff, 'company')->postJson(route('company.reservation.store'), [
            'start_at' => $retirementDate.' 10:00:00',
            'customer_name' => '境界値テスト',
            'customer_phone' => '090-1111-2222',
            'menu_ids' => [$menu->id],
            'staff_id' => $staff->id,
            'is_staff_nominated' => true,
        ]);

        $allowed->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', [
            'is_staff_nominated' => false,
            'nomination_fee' => 0,
            'total_price' => 3000,
        ]);

        $blocked = $this->actingAs($staff, 'company')->postJson(route('company.reservation.store'), [
            'start_at' => $nextDate.' 10:00:00',
            'customer_name' => '翌日テスト',
            'customer_phone' => '090-3333-4444',
            'menu_ids' => [$menu->id],
        ]);

        $blocked->assertStatus(422)->assertJsonPath('success', false);
        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_public_slots_candidates_and_store_enforce_retirement_boundary(): void
    {
        [$company, $staff, $menu, $retirementDate, $nextDate] = $this->fixture();

        $this->getJson(route('reserve.available_staff', [
            'company_code' => $company->company_code,
            'menu_ids' => [$menu->id],
            'date' => $retirementDate,
            'time' => '11:00',
        ]))->assertOk()
            ->assertJsonCount(1, 'staff')
            ->assertJsonPath('staff.0.id', $staff->id);

        $this->getJson(route('reserve.available_staff', [
            'company_code' => $company->company_code,
            'menu_ids' => [$menu->id],
            'date' => $nextDate,
            'time' => '11:00',
        ]))->assertOk()->assertJsonCount(0, 'staff');

        $allowedSlots = $this->getJson(route('reserve.slots', [
            'company_code' => $company->company_code,
            'menu_ids' => [$menu->id],
            'date' => $retirementDate,
            'staff_id' => $staff->id,
        ]));
        $allowedSlots->assertOk();
        $this->assertNotEmpty($allowedSlots->json());

        $this->getJson(route('reserve.slots', [
            'company_code' => $company->company_code,
            'menu_ids' => [$menu->id],
            'date' => $nextDate,
            'staff_id' => $staff->id,
        ]))->assertOk()->assertExactJson([]);

        $this->post(route('reserve.store', ['company_code' => $company->company_code]), [
            'start_at' => $retirementDate.' 12:00:00',
            'customer_name' => '公開予約境界値',
            'customer_phone' => '090-5555-6666',
            'menu_ids' => [$menu->id],
            'staff_id' => $staff->id,
        ])->assertRedirect();
        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('reservations', [
            'is_staff_nominated' => false,
            'nomination_fee' => 0,
            'total_price' => 3000,
        ]);

        $this->from(route('reserve.index', ['company_code' => $company->company_code]))
            ->post(route('reserve.store', ['company_code' => $company->company_code]), [
                'start_at' => $nextDate.' 12:00:00',
                'customer_name' => '公開予約翌日',
                'customer_phone' => '090-7777-8888',
                'menu_ids' => [$menu->id],
                'staff_id' => $staff->id,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('reservations', 1);
    }

    private function fixture(): array
    {
        $openPatterns = array_fill(0, 7, [[
            'open' => '09:00',
            'close' => '18:00',
        ]]);

        $company = Company::create([
            'company_code' => 'RETIRE01',
            'name' => '退職日テストサロン',
            'industry_type' => 'beauty',
            'plan_code' => 'light',
            'is_active' => true,
            'is_initialized' => true,
            'subscription_status' => 'active',
            'is_billing_active' => true,
            'slot_minutes' => 30,
            'max_simultaneous_reservations' => 1,
            'open_patterns' => $openPatterns,
            'regular_holidays' => [],
            'holiday_is_closed' => false,
            'reservation_month_limit' => 3,
            'reservation_open_days' => 0,
            'reservation_close_hours' => 0,
        ]);

        $retirementDate = now()->addDays(2)->toDateString();
        $nextDate = now()->addDays(3)->toDateString();

        $staff = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MST01',
            'name' => '退職予定スタッフ',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_reservable' => true,
            'priority_order' => 0,
            'nomination_fee' => 1000,
            'retired_at' => $retirementDate,
            'force_password_change' => false,
        ]);

        $menu = Menu::create([
            'company_id' => $company->id,
            'name' => 'カット',
            'duration' => 30,
            'price' => 3000,
            'is_active' => true,
        ]);

        foreach ([$retirementDate, $nextDate] as $date) {
            DB::table('company_business_calendars')->insert([
                'company_id' => $company->id,
                'date' => $date,
                'is_open' => true,
                'open_time' => '09:00:00',
                'close_time' => '18:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$company, $staff, $menu, $retirementDate, $nextDate];
    }
}
