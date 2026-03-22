<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanySubscriptionIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $staff = Auth::guard('company')->user();
        $company = $staff?->company;

        if (!$company) {
            return redirect()->route('company.login');
        }

        if (!$company->isSubscriptionAvailable()) {
            return redirect()
                ->route('company.billing.index')
                ->with('error', 'ご契約状況をご確認ください。現在この機能は利用できません。');
        }

        return $next($request);
    }
}