<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\AdminApplicationController;

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
use App\Http\Controllers\CompanyApplicationController;
use App\Http\Controllers\Company\CompanyController as CompanyInfoController;
use App\Http\Controllers\Company\MyProfileController;
/*
|--------------------------------------------------------------------------
| 共通
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));

Route::get('apply', [CompanyApplicationController::class, 'create']);
Route::post('apply', [CompanyApplicationController::class, 'store']);


/*
|--------------------------------------------------------------------------
| 管理者（admin）
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // ログイン
    Route::get('login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

        Route::post('logout', [AuthController::class, 'logout'])
            ->name('admin.logout');

        // 企業申請管理
        Route::get('applications', [AdminApplicationController::class, 'index'])
            ->name('admin.applications');

        Route::post('applications/approve/{id}', [AdminApplicationController::class, 'approve'])
            ->name('admin.applications.approve');

        Route::post('applications/reject/{id}', [AdminApplicationController::class, 'reject'])
            ->name('admin.applications.reject');

        // 企業管理
        Route::get('company', [CompanyController::class, 'index'])
            ->name('admin.company.index');

        Route::get('company/create', [CompanyController::class, 'create'])
            ->name('admin.company.create');

        Route::post('company/store', [CompanyController::class, 'store'])
            ->name('admin.company.store');

        Route::post('company/toggle/{id}', [CompanyController::class, 'toggle'])
            ->name('admin.company.toggle');
    });
});


/*
|--------------------------------------------------------------------------
| 企業（company）
|--------------------------------------------------------------------------
*/

Route::prefix('company')->group(function () {

    // ログイン
    Route::get('login', [CompanyAuth::class, 'showLogin'])
        ->name('company.login');

    Route::post('login', [CompanyAuth::class, 'login']);

    Route::middleware(['auth:company'])->group(function () {

        Route::post('logout', [CompanyAuth::class, 'logout'])
            ->name('company.logout');

        Route::middleware('company.init')->group(function () {

            Route::get('dashboard', [CompanyDash::class, 'index'])
                ->name('company.dashboard');

            Route::get('setup', [SetupController::class, 'index'])
                ->name('company.setup');

            Route::post('setup', [SetupController::class, 'store']);

            Route::get('password-change', [PasswordController::class,'edit'])
                ->name('company.password.change');

            Route::post('password-change', [PasswordController::class,'update']);

            Route::get('calendar', [ReservationController::class,'calendar'])
                ->name('company.calendar');

            Route::post('calendar', [ReservationController::class,'calendar'])
                ->name('company.calendar');

            Route::get('calendar/data', [ReservationController::class,'calendarData'])
                ->name('company.calendar.data');

            Route::get('theme', [ThemeController::class, 'edit'])
                ->name('company.theme');

            Route::post('theme', [ThemeController::class, 'update']);

            Route::get('logo', [LogoController::class,'edit'])
                ->name('company.logo');

            Route::post('logo', [LogoController::class,'update']);


		// =====================
		// 企業情報編集
		// =====================

		Route::get('company-info', [CompanyInfoController::class,'edit'])
		    ->name('company.info.edit');
		Route::post('company-info', [CompanyInfoController::class,'edit'])
		    ->name('company.info.edit');

		Route::post('company-info', [CompanyInfoController::class,'update'])
		    ->name('company.info.update');

		// =====================
		// 担当者管理
		// =====================

		Route::get('staff', [StaffController::class,'index'])
		    ->name('company.staff.index');

		Route::post('staff', [StaffController::class,'index'])
		    ->name('company.staff.index');

		Route::get('staff/create', [StaffController::class,'create'])
		    ->name('company.staff.create');

		Route::post('staff', [StaffController::class,'store'])
		    ->name('company.staff.store');

		Route::get('staff/{staff}/edit', [StaffController::class,'edit'])
		    ->name('company.staff.edit');

		Route::put('staff/{staff}', [StaffController::class,'update'])
		    ->name('company.staff.update');

		Route::delete('staff/{staff}', [StaffController::class,'destroy'])
		    ->name('company.staff.destroy');

		Route::get('vacation/apply',[VacationController::class,'create']);
		Route::post('vacation/apply',[VacationController::class,'store']);

		Route::get('vacation', [VacationController::class,'index'])
		    ->name('company.vacation.index');

		Route::get('vacation/create', [VacationController::class,'create'])
		    ->name('company.vacation.create');

		Route::post('vacation', [VacationController::class,'store'])
		    ->name('company.vacation.store');

		Route::post('vacation/{vacation}/approve',
		    [VacationController::class,'approve'])
		    ->name('company.vacation.approve');

		Route::post('vacation/{vacation}/reject',
		    [VacationController::class,'reject'])
		    ->name('company.vacation.reject');

		Route::delete('vacation/{vacation}',
		    [VacationController::class,'destroy'])
		    ->name('company.vacation.destroy');

		Route::post('vacation/{vacation}/cancel',
		    [VacationController::class, 'cancel'])
		    ->name('company.vacation.cancel');

		Route::get('my-profile', [MyProfileController::class,'edit'])
		    ->name('company.my-profile');

		Route::post('my-profile', [MyProfileController::class,'update'])
		    ->name('company.my-profile.update');

		Route::post('staff/{id}/reset-password',
		    [StaffController::class,'resetPassword'])
		    ->name('company.staff.reset-password');

		//予約処理（企業ダッシュボードから）
		Route::post('reservation',
		    [ReservationController::class,'store'])
		    ->name('company.reservation.store');

		Route::post('reservation/{reservation}/cancel',
		    [ReservationController::class,'cancel'])
		    ->name('company.reservation.cancel');


        });
    });
});

//カレンダーの担当者選択時のAJAX処理
Route::get('/company/staff/list', function () {

    $company = auth()->guard('company')->user()->company;

    return $company->staff()
        ->where('is_reservable',true)
        ->orderBy('priority_order')
        ->get(['id','name']);

})->middleware('auth:company');
/*
|--------------------------------------------------------------------------
| 公開予約
|--------------------------------------------------------------------------
*/

Route::prefix('r/{company_code}')
    ->middleware(['company.code'])
    ->group(function () {

        Route::get('/', fn() => view('reserve.top'));
    });

Route::post('reservation', [ReservationController::class,'store']);


/*
|--------------------------------------------------------------------------
| 問診
|--------------------------------------------------------------------------
*/

Route::get('company/questionnaire/{reservation}',
    [QuestionnaireController::class,'create'])
    ->name('company.questionnaire');

Route::post('company/questionnaire/{reservation}',
    [QuestionnaireController::class,'store']);