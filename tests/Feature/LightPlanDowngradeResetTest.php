<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Mail\ReservationReminderMail;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LightPlanDowngradeResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-06 10:00:00');

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->unique();
            $table->string('name');
            $table->string('industry_type');
            $table->string('plan_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('theme_color')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('reservation_hero_image_path')->nullable();
            $table->timestamp('staff_setup_confirmed_at')->nullable();
            $table->unsignedInteger('max_simultaneous_reservations')->default(1);
            $table->boolean('review_enabled')->default(false);
            $table->unsignedInteger('revisit_reminder_days')->default(45);
            $table->boolean('prefer_less_capable_staff_for_menu_assignment')->default(false);
            $table->boolean('line_login_enabled')->default(false);
            $table->string('customer_notification_channel')->default('both');
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
            $table->unsignedInteger('priority_order')->default(99);
            $table->unsignedInteger('nomination_fee')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('shift_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
        });

        Schema::create('staff_default_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
        });

        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
        });

        Schema::create('shift_review_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('scope');
            $table->string('period')->default('');
        });

        Schema::create('menu_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('staff_id');
        });

        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('status')->default('reserved');
            $table->dateTime('start_at');
            $table->string('cancel_token')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
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
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[DataProvider('paidPlanProvider')]
    public function test_downgrading_to_light_resets_unavailable_settings(string $sourcePlan): void
    {
        $company = Company::create([
            'company_code' => 'DOWN'.strtoupper($sourcePlan),
            'name' => 'ダウングレード対象',
            'industry_type' => 'beauty',
            'plan_code' => $sourcePlan,
            'theme_color' => '#7c3aed',
            'logo_path' => 'companies/1/logos/custom.webp',
            'reservation_hero_image_path' => 'reservation-heroes/1/custom.webp',
            'staff_setup_confirmed_at' => now(),
            'max_simultaneous_reservations' => 5,
            'review_enabled' => true,
            'revisit_reminder_days' => 90,
            'prefer_less_capable_staff_for_menu_assignment' => true,
            'line_login_enabled' => true,
            'customer_notification_channel' => 'line',
        ]);

        $staffId = DB::table('staff')->insertGetId([
            'company_id' => $company->id,
            'staff_code' => 'STAFF01',
            'name' => '担当者',
            'password' => 'password',
            'role' => 'staff',
            'is_reservable' => true,
            'priority_order' => 1,
            'nomination_fee' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $menuId = DB::table('menus')->insertGetId([
            'company_id' => $company->id,
            'name' => 'カット',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $patternId = DB::table('shift_patterns')->insertGetId([
            'company_id' => $company->id,
            'name' => '早番',
        ]);

        DB::table('staff_default_shifts')->insert([
            'staff_id' => $staffId,
            'shift_pattern_id' => $patternId,
        ]);
        DB::table('staff_shifts')->insert([
            'staff_id' => $staffId,
            'date' => '2026-10-10',
            'shift_pattern_id' => $patternId,
        ]);
        DB::table('shift_review_requirements')->insert([
            'company_id' => $company->id,
            'staff_id' => $staffId,
            'scope' => 'monthly',
            'period' => '2026-10',
        ]);
        DB::table('menu_staff')->insert([
            'menu_id' => $menuId,
            'staff_id' => $staffId,
        ]);
        DB::table('vacations')->insert([
            [
                'staff_id' => $staffId,
                'start_at' => '2026-09-01 09:00:00',
                'end_at' => '2026-09-01 18:00:00',
            ],
            [
                'staff_id' => $staffId,
                'start_at' => '2026-10-10 09:00:00',
                'end_at' => '2026-10-10 18:00:00',
            ],
        ]);

        $company->update(['plan_code' => 'light']);
        $company->refresh();

        $this->assertSame('light', $company->plan_code);
        $this->assertSame(Company::DEFAULT_THEME_COLOR, $company->theme_color);
        $this->assertNull($company->logo_path);
        $this->assertNull($company->reservation_hero_image_path);
        $this->assertNull($company->staff_setup_confirmed_at);
        $this->assertSame(1, (int) $company->max_simultaneous_reservations);
        $this->assertFalse((bool) $company->review_enabled);
        $this->assertSame(45, (int) $company->revisit_reminder_days);
        $this->assertFalse((bool) $company->prefer_less_capable_staff_for_menu_assignment);
        $this->assertFalse((bool) $company->line_login_enabled);
        $this->assertSame('email', $company->customer_notification_channel);
        $this->assertSame(0, (int) DB::table('staff')->where('id', $staffId)->value('nomination_fee'));

        $this->assertDatabaseMissing('shift_patterns', ['company_id' => $company->id]);
        $this->assertDatabaseMissing('staff_default_shifts', ['staff_id' => $staffId]);
        $this->assertDatabaseMissing('staff_shifts', ['staff_id' => $staffId]);
        $this->assertDatabaseMissing('shift_review_requirements', ['company_id' => $company->id]);
        $this->assertDatabaseMissing('menu_staff', ['staff_id' => $staffId]);
        $this->assertDatabaseHas('vacations', [
            'staff_id' => $staffId,
            'end_at' => '2026-09-01 18:00:00',
        ]);
        $this->assertDatabaseMissing('vacations', [
            'staff_id' => $staffId,
            'end_at' => '2026-10-10 18:00:00',
        ]);
    }

    public static function paidPlanProvider(): array
    {
        return [
            'standard' => ['standard'],
            'platinum' => ['platinum'],
        ];
    }

    public function test_non_light_plan_change_does_not_reset_settings(): void
    {
        $company = Company::create([
            'company_code' => 'KEEPSETTINGS',
            'name' => '保持対象',
            'industry_type' => 'beauty',
            'plan_code' => 'standard',
            'theme_color' => '#7c3aed',
            'max_simultaneous_reservations' => 4,
            'review_enabled' => true,
            'revisit_reminder_days' => 60,
            'prefer_less_capable_staff_for_menu_assignment' => true,
            'line_login_enabled' => true,
            'customer_notification_channel' => 'both',
        ]);

        $patternId = DB::table('shift_patterns')->insertGetId([
            'company_id' => $company->id,
            'name' => '遅番',
        ]);

        $company->update(['plan_code' => 'platinum']);

        $this->assertSame('#7c3aed', $company->fresh()->theme_color);
        $this->assertDatabaseHas('shift_patterns', ['id' => $patternId]);
    }

    public function test_light_plan_is_excluded_from_automatic_revisit_reminders(): void
    {
        $company = Company::create([
            'company_code' => 'LIGHTMAIL',
            'name' => 'ライト通知対象外',
            'industry_type' => 'beauty',
            'plan_code' => 'light',
            'is_active' => true,
        ]);

        // This test intentionally has no customers table. A Light company must
        // be skipped before the reminder command attempts to query customers.
        $this->artisan('mail:send-revisit-reminders', [
            '--company_id' => $company->id,
        ])->assertSuccessful();
    }

    public function test_light_plan_is_excluded_from_previous_day_reservation_reminders(): void
    {
        Mail::fake();

        $company = Company::create([
            'company_code' => 'LIGHTDAYBEFORE',
            'name' => 'ライト前日通知対象外',
            'industry_type' => 'beauty',
            'plan_code' => 'light',
            'is_active' => true,
        ]);

        $reservationId = DB::table('reservations')->insertGetId([
            'company_id' => $company->id,
            'status' => 'reserved',
            'start_at' => now()->addDay()->setTime(10, 0),
            'reminder_sent_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('mail:send-reservation-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull(DB::table('reservations')->where('id', $reservationId)->value('reminder_sent_at'));
    }

    public function test_previous_day_reservation_reminders_continue_for_standard_plan(): void
    {
        Mail::fake();

        $company = Company::create([
            'company_code' => 'STANDARDDAYBEFORE',
            'name' => 'スタンダード前日通知',
            'industry_type' => 'beauty',
            'plan_code' => 'standard',
            'is_active' => true,
        ]);
        $customerId = DB::table('customers')->insertGetId([
            'company_id' => $company->id,
            'name' => '予約者',
            'phone' => '09011112222',
            'email' => 'customer@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $reservationId = DB::table('reservations')->insertGetId([
            'company_id' => $company->id,
            'customer_id' => $customerId,
            'status' => 'reserved',
            'start_at' => now()->addDay()->setTime(10, 0),
            'reminder_sent_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('mail:send-reservation-reminders')->assertSuccessful();

        Mail::assertSent(ReservationReminderMail::class, function (ReservationReminderMail $mail) {
            return $mail->hasTo('customer@example.test');
        });
        $this->assertNotNull(DB::table('reservations')->where('id', $reservationId)->value('reminder_sent_at'));
    }
}
