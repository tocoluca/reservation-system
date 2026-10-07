<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;
use App\Support\CompanySetupProgress;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompanySetupPlanFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->unique();
            $table->string('name');
            $table->string('industry_type');
            $table->string('email')->nullable();
            $table->string('theme_color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_initialized')->default(false);
            $table->timestamp('staff_setup_confirmed_at')->nullable();
            $table->integer('slot_minutes')->default(30);
            $table->integer('max_simultaneous_reservations')->default(1);
            $table->text('open_patterns')->nullable();
            $table->string('plan_code')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('subscription_status')->nullable();
            $table->boolean('is_billing_active')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_until')->nullable();
            $table->timestamp('billing_starts_at')->nullable();
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
            $table->integer('priority_order')->default(99);
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shift_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('staff_default_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->boolean('is_work')->default(true);
            $table->timestamps();
        });

        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->timestamps();
        });

        Schema::create('reservation_change_notice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('response_status')->nullable();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('status')->nullable();
            $table->text('admin_reply')->nullable();
            $table->boolean('is_read_by_company')->default(false);
        });

        Schema::create('company_dashboard_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('role');
            $table->string('permission_key');
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function test_plan_must_be_selected_before_other_setup_steps_are_shown(): void
    {
        [$company, $staff] = $this->createCompanyAndStaff();

        $this->actingAs($staff, 'company')
            ->get(route('company.setup'))
            ->assertOk()
            ->assertSee('利用プランを選択する')
            ->assertSee('ライトプラン')
            ->assertSee('スタンダードプラン')
            ->assertSee('プラチナプラン')
            ->assertDontSee('担当者を設定する')
            ->assertDontSee('企業情報を設定する');

        $this->assertNull($company->fresh()->plan_code);
    }

    public function test_light_plan_is_saved_and_owner_becomes_reservable(): void
    {
        [$company, $staff] = $this->createCompanyAndStaff();

        $this->actingAs($staff, 'company')
            ->post(route('company.setup.plan'), ['plan_code' => 'light'])
            ->assertRedirect(route('company.setup'));

        $this->assertSame('light', $company->fresh()->plan_code);
        $this->assertTrue($staff->fresh()->is_reservable);
    }

    public function test_initialized_legacy_company_without_plan_keeps_standard_setup_behaviour(): void
    {
        [, $staff] = $this->createCompanyAndStaff(['is_initialized' => true]);

        $this->actingAs($staff, 'company')
            ->get(route('company.setup'))
            ->assertOk()
            ->assertSee('スタンダードプラン')
            ->assertSee('担当者を設定する')
            ->assertSee('シフトを設定する')
            ->assertDontSee('プランを選択すると、そのプランに必要な初期設定が表示されます。');
    }

    public function test_light_setup_hides_staff_and_shift_steps(): void
    {
        [, $staff] = $this->createCompanyAndStaff(['plan_code' => 'light']);

        $this->actingAs($staff, 'company')
            ->get(route('company.setup'))
            ->assertOk()
            ->assertSee('企業情報を設定する')
            ->assertSee('メニューを設定する')
            ->assertSee('メニュー管理を開く')
            ->assertDontSee('担当者を設定する')
            ->assertDontSee('シフトを設定する')
            ->assertDontSee('シフトパターン')
            ->assertDontSee('基本シフト')
            ->assertDontSee('月シフト')
            ->assertDontSee('メニュー対応スタッフ');
    }

    public function test_light_company_information_hides_revisit_reminder_setting(): void
    {
        [, $staff] = $this->createCompanyAndStaff([
            'plan_code' => 'light',
            'is_initialized' => true,
            'subscription_status' => 'active',
            'is_billing_active' => true,
        ]);

        $this->actingAs($staff, 'company')
            ->get(route('company.info.edit'))
            ->assertOk()
            ->assertDontSee('再来店促進メール送信日数');
    }

    public function test_standard_and_platinum_staff_steps_wait_for_list_confirmation(): void
    {
        foreach (['standard', 'platinum'] as $planCode) {
            [$company] = $this->createCompanyAndStaff(['plan_code' => $planCode]);

            $staffStep = collect(CompanySetupProgress::for($company)['steps'])
                ->firstWhere('key', 'staff');

            $this->assertFalse($staffStep['done'], $planCode);

            $company->update(['staff_setup_confirmed_at' => now()]);
            $staffStep = collect(CompanySetupProgress::for($company->fresh())['steps'])
                ->firstWhere('key', 'staff');

            $this->assertTrue($staffStep['done'], $planCode);
        }
    }

    public function test_opening_staff_list_confirms_the_staff_setup_step(): void
    {
        [$company, $staff] = $this->createCompanyAndStaff([
            'plan_code' => 'standard',
            'subscription_status' => 'active',
            'is_billing_active' => true,
        ]);

        $this->assertNull($company->staff_setup_confirmed_at);

        $this->actingAs($staff, 'company')
            ->get(route('company.staff.index'))
            ->assertOk();

        $this->assertNotNull($company->fresh()->staff_setup_confirmed_at);
    }

    public function test_light_setup_can_complete_without_staff_or_shift_configuration(): void
    {
        [$company, $staff] = $this->createCompanyAndStaff([
            'plan_code' => 'light',
            'open_patterns' => [
                'monday' => [
                    ['open' => '09:00', 'close' => '18:00'],
                ],
            ],
        ]);

        Menu::create([
            'company_id' => $company->id,
            'name' => 'カット',
            'duration' => 60,
            'price' => 5000,
        ]);

        $this->actingAs($staff, 'company')
            ->post(route('company.setup.complete'))
            ->assertRedirect(route('company.dashboard'));

        $this->assertTrue((bool) $company->fresh()->is_initialized);
        $this->assertDatabaseCount('shift_patterns', 0);
        $this->assertDatabaseCount('staff_default_shifts', 0);
        $this->assertDatabaseCount('staff_shifts', 0);
    }

    public function test_standard_setup_keeps_staff_and_shift_steps_required(): void
    {
        [$company, $staff] = $this->createCompanyAndStaff([
            'plan_code' => 'standard',
            'open_patterns' => [
                'monday' => [
                    ['open' => '09:00', 'close' => '18:00'],
                ],
            ],
        ]);

        Menu::create([
            'company_id' => $company->id,
            'name' => 'カット',
            'duration' => 60,
            'price' => 5000,
        ]);

        $this->actingAs($staff, 'company')
            ->get(route('company.setup'))
            ->assertOk()
            ->assertSee('担当者を設定する')
            ->assertSee('シフトを設定する');

        $this->actingAs($staff, 'company')
            ->post(route('company.setup.complete'))
            ->assertRedirect(route('company.setup'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, '担当者')
                && str_contains($message, 'シフト'));

        $this->assertFalse((bool) $company->fresh()->is_initialized);
    }

    public function test_invalid_plan_is_rejected(): void
    {
        [$company, $staff] = $this->createCompanyAndStaff();

        $this->actingAs($staff, 'company')
            ->from(route('company.setup'))
            ->post(route('company.setup.plan'), ['plan_code' => 'unknown'])
            ->assertRedirect(route('company.setup'))
            ->assertSessionHasErrors('plan_code');

        $this->assertNull($company->fresh()->plan_code);
    }

    private function createCompanyAndStaff(array $companyAttributes = []): array
    {
        $company = Company::create(array_merge([
            'company_code' => strtoupper(fake()->bothify('??######')),
            'name' => 'テストサロン',
            'industry_type' => 'beauty',
            'email' => fake()->unique()->safeEmail(),
            'theme_color' => '#7c3aed',
            'is_active' => true,
            'is_initialized' => false,
            'slot_minutes' => 30,
            'max_simultaneous_reservations' => 1,
            'open_patterns' => [],
        ], $companyAttributes));

        $staff = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MST01',
            'name' => 'オーナー',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_reservable' => false,
            'priority_order' => 0,
            'force_password_change' => false,
        ]);

        return [$company, $staff];
    }
}
