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
use Illuminate\Support\Facades\Log;

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

        $start = Carbon::parse($month . '-01');
        $end = $start->copy()->endOfMonth();

        $staffs = Staff::where('company_id', $company->id)
            ->orderBy('priority_order')
            ->get();

        $patterns = ShiftPattern::where('company_id', $company->id)->get();

        $vacations = Vacation::where('status', 'approved')
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
            ->keyBy('date');

        $shifts = StaffShift::whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(['staff_id', 'date']);

        return view('company.staff_shifts', [
            'month' => $month,
            'staffs' => $staffs,
            'patterns' => $patterns,
            'shifts' => $shifts,
            'vacations' => $vacations,
            'businessDays' => $businessDays
        ]);
    }

    /*
    基本シフトから月シフト生成
    */
    public function generate(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $month = $request->month;

        $start = Carbon::parse($month . '-01');
        $days = $start->daysInMonth;

        $staffs = Staff::where('company_id', $company->id)->get();

        foreach ($staffs as $staff) {
            for ($d = 1; $d <= $days; $d++) {
                $date = Carbon::parse("$month-$d");
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
                        'date' => $date
                    ],
                    [
                        'shift_pattern_id' => $default->shift_pattern_id,
                        'is_work' => $default->is_work
                    ]
                );
            }
        }

        return back();
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

	    foreach ($request->shifts as $staffId => $dates) {
	        $staff = Staff::where('company_id', $company->id)->find($staffId);

	        if (!$staff) {
	            continue;
	        }

	        foreach ($dates as $date => $pattern) {
	            $existing = StaffShift::where('staff_id', $staffId)
	                ->where('date', $date)
	                ->first();

	            if ($existing) {
	                $wasWorking = (bool) $existing->is_work;
	            } else {
	                $weekday = Carbon::parse($date)->dayOfWeek;

	                $defaultShift = StaffDefaultShift::where('staff_id', $staffId)
	                    ->where('weekday', $weekday)
	                    ->first();

	                $wasWorking = (bool) ($defaultShift->is_work ?? false);
	            }

	            $isWork = $pattern ? 1 : 0;

	            StaffShift::updateOrCreate(
	                [
	                    'staff_id' => $staffId,
	                    'date' => $date
	                ],
	                [
	                    'shift_pattern_id' => $pattern ?: null,
	                    'is_work' => $isWork
	                ]
	            );

	            if ($wasWorking && !$isWork) {
	                $notice = $this->changeNoticeService->createForStaffShiftOff(
	                    company: $company,
	                    staff: $staff,
	                    date: $date,
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

    public function copy(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $month = $request->month;

        $currentStart = Carbon::parse($month . '-01');
        $prevStart = $currentStart->copy()->subMonth();

        $currentEnd = $currentStart->copy()->endOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        $prevShifts = StaffShift::whereBetween('date', [$prevStart, $prevEnd])->get();

        foreach ($prevShifts as $shift) {
            $newDate = Carbon::parse($shift->date)->addMonth();

            StaffShift::updateOrCreate(
                [
                    'staff_id' => $shift->staff_id,
                    'date' => $newDate
                ],
                [
                    'shift_pattern_id' => $shift->shift_pattern_id,
                    'is_work' => $shift->is_work
                ]
            );
        }

        return back()->with('success', '前月シフトをコピーしました');
    }
}