<?php

namespace App\Providers;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Line\LineExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('ja');
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('line', \SocialiteProviders\Line\Provider::class);
        });
    }
}
