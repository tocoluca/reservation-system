<?php

namespace Tests\Feature;

use App\Http\Controllers\Company\StaffController;
use App\Models\Company;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LightPlanStaffEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-08 12:00:00');

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->unique();
            $table->string('name');
            $table->string('plan_code')->nullable();
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
            $table->date('retired_at')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_light_eligibility_counts_only_active_reservable_staff(): void
    {
        $company = $this->company();

        $this->staff($company, 'MST01', 'master', true);
        $this->staff($company, '0001', 'staff', false);
        $this->staff($company, 'SHOP01', 'store_operator', false);
        $this->staff($company, '0002', 'staff', true, '2026-10-08');

        $this->assertSame(1, $company->activeReservableStaffCount());
        $this->assertTrue($company->isEligibleForLightPlan());
        $this->assertSame('MST01', $company->fixedReservableStaff()?->staff_code);
    }

    public function test_deleting_an_extra_staff_makes_the_company_eligible_for_light(): void
    {
        $company = $this->company();
        $master = $this->staff($company, 'MST01', 'master', true);
        $extra = $this->staff($company, '0001', 'staff', true);

        $this->assertSame(2, $company->activeReservableStaffCount());

        $this->actingAs($master, 'company');
        $response = $this->app->make(StaffController::class)->destroy($extra);

        $this->assertSoftDeleted('staff', ['id' => $extra->id]);
        $this->assertSame(1, $company->activeReservableStaffCount());
        $this->assertTrue($company->isEligibleForLightPlan());
        $this->assertSame('担当者を削除しました。', session('success'));
        $this->assertTrue($response->isRedirect());
    }

    public function test_updating_a_staff_shows_a_readable_success_message(): void
    {
        $company = $this->company();
        $master = $this->staff($company, 'MST01', 'master', true);
        $target = $this->staff($company, '0001', 'staff', true);

        $this->actingAs($master, 'company');

        $request = request()->create('/company/staff/'.$target->id, 'PUT', [
            'name' => '更新後スタッフ',
            'role' => 'area_leader',
            'is_reservable' => '1',
            'priority_order' => '1',
            'nomination_fee' => '0',
            'retired_at' => '2026-10-08',
        ]);

        $response = $this->app->make(StaffController::class)->update($request, $target);

        $this->assertSame('担当者情報を更新しました。', session('success'));
        $this->assertTrue($response->isRedirect());
        $this->assertDatabaseHas('staff', [
            'id' => $target->id,
            'name' => '更新後スタッフ',
            'retired_at' => '2026-10-08 00:00:00',
        ]);
    }

    private function company(): Company
    {
        return Company::create([
            'company_code' => 'LIGHT001',
            'name' => 'ライト判定テスト',
            'plan_code' => 'standard',
        ]);
    }

    private function staff(
        Company $company,
        string $code,
        string $role,
        bool $reservable,
        ?string $retiredAt = null,
    ): Staff {
        return Staff::create([
            'company_id' => $company->id,
            'staff_code' => $code,
            'name' => $code,
            'password' => 'password',
            'role' => $role,
            'is_reservable' => $reservable,
            'priority_order' => 0,
            'retired_at' => $retiredAt,
            'force_password_change' => false,
        ]);
    }
}
