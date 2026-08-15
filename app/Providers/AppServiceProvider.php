<?php

namespace App\Providers;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Line\LineExtendSocialite;
use App\Console\Commands\DemoResetCommand;
use App\Console\Commands\DemoExportSeedCommand;
use App\Models\Inquiry;
use App\Models\ReservationChangeNoticeItem;

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
        View::composer('layouts.company', function ($view) {
            $staff = auth()->guard('company')->user();

            if (!$staff || !$staff->company_id) {
                return;
            }

            $companyId = (int) $staff->company_id;
            $companyChangeNoticeCount = ReservationChangeNoticeItem::query()
                ->where('company_id', $companyId)
                ->whereIn('response_status', ['waiting', 'mail_sent', 'no_response'])
                ->count();
            $companySupportUnreadCount = Inquiry::query()
                ->where('company_id', $companyId)
                ->where('status', 'answered')
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
