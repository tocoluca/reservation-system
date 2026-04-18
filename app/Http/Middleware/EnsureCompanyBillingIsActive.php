<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyBillingIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('company')->user();

        if (!$staff || !$staff->company) {
            return $next($request);
        }

        $company = $staff->company;

        if ($company->isSubscriptionAvailable()) {
            return $next($request);
        }

        $allowedRoutes = [
            'company.billing.index',
            'company.billing.checkout',
            'company.billing.success',
            'company.billing.portal',
            'company.support.index',
            'company.support.store',
            'company.support.show',
            'company.logout',
        ];

        $currentRouteName = optional($request->route())->getName();

        if ($currentRouteName && in_array($currentRouteName, $allowedRoutes, true)) {
            return $next($request);
        }

        return redirect()
            ->route('company.billing.index')
            ->with('error', 'お支払いの更新が確認できないため、現在システムの利用を停止しています。カード情報をご確認ください。');
    }
}