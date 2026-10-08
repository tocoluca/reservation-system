<?php

namespace App\Http\Middleware;

use App\Support\PlanCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $staff = auth()->guard('company')->user();
        $company = $staff?->company;

        abort_unless($company, 403, 'この操作を実行する権限がありません。');

        if (! $company->hasFeature($feature)) {
            $availablePlans = PlanCatalog::availablePlanLabelsForFeature($feature);
            $availablePlanText = implode('または', array_map(
                fn (string $plan) => $plan.'プラン',
                $availablePlans
            ));

            $request->attributes->set('company_plan_restriction', [
                'current_plan' => $company->planLabel().'プラン',
                'available_plans' => $availablePlans,
            ]);

            abort(403, $availablePlanText
                ? $availablePlanText.'で利用できる機能です。'
                : '現在のプランではこの機能を利用できません。');
        }

        return $next($request);
    }
}
