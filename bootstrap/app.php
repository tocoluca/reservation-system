<?php

use App\Http\Middleware\CheckCompanyCode;
use App\Http\Middleware\EnsureCompanyBillingIsActive;
use App\Http\Middleware\EnsurePublicReservationAvailable;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company.code' => CheckCompanyCode::class,
            'company.init' => \App\Http\Middleware\CompanyInit::class,
            'company.billing.active' => EnsureCompanyBillingIsActive::class,
            'public.reservation.available' => EnsurePublicReservationAvailable::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe-webhook',
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            if ($request->is('company/*')) {
                return route('company.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();