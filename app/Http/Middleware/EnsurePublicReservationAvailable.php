<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicReservationAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyCode = $request->route('company_code');

        if (!$companyCode) {
            return $next($request);
        }

        $company = Company::where('company_code', $companyCode)->first();

        if (!$company) {
            abort(404);
        }

        if (!$company->isSubscriptionAvailable()) {
            abort(503, '現在この店舗は予約受付を停止しています。');
        }

        return $next($request);
    }
}