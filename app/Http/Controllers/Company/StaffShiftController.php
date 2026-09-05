<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\ShiftPattern;
use App\Models\StaffShift;
use App\Models\StaffDefaultShift;
use App\Models\Vacation;
use App\Models\CompanyBusinessCalendar;
use App\Services\ReservationChangeNoticeService;
use Barryvdh\DomPDF\Facade\Pdf;


class StaffShiftController extends Controller
{
    public function __construct(
        protected ReservationChangeNoticeService $changeNoticeService
    ) {
    }

    /*
    勤務管理画面
    */
    public function index(Request $request)
    {
        $loginStaff = auth()->guard('company')->user();
        abort_if(!$loginStaff || !$loginStaff->canDashboard('card.month_shift'), 403);

        $company = $loginStaff->company;

        $month = $request->month ?? now()->format('Y-m');

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->orderBy('priority_order')
            ->orderBy('id')
            ->get();

        $staffIds = $staffs->pluck('id');

        $patterns = ShiftPattern::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $vacations = Vacation::whereIn('staff_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            })
            ->get()
            ->groupBy('staff_id');

        $businessDays = CompanyBusinessCalendar::where('company_id', $company->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d H:i:s');
            });

        $shifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy([
                'staff_id',
                fn ($row) => Carbon::parse($row->date)->format('Y-m-d'),
            ]);

        return view('company.staff_shifts', [
            'month' => $month,
            'staffs' => $staffs,
            'patterns' => $patterns,
            'shifts' => $shifts,
            'vacations' => $vacations,
            'businessDays' => $businessDays,
        ]);
    }

    /*
    閲覧専用 勤務管理画面
    */
    public function view(Request $request)
    {
        $loginStaff = auth()->guard('company')->user();
        abort_if(!$loginStaff || !$loginStaff->canDashboard('card.month_shift_view'), 403);

        $company = $loginStaff->company;

        $month = $request->query('month', now()->format('Y-m'));
        $topStaffId = (int) $request->query('top_staff_id', $loginStaff->id);

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$topStaffId])
            ->orderByRaw("
                CASE role
                    WHEN 'master' THEN 1
                    WHEN 'chief' THEN 2
                    WHEN 'area_leader' THEN 3
                    WHEN 'leader' THEN 4
                    WHEN 'staff' THEN 5
                    ELSE 99
                END
            ")
            ->orderByRaw('CAST(NULLIF(staff_code, "") AS UNSIGNED)')
            ->orderBy('staff_code')
            ->orderBy('id')
            ->get();

        $staffIds = $staffs->pluck('id');

        $patterns = ShiftPattern::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $defaultShifts = StaffDefaultShift::whereIn('staff_id', $staffIds)
            ->get()
            ->groupBy([
                'staff_id',
                fn ($row) => (int) $row->weekday,
            ]);

        $vacations = Vacation::whereIn('staff_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            })
            ->get()
            ->groupBy('staff_id');

        $businessDays = CompanyBusinessCalendar::where('company_id', $company->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d H:i:s');
            });

        $shifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy([
                'staff_id',
                fn ($row) => Carbon::parse($row->date)->format('Y-m-d'),
            ]);

        return view('company.staff_shifts_view', [
            'company' => $company,
            'month' => $month,
            'staffs' => $staffs,
            'patterns' => $patterns,
            'defaultShifts' => $defaultShifts,
            'shifts' => $shifts,
            'vacations' => $vacations,
            'businessDays' => $businessDays,
            'topStaffId' => $topStaffId,
            'loginStaffId' => (int) $loginStaff->id,
        ]);
    }

    /*
    勤務管理表 PDF
    */
    public function pdf(Request $request)
    {
        $loginStaff = auth()->guard('company')->user();
        abort_if(!$loginStaff || !$loginStaff->canDashboard('card.month_shift_view'), 403);

        $company = $loginStaff->company;

        $month = $request->query('month', now()->format('Y-m'));
        $topStaffId = (int) $request->query('top_staff_id', $loginStaff->id);

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$topStaffId])
            ->orderByRaw("
                CASE role
                    WHEN 'master' THEN 1
                    WHEN 'chief' THEN 2
                    WHEN 'area_leader' THEN 3
                    WHEN 'leader' THEN 4
                    WHEN 'staff' THEN 5
                    ELSE 99
                END
            ")
            ->orderByRaw('CAST(NULLIF(staff_code, "") AS UNSIGNED)')
            ->orderBy('staff_code')
            ->orderBy('id')
            ->get();

        $staffIds = $staffs->pluck('id');

        $patterns = ShiftPattern::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $defaultShifts = StaffDefaultShift::whereIn('staff_id', $staffIds)
            ->get()
            ->groupBy([
                'staff_id',
                fn ($row) => (int) $row->weekday,
            ]);

        $vacations = Vacation::whereIn('staff_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhereBetween('end_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_at', '<=', $start)
                            ->where('end_at', '>=', $end);
                    });
            })
            ->get()
            ->groupBy('staff_id');

        $businessDays = CompanyBusinessCalendar::where('company_id', $company->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d H:i:s');
            });

        $shifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy([
                'staff_id',
                fn ($row) => Carbon::parse($row->date)->format('Y-m-d'),
            ]);

        $pdf = Pdf::loadView('company.staff_shifts_pdf', [
            'company' => $company,
            'month' => $month,
            'staffs' => $staffs,
            'patterns' => $patterns,
            'defaultShifts' => $defaultShifts,
            'shifts' => $shifts,
            'vacations' => $vacations,
            'businessDays' => $businessDays,
            'topStaffId' => $topStaffId,
            'loginStaffId' => (int) $loginStaff->id,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('staff_shift_' . $month . '.pdf');
    }

    /*
    基本シフトから勤務管理生成
    */
    public function generate(Request $request)
    {
        $loginStaff = auth()->guard('company')->user();
        abort_if(!$loginStaff || !$loginStaff->canDashboard('card.month_shift'), 403);

        $company = $loginStaff->company;

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $request->month;

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $days = $start->daysInMonth;

        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->orderBy('priority_order')
            ->orderBy('id')
            ->get();

        foreach ($staffs as $staff) {
            for ($d = 1; $d <= $days; $d++) {
                $date = Carbon::parse($month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT));
                $weekday = $date->dayOfWeek;

                $default = StaffDefaultShift::where('staff_id', $staff->id)
                    ->where('weekday', $weekday)
                    ->first();

                if (!$default) {
                    continue;
                }

                StaffShift::updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'shift_pattern_id' => $default->shift_pattern_id,
                        'is_work' => (int) $default->is_work,
                    ]
                );
            }
        }

        return back()->with('success', '基本シフトから月シフトを生成しました');
    }

    /*
    保存
    */
    public function update(Request $request)
    {
        $loginStaff = auth()->guard('company')->user();
        abort_if(!$loginStaff || !$loginStaff->canDashboard('card.month_shift'), 403);

        $company = $loginStaff->company;
        $createdNoticeCount = 0;

        if (empty($request->shifts) || !is_array($request->shifts)) {
            return back()->with('success', 'シフトを保存しました');
        }

        $validStaffIds = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->pluck('id')
            ->map(fn ($id) => (string) $id);
        $validPatterns = ShiftPattern::where('company_id', $company->id)->get()->keyBy('id');
        $validPatternIds = $validPatterns->keys()->map(fn ($id) => (string) $id);

        foreach ($request->shifts as $staffId => $dates) {
            if (!$validStaffIds->contains((string) $staffId) || !is_array($dates)) {
                continue;
            }

            $staff = Staff::where('company_id', $company->id)
                ->where('role', '!=', 'store_operator')
                ->find($staffId);
            if (!$staff) {
                continue;
            }

            foreach ($dates as $date => $patternId) {
                $dateString = Carbon::parse($date)->format('Y-m-d');

                if ($patternId !== null && $patternId !== '' && !$validPatternIds->contains((string) $patternId)) {
                    continue;
                }

                $existing = StaffShift::where('staff_id', $staffId)
                    ->whereDate('date', $dateString)
                    ->first();

                $previousPatternId = null;

                if ($existing) {
                    $wasWorking = (bool) $existing->is_work;
                    $previousPatternId = $existing->shift_pattern_id ? (int) $existing->shift_pattern_id : null;
                } else {
                    $weekday = Carbon::parse($dateString)->dayOfWeek;

                    $defaultShift = StaffDefaultShift::where('staff_id', $staffId)
                        ->where('weekday', $weekday)
                        ->first();

                    $wasWorking = (bool) ($defaultShift->is_work ?? false);
                    $previousPatternId = $defaultShift?->shift_pattern_id ? (int) $defaultShift->shift_pattern_id : null;
                }

                $isWork = ($patternId !== null && $patternId !== '') ? 1 : 0;
                $newPatternId = $isWork ? (int) $patternId : null;

                StaffShift::updateOrCreate(
                    [
                        'staff_id' => $staffId,
                        'date' => $dateString,
                    ],
                    [
                        'shift_pattern_id' => $newPatternId,
                        'is_work' => $isWork,
                    ]
                );

                if ($wasWorking && !$isWork) {
                    $notice = $this->changeNoticeService->createForStaffShiftOff(
                        company: $company,
                        staff: $staff,
                        date: $dateString,
                        reasonText: $staff->name . ' のシフト変更により、ご予約内容の変更をお願いしております。'
                    );

                    if ($notice) {
                        $createdNoticeCount++;
                    }
                }

                if ($wasWorking && $isWork && $previousPatternId !== $newPatternId) {
                    $newPattern = $validPatterns->get($newPatternId);

                    if ($newPattern && $newPattern->start_time && $newPattern->end_time) {
                        $notice = $this->changeNoticeService->createForStaffShiftTimeChange(
                            company: $company,
                            staff: $staff,
                            date: $dateString,
                            startTime: $newPattern->start_time,
                            endTime: $newPattern->end_time,
                            reasonText: $staff->name . ' のシフト時間変更により、ご予約内容の変更をお願いしております。'
                        );

                        if ($notice) {
                            $createdNoticeCount++;
                        }
                    }
                }
            }
        }

        $message = 'シフトを保存しました';

        if ($createdNoticeCount > 0) {
            $message .= '（予約変更連絡管理を ' . $createdNoticeCount . ' 件作成しました）';
        }

        return back()->with('success', $message);
    }

    /*
    前月コピー
    */
    public function copy(Request $request)
    {
        $loginStaff = auth()->guard('company')->user();
        abort_if(!$loginStaff || !$loginStaff->canDashboard('card.month_shift'), 403);

        $company = $loginStaff->company;

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $request->month;

        $currentStart = Carbon::parse($month . '-01')->startOfMonth();
        $currentEnd = $currentStart->copy()->endOfMonth();

        $prevStart = $currentStart->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        $staffIds = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->pluck('id');

        $prevShifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->get()
            ->groupBy([
                'staff_id',
                fn ($row) => Carbon::parse($row->date)->format('Y-m-d'),
            ]);

        foreach ($staffIds as $staffId) {
            $date = $currentStart->copy();

            while ($date->lte($currentEnd)) {
                $targetDate = $date->copy();
                $targetDay = $targetDate->day;

                $shift = null;

                // まずは同日コピーを優先
                if ($targetDay <= $prevEnd->day) {
                    $sameDay = $prevStart->copy()->day($targetDay)->format('Y-m-d');
                    $shift = $prevShifts[$staffId][$sameDay][0] ?? null;
                }

                // 足りない末日分だけ、曜日で補完
                if (!$shift) {
                    $weekday = $targetDate->dayOfWeek;

                    $cursor = $prevEnd->copy();
                    while ($cursor->gte($prevStart)) {
                        if ($cursor->dayOfWeek === $weekday) {
                            $sourceKey = $cursor->format('Y-m-d');
                            $shift = $prevShifts[$staffId][$sourceKey][0] ?? null;

                            if ($shift) {
                                break;
                            }
                        }
                        $cursor->subDay();
                    }
                }

                if ($shift) {
                    StaffShift::updateOrCreate(
                        [
                            'staff_id' => $staffId,
                            'date' => $targetDate->format('Y-m-d'),
                        ],
                        [
                            'shift_pattern_id' => $shift->shift_pattern_id,
                            'is_work' => $shift->is_work,
                        ]
                    );
                }

                $date->addDay();
            }
        }

        return back()->with('success', '前月シフトをコピーしました');
    }
}
