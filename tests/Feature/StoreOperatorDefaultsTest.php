<?php

namespace Tests\Feature;

use App\Http\Controllers\Company\StaffController;
use App\Models\Company;
use App\Models\Staff;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StoreOperatorDefaultsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        Schema::create('staff_store', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('store_id');
        });
    }

    public function test_store_operator_name_and_reservation_setting_are_forced_when_created(): void
    {
        $company = Company::create(['name' => 'Test', 'company_code' => 'TEST0001']);
        $master = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MST01',
            'name' => 'Master',
            'password' => 'unused',
            'role' => 'master',
        ]);
        Auth::guard('company')->setUser($master);

        $request = Request::create('/company/staff', 'POST', [
            'name' => '入力された個人名',
            'password' => 'password123',
            'role' => 'store_operator',
            'is_reservable' => '1',
        ]);
        $this->app->make(StaffController::class)->store($request);

        $operator = Staff::where('role', 'store_operator')->firstOrFail();
        $this->assertSame('店舗ユーザ', $operator->name);
        $this->assertFalse($operator->is_reservable);
        $this->assertSame('SHOP01', $operator->staff_code);
    }

    public function test_store_operator_name_and_reservation_setting_are_forced_when_updated(): void
    {
        $company = Company::create(['name' => 'Test', 'company_code' => 'TEST0001']);
        $master = Staff::create(['company_id' => $company->id, 'staff_code' => 'MST01', 'name' => 'Master', 'password' => 'unused', 'role' => 'master']);
        $operator = Staff::create(['company_id' => $company->id, 'staff_code' => 'SHOP01', 'name' => '旧名称', 'password' => 'unused', 'role' => 'store_operator']);
        Auth::guard('company')->setUser($master);

        $request = Request::create('/company/staff/'.$operator->id, 'PUT', [
            'name' => '変更された個人名',
            'role' => 'store_operator',
            'is_reservable' => '1',
        ]);
        $this->app->make(StaffController::class)->update($request, $operator);

        $operator->refresh();
        $this->assertSame('店舗ユーザ', $operator->name);
        $this->assertFalse($operator->is_reservable);
    }
}
