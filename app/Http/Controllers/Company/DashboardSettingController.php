<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyDashboardPermission;
use Illuminate\Http\Request;

class DashboardSettingController extends Controller
{
    private function manageableRoles(string $role): array
    {
        return match ($role) {
            'master' => array_keys(CompanyDashboardPermission::roleLabels()),
            'chief' => ['staff', 'leader', 'area_leader', 'store_operator'],
            default => [],
        };
    }

    private function authorizeDashboardSettings($staff, int $companyId): void
    {
        abort_unless(
            in_array($staff->role, ['chief', 'master'], true)
                && CompanyDashboardPermission::can($companyId, $staff->role, 'dashboard.manage'),
            403
        );
    }

    public function index()
    {
        $staff = auth()->guard('company')->user();
        $company = $staff->company;

        $this->authorizeDashboardSettings($staff, (int) $company->id);

        $manageableRoles = $this->manageableRoles($staff->role);
        $roleSettings = array_intersect_key(
            CompanyDashboardPermission::resolveAllForCompany($company->id),
            array_flip($manageableRoles)
        );
        $permissionLabels = CompanyDashboardPermission::permissionLabels();

        return view('company.dashboard-settings.index', compact(
            'staff',
            'company',
            'roleSettings',
            'permissionLabels'
        ));
    }

    public function update(Request $request)
    {
        $staff = auth()->guard('company')->user();
        $company = $staff->company;

        $this->authorizeDashboardSettings($staff, (int) $company->id);

        $submittedPermissions = $request->input('permissions', []);

        $aliases = [
            'card.calendar' => 'card.business_calendar',
            'card.business' => 'card.business_calendar',
            'card.customer' => 'card.customers',
            'card.shift' => 'card.month_shift',
            'card.staff_shift' => 'card.month_shift',
            'card.staff_shifts' => 'card.month_shift',
            'card.company' => 'card.company_info',
            'card.company_edit' => 'card.company_info',
            'card.staffs' => 'card.staff',
            'card.staff_manage' => 'card.staff',
            'card.category_tag' => 'card.menu_category_tag',
            'card.menu_category' => 'card.menu_category_tag',
            'card.menu_categories' => 'card.menu_category_tag',
            'card.dashboard_settings' => 'dashboard.manage',
            'card.dashboard_manage' => 'dashboard.manage',
            'card.sales' => 'dashboard.sales',
        ];

        $permissionLabels = CompanyDashboardPermission::permissionLabels();

        $manageableRoles = $this->manageableRoles($staff->role);

        foreach (CompanyDashboardPermission::roleLabels() as $role => $label) {
            if (!in_array($role, $manageableRoles, true)) {
                continue;
            }

            $rolePermissions = $submittedPermissions[$role] ?? [];

            $normalizedRolePermissions = [];
            foreach ($rolePermissions as $key => $value) {
                $canonicalKey = $aliases[$key] ?? $key;
                $normalizedRolePermissions[$canonicalKey] = !empty($value);
            }

            foreach ($permissionLabels as $permissionKey => $permissionLabel) {
                $isEnabled = !empty($normalizedRolePermissions[$permissionKey]);

                if ($role === 'master' && $permissionKey === 'dashboard.manage') {
                    $isEnabled = true;
                }

                if (!in_array($role, ['chief', 'master'], true) && $permissionKey === 'dashboard.manage') {
                    $isEnabled = false;
                }

                if (in_array($permissionKey, CompanyDashboardPermission::fixedDisabledPermissions($role), true)) {
                    $isEnabled = false;
                }

                CompanyDashboardPermission::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'role' => $role,
                        'permission_key' => $permissionKey,
                    ],
                    [
                        'is_enabled' => $isEnabled,
                    ]
                );
            }
        }

        return redirect()
            ->route('company.dashboard-settings.index')
            ->with('success', 'ダッシュボード権限を更新しました。');
    }
}
