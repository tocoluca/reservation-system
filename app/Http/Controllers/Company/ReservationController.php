<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Vacation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yasumi\Yasumi;

class ReservationController extends Controller
{
    /* ==========================================================
       予約登録
    ========================================================== */
    public function store(Request $request)
    {

        $company = Auth::guard('company')->user()->company;

        try {

            $request->validate([
                'start_at'      => 'required|date',
                'customer_name' => 'required',
                'menu_id'       => 'nullable|integer',
 		'staff_id'      => 'nullable|integer'
            ]);

            $start = Carbon::parse($request->start_at);
            $duration = $company->slot_minutes;

            // 美容院メニュー時間優先
            if (
                $company->industry_type === 'beauty' &&
                $company->menu_time_priority_flag &&
                $request->menu_id
            ) {
                $menu = \App\Models\Menu::findOrFail($request->menu_id);
                $duration = $menu->duration_minutes;
            }

            $end = $start->copy()->addMinutes($duration);

            /* -------------------------
               ① 休業日チェック
            ------------------------- */
            $weekday = $start->dayOfWeek;
            $regularHolidays = $company->regular_holidays ?? [];

            if (in_array($weekday, $regularHolidays)) {
                throw new \Exception('本日は定休日です');
            }

            if ($company->holiday_is_closed) {
                $holidays = Yasumi::create('Japan', $start->year);
                if ($holidays->isHoliday($start)) {
                    throw new \Exception('本日は祝日のため休業です');
                }
            }

            /* -------------------------
               ② 営業時間チェック（曜日別）
            ------------------------- */
            $dayPatterns = $company->open_patterns[$weekday] ?? [];

            if (empty($dayPatterns)) {
                throw new \Exception('本日は休業日です');
            }

            $valid = false;

            foreach ($dayPatterns as $pattern) {

                if (empty($pattern['open']) || empty($pattern['close'])) {
                    continue;
                }

                $open = Carbon::parse($start->format('Y-m-d').' '.$pattern['open']);
                $close = Carbon::parse($start->format('Y-m-d').' '.$pattern['close']);

                if ($start >= $open && $end <= $close) {
                    $valid = true;
                    break;
                }
            }

            if (!$valid) {
                throw new \Exception('営業時間外です');
            }

            /* -------------------------
               ③ スタッフ自動割当
            ------------------------- */

		$reservation = null;
		$assignedStaffId = null;   // ← 外で宣言

		DB::transaction(function () use (
		    $request,
		    $company,
		    $start,
		    $end,
		    &$reservation,
		    &$assignedStaffId   // ← 参照渡し
		) {

		    $selectedStaffId = $request->staff_id;

		    if ($selectedStaffId) {
		        $staffList = $company->staff()
		            ->where('id', $selectedStaffId)
		            ->where('is_reservable', true)
		            ->lockForUpdate()
		            ->get();
		    } else {
		        $staffList = $company->staff()
		            ->where('is_reservable', true)
		            ->orderBy('priority_order')
		            ->lockForUpdate()
		            ->get();
		    }

		    foreach ($staffList as $staff) {

		        $vacationExists = Vacation::where('staff_id', $staff->id)
		            ->where('status','approved')
		            ->where('start_at','<',$end)
		            ->where('end_at','>',$start)
		            ->lockForUpdate()
		            ->exists();

		        if ($vacationExists) continue;

		        $count = Reservation::where('company_id', $company->id)
		            ->where('staff_id', $staff->id)
		            ->where('start_at','<',$end)
		            ->where('end_at','>',$start)
		            ->where('status','reserved')
		            ->lockForUpdate()
		            ->count();

		        if ($count < $company->max_simultaneous_reservations) {
		            $assignedStaffId = $staff->id;   // ← IDだけ保存
		            break;
		        }
		    }

		    if (!$assignedStaffId) {
		        throw new \Exception('この時間は満員です');
		    }

		    $reservation = Reservation::create([
		        'company_id' => $company->id,
		        'staff_id'   => $assignedStaffId,
		        'menu_id'    => $request->menu_id,
		        'customer_name' => $request->customer_name,
		        'start_at'   => $start,
		        'end_at'     => $end,
		        'status'     => 'reserved',
		        'fingerprint'=> request()->ip().'_'.request()->userAgent()
		    ]);
		});

		if ($company->industry_type === 'dental') {
		return response()->json([
		    'success'  => true,
		    'redirect' => route('company.questionnaire', $reservation->id)
		]);
		}

		$assignedStaff = $company->staff()->find($assignedStaffId);

		return response()->json([
		    'success' => true,
		    'staff_name' => $assignedStaff ? $assignedStaff->name : '不明'
		]);

        } catch (\Exception $e) {

//	Log::debug('ログEXCEPTION １');
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],422);
        }
    }

    /* ==========================================================
       キャンセル
    ========================================================== */

	public function cancel(Reservation $reservation)
	{
	    $reservation->status = 'cancelled';
	    $reservation->save();

	    return response()->json(['success'=>true]);
	}
    /* ==========================================================
       カレンダー表示
    ========================================================== */
	public function calendar(Request $request)
	{
	    $mode = $request->get('mode','week');
	    return view('company.calendar', compact('mode'));
	}


    /* ==========================================================
       カレンダーデータ
    ========================================================== */
public function calendarData(Request $request)
{
    $company = Auth::guard('company')->user()->company;
    $mode = $request->get('mode','week');

    /*
    |--------------------------------------------------------------------------
    | 日表示モード
    |--------------------------------------------------------------------------
    */
    if ($mode === 'day') {

        $date = $request->date
            ? Carbon::parse($request->date)
            : now();

        $staffList = $company->staff()
            ->where('is_reservable', true)
            ->orderBy('priority_order')
            ->get();

        $staffIds = $staffList->pluck('id')->toArray();

        $reservations = Reservation::where('company_id', $company->id)
            ->whereIn('staff_id', $staffIds)
            ->whereDate('start_at', $date)
            ->where('status','reserved')
            ->get();

        $vacations = Vacation::whereIn('staff_id', $staffIds)
            ->where('status','approved')
            ->whereDate('start_at', $date)
            ->get();

        $patterns = is_array($company->open_patterns)
            ? $company->open_patterns
            : [];

        $weekday = $date->dayOfWeek;
        $dayPatterns = $patterns[$weekday] ?? [];

        $data = [];

        foreach ($dayPatterns as $pattern) {

            if (empty($pattern['open']) || empty($pattern['close'])) continue;

            $open = Carbon::parse($date->format('Y-m-d').' '.$pattern['open']);
            $close = Carbon::parse($date->format('Y-m-d').' '.$pattern['close']);

            $time = $open->copy();

            while ($time < $close) {

                $slotEnd = $time->copy()
                    ->addMinutes($company->slot_minutes);

                foreach ($staffList as $staff) {

                    // 休暇チェック
                    $vacationExists = $vacations->first(function ($v) use ($staff,$time,$slotEnd) {
                        return $v->staff_id == $staff->id &&
                               $v->start_at < $slotEnd &&
                               $v->end_at > $time;
                    });

                    if ($vacationExists) {

                        $data[$time->format('H:i')][$staff->id] = [
                            'status' => '休'
                        ];

                        continue;
                    }

                    // 予約チェック
                    $reservation = $reservations
                        ->where('staff_id',$staff->id)
                        ->first(function ($r) use ($time,$slotEnd) {
                            return $r->start_at < $slotEnd &&
                                   $r->end_at > $time;
                        });

                    if ($reservation) {

                        $data[$time->format('H:i')][$staff->id] = [
                            'status' => '×',
                            'reservation_id' => $reservation->id,
                            'staff_name' => $staff->name,
                            'customer_name' => $reservation->customer_name,
                            'start_at' => Carbon::parse($reservation->start_at)
                                            ->format('Y-m-d H:i'),
                            'end_at' => Carbon::parse($reservation->end_at)
                                            ->format('Y-m-d H:i'),
                        ];

                    } else {

                        $data[$time->format('H:i')][$staff->id] = [
                            'status' => '○'
                        ];
                    }
                }

                $time->addMinutes($company->slot_minutes);
            }
        }

        return response()->json([
            'mode' => 'day',
            'staffs' => $staffList,
            'slots' => $data
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 週表示モード（既存ロジック）
    |--------------------------------------------------------------------------
    */

    $startDate = $request->date
        ? Carbon::parse($request->date)->startOfWeek()
        : now()->startOfWeek();

    $endDate = $startDate->copy()->addDays(6)->endOfDay();

    $staffId = $request->staff_id;

    if ($staffId) {
        $staffList = $company->staff()
            ->where('id', $staffId)
            ->where('is_reservable', true)
            ->get();
    } else {
        $staffList = $company->staff()
            ->where('is_reservable', true)
            ->orderBy('priority_order')
            ->get();
    }

    $staffIds = $staffList->pluck('id')->toArray();

    $reservations = Reservation::where('company_id', $company->id)
        ->whereIn('staff_id', $staffIds)
        ->whereBetween('start_at', [$startDate, $endDate])
        ->where('status','reserved')
        ->get();

    $vacations = Vacation::whereIn('staff_id', $staffIds)
        ->where('status','approved')
        ->whereBetween('start_at', [$startDate, $endDate])
        ->get();

    $data = [];

    $patterns = is_array($company->open_patterns)
        ? $company->open_patterns
        : [];

    for ($d = 0; $d < 7; $d++) {

        $day = $startDate->copy()->addDays($d);
        $weekday = $day->dayOfWeek;

        $regularHolidays = is_array($company->regular_holidays)
            ? $company->regular_holidays
            : [];

        if (in_array($weekday, $regularHolidays)) continue;

        if ($company->holiday_is_closed) {
            $holidays = Yasumi::create('Japan', $day->year);
            if ($holidays->isHoliday($day)) continue;
        }

        $dayPatterns = $patterns[$weekday] ?? [];

        foreach ($dayPatterns as $pattern) {

            if (empty($pattern['open']) || empty($pattern['close'])) continue;

            $open = Carbon::parse($day->format('Y-m-d').' '.$pattern['open']);
            $close = Carbon::parse($day->format('Y-m-d').' '.$pattern['close']);

            $time = $open->copy();

            while ($time < $close) {

                $slotEnd = $time->copy()
                    ->addMinutes($company->slot_minutes);

                $availableCount = 0;

                foreach ($staffList as $staff) {

                    $vacationExists = $vacations->first(function ($v) use ($staff,$time,$slotEnd) {
                        return $v->staff_id == $staff->id &&
                               $v->start_at < $slotEnd &&
                               $v->end_at > $time;
                    });

                    if ($vacationExists) continue;

                    $exists = $reservations
                        ->where('staff_id',$staff->id)
                        ->first(function ($r) use ($time,$slotEnd) {
                            return $r->start_at < $slotEnd &&
                                   $r->end_at > $time;
                        });

                    if (!$exists) $availableCount++;
                }

                $data[$time->format('H:i')][$day->format('Y-m-d')] = [
                    'status' => $availableCount > 0 ? '○' : '×',
                    'is_holiday' => false
                ];

                $time->addMinutes($company->slot_minutes);
            }
        }
    }

    return response()->json([
        'mode' => 'week',
        'slots' => $data
    ]);
}



	private function calendarDay(Request $request)
	{
	    $company = auth()->guard('company')->user()->company;

	    $date = $request->date
	        ? Carbon::parse($request->date)
	        : now();

	    $staffList = $company->staff()
	        ->where('is_reservable', true)
	        ->orderBy('priority_order')
	        ->get();

	    $reservations = Reservation::where('company_id',$company->id)
	        ->whereDate('start_at',$date)
	        ->where('status','reserved')
	        ->get();

	    $data = [];

	    $patterns = $company->open_patterns[$date->dayOfWeek] ?? [];

	    foreach ($patterns as $pattern) {

	        $open = Carbon::parse($date->format('Y-m-d').' '.$pattern['open']);
	        $close = Carbon::parse($date->format('Y-m-d').' '.$pattern['close']);

	        $time = $open->copy();

	        while ($time < $close) {

	            $slotEnd = $time->copy()
	                ->addMinutes($company->slot_minutes);

	            foreach ($staffList as $staff) {

	                $exists = $reservations
	                    ->where('staff_id',$staff->id)
	                    ->filter(function ($r) use ($time,$slotEnd) {
	                        return $r->start_at < $slotEnd &&
	                               $r->end_at > $time;
	                    })->count();

	                $data[$time->format('H:i')][$staff->id] =
	                    $exists > 0 ? '×' : '○';
	            }

	            $time->addMinutes($company->slot_minutes);
	        }
	    }

	    return response()->json([
	        'staffs' => $staffList,
	        'slots' => $data
	    ]);
	}
}