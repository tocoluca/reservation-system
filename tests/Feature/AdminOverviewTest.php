<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Company;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOverviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code');
            $table->string('email')->nullable();
            $table->string('industry_type');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_initialized')->default(true);
            $table->boolean('is_billing_active')->default(true);
            $table->boolean('line_login_enabled')->default(false);
            $table->string('subscription_status')->default('active');
            $table->timestamp('billing_starts_at')->nullable();
            $table->timestamps();
        });
        foreach (['staff', 'reservations', 'customers'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->softDeletes();
            });
        }
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
                $table->softDeletes();
            $table->string('status');
            $table->string('subject');
            $table->text('body');
            $table->timestamps();
        });
        (require database_path('migrations/2026_02_21_063219_create_company_applications_table.php'))->up();
    }

    private function admin(): void
    {
        $admin = new Admin(['name' => 'Test Admin', 'email' => 'admin@example.com']);
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
    }

    public function test_admin_entry_redirects_and_dashboard_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertRedirect();
    }

    public function test_dashboard_renders_and_excludes_campaign_from_billing_attention(): void
    {
        $this->admin();
        Company::create(['name' => 'Campaign', 'company_code' => 'CAMPAIGN', 'industry_type' => 'hair_salon',
            'is_initialized' => false, 'is_billing_active' => false, 'billing_starts_at' => now()->addDays(10)]);
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('billingAttentionCount', 0)
            ->assertViewHas('uninitializedCount', 1)
            ->assertSee('企業を探す')->assertSee('請求開始前');
        $this->assertFalse(Company::first()->needs_billing_attention);
        $company = Company::first();
        $company->billing_starts_at = now()->subDay();
        $company->save();
        $this->assertTrue($company->needs_billing_attention);
        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('billingAttentionCount', 1);
    }

    public function test_company_search_matches_japanese_industry_labels_and_combines_filters(): void
    {
        $this->admin();
        Company::create(['name' => 'Alpha', 'company_code' => 'A', 'industry_type' => 'nail']);
        Company::create(['name' => 'Beta', 'company_code' => 'B', 'industry_type' => 'barber']);
        $this->get(route('admin.company.index', ['keyword' => 'ネイル']))->assertOk()
            ->assertViewHas('companies', fn ($rows) => $rows->total() === 1 && $rows->first()->name === 'Alpha');
        $this->get(route('admin.company.index', ['industry_type' => 'barber']))->assertOk()
            ->assertViewHas('companies', fn ($rows) => $rows->total() === 1 && $rows->first()->name === 'Beta');
        $this->get(route('admin.company.index', ['industry_type' => 'barber', 'status' => 'inactive']))->assertOk()
            ->assertViewHas('companies', fn ($rows) => $rows->total() === 0);
    }

    public function test_pending_queue_is_oldest_first_and_links_to_exact_application(): void
    {
        $this->admin();
        foreach ([['Older', now()->subDays(2)], ['Newer', now()]] as [$name, $date]) {
            $application = new Application(['company_name' => $name, 'industry_type' => 'nail',
                'contact_person' => 'Test', 'email' => $name.'@example.com', 'phone' => '123', 'status' => 'pending']);
            $application->created_at = $date;
            $application->save();
        }
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('pendingApplications', fn ($rows) => $rows->pluck('company_name')->all() === ['Older', 'Newer']);
        $id = Application::where('company_name', 'Older')->value('id');
        $this->get(route('admin.applications', ['status' => 'pending', 'application_id' => $id]))->assertOk()
            ->assertViewHas('applications', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $id);
    }
}
