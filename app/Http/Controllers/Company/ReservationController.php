<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
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

        $query = Reservation::with(['staff', 'customer', 'menus'])
            ->where('company_id', $company->id);

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
            ->orderBy('start_at', 'desc')
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
            'todayReservedCount' => $todayReservedCount,
            'tomorrowReservedCount' => $tomorrowReservedCount,
            'dayAfterTomorrowReservedCount' => $dayAfterTomorrowReservedCount,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'dayAfterTomorrow' => $dayAfterTomorrow,
        ]);
    }

    public function cancelFromList($id)
    {
        $company = auth()->guard('company')->user()->company;

        $reservation = Reservation::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if (!$reservation) {
            return redirect()
                ->route('company.reservations.index')
                ->withErrors(['reservation' => '予約が見つかりません。']);
        }

        if ($reservation->status === 'cancelled') {
            return redirect()
                ->route('company.reservations.index')
                ->with('success', 'この予約はすでにキャンセル済みです。');
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return redirect()
            ->route('company.reservations.index')
            ->with('success', '予約をキャンセルしました。');
    }

    /* ==========================================================
       予約制御設定
    ========================================================== */
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

    /* ==========================================================
       カレンダー表示
    ========================================================== */
    public function calendar(Request $request)
    {
        try {
            $mode = $request->get('mode', 'week');

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

    /* ==========================================================
       カレンダーデータ
    ========================================================== */
    public function calendarData(Request $request)
    {
        $company = auth()->guard('company')->user()->company;
        $mode = $request->get('mode', 'week');
        $menuIds = $request->menu_ids ?? [];

        if ($mode === 'day') {
            $date = $request->date
                ? Carbon::parse($request->date)
                : now();

            $limits = $this->getReservationLimits($company);

            if ($date->copy()->startOfDay() < $limits['start'] || $date->copy()->startOfDay() > $limits['end']) {
                return response()->json([
                    'staffs' => [],
                    'slots' => [],
                ]);
            }

            $staffList = Staff::where('company_id', $company->id)
                ->where('is_reservable', true)
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
                                'total' => max(1, (int) ($company->max_simultaneous_reservations ?? 1)),
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

                        $reservation = $reservations
                            ->where('staff_id', $staff->id)
                            ->first(function ($r) use ($slotStart, $slotEnd) {
                                return $r->start_at < $slotEnd &&
                                       $r->end_at > $slotStart;
                            });

                        $staffReservedCount = $reservations
                            ->where('staff_id', $staff->id)
                            ->filter(function ($r) use ($slotStart, $slotEnd) {
                                return $r->start_at < $slotEnd &&
                                       $r->end_at > $slotStart;
                            })
                            ->count();

                        $perStaffLimit = max(1, (int) ($company->max_simultaneous_reservations ?? 1));

                        if ($reservation && $staffReservedCount >= $perStaffLimit) {
                            $data[$slotStart->format('H:i')][$staff->id] = [
                                'status'            => '×',
                                'reservation_id'    => $reservation->id,
                                'customer_name'     => $reservation->customer_name,
                                'customer_phone'    => $reservation->customer_phone,
                                'staff_name'        => $staff->name,
                                'reservation_start' => optional($reservation->start_at)->format('Y-m-d H:i'),
                                'available'         => 0,
                                'total'             => $perStaffLimit,
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
                ->where('is_reservable', true);

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
                            ];

                            $time->addMinutes($minutes);
                            continue;
                        }

                        $status = $this->getBusinessStatus($company, $slotStart, $slotEnd);

                        if ($status !== 'open') {
                            $data[$time->format('H:i')][$day->format('Y-m-d')] = [
                                'status' => '×',
                                'available' => 0,
                                'total' => count($staffList) * max(1, (int) ($company->max_simultaneous_reservations ?? 1)),
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

    /* ==========================================================
       予約登録
    ========================================================== */
    public function store(Request $request)
    {
        $company = Auth::guard('company')->user()->company;

        try {
            $request->validate([
                'start_at'       => 'required|date',
                'customer_name'  => 'required|string|max:255',
                'customer_phone' => 'required|string|max:50',
                'menu_ids'       => 'nullable|array',
                'menu_ids.*'     => 'integer',
                'staff_id'       => 'nullable|integer',
            ]);

            $start = Carbon::parse($request->start_at);

            $menus = Menu::where('company_id', $company->id)
                ->whereIn('id', $request->menu_ids ?? [])
                ->get();

            $duration = (int) $menus->sum('duration');
            $price    = (int) $menus->sum('price');

            if ($duration <= 0) {
                $duration = (int) ($company->slot_minutes ?? 30);
            }

            $end = $start->copy()->addMinutes($duration);

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
                ->orderBy('priority_order')
                ->get();

            if ($staffList->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '予約可能なスタッフがいません',
                ], 422);
            }

            $staffIds = $staffList->pluck('id');

            $todayReservations = Reservation::where('company_id', $company->id)
                ->whereDate('start_at', $start->toDateString())
                ->where('status', 'reserved')
                ->select('staff_id', DB::raw('count(*) as total'))
                ->groupBy('staff_id')
                ->pluck('total', 'staff_id');

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

            $requestedStaffId = $request->staff_id ? (int) $request->staff_id : null;
            $maxSimultaneous = max(1, (int) ($company->max_simultaneous_reservations ?? 1));

            DB::transaction(function () use (
                $request,
                $company,
                $start,
                $end,
                $price,
                $menus,
                $staffList,
                $todayReservations,
                $shifts,
                $shiftPatterns,
                $vacations,
                $requestedStaffId,
                $maxSimultaneous
            ) {
                if ($requestedStaffId) {
                    $staff = $staffList->firstWhere('id', $requestedStaffId);

                    if (!$staff) {
                        throw new \Exception('担当者が見つかりません');
                    }

                    if (!$this->isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations)) {
                        throw new \Exception('この担当者はその時間に対応できません');
                    }

                    $staffOverlapCount = Reservation::where('company_id', $company->id)
                        ->where('staff_id', $staff->id)
                        ->where('status', 'reserved')
                        ->where(function ($q) use ($start, $end) {
                            $q->where('start_at', '<', $end)
                              ->where('end_at', '>', $start);
                        })
                        ->lockForUpdate()
                        ->count();

                    if ($staffOverlapCount >= $maxSimultaneous) {
                        throw new \Exception('この担当者の同時予約上限に達しています');
                    }

                    $assignedStaff = $staff;
                } else {
                    $sortedStaffList = $staffList->sortBy(function ($staff) use ($todayReservations) {
                        return (int) ($todayReservations[$staff->id] ?? 0);
                    });

                    $assignedStaff = null;

                    foreach ($sortedStaffList as $staff) {
                        if (!$this->isStaffSchedulable($staff, $start, $end, $shifts, $shiftPatterns, $vacations)) {
                            continue;
                        }

                        $staffOverlapCount = Reservation::where('company_id', $company->id)
                            ->where('staff_id', $staff->id)
                            ->where('status', 'reserved')
                            ->where(function ($q) use ($start, $end) {
                                $q->where('start_at', '<', $end)
                                  ->where('end_at', '>', $start);
                            })
                            ->lockForUpdate()
                            ->count();

                        if ($staffOverlapCount >= $maxSimultaneous) {
                            continue;
                        }

                        $assignedStaff = $staff;
                        break;
                    }

                    if (!$assignedStaff) {
                        throw new \Exception('空いているスタッフがいません');
                    }
                }

                $nominationFee = (int) ($assignedStaff->nomination_fee ?? 0);
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
                    $customer->save();
                } else {
                    $customer = Customer::create([
                        'company_id'  => $company->id,
                        'name'        => $request->customer_name,
                        'phone'       => $normalizedPhone,
                        'email'       => $request->email ?? null,
                        'visit_count' => 1,
                        'last_visit'  => $start,
                    ]);
                }

                $reservation = Reservation::create([
                    'company_id'     => $company->id,
                    'staff_id'       => $assignedStaff->id,
                    'customer_name'  => $request->customer_name,
                    'customer_phone' => $normalizedPhone,
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

                foreach ($menus as $menu) {
                    $attachData[$menu->id] = [
                        'price'      => $menu->price,
                        'duration'   => $menu->duration,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
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

    public function cancel($id)
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

        $reservation->status = 'cancelled';
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

            $overlapCount = Reservation::where('company_id', $company->id)
                ->where('staff_id', $staff->id)
                ->where('status', 'reserved')
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_at', '<', $end)
                      ->where('end_at', '>', $start);
                })
                ->count();

            if ($overlapCount < $maxSimultaneous) {
                $availableStaff[] = $staff;
            }
        }

        return response()->json($availableStaff);
    }

    /* ==========================================================
       営業判定
    ========================================================== */
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

    /* ==========================================================
       スタッフがその時間に対応可能か
    ========================================================== */
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

    /* ==========================================================
       空き判定
    ========================================================== */
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

            $staffReservedCount = $reservations
                ->where('staff_id', $staff->id)
                ->filter(function ($r) use ($start, $end) {
                    return $r->start_at < $end &&
                           $r->end_at > $start;
                })
                ->count();

            $staffRemaining = max(0, $maxSimultaneous - $staffReservedCount);
            $available += $staffRemaining;
        }

        $total = $workingStaff * $maxSimultaneous;

        if ($workingStaff <= 0 || $available <= 0) {
            return [
                'status' => '×',
                'available' => 0,
                'total' => $total,
            ];
        }

        if ($available === 1) {
            return [
                'status' => '△',
                'available' => 1,
                'total' => $total,
            ];
        }

        return [
            'status' => '○',
            'available' => $available,
            'total' => $total,
        ];
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