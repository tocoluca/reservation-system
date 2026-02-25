<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;

class CheckCompanyCode
{
    public function handle($request, Closure $next)
    {
        $companyCode = $request->route('company_code');

        $company = Company::where('company_code', $companyCode)->first();

        if (!$company) {
            abort(404, '企業コードが不正です');
        }

        // 利用停止チェック
        if (!$company->is_active) {
            return response()->view('errors.company_stopped');
        }

        // セッション保存
        session(['company_id' => $company->id]);
        session(['company_code' => $company->company_code]);

        return $next($request);
    }
}