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

    /* ==========================
       メニュー取得
    ========================== */

    $menuIds = $request->menu_ids ?? [];

    /*
    |--------------------------------------------------------------------------
    | DAY MODE
    |--------------------------------------------------------------------------
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

	    $staffList = Staff::where('company_id',$company->id)
	        ->where('is_reservable',true)
	        ->orderBy('priority_order')
	        ->get();

	    $staffIds = $staffList->pluck('id');

	    /* 予約 */
	    $reservations = Reservation::where('company_id',$company->id)
	        ->whereIn('staff_id',$staffIds)
	        ->whereDate('start_at',$date)
	        ->where('status','reserved')
	        ->get();

	    /* 休暇 */
	    $vacations = Vacation::whereIn('staff_id',$staffIds)
	        ->where('status','approved')
	        ->whereDate('start_at','<=',$date)
	        ->whereDate('end_at','>=',$date)
	        ->get();

	    /* シフト */
	    $shifts = StaffShift::whereIn('staff_id',$staffIds)
	        ->whereDate('date',$date)
	        ->get();

		$patternIds = $shifts->pluck('shift_pattern_id');

		$shiftPatterns = ShiftPattern::whereIn('id',$patternIds)
		    ->get()
		    ->keyBy('id');


	    $data=[];

	    $weekday = $date->dayOfWeek;
	    $patterns = (array) ($company->open_patterns[$weekday] ?? []);

	    foreach ($patterns as $p){

	        if(empty($p['open']) || empty($p['close'])) continue;

	        $open = Carbon::parse($date->format('Y-m-d').' '.$p['open']);
	        $close = Carbon::parse($date->format('Y-m-d').' '.$p['close']);

	        $time=$open->copy();
	        $now=Carbon::now();

	        while($time < $close){

	            $slotEnd=$time->copy()->addMinutes($company->slot_minutes);

	            /* 過去時間 */

	            if($date->isToday() && $time < $now){

	                foreach($staffList as $staff){
	                    $data[$time->format('H:i')][$staff->id]=[
	                        'status'=>'×'
	                    ];
	                }

	                $time->addMinutes($company->slot_minutes);
	                continue;
	            }

	            /* 営業判定 */

	            $status = $this->getBusinessStatus($company,$time,$slotEnd);

	            if($status !== 'open'){

	                foreach($staffList as $staff){
	                    $data[$time->format('H:i')][$staff->id]=[
	                        'status'=>'×'
	                    ];
	                }

	                $time->addMinutes($company->slot_minutes);
	                continue;
	            }

	            /* 空き判定 */

	            foreach($staffList as $staff){

	                $result = $this->checkAvailability(
	                    collect([$staff]),
	                    $reservations,
	                    $vacations,
	                    $shifts,
			    $shiftPatterns,
	                    $time,
	                    $slotEnd
	                );

	                $data[$time->format('H:i')][$staff->id]=$result;
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
    |--------------------------------------------------------------------------
    | WEEK MODE
    |--------------------------------------------------------------------------
    */

    $staffId = $request->staff_id;

    $startDate = $request->date
        ? Carbon::parse($request->date)
        : now();

    $startDate = $startDate->copy()->startOfWeek();
    $endDate = $startDate->copy()->addDays(6)->endOfDay();

    $limits = $this->getReservationLimits($company);

    $staffQuery = Staff::where('company_id',$company->id)
        ->where('is_reservable',true);

    if(!empty($menuIds)){
        $staffQuery->whereHas('menus',function($q) use ($menuIds){
            $q->whereIn('menus.id',$menuIds);
        });
    }

    if(!empty($staffId)){
        $staffQuery->where('id',$staffId);
    }

    $staffList = $staffQuery
        ->orderBy('priority_order')
        ->get();

    $staffIds=$staffList->pluck('id');

    $reservations = Reservation::where('company_id',$company->id)
        ->whereIn('staff_id',$staffIds)
        ->whereBetween('start_at',[$startDate,$endDate])
        ->where('status','reserved')
        ->get();

    $vacations = Vacation::whereIn('staff_id',$staffIds)
        ->where('status','approved')
        ->whereBetween('start_at',[$startDate,$endDate])
        ->get();

    $shifts = StaffShift::whereIn('staff_id',$staffIds)
        ->whereBetween('date',[$startDate,$endDate])
        ->get();

    $patternIds = $shifts->pluck('shift_pattern_id');

    $shiftPatterns = ShiftPattern::whereIn('id',$patternIds)
       ->get()
       ->keyBy('id');

    $data=[];

    for($d=0;$d<7;$d++){

        $day=$startDate->copy()->addDays($d);

        if ($day->startOfDay() < $limits['start'] || $day->startOfDay() > $limits['end']) {
            continue;
        }

        $weekday=$day->dayOfWeek;
        $patterns=(array) ($company->open_patterns[$weekday] ?? []);

        foreach($patterns as $p){

            if(empty($p['open'])||empty($p['close'])) continue;

            $open=Carbon::parse($day->format('Y-m-d').' '.$p['open']);
            $close=Carbon::parse($day->format('Y-m-d').' '.$p['close']);

            $time=$open->copy();

		while($time < $close){

		    $slotStart = Carbon::parse($day->format('Y-m-d').' '.$time->format('H:i'));
		    $slotEnd   = $slotStart->copy()->addMinutes($company->slot_minutes);

		    $now = Carbon::now();

		    /* 過去日 */

		    if ($day->format('Y-m-d') < $now->format('Y-m-d')) {

		        $data[$time->format('H:i')][$day->format('Y-m-d')] = [
		            'status'=>'×'
		        ];

		        $time->addMinutes($company->slot_minutes);
		        continue;
		    }

		    /* 当日の過去時間 */

		    if ($day->isToday() && $slotStart < $now) {

		        $data[$time->format('H:i')][$day->format('Y-m-d')] = [
		            'status'=>'×'
		        ];

		        $time->addMinutes($company->slot_minutes);
		        continue;
		    }
		/* =============================
		   ★営業判定追加
		============================= */

		$status = $this->getBusinessStatus($company,$slotStart,$slotEnd);

		if($status !== 'open'){

		    $data[$time->format('H:i')][$day->format('Y-m-d')] = [
		        'status'=>'×'
		    ];

		    $time->addMinutes($company->slot_minutes);
		    continue;

		} else {

		        $result = $this->checkAvailability(
		            $staffList,
		            $reservations,
		            $vacations,
                            $shifts,
                            $shiftPatterns,
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
		================================ */

		/* 今日の予約数取得 */		
		$todayReservations = Reservation::where('company_id',$company->id)
		    ->whereDate('start_at',$start->format('Y-m-d'))
		    ->where('status','reserved')
		    ->select('staff_id', DB::raw('count(*) as total'))
		    ->groupBy('staff_id')
		    ->pluck('total','staff_id');

		/* 現在の同時予約数を取得 */		
		$currentReservations = Reservation::where('company_id',$company->id)
		    ->where('status','reserved')
		    ->where(function ($q) use ($start,$end) {
		        $q->where('start_at','<',$end)
		          ->where('end_at','>',$start);
		    })
		    ->select('staff_id', DB::raw('count(*) as total'))
		    ->groupBy('staff_id')
		    ->pluck('total','staff_id');

		$assignedStaffId = $request->staff_id;

		if (!$assignedStaffId) {

		/* スタッフ並び替え　同時予約を優先回避→次に予約数均等 */
		$staffList = Staff::where('company_id',$company->id)
		    ->where('is_reservable',true)
		    ->get()
		    ->sortBy(function($staff) use ($todayReservations,$currentReservations){

		        $today = $todayReservations[$staff->id] ?? 0;
		        $current = $currentReservations[$staff->id] ?? 0;

		        return $today + ($current * 10);
		    });

		$staffIds = $staffList->pluck('id');

		/* シフト */

		$shifts = StaffShift::whereIn('staff_id',$staffIds)
		->whereDate('date',$start->format('Y-m-d'))
		->get()
		->keyBy(fn($s)=>$s->staff_id.'_'.$s->date);

		/* パターン */

		$patternIds = $shifts->pluck('shift_pattern_id');

		$shiftPatterns = ShiftPattern::whereIn('id',$patternIds)
		->get()
		->keyBy('id');

		/* 休暇 */

		$vacations = Vacation::whereIn('staff_id',$staffIds)
		->where('status','approved')
		->whereDate('start_at','<=',$start)
		->whereDate('end_at','>=',$start)
		->get();

		foreach ($staffList as $staff){

			$key = $staff->id.'_'.$start->format('Y-m-d');

			$shift = $shifts[$key] ?? null;

			/* シフト休 */

			if(!$shift || !$shift->is_work){
			    continue;
			}

			/* シフト時間 */

			$shiftPattern = $shiftPatterns[$shift->shift_pattern_id] ?? null;

			if($shiftPattern){

			    $shiftStart = Carbon::parse($start->format('Y-m-d').' '.$shiftPattern->start_time);
			    $shiftEnd   = Carbon::parse($start->format('Y-m-d').' '.$shiftPattern->end_time);

			    if($start < $shiftStart || $end > $shiftEnd){
			        continue;
			    }
			}

			/* 休暇 */

			$vacation = $vacations->first(function ($v) use ($staff,$start,$end){
			    return $v->staff_id == $staff->id &&
			           $v->start_at < $end &&
			           $v->end_at > $start;
			});

			if($vacation){
			    continue;
			}

			/* 同時予約チェック */
			$current = $currentReservations[$staff->id] ?? 0;

			if($current >= ($staff->max_simultaneous ?? 1)){
			    continue;
			}

		    /* 予約重複 */

			$exists = Reservation::where('company_id',$company->id)
			->where('staff_id',$staff->id)
			->where('status','reserved')
			->where(function ($q) use ($start,$end){
			    $q->where('start_at','<',$end)
			      ->where('end_at','>',$start);
			})
			->exists();

			if($exists){
			continue;
			}

			/* OKスタッフ */

			$assignedStaffId = $staff->id;
			break;
		}

		if(!$assignedStaffId){
		throw new \Exception('空いているスタッフがいません');
		}
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

    $staffList = Staff::where('company_id',$company->id)
        ->where('is_reservable',true)
        ->orderBy('priority_order')
        ->get();

    $staffIds = $staffList->pluck('id');

    /* シフト取得 */

    $shifts = StaffShift::whereIn('staff_id',$staffIds)
        ->whereDate('date',$start->format('Y-m-d'))
        ->get();

    /* 休暇 */

    $vacations = Vacation::whereIn('staff_id',$staffIds)
        ->where('status','approved')
        ->whereDate('start_at','<=',$start)
        ->whereDate('end_at','>=',$start)
        ->get();

    $availableStaff = [];

    foreach ($staffList as $staff){

        /* シフト */

	$shifts = StaffShift::whereIn('staff_id',$staffIds)
	    ->whereDate('date',$start->format('Y-m-d'))
	    ->get()
	    ->keyBy(fn($s)=>$s->staff_id.'_'.$s->date);

	$key = $staff->id.'_'.$start->format('Y-m-d');
	$shift = $shifts[$key] ?? null;

        if(!$shift || !$shift->is_work){
            continue;
        }

        /* 休暇 */

        $vacation = $vacations->first(function ($v) use ($staff,$start,$end){
            return $v->staff_id == $staff->id &&
                   $v->start_at < $end &&
                   $v->end_at > $start;
        });

        if($vacation){
            continue;
        }

        /* 予約 */

        $exists = Reservation::where('company_id',$company->id)
            ->where('staff_id',$staff->id)
            ->where('status','reserved')
            ->where(function ($q) use ($start,$end){
                $q->where('start_at','<',$end)
                  ->where('end_at','>',$start);
            })
            ->exists();

        if(!$exists){
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
	    $shifts,
            $shiftPatterns,
	    $start,
	    $end
	){

	    $totalStaff = $staffList->count();

	    $workingStaff = 0;
	    $reservedStaff = 0;

	    foreach ($staffList as $staff){

	        $shift = $shifts->first(function ($s) use ($staff,$start) {
	            return $s->staff_id == $staff->id &&
	                   $s->date == $start->format('Y-m-d');
	        });

		if(!$shift || !$shift->is_work){
		    continue;
		}

		/* シフトパターン */

		$shiftPattern = $shiftPatterns[$shift->shift_pattern_id] ?? null;

		if($shiftPattern){

		    $shiftStart = Carbon::parse($start->format('Y-m-d').' '.$shiftPattern->start_time);
		    $shiftEnd   = Carbon::parse($start->format('Y-m-d').' '.$shiftPattern->end_time);

		    if($start < $shiftStart || $end > $shiftEnd){
		        continue;
		    }
		}


	        /* 休暇 */

	        $vacation = $vacations->first(function ($v) use ($staff,$start,$end) {
	            return $v->staff_id == $staff->id &&
	                   $v->start_at < $end &&
	                   $v->end_at > $start;
	        });

	        if($vacation){
	            $reservedStaff++;
	            continue;
	        }

	        $workingStaff++;

	        /* 予約 */

	        $reservation = $reservations
	            ->where('staff_id',$staff->id)
	            ->first(function ($r) use ($start,$end){
	                return $r->start_at < $end &&
	                       $r->end_at > $start;
	            });

	        if($reservation){
	            $reservedStaff++;
	        }
	    }

	    /* 出勤スタッフ0 */

	    if($workingStaff == 0){
	        return ['status'=>'×'];
	    }

	    /* 全員埋まり */

	    if($reservedStaff >= $workingStaff){
	        return ['status'=>'×'];
	    }

	    /* 一部埋まり */

	    if($reservedStaff > 0){
	        return ['status'=>'△'];
	    }

	    return ['status'=>'○'];
	}

	public function staffMenus(Request $request)
	{
	    $company = auth()->guard('company')->user()->company;

	    $staffId = $request->staff_id;

	    $menus = Menu::where('company_id',$company->id)
	        ->whereHas('staffs',function($q) use ($staffId){
	            $q->where('staff_id',$staffId);
	        })
	        ->orderBy('sort_order')
	        ->get();

	    return response()->json($menus);
	}
}