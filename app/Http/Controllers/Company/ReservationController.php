<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Vacation;
use App\Models\Menu;
use App\Models\CompanyBusinessCalendar;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yasumi\Yasumi;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
	/* ==========================================================
	予約制御設定
	========================================================== */
	private function getReservationLimits($company)
	{
	    $limitMonth = $company->reservation_month_limit ?? 3;
	    $openDays   = $company->reservation_open_days ?? 0;
	    $closeHours = $company->reservation_close_hours ?? 1;

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
	        'close' => $closeLimit
	    ];
	}
    /* ==========================================================
       カレンダー表示
    ========================================================== */
    public function calendar(Request $request)
    {
        $mode = $request->get('mode','week');

        $company = auth()->guard('company')->user()->company;

        /* ★追加（メニュー取得） */
        $menus = Menu::where('company_id',$company->id)->get();

        return view('company.calendar',[
            'mode'=>$mode,
            'menus'=>$menus
        ]);
    }

    /* ==========================================================
       カレンダーデータ
    ========================================================== */
    public function calendarData(Request $request)
    {
        $company = auth()->guard('company')->user()->company;
        $mode = $request->get('mode','week');

        /*
        |----------------------------------------------------------------------
        | DAY MODE
        |----------------------------------------------------------------------
        */
        if ($mode === 'day') {

            $date = $request->date
                ? Carbon::parse($request->date)
                : now();
		$limits = $this->getReservationLimits($company);

		if ($date->startOfDay() < $limits['start'] || $date->startOfDay() > $limits['end']) {
		    return response()->json([
		        'staffs'=>[],
		        'slots'=>[]
		    ]);
		}
            $staffList = $company->staff()
                ->where('is_reservable', true)
                ->orderBy('priority_order')
                ->get();

            $staffIds = $staffList->pluck('id');

            $reservations = Reservation::where('company_id', $company->id)
                ->whereIn('staff_id', $staffIds)
                ->whereDate('start_at', $date)
                ->where('status','reserved')
                ->get();

            $vacations = Vacation::whereIn('staff_id', $staffIds)
                ->where('status','approved')
                ->whereDate('start_at','<=',$date)
                ->whereDate('end_at','>=',$date)
                ->get();

            $data = [];

            $weekday = $date->dayOfWeek;
            $patterns = (array) ($company->open_patterns[$weekday] ?? []);

            foreach ($patterns as $p) {

                if (empty($p['open']) || empty($p['close'])) continue;

                $open  = Carbon::parse($date->format('Y-m-d').' '.$p['open']);
                $close = Carbon::parse($date->format('Y-m-d').' '.$p['close']);

                $time = $open->copy();

                while ($time < $close) {

                    $slotEnd = $time->copy()->addMinutes($company->slot_minutes);

                    $business = $this->getBusinessStatus($company,$time,$slotEnd);

                    foreach ($staffList as $staff) {

                        if ($business !== 'open') {
                            $data[$time->format('H:i')][$staff->id] = [
                                'status'=>'×'
                            ];
                            continue;
                        }

                        $result = $this->checkAvailability(
                            collect([$staff]),
                            $reservations,
                            $vacations,
                            $time,
                            $slotEnd
                        );

                        $data[$time->format('H:i')][$staff->id] = $result;
                    }

                    $time->addMinutes($company->slot_minutes);
                }
            }

            return response()->json([
                'staffs'=>$staffList,
                'slots'=>$data
            ]);
        }

        /*
        |----------------------------------------------------------------------
        | WEEK MODE
        |----------------------------------------------------------------------
        */

        $staffId = $request->staff_id;

        $startDate = $request->date
            ? Carbon::parse($request->date)->startOfWeek()
            : now()->startOfWeek();

        $endDate = $startDate->copy()->addDays(6)->endOfDay();
	$limits = $this->getReservationLimits($company);

        $staffQuery = $company->staff()
            ->where('is_reservable', true)
            ->orderBy('priority_order');

        if (!empty($staffId)) {
            $staffQuery->where('id', $staffId);
        }

        $staffList = $staffQuery->get();

        $staffIds = $staffList->pluck('id');

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

        for ($d=0;$d<7;$d++) {

            $day = $startDate->copy()->addDays($d);


		if ($day < $limits['start'] || $day > $limits['end']) {
		    continue;
		}

            $weekday = $day->dayOfWeek;
            $patterns = (array) ($company->open_patterns[$weekday] ?? []);

            foreach ($patterns as $p) {

                if (empty($p['open']) || empty($p['close'])) continue;

                $open  = Carbon::parse($day->format('Y-m-d').' '.$p['open']);
                $close = Carbon::parse($day->format('Y-m-d').' '.$p['close']);

                $time = $open->copy();

                while ($time < $close) {

                    $slotStart = Carbon::parse($day->format('Y-m-d').' '.$time->format('H:i'));
                    $slotEnd   = $slotStart->copy()->addMinutes($company->slot_minutes);

                    $business = $this->getBusinessStatus($company,$slotStart,$slotEnd);

                    if ($business !== 'open') {

                        $data[$time->format('H:i')][$day->format('Y-m-d')] = [
                            'status'=>'×'
                        ];

                    } else {

                        $result = $this->checkAvailability(
                            $staffList,
                            $reservations,
                            $vacations,
                            $slotStart,
                            $slotEnd
                        );

                        $data[$time->format('H:i')][$day->format('Y-m-d')] = $result;
                    }

                    $time->addMinutes($company->slot_minutes);
                }
            }
        }

        return response()->json([
            'mode'=>'week',
            'slots'=>$data
        ]);
    }

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

		/* =================================
		   予約可能期間チェック
		================================ */

		$limits = $this->getReservationLimits($company);

		if ($start < $limits['start']) {
		    return response()->json([
		        'success'=>false,
		        'message'=>'この日はまだ予約受付していません'
		    ],422);
		}

		if ($start > $limits['end']) {
		    return response()->json([
		        'success'=>false,
		        'message'=>'予約可能期間を超えています'
		    ],422);
		}

		if ($start < $limits['close']) {
		    return response()->json([
		        'success'=>false,
		        'message'=>'予約締切を過ぎています'
		    ],422);
		}

	        /* =================================
	           所要時間決定
	        ================================= */

	        if (
	            $company->menu_time_priority_flag &&
	            $request->menu_id
	        ) {

	            $menu = Menu::findOrFail($request->menu_id);
	            $duration = $menu->duration;

	        } else {

	            $duration = $company->slot_minutes;
	        }

	        $end = $start->copy()->addMinutes($duration);

	        /* =================================
	           スタッフ決定
	        ================================= */

	        $assignedStaffId = $request->staff_id;

	        if (!$assignedStaffId) {

	            $staff = Staff::where('company_id',$company->id)
	                ->where('is_reservable',true)
	                ->orderBy('priority_order')
	                ->first();

	            if (!$staff) {
	                throw new \Exception('担当者が見つかりません');
	            }

	            $assignedStaffId = $staff->id;
	        }

	        $staff = Staff::find($assignedStaffId);

	        /* =================================
	           料金計算
	        ================================= */

	        $menu = null;
	        $price = 0;

	        if ($request->menu_id) {

	            $menu = Menu::find($request->menu_id);

	            if ($menu) {
	                $price = $menu->price;
	            }
	        }

	        $nominationFee = $staff->nomination_fee ?? 0;

	        $totalPrice = $price + $nominationFee;

		$overlapCount = Reservation::where('company_id',$company->id)
		    ->where('status','reserved')
		    ->where(function ($q) use ($start,$end) {
		        $q->where('start_at','<',$end)
		          ->where('end_at','>',$start);
		    })
		    ->count();

		if ($overlapCount >= $company->max_simultaneous_reservations) {

		    return response()->json([
		        'success'=>false,
		        'message'=>'この時間は予約上限です'
		    ],422);
		}

		DB::transaction(function () use (
		    $request,
		    $company,
		    $start,
		    $end,
		    $assignedStaffId,
		    $price,
		    $nominationFee,
		    $totalPrice
		) {

		    $exists = Reservation::where('company_id',$company->id)
		        ->where('staff_id',$assignedStaffId)
		        ->where('status','reserved')
		        ->where(function ($q) use ($start,$end) {
		            $q->where('start_at','<',$end)
		              ->where('end_at','>',$start);
		        })
		        ->lockForUpdate()
		        ->exists();

		    if ($exists) {
		        throw new \Exception('この時間は既に予約があります');
		    }

		    Reservation::create([
		        'company_id'=>$company->id,
		        'staff_id'=>$assignedStaffId,
		        'customer_name'=>$request->customer_name,
		        'start_at'=>$start,
		        'end_at'=>$end,
		        'menu_id'=>$request->menu_id,
		        'price'=>$price,
		        'nomination_fee'=>$nominationFee,
		        'total_price'=>$totalPrice,
		        'status'=>'reserved',
		    ]);

		});

	        return response()->json(['success'=>true]);

	    } catch (\Exception $e) {

	        return response()->json([
	            'success'=>false,
	            'message'=>$e->getMessage()
	        ],422);
	    }
	}
    public function cancel($id)
    {
        $company = auth()->guard('company')->user()->company;

        $reservation = Reservation::where('id',$id)
            ->where('company_id',$company->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success'=>false,
                'message'=>'予約が見つかりません'
            ],404);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['success'=>true]);
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
	    $end   = $start->copy()->addMinutes($company->slot_minutes);

	    $staffList = \App\Models\Staff::where('company_id', $company->id)
	        ->where('is_reservable', true)
	        ->orderBy('priority_order')
	        ->get();

	    $availableStaff = [];

	    foreach ($staffList as $staff) {

	        $exists = Reservation::where('company_id', $company->id)
	            ->where('staff_id', $staff->id)
	            ->where('status','reserved')
	            ->where(function ($q) use ($start, $end) {
	                $q->where('start_at','<',$end)
	                  ->where('end_at','>',$start);
	            })
	            ->exists();

	        if (!$exists) {
	            $availableStaff[] = $staff;
	        }
	    }

	    return response()->json($availableStaff);
	}

    /* ==========================================================
       営業判定
    ========================================================== */
    private function getBusinessStatus($company,$start,$end)
    {
        $dateKey = $start->format('Y-m-d');
        $weekday = $start->dayOfWeek;

        $calendar = CompanyBusinessCalendar::where('company_id',$company->id)
            ->whereDate('date',$dateKey)
            ->first();

        if ($calendar) {

            if ($calendar->is_open == 0) {
                return 'closed';
            }

            if (!empty($calendar->open_time) && !empty($calendar->close_time)) {

                $open  = Carbon::parse($dateKey.' '.$calendar->open_time);
                $close = Carbon::parse($dateKey.' '.$calendar->close_time);

                if ($start >= $open && $end <= $close) {
                    return 'open';
                }

                return 'out';
            }
        }

        if (in_array($weekday,(array)$company->regular_holidays)) {
            return 'closed';
        }

        if ($company->holiday_is_closed) {
            $holidays = Yasumi::create('Japan',$start->year);
            if ($holidays->isHoliday($start)) {
                return 'closed';
            }
        }

        $patterns = (array) ($company->open_patterns[$weekday] ?? []);

        foreach ($patterns as $p) {

            if (empty($p['open']) || empty($p['close'])) continue;

            $open  = Carbon::parse($dateKey.' '.$p['open']);
            $close = Carbon::parse($dateKey.' '.$p['close']);

            if ($start >= $open && $end <= $close) {
                return 'open';
            }
        }

        return 'out';
    }

    /* ==========================================================
       空き判定
    ========================================================== */
	private function checkAvailability(
	    $staffList,
	    $reservations,
	    $vacations,
	    $start,
	    $end
	) {

	    $totalStaff = $staffList->count();

	    $slotReservations = collect();
	    $availableCount = 0;

	    foreach ($staffList as $staff) {

	        // 休暇チェック（空き計算には含めるが、予約扱いにはしない）
	        $vacationExists = $vacations->first(function ($v) use ($staff,$start,$end) {
	            return $v->staff_id == $staff->id &&
	                   $v->start_at < $end &&
	                   $v->end_at > $start;
	        });

	        // 予約チェック
	        $reservation = $reservations
	            ->where('staff_id',$staff->id)
	            ->first(function ($r) use ($start,$end) {
	                return $r->start_at < $end &&
	                       $r->end_at > $start;
	            });

	        if ($reservation) {
	            $slotReservations->push($reservation);
	        } else {
	            $availableCount++;
	        }
	    }

	    /*
	    |--------------------------------------------------------------------------
	    | 個人モード（スタッフ1人だけ渡された場合）
	    |--------------------------------------------------------------------------
	    */
	    if ($totalStaff === 1) {

	        if ($slotReservations->count() > 0) {

	            $reservation = $slotReservations->first();

	            return [
	                'status' => '×',
	                'reservation_id' => $reservation->id,
	                'staff_name' => $reservation->staff->name ?? null,
	                'customer_name' => $reservation->customer_name ?? null,
	            ];
	        }

	        return ['status' => '○'];
	    }

	    /*
	    |--------------------------------------------------------------------------
	    | 全担当モード
	    |--------------------------------------------------------------------------
	    */
	    if ($slotReservations->count() >= $totalStaff) {

	        return [
	            'status' => '×',
	            'reservations' => $slotReservations->map(function ($r) {
	                return [
	                    'id' => $r->id,
	                    'staff_name' => $r->staff->name ?? null,
	                    'customer_name' => $r->customer_name ?? null,
	                ];
	            })->values()
	        ];
	    }

		/* ★ここ追加 */

		if ($slotReservations->count() > 0) {

		    return [
		        'status' => '△',
		        'reservations' => $slotReservations->map(function ($r) {
		            return [
		                'id' => $r->id,
		                'staff_name' => $r->staff->name ?? null,
		                'customer_name' => $r->customer_name ?? null,
		            ];
		        })->values()
		    ];
		}

		return ['status' => '○'];
	}

}