<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\CompanyBusinessCalendar;
use Carbon\Carbon;
use Yasumi\Yasumi;

class CalendarController extends Controller
{
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
	    ->where('status','reserved')
	    ->get()
	    ->groupBy(function ($r) {
	        return \Carbon\Carbon::parse($r->start_at)->format('Y-m-d');
	    })
	    ->map(function ($items) {
	        return $items->count();
	    });

    /*
    |--------------------------------------------------------------------------
    | 🔥 holiday_is_closed 対応
    |--------------------------------------------------------------------------
    */

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
	'reservationCounts' => $reservationCounts   // ★追加
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

        $calendar = CompanyBusinessCalendar::firstOrCreate([
            'company_id' => $company->id,
            'date' => $request->date,
        ], [
            'is_open' => true
        ]);

        $calendar->is_open = !$calendar->is_open;
        $calendar->save();

        return response()->json([
            'success' => true,
            'is_open' => $calendar->is_open
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
        $calendar->save();

        return response()->json([
            'success' => true
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
	        'weekday' => 'required|integer|min:0|max:6', // 0=日曜
	    ]);

	    $company = auth()->guard('company')->user()->company;

	    $year = (int)$request->year;
	    $month = (int)$request->month;
	    $weekday = (int)$request->weekday;

	    $start = Carbon::create($year, $month, 1)->startOfMonth();
	    $end   = $start->copy()->endOfMonth();

	    $date = $start->copy();

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
	        }

	        $date->addDay();
	    }

	    return response()->json([
	        'success' => true
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

	    $year = (int)$request->year;
	    $weekday = (int)$request->weekday;

	    $start = Carbon::create($year, 1, 1)->startOfYear();
	    $end   = $start->copy()->endOfYear();

	    $date = $start->copy();

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
	        }

	        $date->addDay();
	    }

	    return response()->json([
	        'success' => true
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

    $year = (int)$request->year;
    $weekday = (int)$request->weekday;

    $start = Carbon::create($year, 1, 1)->startOfYear();
    $end   = $start->copy()->endOfYear();

    $date = $start->copy();

    while ($date <= $end) {

        if ($date->dayOfWeek === $weekday) {

            CompanyBusinessCalendar::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'is_open' => true
                ]
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

    $calendar = CompanyBusinessCalendar::where('company_id',$company->id)
        ->where('date',$request->date)
        ->first();

    if ($calendar) {
        $calendar->open_time = null;
        $calendar->close_time = null;
        $calendar->save();
    }

    return response()->json(['success'=>true]);
}
}