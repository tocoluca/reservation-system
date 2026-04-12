<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminCompanyDashboardNoticeController;

use App\Http\Controllers\Company\AuthController as CompanyAuth;
use App\Http\Controllers\Company\DashboardController as CompanyDash;
use App\Http\Controllers\Company\SetupController;
use App\Http\Controllers\Company\ReservationController;
use App\Http\Controllers\Company\QuestionnaireController;
use App\Http\Controllers\Company\ThemeController;
use App\Http\Controllers\Company\LogoController;
use App\Http\Controllers\Company\StaffController;
use App\Http\Controllers\Company\PasswordController;
use App\Http\Controllers\Company\VacationController;
use App\Http\Controllers\Company\CompanyController as CompanyInfoController;
use App\Http\Controllers\Company\MyProfileController;
use App\Http\Controllers\Company\CalendarController;
use App\Http\Controllers\Company\MenuController;
use App\Http\Controllers\Company\MenuStaffController;
use App\Http\Controllers\Company\MenuSettingController;
use App\Http\Controllers\Company\ShiftPatternController;
use App\Http\Controllers\Company\StaffDefaultShiftController;
use App\Http\Controllers\Company\StaffShiftController;
use App\Http\Controllers\Company\CustomerController;
use App\Http\Controllers\Company\NoticeController;
use App\Http\Controllers\Company\DashboardNoticeController;
use App\Http\Controllers\Company\DashboardSettingController;
use App\Http\Controllers\Company\ReviewController as CompanyReviewController;
use App\Http\Controllers\ReservationNoticeResponseController;
use App\Http\Controllers\Company\ReservationChangeNoticeController;

use App\Http\Controllers\CompanyApplicationController;
use App\Http\Controllers\ReserveController;
use App\Http\Controllers\ReserveCancelController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\Company\BillingController;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| 共通
|--------------------------------------------------------------------------
*/

Route::get('/apply', [CompanyApplicationController::class, 'create'])
    ->name('company.application.create');

Route::post('/apply', [CompanyApplicationController::class, 'store'])
    ->name('company.application.store');

Route::get('/apply/complete', [CompanyApplicationController::class, 'complete'])
    ->name('company.application.complete');

/*
|--------------------------------------------------------------------------
| 管理者（admin）
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    Route::get('login', [AuthController::class, 'showLogin'])
        ->name('admin.login');

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

        Route::post('logout', [AuthController::class, 'logout'])
            ->name('admin.logout');

        Route::get('applications', [AdminApplicationController::class, 'index'])
            ->name('admin.applications');

        Route::get('applications/{id}', [AdminApplicationController::class, 'show'])
            ->name('admin.applications.show');

        Route::post('applications/approve/{id}', [AdminApplicationController::class, 'approve'])
            ->name('admin.applications.approve');

        Route::post('applications/reject/{id}', [AdminApplicationController::class, 'reject'])
            ->name('admin.applications.reject');

        Route::post('applications/pending/{id}', [AdminApplicationController::class, 'pending'])
            ->name('admin.applications.pending');

        Route::resource('company-dashboard-notices', AdminCompanyDashboardNoticeController::class)
            ->names('admin.company-dashboard-notices');

        Route::get('/companies', [CompanyController::class, 'index'])->name('admin.company.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('admin.company.create');
        Route::post('/companies/store', [CompanyController::class, 'store'])->name('admin.company.store');

        Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('admin.company.edit');
        Route::post('/companies/{id}/update', [CompanyController::class, 'update'])->name('admin.company.update');

        Route::post('/companies/bulk-edit', [CompanyController::class, 'bulkEdit'])->name('admin.company.bulk-edit');
        Route::post('/companies/bulk-update', [CompanyController::class, 'bulkUpdate'])->name('admin.company.bulk-update');

        Route::post('/companies/{id}/toggle', [CompanyController::class, 'toggle'])->name('admin.company.toggle');
    });
});

/*
|--------------------------------------------------------------------------
| 企業（company）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:company'])->prefix('company')->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('company.billing.index');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('company.billing.checkout');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('company.billing.success');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('company.billing.portal');
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

Route::prefix('company')->group(function () {

    Route::get('login', [CompanyAuth::class, 'showLogin'])
        ->name('company.login');

    Route::post('login', [CompanyAuth::class, 'login'])
        ->name('company.login.post');

    Route::middleware(['auth:company'])->group(function () {

        Route::post('logout', [CompanyAuth::class, 'logout'])
            ->name('company.logout');

        Route::get('password-change', [PasswordController::class, 'edit'])
            ->name('company.password.change');

        Route::post('password-change', [PasswordController::class, 'update'])
            ->name('company.password.change.update');

        Route::get('setup', [SetupController::class, 'index'])
            ->name('company.setup');

        Route::post('setup/complete', [SetupController::class, 'complete'])
            ->name('company.setup.complete');

        Route::middleware('company.init')->group(function () {

            Route::get('dashboard', [CompanyDash::class, 'index'])
                ->name('company.dashboard');

            Route::get('reserve', [ReservationController::class, 'calendar'])
                ->name('company.reserve');

            Route::post('reserve', [ReservationController::class, 'calendar']);

            Route::get('reserve/data', [ReservationController::class, 'calendarData'])
                ->name('company.reserve.data');

            Route::get('theme', [ThemeController::class, 'edit'])
                ->name('company.theme');

            Route::post('theme', [ThemeController::class, 'update'])
                ->name('company.theme.update');

            Route::get('logo', [LogoController::class, 'edit'])
                ->name('company.logo');

            Route::post('logo', [LogoController::class, 'update'])
                ->name('company.logo.update');

            Route::get('dashboard-notices/{dashboardNotice}', [DashboardNoticeController::class, 'show'])
                ->name('company.dashboard-notices.show');

            Route::get('company-info', [CompanyInfoController::class, 'edit'])
                ->name('company.info.edit');

            Route::post('company-info', [CompanyInfoController::class, 'update'])
                ->name('company.info.update');

            Route::get('staff', [StaffController::class, 'index'])
                ->name('company.staff.index');

            Route::get('staff/create', [StaffController::class, 'create'])
                ->name('company.staff.create');

            Route::post('staff', [StaffController::class, 'store'])
                ->name('company.staff.store');

            Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])
                ->name('company.staff.edit');

            Route::put('staff/{staff}', [StaffController::class, 'update'])
                ->name('company.staff.update');

            Route::delete('staff/{staff}', [StaffController::class, 'destroy'])
                ->name('company.staff.destroy');

            Route::post('staff/{id}/reset-password', [StaffController::class, 'resetPassword'])
                ->name('company.staff.reset-password');

            Route::get('vacation/apply', [VacationController::class, 'create']);
            Route::post('vacation/apply', [VacationController::class, 'store']);

            Route::get('vacation', [VacationController::class, 'index'])
                ->name('company.vacation.index');

            Route::get('vacation/create', [VacationController::class, 'create'])
                ->name('company.vacation.create');

            Route::post('vacation', [VacationController::class, 'store'])
                ->name('company.vacation.store');

            Route::post('vacation/{vacation}/approve', [VacationController::class, 'approve'])
                ->name('company.vacation.approve');

            Route::post('vacation/{vacation}/reject', [VacationController::class, 'reject'])
                ->name('company.vacation.reject');

            Route::post('vacation/{vacation}/cancel', [VacationController::class, 'cancel'])
                ->name('company.vacation.cancel');

            Route::delete('vacation/{vacation}', [VacationController::class, 'destroy'])
                ->name('company.vacation.destroy');

            Route::get('my-profile', [MyProfileController::class, 'edit'])
                ->name('company.my-profile');

            Route::post('my-profile', [MyProfileController::class, 'update'])
                ->name('company.my-profile.update');

            Route::post('reservation', [ReservationController::class, 'store'])
                ->name('company.reservation.store');

            Route::post('reservation/{id}/cancel', [ReservationController::class, 'cancel'])
                ->name('company.reservation.cancel');

            Route::delete('reservation/{id}', [ReservationController::class, 'destroy'])
                ->name('company.reservation.destroy');

            Route::get('calendar', [CalendarController::class, 'index'])
                ->name('company.calendar.index');

            Route::post('calendar/toggle', [CalendarController::class, 'toggle'])
                ->name('company.calendar.toggle');

            Route::post('calendar/update-time', [CalendarController::class, 'updateTime'])
                ->name('company.calendar.updateTime');

            Route::post('calendar/delete-time', [CalendarController::class, 'deleteTime'])
                ->name('company.calendar.deleteTime');

            Route::get('calendar/year', [CalendarController::class, 'year'])
                ->name('company.calendar.year');

            Route::post('calendar/bulk-year-weekday', [CalendarController::class, 'bulkYearWeekday'])
                ->name('company.calendar.bulkYearWeekday');

            Route::post('calendar/bulk-weekday', [CalendarController::class, 'bulkWeekday'])
                ->name('company.calendar.bulkWeekday');

            Route::post('calendar/bulk-year-open-weekday', [CalendarController::class, 'bulkYearOpenWeekday'])
                ->name('company.calendar.bulkYearOpenWeekday');

            Route::get('calendar/available-staff', [ReservationController::class, 'availableStaff'])
                ->name('company.calendar.availableStaff');

            Route::get('calendar/staff-menus', [ReservationController::class, 'staffMenus'])
                ->name('company.calendar.staff-menus');

            Route::get('menu', [MenuController::class, 'index'])
                ->name('company.menu.index');

            Route::get('menu/create', [MenuController::class, 'create'])
                ->name('company.menu.create');

            Route::post('menu', [MenuController::class, 'store'])
                ->name('company.menu.store');

            Route::get('menu/settings', [MenuSettingController::class, 'index'])
                ->name('company.menu.settings');

            Route::post('menu/category', [MenuSettingController::class, 'storeCategory'])
                ->name('company.menu.category.store');

            Route::post('menu/tag', [MenuSettingController::class, 'storeTag'])
                ->name('company.menu.tag.store');

            Route::delete('menu/category/{id}', [MenuSettingController::class, 'deleteCategory'])
                ->name('company.menu.category.delete');

            Route::delete('menu/tag/{id}', [MenuSettingController::class, 'deleteTag'])
                ->name('company.menu.tag.delete');

            Route::get('menu/{menu}/edit', [MenuController::class, 'edit'])
                ->name('company.menu.edit');

            Route::put('menu/{menu}', [MenuController::class, 'update'])
                ->name('company.menu.update');

            Route::delete('menu/{menu}', [MenuController::class, 'destroy'])
                ->name('company.menu.destroy');

            Route::get('menu-staff', [MenuStaffController::class, 'index'])
                ->name('company.menu-staff.index');

            Route::post('menu-staff', [MenuStaffController::class, 'update'])
                ->name('company.menu-staff.update');

            Route::post('shift-patterns/order', [ShiftPatternController::class, 'updateOrder'])
                ->name('company.shift-patterns.order');

            Route::get('shift-patterns', [ShiftPatternController::class, 'index'])
                ->name('company.shift-patterns');

            Route::post('shift-patterns/store', [ShiftPatternController::class, 'store'])
                ->name('company.shift-patterns.store');

            Route::get('shift-patterns/delete/{id}', [ShiftPatternController::class, 'delete'])
                ->name('company.shift-patterns.delete');

            Route::get('staff-default-shifts', [StaffDefaultShiftController::class, 'index'])
                ->name('company.staff-default-shifts');

            Route::post('staff-default-shifts', [StaffDefaultShiftController::class, 'update'])
                ->name('company.staff-default-shifts.update');

            Route::get('staff-shifts', [StaffShiftController::class, 'index'])
                ->name('company.staff-shifts');

            Route::post('staff-shifts/generate', [StaffShiftController::class, 'generate'])
                ->name('company.staff-shifts.generate');

            Route::post('staff-shifts/update', [StaffShiftController::class, 'update'])
                ->name('company.staff-shifts.update');

            Route::post('staff-shifts/copy', [StaffShiftController::class, 'copy'])
                ->name('company.staff-shifts.copy');

			Route::get('staff-shifts/view', [StaffShiftController::class, 'view'])
			    ->name('company.staff-shifts.view');

			Route::get('staff-shifts/pdf', [StaffShiftController::class, 'pdf'])
			    ->name('company.staff-shifts.pdf');

            Route::get('customers', [CustomerController::class, 'index'])
                ->name('company.customers');

            Route::get('customers/{id}', [CustomerController::class, 'show'])
                ->name('company.customers.show');

            Route::post('customers/{id}/note', [CustomerController::class, 'note'])
                ->name('company.customers.note');

            Route::post('customers/{id}/photo', [CustomerController::class, 'photo'])
                ->name('company.customers.photo');

            Route::delete('customers/note/{id}', [CustomerController::class, 'deleteNote'])
                ->name('company.customers.note.delete');

            Route::delete('customers/photo/{id}', [CustomerController::class, 'deletePhoto'])
                ->name('company.customers.photo.delete');

            Route::resource('notices', NoticeController::class)
                ->names('company.notices');

            Route::get('/dashboard-settings', [DashboardSettingController::class, 'index'])
                ->name('company.dashboard-settings.index');

            Route::post('/dashboard-settings', [DashboardSettingController::class, 'update'])
                ->name('company.dashboard-settings.update');

            Route::get('reviews', [CompanyReviewController::class, 'index'])
                ->name('company.reviews.index');

            Route::get('reviews/{review}', [CompanyReviewController::class, 'show'])
                ->name('company.reviews.show');

            Route::post('reviews/{review}/approve', [CompanyReviewController::class, 'approve'])
                ->name('company.reviews.approve');

            Route::post('reviews/{review}/reject', [CompanyReviewController::class, 'reject'])
                ->name('company.reviews.reject');

            Route::post('reviews/{review}/reply', [CompanyReviewController::class, 'reply'])
                ->name('company.reviews.reply');

            Route::get('reservations', [\App\Http\Controllers\Company\ReservationController::class, 'index'])
                ->name('company.reservations.index');

            Route::post('reservations/{id}/cancel', [\App\Http\Controllers\Company\ReservationController::class, 'cancelFromList'])
                ->name('company.reservations.cancel');

            Route::get('/reservation-change-notices', [ReservationChangeNoticeController::class, 'index'])
                ->name('company.reservation_change_notices.index');

            Route::get('/reservation-change-notices/{notice}', [ReservationChangeNoticeController::class, 'show'])
                ->name('company.reservation_change_notices.show');

            Route::post('/reservation-change-notices/create-from-closed-date', [ReservationChangeNoticeController::class, 'createFromClosedDate'])
                ->name('company.reservation_change_notices.create_from_closed_date');

            Route::post('/reservation-change-notices/{notice}/send-mails', [ReservationChangeNoticeController::class, 'sendMails'])
                ->name('company.reservation_change_notices.send_mails');

            Route::post('/reservation-change-notices/items/{item}/phone-confirmed', [ReservationChangeNoticeController::class, 'markPhoneConfirmed'])
                ->name('company.reservation_change_notices.items.phone_confirmed');

            Route::post('/reservation-change-notices/items/{item}/update-note', [ReservationChangeNoticeController::class, 'updateNote'])
                ->name('company.reservation_change_notices.items.update_note');

            Route::get('/calendar/assignment-candidates', [\App\Http\Controllers\Company\ReservationController::class, 'assignmentCandidates'])
                ->name('company.calendar.assignment-candidates');
        });
    });
});

Route::get('/company/staff/list', function () {
    $company = auth()->guard('company')->user()->company;

    return $company->staff()
        ->where('is_reservable', true)
        ->orderBy('priority_order')
        ->get(['id', 'name']);
})->middleware('auth:company');

/*
|--------------------------------------------------------------------------
| 公開予約
|--------------------------------------------------------------------------
|
| /r/{company_code} 配下の公開予約画面
|
*/

Route::prefix('r/{company_code}')
    ->middleware(['company.code'])
    ->group(function () {
        Route::get('/', [ReserveController::class, 'index'])
            ->name('reserve.index');

        Route::get('/line/redirect', [ReserveController::class, 'lineRedirect'])
            ->name('reserve.line.redirect');

        Route::get('/line/callback', [ReserveController::class, 'lineCallback'])
            ->name('reserve.line.callback');

        Route::get('/line/logout', [ReserveController::class, 'lineLogout'])
            ->name('reserve.line.logout');

        Route::get('/available-staff', [ReserveController::class, 'availableStaff'])
            ->name('reserve.available_staff');

        Route::get('/slots', [ReserveController::class, 'slots'])
            ->name('reserve.slots');

        Route::match(['get', 'post'], '/confirm', [ReserveController::class, 'confirm'])
            ->name('reserve.confirm');

        Route::post('/store', [ReserveController::class, 'store'])
            ->name('reserve.store');

        Route::get('/complete', [ReserveController::class, 'complete'])
            ->name('reserve.complete');

        Route::get('/notice/{id}', [ReserveController::class, 'noticeShow'])
            ->name('reserve.notice.show');
    });


Route::get('/line/callback', [ReserveController::class, 'lineCallback'])
    ->name('reserve.line.callback');

Route::get('/review/{token}', [ReviewController::class, 'create'])
    ->name('reviews.create');

Route::post('/review/{token}', [ReviewController::class, 'store'])
    ->name('reviews.store');

Route::get('/review/{token}/complete', [ReviewController::class, 'complete'])
    ->name('reviews.complete');

Route::get('/cancel/{token}', [ReserveController::class, 'cancel']);

Route::get('/cancel/{token}', [ReserveCancelController::class, 'show'])->name('reserve.cancel.show');
Route::post('/cancel/{token}', [ReserveCancelController::class, 'cancel'])->name('reserve.cancel.execute');
Route::get('/cancel/{token}/complete', [ReserveCancelController::class, 'complete'])->name('reserve.cancel.complete');

Route::get('/notice-response/{token}', [ReservationNoticeResponseController::class, 'show'])
    ->name('reservation.notice.response.show');

Route::post('/notice-response/{token}/confirm', [ReservationNoticeResponseController::class, 'confirm'])
    ->name('reservation.notice.response.confirm');

/*
|--------------------------------------------------------------------------
| 問診
|--------------------------------------------------------------------------
*/

Route::get('company/questionnaire/{reservation}', [QuestionnaireController::class, 'create'])
    ->name('company.questionnaire');

Route::post('company/questionnaire/{reservation}', [QuestionnaireController::class, 'store']);