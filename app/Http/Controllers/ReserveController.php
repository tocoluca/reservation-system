<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationMenu;
use App\Models\ReservationDetail;
use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\Vacation;
use App\Models\StaffShift;
use App\Models\ShiftPattern;
use App\Models\Customer;
use App\Models\Notice;
use App\Models\Review;
use App\Models\StylePost;
use App\Mail\ReservationCompleteMail;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;

class ReserveController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 予約可能期間
    |--------------------------------------------------------------------------
    */

    private function getReservationLimits($company)
    {
        return [
            'start' => now()->addDays($company->reservation_open_days ?? 0)->startOfDay(),
            'end'   => now()->addMonths($company->reservation_month_limit ?? 3)->endOfMonth(),
            'close' => now()->addHours($company->reservation_close_hours ?? 1),
        ];
    }

    private function perStaffSimultaneousLimit($company): int
    {
        return max(1, (int) ($company->max_simultaneous_reservations ?? 1));
    }

    private function isLineLoginEnabled($company)
    {
        return (bool) $company->line_login_enabled
            && !empty($company->line_channel_id)
            && !empty($company->line_channel_secret);
    }

    private function setupLineConfig($company): void
    {
        abort_unless($this->isLineLoginEnabled($company), 404);
/*TEST
		dd(url('/line/callback'));
		Log::debug($company->line_channel_id);
		Log::debug($company->line_channel_secret);
		Log::debug(url('/line/callback'));
*/

        config([
            'services.line.client_id' => $company->line_channel_id,
            'services.line.client_secret' => $company->line_channel_secret,
            'services.line.redirect' => url('/line/callback'),
        ]);
    }

    private function getLineProfileFromSession($company): ?array
    {
        if ((int) session('reserve_line_company_id') !== (int) $company->id) {
            return null;
        }

        $profile = session('reserve_line_profile');

        return is_array($profile) ? $profile : null;
    }

    private function getLineCustomerFromSession($company): ?Customer
    {
        if ((int) session('reserve_line_company_id') !== (int) $company->id) {
            return null;
        }

        $customerId = session('reserve_line_customer_id');

        if (!$customerId) {
            return null;
        }

        return Customer::where('company_id', $company->id)
            ->where('id', $customerId)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | 勤務判定関連
    |--------------------------------------------------------------------------
    */

    private function calculateRequestedEndAt($company, Collection $menus, Carbon $startAt): Carbon
    {
        $cursor = $startAt->copy();

        foreach ($menus as $menu) {
            $cursor->addMinutes($this->resolveMenuDuration($company, $menu));
        }

        return $cursor;
    }

    private function hasApprovedVacationInWindow(int $staffId, Carbon $startAt, Carbon $endAt): bool
    {
        return Vacation::query()
            ->where('staff_id', $staffId)
            ->where('status', 'approved')
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();
    }

    private function isStaffWorkingOnWindow($company, int $staffId, Carbon $startAt, Carbon $endAt): bool
    {
        $date = $startAt->copy()->format('Y-m-d');

        $shift = StaffShift::with('pattern')
            ->where('staff_id', $staffId)
            ->whereDate('date', $date)
            ->first();

        if (!$shift || !(bool) $shift->is_work) {
            return false;
        }

        if (empty($shift->shift_pattern_id) || !$shift->pattern) {
            return false;
        }

        $shiftStart = Carbon::parse($date . ' ' . $shift->pattern->start_time);
        $shiftEnd   = Carbon::parse($date . ' ' . $shift->pattern->end_time);

        if ($startAt->lt($shiftStart) || $endAt->gt($shiftEnd)) {
            return false;
        }

        return true;
    }

    private function isStaffSelectableForPublic($company, int $staffId, Carbon $startAt, Carbon $endAt): bool
    {
        if ($this->hasApprovedVacationInWindow($staffId, $startAt, $endAt)) {
            return false;
        }

        return $this->isStaffWorkingOnWindow($company, $staffId, $startAt, $endAt);
    }

    private function getPublicSelectableStaff($company, Collection $menus, Carbon $startAt)
    {
        $endAt = $this->calculateRequestedEndAt($company, $menus, $startAt);
        $menuIds = $menus->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        return Staff::query()
            ->where('company_id', $company->id)
            ->where('is_reservable', 1)
            ->when(!empty($menuIds), function ($query) use ($menuIds) {
                $query->whereHas('menus', function ($q) use ($menuIds) {
                    $q->whereIn('menus.id', $menuIds);
                });
            })
            ->orderBy('priority_order')
            ->orderBy('id')
            ->get()
            ->filter(function ($staff) use ($company, $startAt, $endAt) {
                return $this->isStaffSelectableForPublic($company, (int) $staff->id, $startAt, $endAt);
            })
            ->values()
            ->map(function ($s) {
                $s->image_url = $s->image_path
                    ? asset($s->image_path)
                    : asset('logos/logo.png');

                return $s;
            });
    }

    private function getPublicSelectableStaffForDate($company, Collection $menus, Carbon $date)
    {
        $menuIds = $menus->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $targetDate = $date->copy()->toDateString();

        return Staff::query()
            ->where('company_id', $company->id)
            ->where('is_reservable', 1)
            ->when(!empty($menuIds), function ($query) use ($menuIds) {
                $query->whereHas('menus', function ($q) use ($menuIds) {
                    $q->whereIn('menus.id', $menuIds);
                });
            })
            ->orderBy('priority_order')
            ->orderBy('id')
            ->get()
            ->filter(function ($staff) use ($targetDate) {
                $shift = StaffShift::with('pattern')
                    ->where('staff_id', $staff->id)
                    ->whereDate('date', $targetDate)
                    ->first();

                if (!$shift || !(bool) $shift->is_work || empty($shift->shift_pattern_id) || !$shift->pattern) {
                    return false;
                }

                $dayStart = Carbon::parse($targetDate . ' 00:00:00');
                $dayEnd   = Carbon::parse($targetDate . ' 23:59:59');

                $hasFullDayVacation = Vacation::query()
                    ->where('staff_id', $staff->id)
                    ->where('status', 'approved')
                    ->where('start_at', '<', $dayEnd)
                    ->where('end_at', '>', $dayStart)
                    ->where('is_full_day', 1)
                    ->exists();

                return !$hasFullDayVacation;
            })
            ->values()
            ->map(function ($s) {
                $s->image_url = $s->image_path
                    ? asset($s->image_path)
                    : asset('logos/logo.png');

                return $s;
            });
    }

    private function canBuildReservationAt($company, Collection $menus, Carbon $startAt, ?int $selectedStaffId = null): bool
    {
        try {
            if ($company->prefer_less_capable_staff_for_menu_assignment) {
                $this->buildReservationDetailsWithPriorityPolicy($company, $menus, $startAt);
            } else {
                $this->buildReservationDetailsNormal($company, $selectedStaffId, $menus, $startAt);
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LINEログイン
    |--------------------------------------------------------------------------
    */

    public function lineRedirect(Request $request, $company_code)
    {
        $company = Company::where('company_code', $company_code)
            ->where('is_active', 1)
            ->firstOrFail();

        $this->setupLineConfig($company);

        session([
            'line_login_company_code'   => $company_code,
            'reserve_line_company_code' => $company_code,
        ]);

        return Socialite::driver('line')->redirect();
    }

    public function lineCallback(Request $request)
    {
        $company_code = session('line_login_company_code');

        if (!$company_code) {
            abort(404, 'company_code がセッションにありません。');
        }

        $company = Company::where('company_code', $company_code)
            ->where('is_active', 1)
            ->firstOrFail();

        $this->setupLineConfig($company);

        try {
            $lineUser = Socialite::driver('line')->user();
        } catch (\Throwable $e) {
            return redirect('/r/' . $company_code)
                ->with('error', 'LINEログインに失敗しました。時間をおいてもう一度お試しください。');
        }

        $linkedCustomer = Customer::where('company_id', $company->id)
            ->where('line_user_id', $lineUser->getId())
            ->first();

		if ($linkedCustomer) {
		    $linkedCustomer->line_name = $lineUser->getName() ?: $linkedCustomer->line_name;
		    $linkedCustomer->line_picture_url = $lineUser->getAvatar() ?: $linkedCustomer->line_picture_url;
		    $linkedCustomer->line_linked_at = $linkedCustomer->line_linked_at ?: now();
		    $linkedCustomer->line_notifications_enabled = true;
		    $linkedCustomer->save();
		}

        session([
            'reserve_line_company_id'   => $company->id,
            'reserve_line_company_code' => $company_code,
            'reserve_line_customer_id'  => $linkedCustomer?->id,
            'reserve_line_profile'      => [
                'line_user_id' => $lineUser->getId(),
                'name'         => $lineUser->getName(),
                'email'        => $lineUser->getEmail(),
                'avatar'       => $lineUser->getAvatar(),
            ],
        ]);

        session()->forget('line_login_company_code');

        return redirect('/r/' . $company_code)->with(
            'success',
            $linkedCustomer
                ? 'LINEでログインしました。前回情報を利用できます。'
                : 'LINEでログインしました。ご予約時の入力がかんたんになります。'
        );
    }

    public function lineLogout($company_code)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        if ((int) session('reserve_line_company_id') === (int) $company->id) {
            session()->forget([
                'reserve_line_company_id',
                'reserve_line_company_code',
                'reserve_line_customer_id',
                'reserve_line_profile',
            ]);
        }

        return redirect('/r/' . $company_code)
            ->with('success', 'LINEログインを解除しました。');
    }

	private function sendReservationCompleteLine(Company $company, Reservation $reservation): void
	{
	    $customer = $reservation->customer;

	    if (
	        !$customer ||
	        empty($customer->line_user_id) ||
	        !(bool) ($customer->line_notifications_enabled ?? true)
	    ) {
	        return;
	    }

	    $staffName = $reservation->staff->name ?? '担当未定';
	    $dateText = Carbon::parse($reservation->start_at)->format('Y年n月j日 H:i');

	    $menus = $reservation->menus->pluck('name')->filter()->implode('、');
	    if ($menus === '') {
	        $menus = 'ご予約メニュー';
	    }

	    $text = "【{$company->name}】ご予約ありがとうございます。\n"
	        . "日時：{$dateText}\n"
	        . "担当：{$staffName}\n"
	        . "内容：{$menus}\n";

	    if (!empty($reservation->cancel_token)) {
	        $text .= "キャンセルはこちら\n" . url('/cancel/' . $reservation->cancel_token);
	    }

	    app(LineMessagingService::class)->pushText($company, $customer->line_user_id, $text);

	    $customer->forceFill([
	        'last_line_sent_at' => now(),
	    ])->save();
	}

    /*
    |--------------------------------------------------------------------------
    | 予約トップ
    |--------------------------------------------------------------------------
    */

    public function index(Request $request, $company_code)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        $sessionKey = 'reserve_confirm.' . $company->id;
        $confirmData = session($sessionKey, []);

        $notices = Notice::where('company_id', $company->id)
            ->visible()
            ->sorted()
            ->get();

        $menus = Menu::with(['tags', 'category'])
            ->where('company_id', $company->id)
            ->where('is_active', 1)
            ->orderByDesc('is_popular')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($menu) => $menu->category->name ?? 'その他');

        $flatMenus = $menus->flatten(1);

        $requestedStartAt = $request->query('start_at', $confirmData['start_at'] ?? null);

        $requestedStaffId = $request->filled('staff_id')
            ? (int) $request->query('staff_id')
            : ($confirmData['staff_id'] ?? null);

        $requestedMenuIds = collect($request->query('menu_ids', $confirmData['menu_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $requestedDate = null;
        if (!empty($requestedStartAt)) {
            try {
                $requestedDate = Carbon::parse($requestedStartAt)->format('Y-m-d');
            } catch (\Throwable $e) {
                $requestedDate = null;
            }
        }

        $staff = collect();

        if ($requestedDate && $requestedMenuIds->isNotEmpty()) {
            $selectedMenus = $flatMenus
                ->whereIn('id', $requestedMenuIds->all())
                ->sortBy(fn ($menu) => array_search($menu->id, $requestedMenuIds->all()))
                ->values();

            if ($selectedMenus->isNotEmpty()) {
                $startAt = Carbon::parse($requestedDate);
                $staff = $this->getPublicSelectableStaffForDate($company, $selectedMenus, $startAt);
            }
        }

        if ($staff->isEmpty()) {
            $staff = Staff::where('company_id', $company->id)
                ->where('is_reservable', 1)
                ->orderBy('priority_order')
                ->get()
                ->map(function ($s) {
                    $s->image_url = $s->image_path
                        ? asset($s->image_path)
                        : asset('logos/logo.png');

                    return $s;
                });
        }

        $publicReviews = Review::where('company_id', $company->id)
            ->where('is_public', true)
            ->where('status', 'approved')
            ->latest()
            ->take(5)
            ->get();

        $reviewCount = Review::where('company_id', $company->id)
            ->where('is_public', true)
            ->where('status', 'approved')
            ->count();

        $averageRating = Review::where('company_id', $company->id)
            ->where('is_public', true)
            ->where('status', 'approved')
            ->avg('rating');

        $lineProfile = $this->getLineProfileFromSession($company);
        $lineCustomer = $this->getLineCustomerFromSession($company);

		$styles = StylePost::where('company_id', $company->id)
		    ->where('is_public', true)
		    ->orderBy('sort_order')
		    ->orderByDesc('id')
		    ->take(3)
		    ->get()
		    ->map(function ($style) {
		        $style->image_url = $style->image_path ? asset($style->image_path) : null;
		        return $style;
		    });

        return view('reserve.index', [
            'company'          => $company,
            'menus'            => $menus,
            'staff'            => $staff,
            'notices'          => $notices,
            'publicReviews'    => $publicReviews,
            'reviewCount'      => $reviewCount,
            'averageRating'    => $averageRating,
            'lineLoginEnabled' => $this->isLineLoginEnabled($company),
            'lineProfile'      => $lineProfile,
            'lineCustomer'     => $lineCustomer,
            'step'             => 1,
            'prefillMenuIds'   => $requestedMenuIds->all(),
            'prefillStaffId'   => $requestedStaffId,
            'prefillStartAt'   => $requestedStartAt,
			'styles'		   => $styles,
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | 公開側スタッフ取得
    |--------------------------------------------------------------------------
    */

    public function availableStaff(Request $request, $company_code)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        $menuIds = collect($request->query('menu_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $date = $request->query('date');
        $time = $request->query('time');

        if ($menuIds->isEmpty() || !$date) {
            return response()->json([
                'ok' => true,
                'staff' => [],
            ]);
        }

        $menus = Menu::where('company_id', $company->id)
            ->whereIn('id', $menuIds->all())
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn ($menu) => array_search($menu->id, $menuIds->all()))
            ->values();

        if ($menus->isEmpty()) {
            return response()->json([
                'ok' => true,
                'staff' => [],
            ]);
        }

        if ($time) {
            $startAt = Carbon::parse(trim($date . ' ' . $time));
            $staff = $this->getPublicSelectableStaff($company, $menus, $startAt);
        } else {
            $staff = $this->getPublicSelectableStaffForDate(
                $company,
                $menus,
                Carbon::parse($date)
            );
        }

        $staff = $staff->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nomination_fee' => (int) ($s->nomination_fee ?? 0),
                'comment' => $s->comment,
                'image_url' => $s->image_path
                    ? asset($s->image_path)
                    : asset('logos/logo.png'),
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'staff' => $staff,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | confirm
    |--------------------------------------------------------------------------
    */

    public function confirm(Request $request, $company_code)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        $sessionKey = 'reserve_confirm.' . $company->id;

        if ($request->isMethod('post')) {
            session([
                $sessionKey => [
                    'start_at' => $request->input('start_at'),
                    'menu_ids' => collect($request->input('menu_ids', []))
                        ->map(fn ($id) => (int) $id)
                        ->filter()
                        ->values()
                        ->all(),
                    'staff_id' => $request->filled('staff_id')
                        ? (int) $request->input('staff_id')
                        : null,
                ],
            ]);
        }

        $confirmData = session($sessionKey, []);

        $menuIds = collect($request->input('menu_ids', $confirmData['menu_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $startAtValue = $request->input('start_at', $confirmData['start_at'] ?? null);

        $staffId = $request->filled('staff_id')
            ? (int) $request->input('staff_id')
            : ($confirmData['staff_id'] ?? null);

        if ($menuIds->isEmpty() || empty($startAtValue)) {
            return redirect('/r/' . $company_code)->withErrors([
                'menu_ids' => 'メニューと日時を選択してください。',
            ])->withInput();
        }

        $menus = Menu::where('company_id', $company->id)
            ->whereIn('id', $menuIds->all())
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn ($menu) => array_search($menu->id, $menuIds->all()))
            ->values();

        if ($menus->isEmpty()) {
            return redirect('/r/' . $company_code)->withErrors([
                'menu_ids' => 'メニューを選択してください。',
            ])->withInput();
        }

        $startAt = Carbon::parse($startAtValue);
        $endAt = $this->calculateRequestedEndAt($company, $menus, $startAt);

        $staff = null;
        if (!empty($staffId)) {
            $staff = Staff::where('company_id', $company->id)
                ->where('id', $staffId)
                ->first();

            if (!$staff) {
                return redirect('/r/' . $company_code)->withErrors([
                    'staff_id' => '選択した担当者が見つかりません。',
                ])->withInput();
            }

            if (!$this->isStaffSelectableForPublic($company, (int) $staff->id, $startAt, $endAt)) {
                return redirect('/r/' . $company_code)->withErrors([
                    'staff_id' => '選択した担当者は、その日時では勤務対象外です。別の担当者または日時を選択してください。',
                ])->withInput();
            }
        }

        $lineProfile = $this->getLineProfileFromSession($company);
        $lineCustomer = $this->getLineCustomerFromSession($company);

        return view('reserve.confirm', [
            'company'      => $company,
            'menus'        => $menus,
            'staff'        => $staff,
            'start_at'     => $startAtValue,
            'lineProfile'  => $lineProfile,
            'lineCustomer' => $lineCustomer,
            'step'         => 2,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $company_code)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        $request->validate([
            'customer_name'  => ['required', 'max:255'],
            'customer_phone' => ['required', 'regex:/^[0-9\-]+$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'start_at'       => ['required', 'date'],
            'menu_ids'       => ['required', 'array', 'min:1'],
            'menu_ids.*'     => ['integer'],
        ], [
            'customer_name.required' => ':attributeを入力してください',
            'customer_phone.regex'   => ':attributeは半角数字とハイフンのみ入力できます',
        ], [
            'customer_name'  => 'お名前',
            'customer_phone' => '電話番号',
            'customer_email' => 'メールアドレス',
            'start_at'       => '予約日時',
            'menu_ids'       => 'メニュー',
        ]);

        $menus = Menu::where('company_id', $company->id)
            ->whereIn('id', $request->menu_ids ?? [])
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn ($menu) => array_search($menu->id, $request->menu_ids ?? []))
            ->values();

        if ($menus->isEmpty()) {
            return back()->with('error', 'メニューを選択してください');
        }

        $start = Carbon::parse($request->start_at);
        $limits = $this->getReservationLimits($company);

        if ($start < $limits['start']) {
            return back()->with('error', 'この日はまだ予約受付していません');
        }

        if ($start > $limits['end']) {
            return back()->with('error', '予約可能期間を超えています');
        }

        if ($start < $limits['close']) {
            return back()->with('error', '予約締切を過ぎています');
        }

        if ($request->filled('staff_id')) {
            $selectedStaff = Staff::where('company_id', $company->id)
                ->where('id', (int) $request->staff_id)
                ->first();

            if (!$selectedStaff) {
                return back()->withErrors([
                    'staff_id' => '選択した担当者が見つかりません。',
                ])->withInput();
            }

            $requestedEndAt = $this->calculateRequestedEndAt($company, $menus, $start);

            if (!$this->isStaffSelectableForPublic($company, (int) $selectedStaff->id, $start, $requestedEndAt)) {
                return back()->withErrors([
                    'staff_id' => '選択した担当者は、その日時では勤務対象外です。別の担当者または日時を選択してください。',
                ])->withInput();
            }
        }

        try {
            if ($company->prefer_less_capable_staff_for_menu_assignment) {
                [$detailPlans, $end, $totalPrice, $representativeStaffId] =
                    $this->buildReservationDetailsWithPriorityPolicy(
                        $company,
                        $menus,
                        $start
                    );
            } else {
                [$detailPlans, $end, $totalPrice, $representativeStaffId] =
                    $this->buildReservationDetailsNormal(
                        $company,
                        $request->staff_id ? (int) $request->staff_id : null,
                        $menus,
                        $start
                    );
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $lineProfile = $this->getLineProfileFromSession($company);

        $lineCustomer = null;
        if ($lineProfile && !empty($lineProfile['line_user_id'])) {
            $lineCustomer = Customer::where('company_id', $company->id)
                ->where('line_user_id', $lineProfile['line_user_id'])
                ->first();
        }

        DB::beginTransaction();

        try {
            if ($lineCustomer) {
                $customer = Customer::where('id', $lineCustomer->id)
                    ->lockForUpdate()
                    ->first();
            } else {
                $customer = Customer::where('company_id', $company->id)
                    ->where('phone', str_replace('-', '', $request->customer_phone))
                    ->lockForUpdate()
                    ->first();

                if (!$customer) {
                    $customer = Customer::create([
                        'company_id' => $company->id,
                        'phone'      => str_replace('-', '', $request->customer_phone),
                        'name'       => $request->customer_name,
                        'email'      => $request->customer_email,
                    ]);
                }
            }

            $customer->name = $request->customer_name;
            $customer->phone = str_replace('-', '', $request->customer_phone);

            if ($request->filled('customer_email')) {
                $customer->email = $request->customer_email;
            } elseif (!$customer->email && $lineProfile && !empty($lineProfile['email'])) {
                $customer->email = $lineProfile['email'];
            }

            if ($lineProfile && !empty($lineProfile['line_user_id'])) {
                $alreadyLinkedOther = Customer::where('company_id', $company->id)
                    ->where('line_user_id', $lineProfile['line_user_id'])
                    ->where('id', '!=', $customer->id)
                    ->exists();

                $canLinkThisCustomer = empty($customer->line_user_id)
                    || $customer->line_user_id === $lineProfile['line_user_id'];

				if (!$alreadyLinkedOther && $canLinkThisCustomer) {
				    $customer->line_user_id = $lineProfile['line_user_id'];
				    $customer->line_name = $lineProfile['name'] ?? $customer->line_name;
				    $customer->line_picture_url = $lineProfile['avatar'] ?? $customer->line_picture_url;
				    $customer->line_linked_at = $customer->line_linked_at ?: now();
				    $customer->line_notifications_enabled = true;

				    session([
				        'reserve_line_customer_id' => $customer->id,
				    ]);
				}
            }

            $customer->save();

            $representativeStaff = Staff::findOrFail($representativeStaffId);

            $reservation = Reservation::create([
                'company_id'      => $company->id,
                'customer_id'     => $customer->id,
                'staff_id'        => $representativeStaffId,
                'customer_name'   => $request->customer_name,
                'customer_phone'  => str_replace('-', '', $request->customer_phone),
                'customer_email'  => $request->customer_email ?: $customer->email,
                'start_at'        => $start,
                'end_at'          => $end,
                'price'           => $totalPrice,
                'nomination_fee'  => $request->filled('staff_id')
                    ? ((int) ($representativeStaff->nomination_fee ?? 0))
                    : 0,
                'total_price'     => $totalPrice + (
                    $request->filled('staff_id')
                        ? ((int) ($representativeStaff->nomination_fee ?? 0))
                        : 0
                ),
                'status'          => 'reserved',
                'cancel_token'    => Str::random(6),
                'review_token'    => Str::random(40),
            ]);

            foreach ($menus as $menu) {
                ReservationMenu::create([
                    'reservation_id' => $reservation->id,
                    'menu_id'        => $menu->id,
                    'price'          => $menu->price,
                    'duration'       => $menu->duration,
                ]);
            }

            foreach ($detailPlans as $index => $detail) {
                ReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'menu_id'        => $detail['menu_id'],
                    'staff_id'       => $detail['staff_id'],
                    'start_at'       => $detail['start_at'],
                    'end_at'         => $detail['end_at'],
                    'duration'       => $detail['duration'],
                    'price'          => $detail['price'],
                    'sort_order'     => $index + 1,
                ]);
            }

            $maxCycle = $menus->max('revisit_days');
            if ($maxCycle) {
                $customer->next_visit_at = Carbon::parse($reservation->start_at)->addDays($maxCycle);
                $customer->save();
            }

            DB::commit();

            if (!empty($reservation->customer_email)) {
                try {
                    Mail::to($reservation->customer_email)->send(
                        new ReservationCompleteMail($company, $reservation->load([
                            'staff',
                            'details.menu',
                            'details.staff',
                        ]))
                    );


					$reservation->load([
					    'customer',
					    'staff',
					    'menus',
					    'details.menu',
					    'details.staff',
					]);

					$this->sendReservationCompleteLine($company, $reservation);

                    Log::info('予約完了メール送信成功', [
                        'reservation_id' => $reservation->id,
                        'email' => $reservation->customer_email,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('予約完了メール送信失敗', [
                        'reservation_id' => $reservation->id,
                        'email' => $reservation->customer_email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            session()->forget('reserve_confirm.' . $company->id);

            return redirect("/r/" . $company_code . "/complete?reservation_id=" . $reservation->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('公開予約登録失敗', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', '予約登録に失敗しました。時間をおいて再度お試しください。');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | complete
    |--------------------------------------------------------------------------
    */

    public function complete($company_code, Request $request)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        $reservation = Reservation::where('id', $request->reservation_id)
            ->where('company_id', $company->id)
            ->with(['staff', 'details.menu', 'details.staff'])
            ->firstOrFail();

        $menus = ReservationMenu::where('reservation_id', $reservation->id)
            ->with('menu')
            ->get();

        $staff = $reservation->staff;

        return view('reserve.complete', [
            'company'     => $company,
            'reservation' => $reservation,
            'menus'       => $menus,
            'staff'       => $staff,
            'details'     => $reservation->details,
            'start_at'    => $reservation->start_at,
            'step'        => 3,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | cancel
    |--------------------------------------------------------------------------
    */

    public function cancel($token)
    {
        $reservation = Reservation::where('cancel_token', $token)
            ->firstOrFail();

        $reservation->status = 'cancelled';
        $reservation->save();

        return view('reserve.cancel_complete', [
            'reservation' => $reservation,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | スタッフ割当
    |--------------------------------------------------------------------------
    */

    private function buildReservationDetailsNormal($company, ?int $selectedStaffId, Collection $menus, Carbon $requestedStartAt): array
    {
        $cursor = $requestedStartAt->copy();
        $details = [];
        $totalPrice = 0;
        $representativeStaffId = $selectedStaffId;

        foreach ($menus as $menu) {
            $duration = $this->resolveMenuDuration($company, $menu);

            $assignedStaffId = $this->resolveStaffForMenuNormal(
                $company,
                $selectedStaffId,
                $menu,
                $cursor,
                $duration
            );

            if (!$assignedStaffId) {
                throw ValidationException::withMessages([
                    'menu_ids' => "「{$menu->name}」を担当できるスタッフを確保できませんでした。",
                ]);
            }

            if (!$representativeStaffId) {
                $representativeStaffId = $assignedStaffId;
            }

            $detailStart = $cursor->copy();
            $detailEnd = $cursor->copy()->addMinutes($duration);

            $details[] = [
                'menu_id'  => $menu->id,
                'staff_id' => $assignedStaffId,
                'start_at' => $detailStart,
                'end_at'   => $detailEnd,
                'duration' => $duration,
                'price'    => (int) $menu->price,
            ];

            $totalPrice += (int) $menu->price;
            $cursor = $detailEnd->copy();
        }

        return [$details, $cursor, $totalPrice, $representativeStaffId];
    }

    private function resolveStaffForMenuNormal($company, ?int $selectedStaffId, Menu $menu, Carbon $startAt, int $duration): ?int
    {
        $endAt = $startAt->copy()->addMinutes($duration);
        $candidateStaffIds = $this->getCandidateStaffIdsForMenu($company->id, $menu->id);

        if (empty($candidateStaffIds)) {
            return null;
        }

        if ($selectedStaffId && in_array($selectedStaffId, $candidateStaffIds, true)) {
            if ($this->isStaffAvailableForWindow($company, $selectedStaffId, $startAt, $endAt)) {
                return $selectedStaffId;
            }
        }

        foreach ($candidateStaffIds as $staffId) {
            if ($this->isStaffAvailableForWindow($company, $staffId, $startAt, $endAt)) {
                return $staffId;
            }
        }

        return null;
    }

    private function buildReservationDetailsWithPriorityPolicy($company, Collection $menus, Carbon $requestedStartAt): array
    {
        $menuCandidates = [];
        $slotMap = [];
        $cursor = $requestedStartAt->copy();

        foreach ($menus as $index => $menu) {
            $duration = $this->resolveMenuDuration($company, $menu);
            $candidateIds = $this->getCandidateStaffIdsForMenu($company->id, $menu->id);

            if (empty($candidateIds)) {
                throw ValidationException::withMessages([
                    'menu_ids' => "「{$menu->name}」を担当できるスタッフがいません。",
                ]);
            }

            $menuCandidates[$index] = $candidateIds;
            $slotMap[$index] = [
                'menu'     => $menu,
                'start_at' => $cursor->copy(),
                'end_at'   => $cursor->copy()->addMinutes($duration),
                'duration' => $duration,
            ];

            $cursor = $cursor->copy()->addMinutes($duration);
        }

        $capabilityCounts = $this->getStaffCapabilityCounts($company->id);
        $assignments = $this->generateAssignments($menuCandidates);
        $validAssignments = [];

        foreach ($assignments as $assignment) {
            $isValid = true;

            foreach ($assignment as $index => $staffId) {
                $slot = $slotMap[$index];

                if (!$this->isStaffAvailableForWindow(
                    $company,
                    $staffId,
                    $slot['start_at'],
                    $slot['end_at']
                )) {
                    $isValid = false;
                    break;
                }
            }

            if ($isValid) {
                $validAssignments[] = $assignment;
            }
        }

        if (empty($validAssignments)) {
            throw ValidationException::withMessages([
                'menu_ids' => '担当可能なスタッフを確保できませんでした。',
            ]);
        }

        usort($validAssignments, function ($a, $b) use ($capabilityCounts) {
            $sa = $this->scoreAssignment($a, $capabilityCounts);
            $sb = $this->scoreAssignment($b, $capabilityCounts);

            return [
                $sa['capability_sum'],
                $sa['generalist_usage_count'],
                $sa['max_staff_assignment_count'],
                $sa['staff_id_sequence'],
            ] <=> [
                $sb['capability_sum'],
                $sb['generalist_usage_count'],
                $sb['max_staff_assignment_count'],
                $sb['staff_id_sequence'],
            ];
        });

        $best = $validAssignments[0];

        $details = [];
        $totalPrice = 0;
        $representativeStaffId = null;

        foreach ($menus as $index => $menu) {
            $slot = $slotMap[$index];
            $assignedStaffId = $best[$index];

            if ($representativeStaffId === null) {
                $representativeStaffId = $assignedStaffId;
            }

            $details[] = [
                'menu_id'  => $menu->id,
                'staff_id' => $assignedStaffId,
                'start_at' => $slot['start_at'],
                'end_at'   => $slot['end_at'],
                'duration' => $slot['duration'],
                'price'    => (int) $menu->price,
            ];

            $totalPrice += (int) $menu->price;
        }

        return [$details, $cursor, $totalPrice, $representativeStaffId];
    }

    private function resolveMenuDuration($company, $menu): int
    {
        $duration = (int) $menu->duration;

        if ((bool) ($company->menu_time_priority_flag ?? false)) {
            return max(1, $duration > 0 ? $duration : (int) ($company->slot_minutes ?: 30));
        }

        return max(1, (int) ($company->slot_minutes ?: ($duration > 0 ? $duration : 30)));
    }

    private function getCandidateStaffIdsForMenu(int $companyId, int $menuId): array
    {
        return DB::table('menu_staff')
            ->join('staff', 'staff.id', '=', 'menu_staff.staff_id')
            ->where('menu_staff.menu_id', $menuId)
            ->where('staff.company_id', $companyId)
            ->where('staff.is_reservable', 1)
            ->orderBy('staff.priority_order')
            ->orderBy('staff.id')
            ->pluck('staff.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    private function getStaffCapabilityCounts(int $companyId): array
    {
        return DB::table('menu_staff')
            ->join('staff', 'staff.id', '=', 'menu_staff.staff_id')
            ->where('staff.company_id', $companyId)
            ->select('staff.id', DB::raw('COUNT(menu_staff.menu_id) as capability_count'))
            ->groupBy('staff.id')
            ->pluck('capability_count', 'staff.id')
            ->map(fn ($count) => (int) $count)
            ->toArray();
    }

    private function generateAssignments(array $menuCandidates, int $index = 0, array $current = []): array
    {
        $keys = array_keys($menuCandidates);

        if (!isset($keys[$index])) {
            return [$current];
        }

        $menuKey = $keys[$index];
        $results = [];

        foreach ($menuCandidates[$menuKey] as $staffId) {
            $next = $current;
            $next[$menuKey] = $staffId;

            $results = array_merge(
                $results,
                $this->generateAssignments($menuCandidates, $index + 1, $next)
            );
        }

        return $results;
    }

    private function scoreAssignment(array $assignment, array $capabilityCounts): array
    {
        $capabilitySum = 0;
        $generalistUsageCount = 0;
        $staffUsage = [];

        foreach ($assignment as $staffId) {
            $capabilityCount = $capabilityCounts[$staffId] ?? 999;

            $capabilitySum += $capabilityCount;
            $staffUsage[$staffId] = ($staffUsage[$staffId] ?? 0) + 1;

            if ($capabilityCount >= 3) {
                $generalistUsageCount++;
            }
        }

        $maxStaffAssignmentCount = empty($staffUsage) ? 0 : max($staffUsage);

        return [
            'capability_sum' => $capabilitySum,
            'generalist_usage_count' => $generalistUsageCount,
            'max_staff_assignment_count' => $maxStaffAssignmentCount,
            'staff_id_sequence' => implode('-', $assignment),
        ];
    }

    private function isStaffAvailableForWindow($company, int $staffId, Carbon $startAt, Carbon $endAt): bool
    {
        $vacation = Vacation::where('staff_id', $staffId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startAt, $endAt) {
                $q->where('start_at', '<', $endAt)
                  ->where('end_at', '>', $startAt);
            })
            ->exists();

        if ($vacation) {
            return false;
        }

        if (!$this->isStaffWorkingOnWindow($company, $staffId, $startAt, $endAt)) {
            return false;
        }

        $perStaffLimit = $this->perStaffSimultaneousLimit($company);

        $currentCount = ReservationDetail::query()
            ->where('staff_id', $staffId)
            ->whereHas('reservation', function ($q) use ($company) {
                $q->where('company_id', $company->id)
                  ->where('status', 'reserved');
            })
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->count();

        return $currentCount < $perStaffLimit;
    }

    /*
    |--------------------------------------------------------------------------
    | 空き時間
    |--------------------------------------------------------------------------
    */

    public function slots(Request $request, $company_code)
    {
        $company = Company::where('company_code', $company_code)->firstOrFail();

        $menuIds = collect($request->menu_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $menus = Menu::where('company_id', $company->id)
            ->whereIn('id', $menuIds)
            ->get()
            ->sortBy(fn ($menu) => array_search($menu->id, $menuIds))
            ->values();

        if ($menus->isEmpty()) {
            return response()->json([]);
        }

        $date = Carbon::parse($request->date)->startOfDay();
        $limits = $this->getReservationLimits($company);

        if ($date->copy()->endOfDay()->lt($limits['start'])) {
            return response()->json([]);
        }

        if ($date->copy()->startOfDay()->gt($limits['end'])) {
            return response()->json([]);
        }

        $openPatterns = $company->open_patterns ?? [];
        $patterns = $openPatterns[$date->dayOfWeek] ?? [];

        if (empty($patterns)) {
            return response()->json([]);
        }

        $selectedStaffId = $request->filled('staff_id') && $request->staff_id !== ''
            ? (int) $request->staff_id
            : null;

        $slots = [];
        $slotStep = (int) ($company->slot_minutes ?: 30);

        foreach ($patterns as $p) {
            if (empty($p['open']) || empty($p['close'])) {
                continue;
            }

            $open = Carbon::parse($date->format('Y-m-d') . ' ' . $p['open']);
            $close = Carbon::parse($date->format('Y-m-d') . ' ' . $p['close']);
            $time = $open->copy();

            while ($time < $close) {
                $start = $time->copy();

                $isReservable = $this->canBuildReservationAt(
                    $company,
                    $menus,
                    $start,
                    $selectedStaffId
                );

                if (!$isReservable) {
                    $time->addMinutes($slotStep);
                    continue;
                }

                $end = $this->calculateRequestedEndAt($company, $menus, $start);

                if ($end->gt($close)) {
                    $time->addMinutes($slotStep);
                    continue;
                }

                if ($start->lt($limits['close']) || $start->lt($limits['start']) || $start->gt($limits['end'])) {
                    $time->addMinutes($slotStep);
                    continue;
                }

                $slots[] = [
                    'time'      => $start->format('H:i'),
                    'remaining' => 1,
                    'total'     => 1,
                ];

                $time->addMinutes($slotStep);
            }
        }

        return response()->json($slots);
    }

    public function staffMenus(Request $request)
    {
        $company = auth()->guard('company')->user()->company;
        $staffId = $request->staff_id;

        $menus = Menu::where('company_id', $company->id)
            ->whereHas('staffs', function ($q) use ($staffId) {
                $q->where('staff_id', $staffId);
            })
            ->orderBy('sort_order')
            ->get();

        return response()->json($menus);
    }

    public function noticeShow($company_code, $id)
    {
        $company = Company::where('company_code', $company_code)
            ->firstOrFail();

        $notice = Notice::where('company_id', $company->id)
            ->visible()
            ->findOrFail($id);

        return view('reserve.notice_show', compact('notice', 'company'));
    }
}