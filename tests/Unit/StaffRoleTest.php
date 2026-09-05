<?php

namespace Tests\Unit;

use App\Http\Controllers\Company\DashboardSettingController;
use App\Models\CompanyDashboardPermission;
use App\Models\Staff;
use PHPUnit\Framework\TestCase;

class StaffRoleTest extends TestCase
{
    public function test_role_options_include_renamed_area_leader_and_chief_in_order(): void
    {
        $this->assertSame([
            'staff' => 'スタッフ',
            'leader' => 'リーダー',
            'area_leader' => '統括リーダー',
            'store_operator' => '店舗運営',
            'chief' => 'チーフ',
            'master' => 'マスター',
        ], Staff::roleOptions());
    }

    public function test_existing_area_leader_and_new_chief_have_expected_labels(): void
    {
        $areaLeader = new Staff(['role' => 'area_leader']);
        $chief = new Staff(['role' => 'chief']);

        $this->assertSame('統括リーダー', $areaLeader->roleLabel());
        $this->assertSame('チーフ', $chief->roleLabel());
        $this->assertTrue($chief->isChief());
    }

    public function test_chief_has_a_configurable_dashboard_permission_profile(): void
    {
        $this->assertArrayHasKey('chief', CompanyDashboardPermission::roleLabels());
        $this->assertTrue(CompanyDashboardPermission::defaultPermissions('chief')['card.staff']);
        $this->assertFalse(CompanyDashboardPermission::defaultPermissions('chief')['dashboard.manage']);
    }

    public function test_chief_can_manage_only_lower_dashboard_roles(): void
    {
        $controller = new DashboardSettingController();
        $method = new \ReflectionMethod($controller, 'manageableRoles');

        $this->assertSame(
            ['staff', 'leader', 'area_leader', 'store_operator'],
            $method->invoke($controller, 'chief')
        );
        $this->assertSame([], $method->invoke($controller, 'store_operator'));
        $this->assertContains('chief', $method->invoke($controller, 'master'));
    }

    public function test_store_operator_cannot_use_store_and_announcement_settings(): void
    {
        $permissions = CompanyDashboardPermission::defaultPermissions('store_operator');

        foreach (['card.company_info', 'card.logo', 'card.theme', 'card.billing', 'card.notices'] as $permission) {
            $this->assertFalse($permissions[$permission]);
            $this->assertContains(
                $permission,
                CompanyDashboardPermission::fixedDisabledPermissions('store_operator')
            );
        }
    }
}
