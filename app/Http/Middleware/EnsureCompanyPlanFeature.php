<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $staff = auth()->guard('company')->user();
        $company = $staff?->company;

        abort_unless($company && $company->hasFeature($feature), 403, '現在のプランではこの機能を利用できません。');

        return $next($request);
    }
}
