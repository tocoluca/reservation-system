<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\Vacation;
use App\Models\Menu;
use App\Models\CompanyBusinessCalendar;
use App\Models\Staff;
use App\Models\StaffShift;
use App\Models\ShiftPattern;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yasumi\Yasumi;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $keyword  = trim((string) $request->get('keyword'));
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $status   = $request->get('status');
        $customerId = $request->get('customer_id');
        $customerFilter = null;

        $query = Reservation::with(['staff', 'customer', 'menus', 'details.staff', 'details.menu'])
            ->where('company_id', $company->id);

        if (!empty($customerId)) {
            $customerFilter = Customer::where('company_id', $company->id)->find($customerId);

            if ($customerFilter) {
                $query->where('customer_id', $customerFilter->id);
            }
        }

        if ($keyword !== '') {
            $normalizedKeyword = str_replace(['-', 'ー', ' '], '', $keyword);

            $query->where(function ($q) use ($keyword, $normalizedKeyword) {
                $q->where('customer_name', 'like', '%' . $keyword . '%')
                  ->orWhere('customer_phone', 'like', '%' . $normalizedKeyword . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($keyword, $normalizedKeyword) {
                      $customerQuery->where('name', 'like', '%' . $keyword . '%')
                                    ->orWhere('phone', 'like', '%' . $normalizedKeyword . '%');
                  });
            });
        }

        if (!empty($dateFrom)) {
            $query->whereDate('start_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('start_at', '<=', $dateTo);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $reservations = $query
            ->orderByDesc('start_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $dayAfterTomorrow = now()->addDays(2)->toDateString();

        $todayReservedCount = Reservation::where('company_id', $company->id)
            ->whereDate('start_at', $today)
            ->where('status', 'reserved')
            ->count();

        $tomorrowReservedCount = Reservation::where('company_id', $company->id)
            ->whereDate('start_at', $tomorrow)
            ->where('status', 'reserved')
            ->count();

        $dayAfterTomorrowReservedCount = Reservation::where('company_id', $company->id)
            ->whereDate('start_at', $dayAfterTomorrow)
            ->where('status', 'reserved')
            ->count();

        return view('company.reservations.index', [
            'company' => $company,
            'reservations' => $reservations,
            'keyword' => $keyword,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'customerId' => $customerId,
            'customerFilter' => $customerFilter,
            'todayReservedCount' => $todayReservedCount,
            'tomorrowReservedCount' => $tomorrowReservedCount,
            'dayAfterTomorrowReservedCount' => $dayAfterTomorrowReservedCount,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'dayAfterTomorrow' => $dayAfterTomorrow,
        ]);
    }

    public function cancelFromList(Request $request, $id)
    {
        $company = auth()->guard('company')->user()->company;
        $redirectParams = $this->reservationIndexRedirectParams($request);

        $reservation = Reservation::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$reservation) {
            return redirect()
                ->route('company.reservations.index', $redirectParams)
                ->withErrors(['reservation' => '予約が見つかりません。']);
        }

        if ($reservation->status === Reservation::STATUS_CANCELLED) {
            return redirect()
                ->route('company.reservations.index', $redirectParams)
                ->with('success', 'この予約はすでにキャンセル済みです。');
        }

        if ($reservation->status === Reservation::STATUS_COMPLETED) {
            return redirect()
                ->route('company.reservations.index', $redirectParams)
                ->withErrors(['reservation' => '来店済みの予約はキャンセルできません。']);
        }

        $cancelKind = $request->input('cancel_kind', 'customer');

        if (!in_array($cancelKind, ['customer', 'shop', 'no_show'], true)) {
            $cancelKind = 'customer';
        }

        $reservation->status = $cancelKind === 'no_show'
            ? Reservation::STATUS_NO_SHOW
            : Reservation::STATUS_CANCELLED;
        $reservation->cancelled_at = now();
        $reservation->cancelled_type = $cancelKind;
        $reservation->save();

        return redirect()
            ->route('company.reservations.index', $redirectParams)
            ->with('success', '予約をキャンセルしました。');
    }

    public function completeFromList(Request $request, $id)
    {
        $company = auth()->guard('company')->user()->company;
        $redirectParams = $this->reservationIndexRedirectParams($request);

        $reservation = Reservation::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$reservation) {
            return redirect()
                ->route('company.reservations.index', $redirectParams)
                ->withErrors(['reservation' => '予約が見つかりません。']);
        }

        if ($reservation->status === Reservation::STATUS_CANCELLED) {
            return redirect()
                ->route('company.reservations.index', $redirectParams)
                ->withErrors(['reservation' => 'キャンセル済みの予約は来店済みにできません。']);
        }

        if ($reservation->status === Reservation::STATUS_COMPLETED) {
            return redirect()
                ->route('company.reservations.index', $redirectParams)
                ->with('success', 'この予約はすでに来店済みです。');
        }

        $reservation->status = Reservation::STATUS_COMPLETED;
        $reservation->save();

        return redirect()
            ->route('company.reservations.index', $redirectParams)
            ->with('success', '予約を来店済みにしました。');
    }

    private function reservationIndexRedirectParams(Request $request): array
    {
        $filters = $request->input('filters', []);

        if (!is_array($filters)) {
            return [];
        }

        return collect($filters)
            ->only(['keyword', 'date_from', 'date_to', 'status', 'customer_id'])
            ->filter(fn ($value) => filled($value))
            ->all();
    }

    private function getReservationLimits($company)
    {
        $limitMonth = $company->reservation_month_limit ?? 3;
        $openDays   = $company->reservation_open_days ?? 0;

        $closeHours = is_numeric($company->reservation_close_hours)
            ? (int) $company->reservation_close_hours
            : 1;

        $startReservableDate = now()
            ->addDays($openDays)
            ->startOfDay();

        $lastReservableDate = now()
            ->addMonths($limitMonth)
            ->endOfMonth();

        $closeLimit = now()->addHours($closeHours);

        return [
            'start' => $startReservableDate,
            'end'   => $lastReservableDate,
            'close' => $closeLimit,
        ];
    }

    public function calendar(Request $request)
    {
        try {
            $isMobile = (bool) preg_match(
                '/Android|iPhone|iPod|Mobile/i',
                (string) $request->userAgent()
            );
            $mode = $request->has('mode')
                ? $request->get('mode')
                : ($isMobile ? 'day' : 'week');

            if (!in_array($mode, ['day', 'week'], true)) {
                $mode = 'week';
            }

            $company = auth()->guard('company')->user()->company;

            $menus = Menu::with('category')
                ->where('company_id', $company->id)
                ->orderBy('sort_order')
                ->get()
                ->groupBy(function ($menu) {
                    return optional($menu->category)->name ?? 'その他';
                });

            return view('company.calendar', [
                'mode' => $mode,
                'menus' => $menus,
            ]);
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getLine());
        }
    }

    public function calendarData(Request $request)
    {
        $company = auth()->guard('company')->user()->company;
        $mode = $request->get('mode', 'week');
        $menuIds = $request->menu_ids ?? [];

        if ($mode === 'day') {
            $date = $request->date
                ? Carbon::parse($request->date)
                : now();
            $staffId = $request->staff_id;

            $limits = $this->getReservationLimits($company);

            if (
                ($date->copy()->startOfDay() < $limits['start'] && !$date->isToday())
                || $date->copy()->startOfDay() > $limits['end']
            ) {
                return response()->json([
                    'staffs' => [],
                    'slots' => [],
                ]);
            }

            $staffQuery = Staff::where('company_id', $company->id)
                ->where('is_reservable', true)
                ->where('role', '!=', 'store_operator');

            if (!empty($staffId)) {
                $staffQuery->where('id', $staffId);
            }

            $staffList = $staffQuery
                ->orderBy('priority_order')
                ->get();

            $staffIds = $staffList->pluck('id');

            $reservations = Reservation::where('company_id', $company->id)
                ->whereIn('staff_id', $staffIds)
                ->whereDate('start_at', $date->toDateString())
                ->where('status', 'reserved')
                ->get();

            $vacations = Vacation::whereIn('staff_id', $staffIds)
                ->where('status', 'approved')
                ->whereDate('start_at', '<=', $date->toDateString())
                ->whereDate('end_at', '>=', $date->toDateString())
                ->get();

            $shifts = StaffShift::whereIn('staff_id', $staffIds)
                ->whereDate('date', $date->toDateString())
                ->get()
                ->keyBy(function ($s) {
                    return $s->staff_id . '_' . Carbon::parse($s->date)->toDateString();
                });

            $patternIds = $shifts->pluck('shift_pattern_id')->filter();

            $shiftPatterns = ShiftPattern::whereIn('id', $patternIds)
                ->get()
                ->keyBy('id');

            $data = [];
            $weekday = $date->dayOfWeek;
            $patterns = (array) ($company->open_patterns[$weekday] ?? []);

            foreach ($patterns as $p) {
                if (empty($p['open']) || empty($p['close'])) {
                    continue;
                }

                $open = Carbon::parse($date->format('Y-m-d') . ' ' . $p['open']);
                $close = Carbon::parse($date->format('Y-m-d') . ' ' . $p['close']);

                $time = $open->copy();
                $now = Carbon::now();

                while ($time < $close) {
                    $slotStart = $time->copy();
                    $slotEnd = $time->copy()->addMinutes($company->slot_minutes);

                    if ($date->isToday() && $slotStart < $now) {
                        foreach ($staffList as $staff) {
                            $data[$slotStart->format('H:i')][$staff->id] = [
                                'status' => '×',
                                'available' => 0,
                                'total' => max(1, (int) ($company->max_simultaneous_reservations ?? 1)),
                                'is_closed' => false,
                                'unavailable_reason' => 'past',
                            ];
                        }

                        $time->addMinutes($company->slot_minutes);
                        continue;
                    }

                    $status = $this->getBusinessStatus($company, $slotStart, $slotEnd);

                    if ($status !== 'open') {
                        foreach ($staffList as $staff) {
                            $data[$slotStart->format('H:i')][$staff->id] = [
                                'status' => '×',
                                'available' => 0,
                                'total' => 0,
                                'is_closed' => true,
                                'unavailable_reason' => $status === 'closed' ? 'non_business_day' : 'outside_business_hours',
                            ];
                        }

                        $time->addMinutes($company->slot_minutes);
                        continue;
                    }

                    foreach ($staffList as $staff) {
                        $result = $this->checkAvailability(
                            $company,
                            collect([$staff]),
                            $reservations,
                            $vacations,
                            $shifts,
                            $shiftPatterns,
                            $slotStart,
                            $slotEnd
                        );

                        $overlapDetail = $this->firstOverlappingReservationDetail(
                            $company,
                            (int) $staff->id,
                            $slotStart,
                            $slotEnd
                        );

                        $staffReservedCount = $this->countReservationDetailOverlaps(
                            $company,
                            (int) $staff->id,
                            $slotStart,
                            $slotEnd
                        );

                        $perStaffLimit = max(1, (int) ($company->max_simultaneous_reservations ?? 1));

                        if (
                            !$result['is_closed']
                            && $overlapDetail
                            && $staffReservedCount >= $perStaffLimit
                        ) {
                            $reservation = $overlapDetail->reservation;

                            $data[$slotStart->format('H:i')][$staff->id] = [
                                'status'            => '×',
                                'reservation_id'    => $reservation?->id,
                                'customer_name'     => $reservation?->customer_name,
                                'customer_phone'    => $reservation?->customer_phone,
                                'staff_name'        => $staff->name,
                                'reservation_start' => optional($reservation?->start_at)->format('Y-m-d H:i'),
                                'available'         => 0,
                                'total'             => $perStaffLimit,
                                'is_closed'         => false,
                                'unavailable_reason'=> 'already_booked',
                            ];
                        } else {
                            $data[$slotStart->format('H:i')][$staff->id] = $result;
                        }
                    }

                    $time->addMinutes($company->slot_minutes);
                }
            }

            return response()->json([
                'staffs' => $staffList,
                'slots' => $data,
            ]);
        }

        try {
            $staffId = $request->staff_id;

            $startDate = $request->date
                ? Carbon::parse($request->date)
                : now();

            $startDate = $startDate->copy()->startOfWeek();
            $endDate = $startDate->copy()->addDays(6)->endOfDay();

            $limits = $this->getReservationLimits($company);

            $staffQuery = Staff::where('company_id', $company->id)
                ->where('is_reservable', true)
                ->where('role', '!=', 'store_operator');

            if (!empty($menuIds)) {
                $staffQuery->whereHas('menus', function ($q) use ($menuIds) {
                    $q->whereIn('menus.id', $menuIds);
                });
            }

            if (!empty($staffId)) {
                $staffQuery->where('id', $staffId);
            }

            $staffList = $staffQuery
                ->orderBy('priority_order')
                ->get();

            $staffIds = $staffList->pluck('id');

            $reservations = Reservation::where('company_id', $company->id)
                ->whereIn('staff_id', $staffIds)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_at', '<', $endDate)
                      ->where('end_at', '>', $startDate);
                })
                ->where('status', 'reserved')
                ->get();

            $vacations = Vacation::whereIn('staff_id', $staffIds)
                ->where('status', 'approved')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_at', '<', $endDate)
                      ->where('end_at', '>', $startDate);
                })
                ->get();

            $shifts = StaffShift::whereIn('staff_id', $staffIds)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get()
                ->keyBy(function ($s) {
                    return $s->staff_id . '_' . Carbon::parse($s->date)->toDateString();
                });

            $patternIds = $shifts->pluck('shift_pattern_id')->filter();

            $shiftPatterns = ShiftPattern::whereIn('id', $patternIds)
                ->get()
                ->keyBy('id');

            $data = [];
            $minutes = $company->slot_minutes ?? 30;

            for ($d = 0; $d < 7; $d++) {
                $day = $startDate->copy()->addDays($d);

                if (
                    $day->copy()->startOfDay() < $limits['start'] ||
                    $day->copy()->startOfDay() > $limits['end']
                ) {
                    if (!$day->isToday()) {
                        continue;
                    }
                }

                $weekday = $day->dayOfWeek;

                $patterns = is_array($company->open_patterns)
                    ? ($company->open_patterns[$weekday] ?? [])
                    : [];

                foreach ($patterns as $p) {
                    if (empty($p['open']) || empty($p['close'])) {
                        continue;
                    }

                    $open = Carbon::parse($day->format('Y-m-d') . ' ' . $p['open']);
                    $close = Carbon::parse($day->format('Y-m-d') . ' ' . $p['close']);

                    $time = $open->copy();

                    while ($time < $close) {
                        $slotStart = Carbon::parse($day->format('Y-m-d') . ' ' . $time->format('H:i'));
                        $slotEnd   = $slotStart->copy()->addMinutes($minutes);

                        if ($day->isToday() && $slotStart < now()) {
                            $data[$time->format('H:i')][$day->format('Y-m-d')] = [
                                'status' => '×',
                                'available' => 0,
                                'total' => count($staffList) * max(1, (int) ($company->max_simultaneous_reservations ?? 1)),
                                'is_closed' => false,
                                'unavailable_reason' => 'past',
                            ];

                            $time->addMinutes($minutes);
                            continue;
                        }

                        $status = $this->getBusinessStatus($company, $slotStart, $slotEnd);

                        if ($status !== 'open') {
                            $data[$time->format('H:i')][$day->format('Y-m-d')] = [
                                'status' => '×',
                                'available' => 0,
                                'total' => 0,
                                'is_closed' => true,
                                'unavailable_reason' => $status === 'closed' ? 'non_business_day' : 'outside_business_hours',
                            ];
                            $time->addMinutes($minutes);
                            continue;
                        }

                        $result = $this->checkAvailability(
                            $company,
                            $staffList,
                            $reservations,
                            $vacations,
                            $shifts,
                            $shiftPatterns,
                            $slotStart,
                            $slotEnd
                        );

                        if ((int) ($result['total'] ?? 0) > (int) ($result['available'] ?? 0)) {
                            $reservationOptions = $this->overlappingReservationOptionsForSlot(
                                $company,
                                $staffList,
                                $slotStart,
                                $slotEnd,
                                $shifts,
                                $shiftPatterns,
                                $vacations
                            );

                            if (!empty($reservationOptions)) {
                                $firstReservation = $reservationOptions[0];

                                $result['reservations'] = $reservationOptions;
                                $result['reservation_id'] = $firstReservation['id'];
                                $result['customer_name'] = $firstReservation['customer_name'];
                                $result['customer_phone'] = $firstReservation['customer_phone'];
                                $result['staff_name'] = $firstReservation['staff_name'];
                                $result['reservation_start'] = $firstReservation['reservation_start'];
                            }
                        }

                        $data[$time->format('H:i')][$day->format('Y-m-d')] = $result;

                        $time->addMinutes($minutes);
                    }
                }
            }

            return response()->json([
                'mode' => 'week',
                'slots' => $data ?? [],
                'staffs' => $staffList,
            ]);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $company = Auth::guard('company')->user()->company;

        try {
            $request->validate([
                'start_at'                  => 'required|date',
                'customer_name'             => 'required|string|max:255',
                'customer_phone'            => 'required|string|max:50',
                'customer_email'            => 'nullable|email|max:255',
                'menu_ids'                  => 'required|array|min:1',
                'menu_ids.*'                => 'integer',
                'staff_id'                  => 'nullable|integer',
                'assignments'               => 'nullable|array',
                'assignments.*.menu_id'     => 'required_with:assignments|integer',
                'assignments.*.staff_id'    => 'required_with:assignments|integer',
            ]);

            $start = Carbon::parse($request->start_at);

            $menuIds = collect($request->menu_ids ?? [])->map(fn ($id) => (int) $id)->values();

            $menus = Menu::where('company_id', $company->id)
                ->whereIn('id', $menuIds)
                ->get()
                ->keyBy('id');

            if ($menus->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'メニューを選択してください',
                ], 422);
            }

            $orderedMenus = $menuIds->map(function ($id) use ($menus) {
                return $menus->get($id);
            })->filter()->values();

            $segments = $this->buildMenuSegments($orderedMenus, $start, $company);
            $end = collect($segments)->last()['end_at'] ?? $start->copy()->addMinutes((int) ($company->slot_minutes ?? 30));

            $limits = $this->getReservationLimits($company);

            if ($start < $limits['start']) {
                return response()->json([
                    'success' => false,
                    'message' => 'この日はまだ予約受付していません',
                ], 422);
            }

            if ($start > $limits['end']) {
                return response()->json([
                    'success' => false,
                    'message' => '予約可能期間を超えています',
                ], 422);
            }

            $staffList = Staff::where('company_id', $company->id)
                ->where('is_reservable', true)
                ->where('role', '!=', 'store_operator')
                ->with('menus:id')
                ->orderBy('priority_order')
                ->get();

            if ($staffList->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '予約可能なスタッフがいません',
                ], 422);
            }

            $staffIds = $staffList->pluck('id');

            $shiftDateFrom = $start->toDateString();
            $shiftDateTo   = $end->copy()->toDateString();

            $shifts = StaffShift::whereIn('staff_id', $staffIds)
                ->whereBetween('date', [$shiftDateFrom, $shiftDateTo])
                ->get()
                ->keyBy(function ($s) {
                    return $s->staff_id . '_' . Carbon::parse($s->date)->toDateString();
                });

            $patternIds = $shifts->pluck('shift_pattern_id')->filter()->unique()->values();

            $shiftPatterns = ShiftPattern::whereIn('id', $patternIds)
                ->get()
                ->keyBy('id');

            $vacations = Vacation::whereIn('staff_id', $staffIds)
                ->where('status', 'approved')
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_at', '<', $end)
                      ->where('end_at', '>', $start);
                })
                ->get();

            $requestedAssignments = collect($request->assignments ?? [])
                ->map(function ($row) {
                    return [
                        'menu_id'  => (int) ($row['menu_id'] ?? 0),
                        'staff_id' => (int) ($row['staff_id'] ?? 0),
                    ];
                })
                ->values();

            $requestedStaffId = $request->staff_id ? (int) $request->staff_id : null;
            $preferLessCapable = (bool) ($company->prefer_less_capable_staff_for_menu_assignment ?? false);

            DB::transaction(function () use (
                $request,
                $company,
                $start,
                $end,
                $orderedMenus,
                $segments,
                $staffList,
                $shifts,
                $shiftPatterns,
                $vacations,
                $requestedAssignments,
                $requestedStaffId,
                $preferLessCapable
            ) {
                $finalAssignments = [];

                if ($preferLessCapable) {
                    if ($requestedAssignments->isEmpty()) {
                        throw new \Exception('担当パターンを選択してください');
                    }

                    foreach ($segments as $segment) {
                        $menuId = (int) $segment['menu']->id;
                        $assignment = $requestedAssignments->firstWhere('menu_id', $menuId);

                        if (!$assignment) {
                            throw new \Exception('担当パターンが不正です');
                        }

                        $staff = $staffList->firstWhere('id', (int) $assignment['staff_id']);

                        if (!$staff) {
                            throw new \Exception('担当者が見つかりません');
                        }

                        if (!$staff->menus->contains('id', $menuId)) {
                            throw new \Exception('担当者とメニューの組み合わせが不正です');
                        }

                        if (!$this->isSegmentReservableForStaff(
                            $company,
                            $staff,
                            $segment['start_at'],
                            $segment['end_at'],
                            $shifts,
                            $shiftPatterns,
                            $vacations
                        )) {
                            throw new \Exception("{$staff->name} さんは {$segment['menu']->name} をその時間に担当できません");
                        }

                        $finalAssignments[] = [
                            'menu'      => $segment['menu'],
                            'staff'     => $staff,
                            'start_at'  => $segment['start_at'],
                            'end_at'    => $segment['end_at'],
                            'duration'  => $segment['duration'],
                        ];
                    }
                } else {
                    $assignedStaff = null;

                    if ($requestedStaffId) {
                        $staff = $staffList->firstWhere('id', $requestedStaffId);

                        if (!$staff) {
                            throw new \Exception('担当者が見つかりません');
                        }

                        foreach ($segments as $segment) {
                            if (!$staff->menus->contains('id', $segment['menu']->id)) {
                                throw new \Exception('この担当者は選択メニューすべてに対応していません');
                            }

                            if (!$this->isSegmentReservableForStaff(
                                $company,
                                $staff,
                                $segment['start_at'],
                                $segment['end_at'],
                                $shifts,
                                $shiftPatterns,
                                $vacations
                            )) {
                                throw new \Exception('この担当者はその時間に対応できません');
                            }
                        }

                        $assignedStaff = $staff;
                    } else {
                        $candidateStaff = $staffList->filter(function ($staff) use ($segments, $company, $shifts, $shiftPatterns, $vacations) {
                            foreach ($segments as $segment) {
                                if (!$staff->menus->contains('id', $segment['menu']->id)) {
                                    return false;
                                }

                                if (!$this->isSegmentReservableForStaff(
                                    $company,
                                    $staff,
                                    $segment['start_at'],
                                    $segment['end_at'],
                                    $shifts,
                                    $shiftPatterns,
                                    $vacations
                                )) {
                                    return false;
                                }
                            }

                            return true;
                        })->sortBy('priority_order')->values();

                        $assignedStaff = $candidateStaff->first();

                        if (!$assignedStaff) {
                            throw new \Exception('空いているスタッフがいません');
                        }
                    }

                    foreach ($segments as $segment) {
                        $finalAssignments[] = [
                            'menu'      => $segment['menu'],
                            'staff'     => $assignedStaff,
                            'start_at'  => $segment['start_at'],
                            'end_at'    => $segment['end_at'],
                            'duration'  => $segment['duration'],
                        ];
                    }
                }

                $price = collect($finalAssignments)->sum(function ($row) {
                    return (int) ($row['menu']->price ?? 0);
                });

                $mainStaffGroup = collect($finalAssignments)
                    ->groupBy(fn ($row) => $row['staff']->id)
                    ->sortByDesc(fn ($rows) => $rows->count())
                    ->first();

                $mainStaff = $mainStaffGroup ? collect($mainStaffGroup)->first()['staff'] : null;

                if (!$mainStaff) {
                    throw new \Exception('担当者の割当ができませんでした');
                }

                $nominationFee = (int) ($mainStaff->nomination_fee ?? 0);
                $totalPrice = (int) $price + $nominationFee;

                $normalizedPhone = str_replace('-', '', $request->customer_phone);

                $customer = Customer::where('company_id', $company->id)
                    ->where('phone', $normalizedPhone)
                    ->lockForUpdate()
                    ->first();

                if ($customer) {
                    $customer->visit_count = (int) $customer->visit_count + 1;
                    $customer->last_visit = $start;
                    $customer->name = $request->customer_name;
                    if ($request->filled('customer_email')) {
                        $customer->email = $request->customer_email;
                    }
                    $customer->save();
                } else {
                    $customer = Customer::create([
                        'company_id'  => $company->id,
                        'name'        => $request->customer_name,
                        'phone'       => $normalizedPhone,
                        'email'       => $request->customer_email ?? null,
                        'visit_count' => 1,
                        'last_visit'  => $start,
                    ]);
                }

                $reservation = Reservation::create([
                    'company_id'     => $company->id,
                    'staff_id'       => $mainStaff->id,
                    'customer_name'  => $request->customer_name,
                    'customer_phone' => $normalizedPhone,
                    'customer_email' => $request->customer_email ?? null,
                    'start_at'       => $start,
                    'end_at'         => $end,
                    'price'          => $price,
                    'nomination_fee' => $nominationFee,
                    'total_price'    => $totalPrice,
                    'status'         => 'reserved',
                    'customer_id'    => $customer->id,
                    'source'         => 'staff',
                ]);

                $attachData = [];

                foreach ($finalAssignments as $index => $row) {
                    $menu = $row['menu'];
                    $staff = $row['staff'];

                    $attachData[$menu->id] = [
                        'price'      => $menu->price,
                        'duration'   => $row['duration'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    ReservationDetail::create([
                        'reservation_id' => $reservation->id,
                        'menu_id'        => $menu->id,
                        'staff_id'       => $staff->id,
                        'start_at'       => $row['start_at'],
                        'end_at'         => $row['end_at'],
                        'duration'       => $row['duration'],
                        'price'          => (int) ($menu->price ?? 0),
                        'sort_order'     => $index + 1,
                    ]);
                }

                if (!empty($attachData)) {
                    $reservation->menus()->attach($attachData);
                }
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function assignmentCandidates(Request $request)
    {
        $companyUser = auth()->guard('company')->user();

        if (!$companyUser) {
            return response()->json([], 401);
        }

        $company = $companyUser->company;

        $request->validate([
            'datetime'   => 'required|date',
            'menu_ids'   => 'required|array|min:1',
            'menu_ids.*' => 'integer',
        ]);

        $start = Carbon::parse($request->datetime);

        $menuIds = collect($request->menu_ids ?? [])->map(fn ($id) => (int) $id)->values();

        $menus = Menu::where('company_id', $company->id)
            ->whereIn('id', $menuIds)
            ->get()
            ->keyBy('id');

        $orderedMenus = $menuIds->map(function ($id) use ($menus) {
            return $menus->get($id);
        })->filter()->values();

        if ($orderedMenus->isEmpty()) {
            Log::info('assignmentCandidates: orderedMenus empty', [
                'company_id' => $company->id,
                'menu_ids' => $menuIds->all(),
            ]);

            return response()->json([
                'mode' => 'single',
                'candidates' => [],
                'message' => '対象メニューが見つかりませんでした。',
                'split_possible' => false,
                'reasons' => [],
            ]);
        }

        $segments = $this->buildMenuSegments($orderedMenus, $start, $company);
        $preferLessCapable = (bool) ($company->prefer_less_capable_staff_for_menu_assignment ?? false);

        $staffList = Staff::where('company_id', $company->id)
            ->where('is_reservable', true)
            ->where('role', '!=', 'store_operator')
            ->with('menus:id')
            ->orderBy('priority_order')
            ->get();

        $staffIds = $staffList->pluck('id');

        $end = collect($segments)->last()['end_at'] ?? $start->copy();

        $shifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(function ($s) {
                return $s->staff_id . '_' . Carbon::parse($s->date)->toDateString();
            });

        $patternIds = $shifts->pluck('shift_pattern_id')->filter()->unique()->values();

        $shiftPatterns = ShiftPattern::whereIn('id', $patternIds)
            ->get()
            ->keyBy('id');

        $vacations = Vacation::whereIn('staff_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            })
            ->get();

        if ($preferLessCapable) {
            $candidates = $this->buildMultiStaffAssignmentCandidates(
                $company,
                $segments,
                $staffList,
                $shifts,
                $shiftPatterns,
                $vacations
            );

            if (empty($candidates)) {
                $reasons = $this->buildNoCandidateSummary(
                    $company,
                    $segments,
                    $staffList,
                    $shifts,
                    $shiftPatterns,
                    $vacations
                );

                Log::warning('assignmentCandidates: no multi candidates', [
                    'company_id' => $company->id,
                    'datetime' => $start->format('Y-m-d H:i:s'),
                    'menu_ids' => $menuIds->all(),
                    'reasons' => $reasons,
                ]);

                return response()->json([
                    'mode' => 'multi',
                    'candidates' => [],
                    'message' => 'このメニュー内容で対応できる担当パターンが見つかりませんでした。',
                    'split_possible' => false,
                    'reasons' => $reasons,
                ]);
            }

            return response()->json([
                'mode' => 'multi',
                'candidates' => $candidates,
            ]);
        }

        $candidates = $this->buildSingleStaffAssignmentCandidates(
            $company,
            $segments,
            $staffList,
            $shifts,
            $shiftPatterns,
            $vacations
        );

        if (empty($candidates)) {
            $reasons = $this->buildNoCandidateSummary(
                $company,
                $segments,
                $staffList,
                $shifts,
                $shiftPatterns,
                $vacations
            );

            $splitPossible = $this->canBeHandledBySplitAssignments(
                $company,
                $segments,
                $staffList,
                $shifts,
                $shiftPatterns,
                $vacations
            );

            Log::warning('assignmentCandidates: no single candidates', [
                'company_id' => $company->id,
                'datetime' => $start->format('Y-m-d H:i:s'),
                'menu_ids' => $menuIds->all(),
                'split_possible' => $splitPossible,
                'reasons' => $reasons,
            ]);

            return response()->json([
                'mode' => 'single',
                'candidates' => [],
                'message' => $splitPossible
                    ? '単独担当では対応できませんが、分担予約なら受付できる可能性があります。'
                    : 'このメニュー内容で対応できる担当パターンが見つかりませんでした。',
                'split_possible' => $splitPossible,
                'reasons' => $reasons,
            ]);
        }

        return response()->json([
            'mode' => 'single',
            'candidates' => $candidates,
        ]);
    }

    private function buildMultiStaffAssignmentCandidates($company, array $segments, $staffList, $shifts, $shiftPatterns, $vacations): array
    {
        $segmentCandidates = [];

        foreach ($segments as $segment) {
            $candidates = $staffList->filter(function ($staff) use ($company, $segment, $shifts, $shiftPatterns, $vacations) {
                if (!$staff->menus->contains('id', $segment['menu']->id)) {
                    return false;
                }

                return $this->isSegmentReservableForStaff(
                    $company,
                    $staff,
                    $segment['start_at'],
                    $segment['end_at'],
                    $shifts,
                    $shiftPatterns,
                    $vacations
                );
            })->values();

            if ($candidates->isEmpty()) {
                return [];
            }

            $segmentCandidates[] = [
                'segment' => $segment,
                'staffs'  => $candidates,
            ];
        }

        $patterns = [];
        $current  = [];

        $walk = function ($index) use (&$walk, &$patterns, &$current, $segmentCandidates) {
            if ($index >= count($segmentCandidates)) {
                $patterns[] = $current;
                return;
            }

            $entry = $segmentCandidates[$index];

            foreach ($entry['staffs'] as $staff) {
                $current[] = [
                    'menu_id'          => $entry['segment']['menu']->id,
                    'menu_name'        => $entry['segment']['menu']->name,
                    'staff_id'         => $staff->id,
                    'staff_name'       => $staff->name,
                    'staff_menu_count' => $staff->menus->count(),
                    'duration'         => $entry['segment']['duration'],
                    'start_at'         => $entry['segment']['start_at']->format('Y-m-d H:i:s'),
                    'end_at'           => $entry['segment']['end_at']->format('Y-m-d H:i:s'),
                ];
                $walk($index + 1);
                array_pop($current);
            }
        };

        $walk(0);

        foreach ($patterns as &$pattern) {
            $pattern['_score'] = $this->scoreAssignmentPattern($pattern);
        }
        unset($pattern);

        usort($patterns, function ($a, $b) {
            return ($a['_score'] ?? 0) <=> ($b['_score'] ?? 0);
        });

        $result = [];
        foreach (array_slice($patterns, 0, 10) as $i => $pattern) {
            unset($pattern['_score']);

            $uniqueStaffCount = collect($pattern)->pluck('staff_id')->unique()->count();

            $result[] = [
                'rank' => $i + 1,
                'label' => $uniqueStaffCount >= 2
                    ? '分担優先パターン'
                    : '同一担当パターン',
                'assignments' => array_values($pattern),
            ];
        }

        return $result;
    }

    public function cancel(Request $request, $id)
    {
        $company = auth()->guard('company')->user()->company;

        $reservation = Reservation::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => '予約が見つかりません',
            ], 404);
        }

        $cancelKind = $request->input('cancel_kind', 'shop');

        if (!in_array($cancelKind, ['customer', 'shop', 'no_show'], true)) {
            $cancelKind = 'shop';
        }

        $reservation->status = $cancelKind === 'no_show'
            ? Reservation::STATUS_NO_SHOW
            : Reservation::STATUS_CANCELLED;
        $reservation->cancelled_at = now();
        $reservation->cancelled_type = $cancelKind;
        $reservation->save();

        return response()->json(['success' => true]);
    }

    public function availableStaff(Request $request)
    {
        $companyUser = auth()->guard('company')->user();

        if (!$companyUser) {
            return response()->json([], 401);
        }

        $company = $companyUser->company;
        $datetime = $request->datetime;

        if (!$datetime) {
            return response()->json([], 400);
        }

        $start = Carbon::parse($datetime);

        $menus = Menu::where('company_id', $company->id)
            ->whereIn('id', $request->menu_ids ?? [])
            ->get();

        $duration = (int) $menus->sum('duration');
        if ($duration <= 0) {
            $duration = (int) ($company->slot_minutes ?? 30);
        }

        $end = $start->copy()->addMinutes($duration);

        $staffList = Staff::where('company_id', $company->id)
            ->where('is_reservable', true)
            ->where('role', '!=', 'store_operator')
            ->orderBy('priority_order')
            ->get();

        $staffIds = $staffList->pluck('id');

        $shifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereDate('date', $start->toDateString())
            ->get()
            ->keyBy(function ($s) {
                return $s->staff_id . '_' . Carbon::parse($s->date)->toDateString();
            });

        $patternIds = $shifts->pluck('shift_pattern_id')->filter();

        $shiftPatterns = ShiftPattern::whereIn('id', $patternIds)
            ->get()
            ->keyBy('id');

        $vacations = Vacation::whereIn('staff_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            })
            ->get();

        $maxSimultaneous = max(1, (int) ($company->max_simultaneous_reservations ?? 1));

        $availableStaff = [];

        foreach ($staffList as $staff) {
            if (!$this->isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations)) {
                continue;
            }

            $overlapCount = $this->countReservationDetailOverlaps($company, (int) $staff->id, $start, $end);

            if ($overlapCount < $maxSimultaneous) {
                $availableStaff[] = $staff;
            }
        }

        return response()->json($availableStaff);
    }

    private function getBusinessStatus($company, $start, $end)
    {
        $dateKey = $start->format('Y-m-d');
        $weekday = $start->dayOfWeek;

        $calendar = CompanyBusinessCalendar::where('company_id', $company->id)
            ->whereDate('date', $dateKey)
            ->first();

        if ($calendar) {
            if ($calendar->is_open == 0) {
                return 'closed';
            }

            if (!empty($calendar->open_time) && !empty($calendar->close_time)) {
                $open  = Carbon::parse($dateKey . ' ' . $calendar->open_time);
                $close = Carbon::parse($dateKey . ' ' . $calendar->close_time);

                if ($start >= $open && $end <= $close) {
                    return 'open';
                }

                return 'out';
            }
        }

        if (in_array($weekday, (array) $company->regular_holidays)) {
            return 'closed';
        }

        if ($company->holiday_is_closed) {
            $holidays = Yasumi::create('Japan', $start->year);
            if ($holidays->isHoliday($start)) {
                return 'closed';
            }
        }

        $patterns = (array) ($company->open_patterns[$weekday] ?? []);

        foreach ($patterns as $p) {
            if (empty($p['open']) || empty($p['close'])) {
                continue;
            }

            $open  = Carbon::parse($dateKey . ' ' . $p['open']);
            $close = Carbon::parse($dateKey . ' ' . $p['close']);

            if ($start >= $open && $end <= $close) {
                return 'open';
            }
        }

        return 'out';
    }

    private function isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations): bool
    {
        $key = $staff->id . '_' . $start->toDateString();
        $shift = $shifts[$key] ?? null;

        if (!$shift || !$shift->is_work) {
            return false;
        }

        $shiftPattern = $shiftPatterns[$shift->shift_pattern_id] ?? null;

        if ($shiftPattern) {
            $shiftStart = Carbon::parse($start->format('Y-m-d') . ' ' . $shiftPattern->start_time);
            $shiftEnd   = Carbon::parse($start->format('Y-m-d') . ' ' . $shiftPattern->end_time);

            if ($start < $shiftStart || $end > $shiftEnd) {
                return false;
            }
        }

        $vacation = $vacations->first(function ($v) use ($staff, $start, $end) {
            return $v->staff_id == $staff->id &&
                   $v->start_at < $end &&
                   $v->end_at > $start;
        });

        if ($vacation) {
            return false;
        }

        return true;
    }

    private function reservationDetailOverlapQuery($company, int $staffId, Carbon $start, Carbon $end)
    {
        return ReservationDetail::query()
            ->where('staff_id', $staffId)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->whereHas('reservation', function ($q) use ($company) {
                $q->where('company_id', $company->id)
                  ->where('status', 'reserved');
            });
    }

    private function countReservationDetailOverlaps($company, int $staffId, Carbon $start, Carbon $end): int
    {
        return $this->reservationDetailOverlapQuery($company, $staffId, $start, $end)->count();
    }

    private function firstOverlappingReservationDetail($company, int $staffId, Carbon $start, Carbon $end): ?ReservationDetail
    {
        return $this->reservationDetailOverlapQuery($company, $staffId, $start, $end)
            ->with('reservation')
            ->orderBy('start_at')
            ->first();
    }

    private function overlappingReservationDetails($company, int $staffId, Carbon $start, Carbon $end)
    {
        return $this->reservationDetailOverlapQuery($company, $staffId, $start, $end)
            ->with(['reservation', 'staff'])
            ->orderBy('start_at')
            ->get();
    }

    private function overlappingReservationOptionsForSlot(
        $company,
        $staffList,
        Carbon $start,
        Carbon $end,
        $shifts,
        $shiftPatterns,
        $vacations
    ): array {
        $reservations = [];

        foreach ($staffList as $staff) {
            if (!$this->isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations)) {
                continue;
            }

            foreach ($this->overlappingReservationDetails($company, (int) $staff->id, $start, $end) as $detail) {
                $reservation = $detail->reservation;

                if (!$reservation) {
                    continue;
                }

                $reservationId = (int) $reservation->id;

                if (!isset($reservations[$reservationId])) {
                    $reservations[$reservationId] = [
                        'id' => $reservationId,
                        'customer_name' => $reservation->customer_name,
                        'customer_phone' => $reservation->customer_phone,
                        'reservation_start' => optional($reservation->start_at)->format('Y-m-d H:i'),
                        'staff_names' => [],
                    ];
                }

                $staffName = $detail->staff?->name ?? $staff->name;

                if ($staffName && !in_array($staffName, $reservations[$reservationId]['staff_names'], true)) {
                    $reservations[$reservationId]['staff_names'][] = $staffName;
                }
            }
        }

        return array_values(array_map(function (array $reservation) {
            $reservation['staff_name'] = implode(' / ', $reservation['staff_names']);
            unset($reservation['staff_names']);

            return $reservation;
        }, $reservations));
    }

    private function checkAvailability(
        $company,
        $staffList,
        $reservations,
        $vacations,
        $shifts,
        $shiftPatterns,
        $start,
        $end
    ) {
        $maxSimultaneous = max(1, (int) ($company->max_simultaneous_reservations ?? 1));

        $workingStaff = 0;
        $available = 0;

        foreach ($staffList as $staff) {
            if (!$this->isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations)) {
                continue;
            }

            $workingStaff++;

            $staffReservedCount = $this->countReservationDetailOverlaps(
                $company,
                (int) $staff->id,
                $start,
                $end
            );

            $staffRemaining = max(0, $maxSimultaneous - $staffReservedCount);
            $available += $staffRemaining;
        }

        $total = $workingStaff * $maxSimultaneous;

        if ($workingStaff <= 0) {
            return [
                'status' => '×',
                'available' => 0,
                'total' => 0,
                'is_closed' => true,
                'unavailable_reason' => 'shift_off',
            ];
        }

        if ($available <= 0) {
            return [
                'status' => '×',
                'available' => 0,
                'total' => $total,
                'is_closed' => false,
                'unavailable_reason' => 'already_booked',
            ];
        }

        if ($available === 1) {
            return [
                'status' => '△',
                'available' => 1,
                'total' => $total,
                'is_closed' => false,
                'unavailable_reason' => null,
            ];
        }

        return [
            'status' => '○',
            'available' => $available,
            'total' => $total,
            'is_closed' => false,
            'unavailable_reason' => null,
        ];
    }

    private function buildMenuSegments($orderedMenus, Carbon $reservationStart, $company): array
    {
        $segments = [];
        $cursor = $reservationStart->copy();

        foreach ($orderedMenus as $menu) {
            $duration = (int) ($menu->duration ?? 0);
            if ($duration <= 0) {
                $duration = (int) ($company->slot_minutes ?? 30);
            }

            $segmentStart = $cursor->copy();
            $segmentEnd   = $cursor->copy()->addMinutes($duration);

            $segments[] = [
                'menu'      => $menu,
                'start_at'  => $segmentStart,
                'end_at'    => $segmentEnd,
                'duration'  => $duration,
            ];

            $cursor = $segmentEnd->copy();
        }

        return $segments;
    }

    private function isSegmentReservableForStaff($company, $staff, Carbon $start, Carbon $end, $shifts, $shiftPatterns, $vacations): bool
    {
        if (!$this->isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations)) {
            return false;
        }

        $status = $this->getBusinessStatus($company, $start, $end);
        if ($status !== 'open') {
            return false;
        }

        $maxSimultaneous = max(1, (int) ($company->max_simultaneous_reservations ?? 1));

        $overlapCount = $this->countReservationDetailOverlaps(
            $company,
            (int) $staff->id,
            $start,
            $end
        );

        return $overlapCount < $maxSimultaneous;
    }

    private function buildSingleStaffAssignmentCandidates($company, array $segments, $staffList, $shifts, $shiftPatterns, $vacations): array
    {
        $rows = [];

        foreach ($staffList as $staff) {
            $ok = true;

            foreach ($segments as $segment) {
                if (!$staff->menus->contains('id', $segment['menu']->id)) {
                    $ok = false;
                    break;
                }

                if (!$this->isSegmentReservableForStaff(
                    $company,
                    $staff,
                    $segment['start_at'],
                    $segment['end_at'],
                    $shifts,
                    $shiftPatterns,
                    $vacations
                )) {
                    $ok = false;
                    break;
                }
            }

            if (!$ok) {
                continue;
            }

            $assignments = [];
            foreach ($segments as $segment) {
                $assignments[] = [
                    'menu_id'    => $segment['menu']->id,
                    'menu_name'  => $segment['menu']->name,
                    'staff_id'   => $staff->id,
                    'staff_name' => $staff->name,
                    'duration'   => $segment['duration'],
                    'start_at'   => $segment['start_at']->format('Y-m-d H:i:s'),
                    'end_at'     => $segment['end_at']->format('Y-m-d H:i:s'),
                ];
            }

            $rows[] = [
                'rank' => count($rows) + 1,
                'label' => $staff->name . ' さんが全メニューを担当',
                'assignments' => $assignments,
            ];
        }

        return array_slice($rows, 0, 10);
    }

    private function buildNoCandidateSummary($company, array $segments, $staffList, $shifts, $shiftPatterns, $vacations): array
    {
        $summary = [];

        foreach ($segments as $segment) {
            $rows = [];

            foreach ($staffList as $staff) {
                $hasMenu = $staff->menus->contains('id', $segment['menu']->id);

                $schedulable = false;
                $businessOpen = false;
                $overlapCount = null;
                $reservable = false;

                if ($hasMenu) {
                    $schedulable = $this->isStaffSchedulable(
                        $staff,
                        $segment['start_at'],
                        $segment['end_at'],
                        $shifts,
                        $shiftPatterns,
                        $vacations
                    );

                    $businessOpen = $this->getBusinessStatus(
                        $company,
                        $segment['start_at'],
                        $segment['end_at']
                    ) === 'open';

                    if ($schedulable && $businessOpen) {
                        $overlapCount = $this->countReservationDetailOverlaps(
                            $company,
                            (int) $staff->id,
                            $segment['start_at'],
                            $segment['end_at']
                        );
                    }

                    $reservable = $this->isSegmentReservableForStaff(
                        $company,
                        $staff,
                        $segment['start_at'],
                        $segment['end_at'],
                        $shifts,
                        $shiftPatterns,
                        $vacations
                    );
                }

                $reason = null;

                if (!$hasMenu) {
                    $reason = 'menu_not_supported';
                } elseif (!$schedulable) {
                    $reason = 'not_schedulable';
                } elseif (!$businessOpen) {
                    $reason = 'outside_business_hours';
                } elseif (($overlapCount ?? 0) >= max(1, (int) ($company->max_simultaneous_reservations ?? 1))) {
                    $reason = 'already_booked';
                } elseif (!$reservable) {
                    $reason = 'not_reservable';
                } else {
                    $reason = 'ok';
                }

                $rows[] = [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->name,
                    'reason' => $reason,
                ];
            }

            $summary[] = [
                'menu_id' => $segment['menu']->id,
                'menu_name' => $segment['menu']->name,
                'start_at' => $segment['start_at']->format('Y-m-d H:i:s'),
                'end_at' => $segment['end_at']->format('Y-m-d H:i:s'),
                'staff_reasons' => $rows,
            ];
        }

        return $summary;
    }

    private function canBeHandledBySplitAssignments($company, array $segments, $staffList, $shifts, $shiftPatterns, $vacations): bool
    {
        foreach ($segments as $segment) {
            $exists = $staffList->contains(function ($staff) use ($company, $segment, $shifts, $shiftPatterns, $vacations) {
                if (!$staff->menus->contains('id', $segment['menu']->id)) {
                    return false;
                }

                return $this->isSegmentReservableForStaff(
                    $company,
                    $staff,
                    $segment['start_at'],
                    $segment['end_at'],
                    $shifts,
                    $shiftPatterns,
                    $vacations
                );
            });

            if (!$exists) {
                return false;
            }
        }

        return true;
    }

    private function scoreAssignmentPattern(array $pattern): int
    {
        $staffUseCounts = [];
        $capabilityScore = 0;

        foreach ($pattern as $row) {
            $staffId = (int) $row['staff_id'];
            $staffUseCounts[$staffId] = ($staffUseCounts[$staffId] ?? 0) + 1;
            $capabilityScore += (int) ($row['staff_menu_count'] ?? 999);
        }

        $duplicatePenalty = 0;
        foreach ($staffUseCounts as $count) {
            if ($count > 1) {
                $duplicatePenalty += ($count - 1) * 100;
            }
        }

        return $capabilityScore + $duplicatePenalty;
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
}
