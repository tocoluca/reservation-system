<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Staff;
use App\Services\ReservationHeroImageProcessor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReservationHeroSettingsTest extends TestCase
{
    private string $imageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'reserve-hero-feature-'.bin2hex(random_bytes(6));
        $this->app->instance(ReservationHeroImageProcessor::class, new ReservationHeroImageProcessor($this->imageRoot));

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->unique();
            $table->string('name');
            $table->string('industry_type');
            $table->string('theme_color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_initialized')->default(true);
            $table->timestamp('billing_starts_at')->nullable();
            $table->string('subscription_status')->nullable();
            $table->boolean('is_billing_active')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_until')->nullable();
            $table->string('reservation_hero_image_path')->nullable();
            $table->string('reservation_hero_heading')->nullable();
            $table->string('reservation_hero_subheading')->nullable();
            $table->unsignedTinyInteger('reservation_hero_heading_size')->default(40);
            $table->unsignedTinyInteger('reservation_hero_subheading_size')->default(16);
            $table->string('reservation_hero_text_color')->default('#ffffff');
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
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->imageRoot);
        Schema::dropIfExists('company_dashboard_permissions');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('companies');
        parent::tearDown();
    }

    public function test_company_can_save_preview_settings_and_replace_its_own_image(): void
    {
        [$company, $staff] = $this->companyAndMaster('HERO0001');

        $this->actingAs($staff, 'company')
            ->post(route('company.reservation-hero.update'), [
                'hero_image' => UploadedFile::fake()->image('salon.jpg', 1800, 900),
                'reservation_hero_heading' => '髪から、毎日を美しく。',
                'reservation_hero_subheading' => 'ご希望のメニューと日時をお選びください。',
                'reservation_hero_heading_size' => 52,
                'reservation_hero_subheading_size' => 18,
                'reservation_hero_text_color' => '#F7E9DD',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $company->refresh();

        $this->assertSame('髪から、毎日を美しく。', $company->reservation_hero_heading);
        $this->assertSame('#f7e9dd', $company->reservation_hero_text_color);
        $this->assertStringStartsWith("reservation-heroes/{$company->id}/", $company->reservation_hero_image_path);
        $this->assertFileExists($this->app->make(ReservationHeroImageProcessor::class)->absolutePath(
            $company->reservation_hero_image_path,
            $company->id
        ));

        $this->get(route('reserve.hero-image', ['company_code' => $company->company_code]))
            ->assertOk()
            ->assertHeader('content-type', 'image/webp');

        $firstPath = $company->reservation_hero_image_path;
        $firstAbsolutePath = $this->app->make(ReservationHeroImageProcessor::class)
            ->absolutePath($firstPath, $company->id);

        $this->actingAs($staff, 'company')
            ->post(route('company.reservation-hero.update'), [
                'hero_image' => UploadedFile::fake()->image('replacement.png', 1600, 700),
                'reservation_hero_heading' => '新しい見出し',
                'reservation_hero_subheading' => '新しいサブ見出し',
                'reservation_hero_heading_size' => 44,
                'reservation_hero_subheading_size' => 16,
                'reservation_hero_text_color' => '#ffffff',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $company->refresh();
        $this->assertNotSame($firstPath, $company->reservation_hero_image_path);
        $this->assertFileDoesNotExist($firstAbsolutePath);

        $replacementPath = $this->app->make(ReservationHeroImageProcessor::class)
            ->absolutePath($company->reservation_hero_image_path, $company->id);

        $this->actingAs($staff, 'company')
            ->delete(route('company.reservation-hero.image.destroy'))
            ->assertRedirect();

        $this->assertNull($company->refresh()->reservation_hero_image_path);
        $this->assertFileDoesNotExist($replacementPath);
    }

    public function test_image_route_will_not_serve_a_path_owned_by_another_company(): void
    {
        [$owner] = $this->companyAndMaster('HERO0002');
        [$other] = $this->companyAndMaster('HERO0003');
        $processor = $this->app->make(ReservationHeroImageProcessor::class);
        $ownerPath = $processor->store(UploadedFile::fake()->image('owner.jpg', 1000, 500), $owner->id);

        $owner->update(['reservation_hero_image_path' => $ownerPath]);
        $other->update(['reservation_hero_image_path' => $ownerPath]);

        $this->get(route('reserve.hero-image', ['company_code' => $owner->company_code]))->assertOk();
        $this->get(route('reserve.hero-image', ['company_code' => $other->company_code]))->assertNotFound();
    }

    private function companyAndMaster(string $code): array
    {
        $company = Company::create([
            'company_code' => $code,
            'name' => 'テストサロン',
            'industry_type' => 'beauty',
            'theme_color' => '#8b5e3c',
            'is_active' => true,
            'is_initialized' => true,
            'billing_starts_at' => now()->addDay(),
        ]);

        $staff = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MASTER01',
            'name' => '管理者',
            'password' => bcrypt('password'),
            'role' => 'master',
            'force_password_change' => false,
        ]);

        return [$company, $staff];
    }
}
