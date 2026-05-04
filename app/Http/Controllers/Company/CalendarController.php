<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\CompanyBusinessCalendar;
use App\Services\ReservationChangeNoticeService;
use Carbon\Carbon;
use Yasumi\Yasumi;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function __construct(
        protected ReservationChangeNoticeService $changeNoticeService
    ) {
    }

    /**
     * 月表示
     */
    public function index(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $year  = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $current = Carbon::create($year, $month, 1);

        $start = $current->copy()->startOfMonth();
        $end   = $current->copy()->endOfMonth();

        $calendars = CompanyBusinessCalendar::where('company_id', $company->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn ($item) => $item->date->format('Y-m-d'));

        $reservationCounts = Reservation::where('company_id', $company->id)
            ->whereBetween('start_at', [$start, $end])
            ->where('status', 'reserved')
            ->get()
            ->groupBy(function ($r) {
                return Carbon::parse($r->start_at)->format('Y-m-d');
            })
            ->map(function ($items) {
                return $items->count();
            });

        $holidayDates = [];
        $holidayNames = [];

        $holidays = Yasumi::create('Japan', $year, 'ja_JP');

        $date = $start->copy();

        while ($date <= $end) {
            if ($holidays->isHoliday($date)) {
                $dateStr = $date->format('Y-m-d');

                $holidayDates[] = $dateStr;

                foreach ($holidays->on($date) as $holiday) {
                    $holidayNames[$dateStr] = $holiday->getName();
                    break;
                }
            }

            $date->addDay();
        }

        return view('company.calendar.index', [
            'year' => $year,
            'month' => $month,
            'current' => $current,
            'prev' => $current->copy()->subMonth(),
            'next' => $current->copy()->addMonth(),
            'daysInMonth' => $current->daysInMonth,
            'startDayOfWeek' => $current->dayOfWeek,
            'calendars' => $calendars,
            'holidayDates' => $holidayDates,
            'holidayNames' => $holidayNames,
            'reservationCounts' => $reservationCounts,
        ]);
    }

    /**
     * 営業日トグル
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $company = auth()->guard('company')->user()->company;
        $targetDate = Carbon::parse($request->date);
        $weekday = $targetDate->dayOfWeek;

        $calendar = CompanyBusinessCalendar::firstOrCreate([
            'company_id' => $company->id,
            'date' => $request->date,
        ], [
            'is_open' => true
        ]);

        $calendar->is_open = !$calendar->is_open;

        // 休業日 → 営業日に戻したとき、営業時間が空なら通常営業時間を自動補完
        if ((int) $calendar->is_open === 1) {
            $needsTime = empty($calendar->open_time) || empty($calendar->close_time);

            if ($needsTime) {
                $patterns = (array) ($company->open_patterns[$weekday] ?? []);

                $firstPattern = collect($patterns)->first(function ($p) {
                    return !empty($p['open']) && !empty($p['close']);
                });

                if ($firstPattern) {
                    $calendar->open_time = $firstPattern['open'];
                    $calendar->close_time = $firstPattern['close'];
                }
            }
        }

        $calendar->save();

        $createdNotice = null;

        if (!(bool) $calendar->is_open) {
            $createdNotice = $this->changeNoticeService->createForClosedDate(
                company: $company,
                date: $request->date,
                reasonText: '営業日が休業日に変更されたため、ご予約内容の変更をお願いしております。'
            );
        }

        return response()->json([
            'success' => true,
            'is_open' => $calendar->is_open,
            'open_time' => $calendar->open_time,
            'close_time' => $calendar->close_time,
            'change_notice_created' => !is_null($createdNotice),
        ]);
    }

    /**
     * 年表示
     */
    public function year(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);

        return view('company.calendar.year', compact('year'));
    }

    /**
     * 営業時間更新
     */
    public function updateTime(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'open_time' => 'nullable',
            'close_time' => 'nullable',
        ]);

        $company = auth()->guard('company')->user()->company;

        $calendar = CompanyBusinessCalendar::firstOrCreate([
            'company_id' => $company->id,
            'date' => $request->date,
        ]);

        $calendar->open_time = $request->open_time;
        $calendar->close_time = $request->close_time;

        // 時間を設定したら営業日扱いに戻す
        if (!empty($request->open_time) && !empty($request->close_time)) {
            $calendar->is_open = true;
        }

        $calendar->save();

        $createdNotice = $this->changeNoticeService->createForTimeChange(
            company: $company,
            date: $request->date,
            openTime: $request->open_time,
            closeTime: $request->close_time,
            reasonText: '営業時間変更のため、ご予約内容の変更をお願いしております。'
        );

        return response()->json([
            'success' => true,
            'is_open' => $calendar->is_open,
            'change_notice_created' => !is_null($createdNotice),
        ]);
    }

    /**
     * 曜日一括休日設定
     */
    public function bulkWeekday(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer',
            'weekday' => 'required|integer|min:0|max:6',
        ]);

        $company = auth()->guard('company')->user()->company;

        $year = (int) $request->year;
        $month = (int) $request->month;
        $weekday = (int) $request->weekday;

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $date = $start->copy();
        $createdCount = 0;

        while ($date <= $end) {
            if ($date->dayOfWeek === $weekday) {
                CompanyBusinessCalendar::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'is_open' => false
                    ]
                );

                $notice = $this->changeNoticeService->createForClosedDate(
                    company: $company,
                    date: $date->format('Y-m-d'),
                    reasonText: '営業日が休業日に一括変更されたため、ご予約内容の変更をお願いしております。'
                );

                if ($notice) {
                    $createdCount++;
                }
            }

            $date->addDay();
        }

        return response()->json([
            'success' => true,
            'change_notice_created_count' => $createdCount,
        ]);
    }

    /**
     * 年間 曜日一括休日設定
     */
    public function bulkYearWeekday(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'weekday' => 'required|integer|min:0|max:6',
        ]);

        $company = auth()->guard('company')->user()->company;

        $year = (int) $request->year;
        $weekday = (int) $request->weekday;

        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end   = $start->copy()->endOfYear();

        $date = $start->copy();
        $createdCount = 0;

        while ($date <= $end) {
            if ($date->dayOfWeek === $weekday) {
                CompanyBusinessCalendar::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'is_open' => false
                    ]
                );

                $notice = $this->changeNoticeService->createForClosedDate(
                    company: $company,
                    date: $date->format('Y-m-d'),
                    reasonText: '営業日が休業日に一括変更されたため、ご予約内容の変更をお願いしております。'
                );

                if ($notice) {
                    $createdCount++;
                }
            }

            $date->addDay();
        }

        return response()->json([
            'success' => true,
            'change_notice_created_count' => $createdCount,
        ]);
    }

    /**
     * 年間 曜日一括営業日設定
     */
    public function bulkYearOpenWeekday(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'weekday' => 'required|integer|min:0|max:6',
        ]);

        $company = auth()->guard('company')->user()->company;

        $year = (int) $request->year;
        $weekday = (int) $request->weekday;

        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end   = $start->copy()->endOfYear();

        $date = $start->copy();

        while ($date <= $end) {
            if ($date->dayOfWeek === $weekday) {
                $payload = [
                    'is_open' => true,
                ];

                $patterns = (array) ($company->open_patterns[$weekday] ?? []);
                $firstPattern = collect($patterns)->first(function ($p) {
                    return !empty($p['open']) && !empty($p['close']);
                });

                if ($firstPattern) {
                    $payload['open_time'] = $firstPattern['open'];
                    $payload['close_time'] = $firstPattern['close'];
                }

                CompanyBusinessCalendar::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    $payload
                );
            }

            $date->addDay();
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function deleteTime(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $calendar = CompanyBusinessCalendar::where('company_id', $company->id)
            ->where('date', $request->date)
            ->first();

        if ($calendar) {
            $calendar->open_time = null;
            $calendar->close_time = null;
            $calendar->save();
        }

        return response()->json(['success' => true]);
    }

    public function assignmentCandidates(Request $request)
    {
        $company = auth('company')->user();

        $datetime = $request->query('datetime');
        $menuIds = collect($request->query('menu_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if (!$datetime || $menuIds->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => '日時またはメニューが不正です。',
                'mode' => 'single',
                'candidates' => [],
            ], 422);
        }

        $startAt = Carbon::parse($datetime);

        $menus = Menu::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $menuIds)
            ->get()
            ->keyBy('id');

        if ($menus->count() !== $menuIds->count()) {
            return response()->json([
                'ok' => false,
                'message' => 'メニュー情報が見つかりません。',
                'mode' => 'single',
                'candidates' => [],
            ], 404);
        }

        $candidatesByMenu = [];
        $allAvailableStaff = collect();

        foreach ($menuIds as $menuId) {
            $baseStaff = Staff::query()
                ->where('company_id', $company->id)
                ->where('is_reservable', 1)
                ->where('role', '!=', 'store_operator')
                ->whereHas('menus', function ($q) use ($menuId) {
                    $q->where('menus.id', $menuId);
                })
                ->get();

            Log::info('assignmentCandidates base staff', [
                'menu_id' => $menuId,
                'staff_ids' => $baseStaff->pluck('id')->all(),
                'staff_names' => $baseStaff->pluck('name')->all(),
            ]);

            $staffList = $baseStaff
                ->filter(function ($staff) use ($startAt, $menus, $menuId, $company) {
                    $duration = (int) ($menus[$menuId]->duration ?: $company->slot_minutes ?: 30);
                    $ok = $this->isStaffAvailable($staff, $startAt, $duration);
                    return $ok;
                })
                ->values();

            $candidatesByMenu[$menuId] = $staffList;
            $allAvailableStaff = $allAvailableStaff->merge($staffList);

            if ($staffList->isEmpty()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'このメニュー内容で対応できる担当パターンが見つかりませんでした。',
                    'mode' => $menuIds->count() <= 1 ? 'single' : 'multi',
                    'candidates' => [],
                ]);
            }
        }

        $patterns = $this->buildAssignmentPatterns($menuIds->all(), $candidatesByMenu);

        if (empty($patterns)) {
            return response()->json([
                'ok' => true,
                'message' => 'このメニュー内容で対応できる担当パターンが見つかりませんでした。',
                'mode' => $menuIds->count() <= 1 ? 'single' : 'multi',
                'candidates' => [],
            ]);
        }

        $patterns = $this->sortAssignmentPatterns($patterns);

        $isSingleMenu = $menuIds->count() <= 1;

        $candidates = collect($patterns)
            ->values()
            ->map(function ($pattern, $index) use ($menus, $isSingleMenu) {
                $assignments = collect($pattern)
                    ->values()
                    ->map(function ($row, $rowIndex) use ($menus) {
                        $menuId = $row['menu_id'] ?? null;
                        $staffId = $row['staff_id'] ?? null;
                        $staffName = $row['staff_name'] ?? '担当者';

                        return [
                            'menu_id' => $menuId,
                            'menu_name' => $menus[$menuId]->name ?? ('メニュー' . ($rowIndex + 1)),
                            'staff_id' => $staffId,
                            'staff_name' => $staffName,
                        ];
                    })
                    ->all();

                return [
                    'rank' => $index + 1,
                    'label' => $isSingleMenu
                        ? '担当者候補'
                        : (count($assignments) > 1 ? '複数担当' : '単独担当'),
                    'assignments' => $assignments,
                ];
            })
            ->all();

        return response()->json([
            'ok' => true,
            'message' => null,
            'mode' => $isSingleMenu ? 'single' : 'multi',
            'candidates' => $candidates,
        ]);
    }

    protected function isStaffAvailable($staff, $startAt, int $durationMinutes): bool
    {
        $start = $startAt instanceof \Carbon\Carbon
            ? $startAt->copy()
            : \Carbon\Carbon::parse($startAt);

        $end = (clone $start)->addMinutes($durationMinutes);

        $hasVacation = \App\Models\Vacation::query()
            ->where('staff_id', $staff->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            })
            ->exists();

        if ($hasVacation) {
            \Log::info('isStaffAvailable NG: vacation', [
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
            return false;
        }

        $shift = \App\Models\StaffShift::query()
            ->where('staff_id', $staff->id)
            ->whereDate('date', $start->toDateString())
            ->first();

        if ($shift) {
            if ((int) $shift->is_work !== 1) {
                \Log::info('isStaffAvailable NG: shift is_work=0', [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->name,
                    'target_date' => $start->toDateString(),
                    'shift_date' => $shift->date,
                ]);
                return false;
            }

            if (!empty($shift->shift_pattern_id)) {
                $pattern = \App\Models\ShiftPattern::query()
                    ->where('id', $shift->shift_pattern_id)
                    ->first();

                if ($pattern && !empty($pattern->start_time) && !empty($pattern->end_time)) {
                    $shiftStart = \Carbon\Carbon::parse($start->toDateString() . ' ' . $pattern->start_time);
                    $shiftEnd   = \Carbon\Carbon::parse($start->toDateString() . ' ' . $pattern->end_time);

                    if ($start < $shiftStart || $end > $shiftEnd) {
                        return false;
                    }
                }
            }
        }

        $hasReservation = \App\Models\ReservationDetail::query()
            ->where('staff_id', $staff->id)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            })
            ->whereHas('reservation', function ($q) {
                $q->where('status', 'reserved');
            })
            ->exists();

        if ($hasReservation) {
            \Log::info('isStaffAvailable NG: already reserved', [
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
            return false;
        }

        \Log::info('isStaffAvailable OK', [
            'staff_id' => $staff->id,
            'staff_name' => $staff->name,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ]);

        return true;
    }

    protected function buildAssignmentPatterns(array $menuIds, array $candidatesByMenu): array
    {
        $results = [];

        $walk = function (int $index, array $current) use (&$walk, &$results, $menuIds, $candidatesByMenu) {
            if ($index >= count($menuIds)) {
                $results[] = $current;
                return;
            }

            $menuId = $menuIds[$index];
            $candidates = $candidatesByMenu[$menuId] ?? collect();

            foreach ($candidates as $staff) {
                $next = $current;
                $next[] = [
                    'menu_id' => $menuId,
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->name,
                ];

                $walk($index + 1, $next);
            }
        };

        $walk(0, []);

        return $results;
    }

    protected function sortAssignmentPatterns(array $patterns): array
    {
        usort($patterns, function ($a, $b) {
            $scoreA = $this->scoreAssignmentPattern($a);
            $scoreB = $this->scoreAssignmentPattern($b);

            if ($scoreA === $scoreB) {
                return 0;
            }

            return $scoreA < $scoreB ? -1 : 1;
        });

        return $patterns;
    }

    protected function scoreAssignmentPattern(array $pattern): int
    {
        $staffIds = array_column($pattern, 'staff_id');
        $uniqueStaffIds = array_unique($staffIds);

        $duplicateCount = count($staffIds) - count($uniqueStaffIds);

        $menuCapabilityScore = 0;

        foreach ($pattern as $row) {
            $staff = \App\Models\Staff::withCount('menus')->find($row['staff_id']);
            $menuCapabilityScore += (int) ($staff->menus_count ?? 999);
        }

        return ($duplicateCount * 10000) + $menuCapabilityScore;
    }
}
