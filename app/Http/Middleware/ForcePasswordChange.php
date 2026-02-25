<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('company')->check()) {

            $staff = Auth::guard('company')->user();

            if ($staff->force_password_change &&
                !$request->is('company/password-change') &&
                !$request->is('company/logout')) {

                return redirect()->route('company.password.change');
            }
        }

        return $next($request);
    }
}