<?php

namespace App\Providers;

use App\Console\Commands\DemoExportSeedCommand;
use App\Console\Commands\DemoResetCommand;
use App\Models\Company;
use App\Models\Inquiry;
use App\Models\ReservationChangeNoticeItem;
use App\Observers\CompanyObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Line\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        Company::observe(CompanyObserver::class);

        Carbon::setLocale('ja');
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('line', Provider::class);
        });
        View::composer('layouts.company', function ($view) {
            $staff = auth()->guard('company')->user();

            if (! $staff || ! $staff->company_id) {
                return;
            }

            $companyId = (int) $staff->company_id;
            $companyChangeNoticeCount = ReservationChangeNoticeItem::query()
                ->where('company_id', $companyId)
                ->whereIn('response_status', ['waiting', 'mail_sent', 'no_response'])
                ->count();
            $companySupportUnreadCount = Inquiry::query()
                ->where('company_id', $companyId)
                ->whereIn('status', ['answered', 'closed'])
                ->whereNotNull('admin_reply')
                ->where('is_read_by_company', false)
                ->count();

            $view->with(compact('companyChangeNoticeCount', 'companySupportUnreadCount'));
        });
        $this->commands([
            DemoResetCommand::class,
            DemoExportSeedCommand::class,
        ]);
    }
}
