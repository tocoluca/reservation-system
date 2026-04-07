<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\Menu;
use App\Models\CompanyDashboardNotice;
use App\Models\ShiftPattern;
use App\Models\StaffDefaultShift;
use App\Models\StaffShift;
use App\Models\CompanyBusinessCalendar;
use App\Models\CompanyDashboardPermission;
use App\Models\ReservationChangeNoticeItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $staff = auth()->guard('company')->user();
        $company = $staff->company;

        $rawDashboardPermissions = CompanyDashboardPermission::resolveForCompanyRole($company->id, $staff->role);
        $dashboardPermissions = $this->normalizeDashboardPermissions($rawDashboardPermissions, $staff->role);

        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        /* ===============================
           予約変更連絡サマリー
        =============================== */
        $changeNoticeSummary = ReservationChangeNoticeItem::query()
            ->where('company_id', $company->id)
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN response_status IN ('waiting', 'mail_sent', 'no_response') THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN contact_type = 'phone' AND response_status NOT IN ('closed', 'confirmed', 'phone_confirmed') THEN 1 ELSE 0 END) as phone_pending_count,
                SUM(CASE WHEN response_status IN ('closed', 'confirmed', 'phone_confirmed') THEN 1 ELSE 0 END) as confirmed_count
            ")
            ->first();

        $changeNoticePendingCount = (int) ($changeNoticeSummary->pending_count ?? 0);
        $changeNoticePhonePendingCount = (int) ($changeNoticeSummary->phone_pending_count ?? 0);
        $changeNoticeConfirmedCount = (int) ($changeNoticeSummary->confirmed_count ?? 0);
        $changeNoticeTotalCount = (int) ($changeNoticeSummary->total_count ?? 0);

        $hasChangeNoticeAlert = $changeNoticePendingCount > 0 || $changeNoticePhonePendingCount > 0;

        /* ===============================
           初期設定ガイド表示判定
        =============================== */
        $openPatterns = $company->open_patterns ?? [];
        $hasOpenPattern = false;

        if (is_array($openPatterns)) {
            foreach ($openPatterns as $weekdayPatterns) {
                if (!is_array($weekdayPatterns)) {
                    continue;
                }

                foreach ($weekdayPatterns as $pattern) {
                    if (
                        (!empty($pattern['open']) && !empty($pattern['close'])) ||
                        (!empty($pattern['open_time']) && !empty($pattern['close_time']))
                    ) {
                        $hasOpenPattern = true;
                        break 2;
                    }
                }
            }
        }

        $setupCompanyInfoDone =
            !empty($company->slot_minutes) &&
            $hasOpenPattern;

        $setupStaffDone = Staff::where('company_id', $company->id)->exists();
        $setupMenuDone = Menu::where('company_id', $company->id)->exists();

        $setupShiftPatternDone = ShiftPattern::where('company_id', $company->id)->exists();
        $setupStaffIds = Staff::where('company_id', $company->id)->pluck('id');

        $setupDefaultShiftDone = false;
        $setupMonthlyShiftDone = false;

        if ($setupStaffIds->isNotEmpty()) {
            $setupDefaultShiftDone = StaffDefaultShift::whereIn('staff_id', $setupStaffIds)
                ->where('is_work', 1)
                ->exists();

            $setupMonthlyShiftDone = StaffShift::whereIn('staff_id', $setupStaffIds)
                ->exists();
        }

        $setupShiftDone = $setupShiftPatternDone && ($setupDefaultShiftDone || $setupMonthlyShiftDone);
        $setupReserveDone = $setupCompanyInfoDone && $setupStaffDone && $setupMenuDone && $setupShiftDone;

        $setupStatusList = [
            ['label' => '担当者', 'done' => $setupStaffDone],
            ['label' => '企業情報', 'done' => $setupCompanyInfoDone],
            ['label' => 'メニュー', 'done' => $setupMenuDone],
            ['label' => 'シフト', 'done' => $setupShiftDone],
            ['label' => '予約確認', 'done' => $setupReserveDone],
        ];

        $setupDoneCount = collect($setupStatusList)->where('done', true)->count();
        $setupTotalCount = count($setupStatusList);
        $showSetupGuide = $setupDoneCount < $setupTotalCount;

        /* ===============================
           営業日カレンダー / 月シフト 警告集計
        =============================== */
        $settingWarnings = $this->buildReservationSettingWarnings($company);

        /* ===============================
           今月の予約時間（分）
        =============================== */
        $totalReservedMinutes = (int) Reservation::where('company_id', $company->id)
            ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'reserved')
            ->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE,start_at,end_at)) as total'))
            ->value('total');

        /* ===============================
           今月の営業時間（分）
        =============================== */
        $totalAvailableMinutes = 0;

        $staffCount = Staff::where('company_id', $company->id)
            ->where('is_reservable', true)
            ->count();

        $patterns = $company->open_patterns ?? [];
        $date = $startOfMonth->copy();

        while ($date <= $endOfMonth) {
            $weekday = $date->dayOfWeek;
            $dayPatterns = $patterns[$weekday] ?? [];

            if (is_array($dayPatterns)) {
                foreach ($dayPatterns as $pattern) {
                    if (!is_array($pattern)) {
                        continue;
                    }

                    $openTime = $pattern['open'] ?? $pattern['open_time'] ?? null;
                    $closeTime = $pattern['close'] ?? $pattern['close_time'] ?? null;

                    if (empty($openTime) || empty($closeTime)) {
                        continue;
                    }

                    $open = Carbon::parse($date->format('Y-m-d') . ' ' . $openTime);
                    $close = Carbon::parse($date->format('Y-m-d') . ' ' . $closeTime);

                    if ($close->gt($open)) {
                        $totalAvailableMinutes += $open->diffInMinutes($close) * $staffCount;
                    }
                }
            }

            $date->addDay();
        }

        /* ===============================
           稼働率
        =============================== */
        $utilizationRate = 0;

        if ($totalAvailableMinutes > 0 && $totalReservedMinutes > 0) {
            $utilizationRate = round(($totalReservedMinutes / $totalAvailableMinutes) * 100, 1);
        }

        /* ===============================
           今日 / 今月 予約数
        =============================== */
        $todayCount = Reservation::where('company_id', $company->id)
            ->whereDate('start_at', $today->toDateString())
            ->where('status', 'reserved')
            ->count();

        $monthlyCount = Reservation::where('company_id', $company->id)
            ->whereYear('start_at', $now->year)
            ->whereMonth('start_at', $now->month)
            ->where('status', 'reserved')
            ->count();

        /* ===============================
           今日の予約一覧
        =============================== */
        $todayReservations = Reservation::where('company_id', $company->id)
            ->whereDate('start_at', $today->toDateString())
            ->where('status', 'reserved')
            ->with(['staff', 'menus'])
            ->orderBy('start_at')
            ->get();

        /* ===============================
           ダッシュボードお知らせ
        =============================== */
        $notices = CompanyDashboardNotice::visibleForCompany($company->id)
            ->orderByDesc('is_important')
            ->orderByDesc('is_new')
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        /* ===============================
           今日 / 今月 / 今年の売上
        =============================== */
        $todaySales = Reservation::where('company_id', $company->id)
            ->whereDate('start_at', $today->toDateString())
            ->where('status', 'reserved')
            ->sum('total_price');

        $monthlySales = Reservation::where('company_id', $company->id)
            ->whereYear('start_at', $now->year)
            ->whereMonth('start_at', $now->month)
            ->where('status', 'reserved')
            ->sum('total_price');

        $yearlySales = Reservation::where('company_id', $company->id)
            ->whereYear('start_at', $now->year)
            ->where('status', 'reserved')
            ->sum('total_price');

        /* ===============================
           売上集計条件
        =============================== */
        $period = request('period', 'month');
        $year = (int) request('year', now()->year);
        $month = (int) request('month', now()->month);

        $query = Reservation::where('company_id', $company->id)
            ->where('status', 'reserved');

        if ($period === 'month') {
            $query->whereYear('start_at', $year)
                ->whereMonth('start_at', $month);
        } else {
            $query->whereYear('start_at', $year);
        }

        /* ===============================
           月別売上（グラフ用）
        =============================== */
        $monthlyChart = collect(range(1, 12))->map(function ($chartMonth) use ($company, $year) {
            $total = Reservation::where('company_id', $company->id)
                ->whereYear('start_at', $year)
                ->whereMonth('start_at', $chartMonth)
                ->where('status', 'reserved')
                ->sum('total_price');

            return (object) [
                'month' => $chartMonth,
                'total' => (int) $total,
            ];
        });

        /* ===============================
           スタッフ売上ランキング
        =============================== */
        $staffRanking = (clone $query)
            ->select('staff_id', DB::raw('SUM(total_price) as total'))
            ->groupBy('staff_id')
            ->with('staff')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        /* ===============================
           指名ランキング
        =============================== */
        $nominationRanking = (clone $query)
            ->where('nomination_fee', '>', 0)
            ->select(
                'staff_id',
                DB::raw('COUNT(*) as nomination_count'),
                DB::raw('SUM(nomination_fee) as nomination_sales')
            )
            ->groupBy('staff_id')
            ->with('staff')
            ->orderByDesc('nomination_count')
            ->limit(10)
            ->get();

        /* ===============================
           人気メニュー
        =============================== */
        $menuRanking = DB::table('reservation_menus')
            ->join('reservations', 'reservations.id', '=', 'reservation_menus.reservation_id')
            ->join('menus', 'menus.id', '=', 'reservation_menus.menu_id')
            ->where('reservations.company_id', $company->id)
            ->where('reservations.status', 'reserved')
            ->select('menus.name', DB::raw('COUNT(*) as total'))
            ->groupBy('menus.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $totalSales = (clone $query)->sum('total_price');
        $totalReservations = (clone $query)->count();

        $averagePrice = $totalReservations
            ? round($totalSales / $totalReservations)
            : 0;

        /* ===============================
           契約状態表示用
        =============================== */
        $subscriptionStatusLabel = $company->subscription_status_label;
        $subscriptionAvailable = $company->isSubscriptionAvailable();
        $billingWarning = null;

        if (in_array($company->subscription_status, ['past_due', 'unpaid'], true)) {
            $billingWarning = 'お支払い状況をご確認ください。必要に応じてカード情報の更新をお願いします。';
        } elseif ($company->subscription_status === 'canceled') {
            $billingWarning = '現在は解約済みです。再開する場合はプランをお申し込みください。';
        } elseif (!$company->subscription_status) {
            $billingWarning = 'まだご契約がありません。プランを選んでお申し込みください。';
        }

        return view('company.dashboard', compact(
            'staff',
            'company',
            'todayCount',
            'monthlyCount',
            'utilizationRate',
            'todayReservations',
            'notices',
            'todaySales',
            'monthlySales',
            'yearlySales',
            'monthlyChart',
            'staffRanking',
            'menuRanking',
            'nominationRanking',
            'averagePrice',
            'year',
            'month',
            'period',
            'showSetupGuide',
            'setupStatusList',
            'setupDoneCount',
            'setupTotalCount',
            'subscriptionStatusLabel',
            'subscriptionAvailable',
            'billingWarning',
            'settingWarnings',
            'dashboardPermissions',
            'changeNoticePendingCount',
            'changeNoticePhonePendingCount',
            'changeNoticeConfirmedCount',
            'changeNoticeTotalCount',
            'hasChangeNoticeAlert'
        ));
    }

    private function normalizeDashboardPermissions($rawPermissions, string $role): array
    {
        $raw = is_array($rawPermissions) ? $rawPermissions : [];

        $canonicalKeys = [
            'card.reserve',
            'card.business_calendar',
            'card.customers',
            'card.month_shift',
            'card.reservation_change_notices',
            'card.reviews',
            'card.vacation',
            'card.my_profile',
            'dashboard.sales',
            'card.company_info',
            'card.staff',
            'card.logo',
            'card.menu_category_tag',
            'card.menu',
            'card.menu_staff',
            'card.shift_patterns',
            'card.default_shift',
            'card.notices',
            'card.billing',
            'card.theme',
            'dashboard.manage',
        ];

        $aliases = [
            'card.calendar' => 'card.business_calendar',
            'card.business' => 'card.business_calendar',
            'card.customer' => 'card.customers',
            'card.shift' => 'card.month_shift',
            'card.staff_shift' => 'card.month_shift',
            'card.staff_shifts' => 'card.month_shift',
            'card.company' => 'card.company_info',
            'card.company_edit' => 'card.company_info',
            'card.staffs' => 'card.staff',
            'card.staff_manage' => 'card.staff',
            'card.category_tag' => 'card.menu_category_tag',
            'card.menu_category' => 'card.menu_category_tag',
            'card.menu_categories' => 'card.menu_category_tag',
            'card.dashboard_settings' => 'dashboard.manage',
            'card.dashboard_manage' => 'dashboard.manage',
            'card.sales' => 'dashboard.sales',
        ];

        $normalized = [];

        foreach ($raw as $key => $value) {
            $key = $aliases[$key] ?? $key;
            $normalized[$key] = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($normalized[$key] === null) {
                $normalized[$key] = !empty($value);
            }
        }

        $defaultsByRole = [
            'master' => array_fill_keys($canonicalKeys, true),
            'area_leader' => array_fill_keys($canonicalKeys, false),
            'leader' => array_fill_keys($canonicalKeys, false),
            'staff' => array_fill_keys($canonicalKeys, false),
        ];

        $base = $defaultsByRole[$role] ?? array_fill_keys($canonicalKeys, false);

        // 最低限、本人設定系はスタッフでも出せるようにする
        if (array_key_exists('card.my_profile', $base)) {
            $base['card.my_profile'] = true;
        }

        foreach ($canonicalKeys as $key) {
            if (array_key_exists($key, $normalized)) {
                $base[$key] = (bool) $normalized[$key];
            }
        }

        // master の dashboard.manage は強制表示
        if ($role === 'master') {
            $base['dashboard.manage'] = true;
        }

        return $base;
    }

    private function buildReservationSettingWarnings($company): array
    {
        $today = now()->startOfDay();

        $reservationMonthLimit = max((int) ($company->reservation_month_limit ?? 0), 0);

        $alertEnd = $today->copy()
            ->addMonthsNoOverflow($reservationMonthLimit)
            ->endOfMonth();

        $warningEnd = $today->copy()
            ->addMonthsNoOverflow($reservationMonthLimit + 1)
            ->endOfMonth();

        $businessLastDate = CompanyBusinessCalendar::where('company_id', $company->id)
            ->max('date');

        $reservableStaffIds = Staff::where('company_id', $company->id)
            ->where('is_reservable', 1)
            ->pluck('id');

        $shiftLastDate = null;
        if ($reservableStaffIds->isNotEmpty()) {
            $shiftLastDate = StaffShift::whereIn('staff_id', $reservableStaffIds)
                ->max('date');
        }

        return [
            'reservation_month_limit' => $reservationMonthLimit,
            'today' => $today->format('Y-m-d'),
            'alert_end' => $alertEnd->format('Y-m-d'),
            'warning_end' => $warningEnd->format('Y-m-d'),
            'business_calendar' => $this->buildSingleTableWarningData($businessLastDate, $alertEnd, $warningEnd),
            'staff_shifts' => $this->buildSingleTableWarningData($shiftLastDate, $alertEnd, $warningEnd),
        ];
    }

    private function buildSingleTableWarningData($lastDate, Carbon $alertEnd, Carbon $warningEnd): array
    {
        $lastDateCarbon = $lastDate ? Carbon::parse($lastDate)->startOfDay() : null;

        $hasAlert = false;
        $hasWarning = false;

        if (!$lastDateCarbon) {
            $hasAlert = true;
        } elseif ($lastDateCarbon->lt($alertEnd)) {
            $hasAlert = true;
        } elseif ($lastDateCarbon->lt($warningEnd)) {
            $hasWarning = true;
        }

        return [
            'last_date' => $lastDateCarbon?->format('Y-m-d'),
            'has_alert' => $hasAlert,
            'has_warning' => $hasWarning,
        ];
    }
}