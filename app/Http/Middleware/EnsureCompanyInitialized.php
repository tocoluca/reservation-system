<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyInitialized
{
    public function handle($request, Closure $next)
    {
        $staff = Auth::guard('company')->user();

        if ($staff->role === 'master' &&
            !$staff->company->is_initialized &&
            !$request->is('company/setup*')) {

            return redirect()->route('company.setup');
        }

        return $next($request);
    }
}