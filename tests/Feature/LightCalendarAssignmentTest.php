<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LightCalendarAssignmentTest extends TestCase
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
            $table->integer('price')->nullable();
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

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('status')->default('reserved');
            $table->timestamps();
        });

        Schema::create('reservation_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('staff_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->timestamps();
        });
    }

    public function test_light_plan_candidate_handles_every_company_menu_without_menu_staff_links(): void
    {
        $company = Company::create([
            'company_code' => 'LIGHT001',
            'name' => 'Lightサロン',
            'industry_type' => 'beauty',
            'plan_code' => 'light',
            'is_initialized' => false,
            'subscription_status' => 'active',
            'is_billing_active' => true,
            'slot_minutes' => 30,
            'max_simultaneous_reservations' => 1,
            'open_patterns' => [],
            'regular_holidays' => [],
        ]);

        $staff = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MST01',
            'name' => 'オーナー',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_reservable' => true,
            'priority_order' => 0,
            'force_password_change' => false,
        ]);

        $menus = collect([
            Menu::create(['company_id' => $company->id, 'name' => 'カット', 'duration' => 30, 'price' => 3000]),
            Menu::create(['company_id' => $company->id, 'name' => 'カラー', 'duration' => 60, 'price' => 6000]),
        ]);

        $start = now()->addDay()->setTime(10, 0, 0);

        Schema::getConnection()->table('company_business_calendars')->insert([
            'company_id' => $company->id,
            'date' => $start->toDateString(),
            'is_open' => true,
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseCount('menu_staff', 0);

        $response = $this->actingAs($staff, 'company')->getJson(route('company.calendar.assignment-candidates', [
            'datetime' => $start->format('Y-m-d H:i:s'),
            'menu_ids' => $menus->pluck('id')->all(),
        ]));

        $response->assertOk()
            ->assertJsonPath('mode', 'single')
            ->assertJsonCount(1, 'candidates')
            ->assertJsonCount(2, 'candidates.0.assignments')
            ->assertJsonPath('candidates.0.assignments.0.menu_id', $menus[0]->id)
            ->assertJsonPath('candidates.0.assignments.1.menu_id', $menus[1]->id)
            ->assertJsonPath('candidates.0.assignments.0.staff_id', $staff->id)
            ->assertJsonPath('candidates.0.assignments.1.staff_id', $staff->id);
    }
}
