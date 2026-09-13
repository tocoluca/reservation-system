<?php

namespace Tests\Feature;

use App\Http\Controllers\Company\ShiftPatternController;
use App\Http\Controllers\Company\StaffDefaultShiftController;
use App\Http\Controllers\Company\StaffShiftController;
use App\Models\Company;
use App\Models\ShiftPattern;
use App\Models\Staff;
use App\Models\StaffDefaultShift;
use App\Models\StaffShift;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffShiftLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-13 10:00:00');

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code');
            $table->string('theme_color')->nullable();
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
            $table->date('retired_at')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('company_dashboard_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('role');
            $table->string('permission_key');
            $table->boolean('is_enabled');
            $table->timestamps();
        });
        Schema::create('shift_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('staff_default_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
            $table->unsignedTinyInteger('weekday');
            $table->boolean('is_work')->default(true);
            $table->timestamps();
        });
        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('shift_pattern_id')->nullable();
            $table->date('date');
            $table->boolean('is_work')->default(true);
            $table->timestamps();
        });
        Schema::create('shift_review_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('scope');
            $table->string('period')->default('');
            $table->string('reason');
            $table->timestamps();
            $table->unique(['company_id', 'staff_id', 'scope', 'period']);
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_default_generation_only_fills_empty_dates_and_stops_at_retirement(): void
    {
        [$company, $master, $patternA, $patternB] = $this->baseRecords();
        $existingStaff = $this->staff($company, 'Existing', 'ST001');
        $newStaff = $this->staff($company, 'New', 'ST002');
        $retiringStaff = $this->staff($company, 'Retiring', 'ST003', '2026-10-02');

        foreach ([$existingStaff, $newStaff, $retiringStaff] as $staff) {
            StaffDefaultShift::create([
                'staff_id' => $staff->id,
                'weekday' => 4,
                'shift_pattern_id' => $patternA->id,
                'is_work' => true,
            ]);
        }
        StaffShift::create([
            'staff_id' => $existingStaff->id,
            'date' => '2026-10-01',
            'shift_pattern_id' => $patternB->id,
            'is_work' => true,
        ]);

        app(StaffShiftController::class)->generate(Request::create('/company/staff-shifts/generate', 'POST', [
            'month' => '2026-10',
        ]));

        $this->assertSame($patternB->id, StaffShift::where('staff_id', $existingStaff->id)->whereDate('date', '2026-10-01')->value('shift_pattern_id'));
        $this->assertSame(1, StaffShift::where('staff_id', $existingStaff->id)->whereBetween('date', ['2026-10-01', '2026-10-31'])->count());
        $this->assertSame($patternA->id, StaffShift::where('staff_id', $newStaff->id)->whereDate('date', '2026-10-01')->value('shift_pattern_id'));
        $this->assertDatabaseHas('staff_shifts', ['staff_id' => $retiringStaff->id, 'date' => '2026-10-01']);
        $this->assertDatabaseMissing('staff_shifts', ['staff_id' => $retiringStaff->id, 'date' => '2026-10-08']);
    }

    public function test_previous_month_copy_only_fills_empty_dates(): void
    {
        [$company, $master, $patternA, $patternB] = $this->baseRecords();
        $existingStaff = $this->staff($company, 'Existing', 'ST001');
        $newStaff = $this->staff($company, 'New', 'ST002');

        foreach ([$existingStaff, $newStaff] as $staff) {
            StaffShift::create([
                'staff_id' => $staff->id,
                'date' => '2026-09-01',
                'shift_pattern_id' => $patternA->id,
                'is_work' => true,
            ]);
        }
        StaffShift::create([
            'staff_id' => $existingStaff->id,
            'date' => '2026-10-01',
            'shift_pattern_id' => $patternB->id,
            'is_work' => true,
        ]);

        app(StaffShiftController::class)->copy(Request::create('/company/staff-shifts/copy', 'POST', [
            'month' => '2026-10',
        ]));

        $this->assertSame($patternB->id, StaffShift::where('staff_id', $existingStaff->id)->whereDate('date', '2026-10-01')->value('shift_pattern_id'));
        $this->assertSame(1, StaffShift::where('staff_id', $existingStaff->id)->whereBetween('date', ['2026-10-01', '2026-10-31'])->count());
        $this->assertSame($patternA->id, StaffShift::where('staff_id', $newStaff->id)->whereDate('date', '2026-10-01')->value('shift_pattern_id'));
    }

    public function test_monthly_shift_update_ignores_past_dates_but_allows_today_and_future(): void
    {
        [$company, $master, $patternA, $patternB] = $this->baseRecords();
        $staff = $this->staff($company, 'Editable', 'ST001');

        StaffShift::create([
            'staff_id' => $staff->id,
            'date' => '2026-09-12',
            'shift_pattern_id' => $patternA->id,
            'is_work' => true,
        ]);

        app(StaffShiftController::class)->update(Request::create('/company/staff-shifts/update', 'POST', [
            'shifts' => [
                $staff->id => [
                    '2026-09-12' => $patternB->id,
                    '2026-09-13' => $patternB->id,
                    '2026-09-14' => $patternB->id,
                ],
            ],
        ]));

        $this->assertSame($patternA->id, StaffShift::where('staff_id', $staff->id)->whereDate('date', '2026-09-12')->value('shift_pattern_id'));
        $this->assertDatabaseHas('staff_shifts', ['staff_id' => $staff->id, 'date' => '2026-09-13', 'shift_pattern_id' => $patternB->id]);
        $this->assertDatabaseHas('staff_shifts', ['staff_id' => $staff->id, 'date' => '2026-09-14', 'shift_pattern_id' => $patternB->id]);
    }

    public function test_deleting_a_pattern_removes_all_references_and_requires_review(): void
    {
        [$company, $master, $pattern, $replacementPattern] = $this->baseRecords();
        $staff = $this->staff($company, 'Affected', 'ST001');
        StaffDefaultShift::create(['staff_id' => $staff->id, 'weekday' => 1, 'shift_pattern_id' => $pattern->id, 'is_work' => true]);
        StaffShift::create(['staff_id' => $staff->id, 'date' => '2026-10-05', 'shift_pattern_id' => $pattern->id, 'is_work' => true]);

        $deleteResponse = app(ShiftPatternController::class)->delete($pattern->id);

        $this->assertSame(route('company.staff-default-shifts'), $deleteResponse->getTargetUrl());
        $this->assertDatabaseMissing('shift_patterns', ['id' => $pattern->id]);
        $this->assertDatabaseHas('staff_default_shifts', ['staff_id' => $staff->id, 'shift_pattern_id' => null, 'is_work' => false]);
        $this->assertDatabaseHas('staff_shifts', ['staff_id' => $staff->id, 'shift_pattern_id' => null, 'is_work' => false]);
        $this->assertDatabaseHas('shift_review_requirements', ['staff_id' => $staff->id, 'scope' => 'default', 'period' => '']);
        $this->assertDatabaseHas('shift_review_requirements', ['staff_id' => $staff->id, 'scope' => 'monthly', 'period' => '2026-10']);
        $this->assertSame(0, StaffDefaultShift::whereNotNull('shift_pattern_id')->where('shift_pattern_id', $pattern->id)->count());
        $this->assertSame(0, StaffShift::whereNotNull('shift_pattern_id')->where('shift_pattern_id', $pattern->id)->count());

        app(StaffDefaultShiftController::class)->update(Request::create('/company/staff-default-shifts', 'POST', [
            'shifts' => [$staff->id => [1 => $pattern->id]],
        ]));
        $this->assertDatabaseHas('shift_review_requirements', ['staff_id' => $staff->id, 'scope' => 'default']);

        $defaultResponse = app(StaffDefaultShiftController::class)->update(Request::create('/company/staff-default-shifts', 'POST', [
            'shifts' => [$staff->id => [1 => $replacementPattern->id]],
        ]));
        $this->assertDatabaseMissing('shift_review_requirements', ['staff_id' => $staff->id, 'scope' => 'default']);
        $this->assertSame(route('company.staff-shifts', ['month' => '2026-10']), $defaultResponse->getTargetUrl());

        app(StaffShiftController::class)->update(Request::create('/company/staff-shifts/update', 'POST', [
            'shifts' => [$staff->id => ['2026-10-05' => $pattern->id]],
        ]));
        $this->assertDatabaseHas('shift_review_requirements', ['staff_id' => $staff->id, 'scope' => 'monthly', 'period' => '2026-10']);

        app(StaffShiftController::class)->update(Request::create('/company/staff-shifts/update', 'POST', [
            'shifts' => [$staff->id => ['2026-10-05' => $replacementPattern->id]],
        ]));
        $this->assertDatabaseMissing('shift_review_requirements', ['staff_id' => $staff->id, 'scope' => 'monthly', 'period' => '2026-10']);
        $this->assertDatabaseHas('staff_shifts', ['staff_id' => $staff->id, 'date' => '2026-10-05', 'shift_pattern_id' => $replacementPattern->id, 'is_work' => true]);
    }

    private function baseRecords(): array
    {
        $company = Company::create(['name' => 'Test Company', 'company_code' => 'TEST01']);
        $master = $this->staff($company, 'Master', 'MST01', null, 'master');
        Auth::guard('company')->setUser($master);

        $patternA = ShiftPattern::create([
            'company_id' => $company->id,
            'name' => 'A',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'color' => '#2563eb',
            'sort_order' => 1,
        ]);
        $patternB = ShiftPattern::create([
            'company_id' => $company->id,
            'name' => 'B',
            'start_time' => '10:00',
            'end_time' => '19:00',
            'color' => '#16a34a',
            'sort_order' => 2,
        ]);

        return [$company, $master, $patternA, $patternB];
    }

    private function staff(Company $company, string $name, string $code, ?string $retiredAt = null, string $role = 'staff'): Staff
    {
        return Staff::create([
            'company_id' => $company->id,
            'staff_code' => $code,
            'name' => $name,
            'password' => 'unused',
            'role' => $role,
            'retired_at' => $retiredAt,
        ]);
    }
}
