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

class StaffShiftController extends Controller
{
    public function __construct(
        protected ReservationChangeNoticeService $changeNoticeService
    ) {
    }

    /*
    月シフト画面
    */
    public function index(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $month = $request->month ?? now()->format('Y-m');

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $staffs = Staff::where('company_id', $company->id)
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
    基本シフトから月シフト生成
    */
    public function generate(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $request->month;

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $days = $start->daysInMonth;

        $staffs = Staff::where('company_id', $company->id)
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
        $company = auth()->guard('company')->user()->company;
        $createdNoticeCount = 0;

        if (empty($request->shifts) || !is_array($request->shifts)) {
            return back()->with('success', 'シフトを保存しました');
        }

        $validStaffIds = Staff::where('company_id', $company->id)->pluck('id')->map(fn ($id) => (string) $id);
        $validPatternIds = ShiftPattern::where('company_id', $company->id)->pluck('id')->map(fn ($id) => (string) $id);

        foreach ($request->shifts as $staffId => $dates) {
            if (!$validStaffIds->contains((string) $staffId) || !is_array($dates)) {
                continue;
            }

            $staff = Staff::where('company_id', $company->id)->find($staffId);
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

                if ($existing) {
                    $wasWorking = (bool) $existing->is_work;
                } else {
                    $weekday = Carbon::parse($dateString)->dayOfWeek;

                    $defaultShift = StaffDefaultShift::where('staff_id', $staffId)
                        ->where('weekday', $weekday)
                        ->first();

                    $wasWorking = (bool) ($defaultShift->is_work ?? false);
                }

                $isWork = ($patternId !== null && $patternId !== '') ? 1 : 0;

                StaffShift::updateOrCreate(
                    [
                        'staff_id' => $staffId,
                        'date' => $dateString,
                    ],
                    [
                        'shift_pattern_id' => $isWork ? $patternId : null,
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
        $company = auth()->guard('company')->user()->company;

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $request->month;

        $currentStart = Carbon::parse($month . '-01')->startOfMonth();
        $currentEnd = $currentStart->copy()->endOfMonth();

        $prevStart = $currentStart->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        $staffIds = Staff::where('company_id', $company->id)->pluck('id');

        $prevShifts = StaffShift::whereIn('staff_id', $staffIds)
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->get();

        foreach ($prevShifts as $shift) {
            $shiftDate = Carbon::parse($shift->date);
            $targetDay = $shiftDate->day;

            if ($targetDay > $currentEnd->day) {
                continue;
            }

            $newDate = $currentStart->copy()->day($targetDay)->format('Y-m-d');

            StaffShift::updateOrCreate(
                [
                    'staff_id' => $shift->staff_id,
                    'date' => $newDate,
                ],
                [
                    'shift_pattern_id' => $shift->shift_pattern_id,
                    'is_work' => $shift->is_work,
                ]
            );
        }

        return back()->with('success', '前月シフトをコピーしました');
    }
}