<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminCompanyDashboardNoticeController;
use App\Models\Company;
use App\Models\CompanyDashboardNotice;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CompanyDashboardNoticeTargetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code')->unique();
            $table->timestamps();
        });

        Schema::create('company_dashboard_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('target_type')->default('all');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function test_specific_company_notice_resolves_company_code_and_is_visible_only_to_that_company(): void
    {
        $target = Company::create(['name' => 'Target Company', 'company_code' => 'TARGET01']);
        $other = Company::create(['name' => 'Other Company', 'company_code' => 'OTHER001']);

        $request = Request::create('/admin/company-dashboard-notices', 'POST', [
            'title' => 'Target notice',
            'target_type' => 'company',
            'company_code' => ' TARGET01 ',
            'is_active' => '1',
        ]);

        $this->app->make(AdminCompanyDashboardNoticeController::class)->store($request);

        $notice = CompanyDashboardNotice::firstOrFail();
        $this->assertSame('company', $notice->target_type);
        $this->assertSame($target->id, $notice->company_id);
        $this->assertTrue(CompanyDashboardNotice::visibleForCompany($target->id)->whereKey($notice->id)->exists());
        $this->assertFalse(CompanyDashboardNotice::visibleForCompany($other->id)->whereKey($notice->id)->exists());
    }

    public function test_specific_company_notice_requires_company_code(): void
    {
        $this->expectException(ValidationException::class);

        $request = Request::create('/admin/company-dashboard-notices', 'POST', [
            'title' => 'Missing target',
            'target_type' => 'company',
            'company_code' => '',
        ]);

        $this->app->make(AdminCompanyDashboardNoticeController::class)->store($request);
    }

    public function test_all_company_notice_ignores_submitted_company_code(): void
    {
        $company = Company::create(['name' => 'Target Company', 'company_code' => 'TARGET01']);

        $request = Request::create('/admin/company-dashboard-notices', 'POST', [
            'title' => 'All companies',
            'target_type' => 'all',
            'company_code' => $company->company_code,
            'is_active' => '1',
        ]);

        $this->app->make(AdminCompanyDashboardNoticeController::class)->store($request);

        $this->assertDatabaseHas('company_dashboard_notices', [
            'title' => 'All companies',
            'target_type' => 'all',
            'company_id' => null,
        ]);
    }

    public function test_notice_target_can_be_updated_between_all_and_specific_company(): void
    {
        $company = Company::create(['name' => 'Target Company', 'company_code' => 'TARGET01']);
        $notice = CompanyDashboardNotice::create([
            'title' => 'Before update',
            'target_type' => 'all',
            'company_id' => null,
            'is_active' => true,
        ]);

        $specificRequest = Request::create('/admin/company-dashboard-notices/' . $notice->id, 'PUT', [
            'title' => 'Specific company',
            'target_type' => 'company',
            'company_code' => $company->company_code,
            'is_active' => '1',
        ]);

        $this->app->make(AdminCompanyDashboardNoticeController::class)
            ->update($specificRequest, $notice);

        $this->assertDatabaseHas('company_dashboard_notices', [
            'id' => $notice->id,
            'target_type' => 'company',
            'company_id' => $company->id,
        ]);

        $allRequest = Request::create('/admin/company-dashboard-notices/' . $notice->id, 'PUT', [
            'title' => 'All companies again',
            'target_type' => 'all',
            'company_code' => $company->company_code,
            'is_active' => '1',
        ]);

        $this->app->make(AdminCompanyDashboardNoticeController::class)
            ->update($allRequest, $notice->fresh());

        $this->assertDatabaseHas('company_dashboard_notices', [
            'id' => $notice->id,
            'target_type' => 'all',
            'company_id' => null,
        ]);
    }

    public function test_notice_display_status_respects_active_flag_and_display_period(): void
    {
        Carbon::setTestNow('2026-09-21 12:00:00');

        $active = CompanyDashboardNotice::create([
            'title' => 'Displaying', 'is_active' => true,
            'start_date' => '2026-09-20', 'end_date' => '2026-09-21',
        ]);
        $scheduled = CompanyDashboardNotice::create([
            'title' => 'Scheduled', 'is_active' => true, 'start_date' => '2026-09-22',
        ]);
        $expired = CompanyDashboardNotice::create([
            'title' => 'Expired', 'is_active' => true, 'end_date' => '2026-09-20',
        ]);
        $inactive = CompanyDashboardNotice::create([
            'title' => 'Inactive', 'is_active' => false,
        ]);

        $this->assertSame('active', $active->display_status);
        $this->assertSame('scheduled', $scheduled->display_status);
        $this->assertSame('expired', $expired->display_status);
        $this->assertSame('inactive', $inactive->display_status);
        $this->assertSame([$active->id], CompanyDashboardNotice::currentlyPublished()->pluck('id')->all());

        Carbon::setTestNow();
    }
}
