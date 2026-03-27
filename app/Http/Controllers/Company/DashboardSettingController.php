<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyDashboardPermission;
use Illuminate\Http\Request;

class DashboardSettingController extends Controller
{
    public function index()
    {
        $staff = auth()->guard('company')->user();
        $company = $staff->company;

        abort_unless(
            CompanyDashboardPermission::can($company->id, $staff->role, 'dashboard.manage'),
            403
        );

        $roleSettings = CompanyDashboardPermission::resolveAllForCompany($company->id);
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

        abort_unless(
            CompanyDashboardPermission::can($company->id, $staff->role, 'dashboard.manage'),
            403
        );

        $submittedPermissions = $request->input('permissions', []);

        foreach (CompanyDashboardPermission::roleLabels() as $role => $label) {
            $rolePermissions = $submittedPermissions[$role] ?? [];

            foreach (CompanyDashboardPermission::permissionLabels() as $permissionKey => $permissionLabel) {
                $isEnabled = !empty($rolePermissions[$permissionKey]);

                // master の dashboard.manage は常に有効
                if ($role === 'master' && $permissionKey === 'dashboard.manage') {
                    $isEnabled = true;
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