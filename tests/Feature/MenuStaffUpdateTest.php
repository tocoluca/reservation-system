<?php

namespace Tests\Feature;

use App\Http\Controllers\Company\MenuStaffController;
use App\Models\Company;
use App\Models\Staff;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MenuStaffUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code');
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('menu_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('staff_id');
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

    public function test_update_replaces_only_current_company_relations_and_ignores_foreign_ids(): void
    {
        $company = Company::create(['name' => 'Company A', 'company_code' => 'COMP0001']);
        $otherCompany = Company::create(['name' => 'Company B', 'company_code' => 'COMP0002']);

        $master = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'MASTER01',
            'name' => 'Master A',
            'password' => 'unused',
            'role' => 'master',
        ]);
        $staff = Staff::create([
            'company_id' => $company->id,
            'staff_code' => 'STAFF001',
            'name' => 'Staff A',
            'password' => 'unused',
            'role' => 'staff',
        ]);
        $otherStaff = Staff::create([
            'company_id' => $otherCompany->id,
            'staff_code' => 'STAFF002',
            'name' => 'Staff B',
            'password' => 'unused',
            'role' => 'staff',
        ]);

        $menuId = DB::table('menus')->insertGetId([
            'company_id' => $company->id,
            'name' => 'Cut',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherMenuId = DB::table('menus')->insertGetId([
            'company_id' => $otherCompany->id,
            'name' => 'Perm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_staff')->insert([
            ['menu_id' => $menuId, 'staff_id' => $master->id],
            ['menu_id' => $otherMenuId, 'staff_id' => $otherStaff->id],
        ]);

        Auth::guard('company')->setUser($master);

        $request = Request::create('/company/menu-staff', 'POST', [
            'relations' => [
                $menuId => [$staff->id, $otherStaff->id],
                $otherMenuId => [$master->id],
            ],
        ]);

        $this->app->make(MenuStaffController::class)->update($request);

        $this->assertDatabaseHas('menu_staff', ['menu_id' => $menuId, 'staff_id' => $staff->id]);
        $this->assertDatabaseMissing('menu_staff', ['menu_id' => $menuId, 'staff_id' => $master->id]);
        $this->assertDatabaseMissing('menu_staff', ['menu_id' => $menuId, 'staff_id' => $otherStaff->id]);
        $this->assertDatabaseHas('menu_staff', ['menu_id' => $otherMenuId, 'staff_id' => $otherStaff->id]);
        $this->assertDatabaseMissing('menu_staff', ['menu_id' => $otherMenuId, 'staff_id' => $master->id]);
    }
}
