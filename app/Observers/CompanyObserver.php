<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\LightPlanDowngradeService;

class CompanyObserver
{
    public function updating(Company $company): void
    {
        $previousPlan = $company->getOriginal('plan_code');
        $isDowngradingToLight = $company->isDirty('plan_code')
            && $company->plan_code === 'light'
            && in_array($previousPlan, ['standard', 'platinum'], true);

        if (! $isDowngradingToLight) {
            return;
        }

        app(LightPlanDowngradeService::class)->applyCompanyDefaults($company);
    }

    public function updated(Company $company): void
    {
        $previous = $company->getPrevious();
        $previousPlan = $previous['plan_code'] ?? null;

        if ($company->plan_code !== 'light' || ! in_array($previousPlan, ['standard', 'platinum'], true)) {
            return;
        }

        app(LightPlanDowngradeService::class)->resetRelatedSettings($company, $previous);
    }
}
