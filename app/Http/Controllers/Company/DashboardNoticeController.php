<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyDashboardNotice;

class DashboardNoticeController extends Controller
{
    public function show(CompanyDashboardNotice $dashboardNotice)
    {
        $company = auth()->guard('company')->user()->company;

        $visible = CompanyDashboardNotice::visibleForCompany($company->id)
            ->where('id', $dashboardNotice->id)
            ->exists();

        abort_unless($visible, 403);

        return view('company.dashboard_notice_show', [
            'notice' => $dashboardNotice,
        ]);
    }
}