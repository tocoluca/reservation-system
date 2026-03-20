<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationMenu;
use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;
use App\Models\Vacation;
use App\Models\StaffShift;
use App\Models\ShiftPattern;
use App\Models\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReserveController extends Controller
{

/*
|--------------------------------------------------------------------------
| 予約可能期間
|--------------------------------------------------------------------------
*/

private function getReservationLimits($company)
{
    return [
        'start' => now()->addDays($company->reservation_open_days ?? 0)->startOfDay(),
        'end'   => now()->addMonths($company->reservation_month_limit ?? 3)->endOfMonth(),
        'close' => now()->addHours($company->reservation_close_hours ?? 1)
    ];
}

/*
|--------------------------------------------------------------------------
| 予約トップ
|--------------------------------------------------------------------------
*/

public function index($company_code)
{

    $company = Company::where('company_code',$company_code)->firstOrFail();

    $menus = Menu::with(['tags','category'])
        ->where('company_id',$company->id)
        ->where('is_active',1)
        ->orderByDesc('is_popular')
        ->orderBy('sort_order')
        ->get()
        ->groupBy(fn($menu)=>$menu->category->name ?? 'その他');

    $staff = Staff::where('company_id',$company->id)
        ->where('is_reservable',1)
        ->orderBy('priority_order')
        ->get()
        ->map(function($s){

            $s->image_url = $s->image_path
                ? asset($s->image_path)
                : asset('logos/logo.png');

            return $s;
        });

    return view('reserve.index',[
        'company'=>$company,
        'menus'=>$menus,
        'staff'=>$staff,
        'step'=>1
    ]);
}

/*
|--------------------------------------------------------------------------
| confirm
|--------------------------------------------------------------------------
*/

public function confirm(Request $request,$company_code)
{

    $company = Company::where('company_code',$company_code)->firstOrFail();

    $menus = Menu::where('company_id',$company->id)
        ->whereIn('id',$request->menu_ids ?? [])
        ->get();

    $staff = $request->staff_id
        ? Staff::find($request->staff_id)
        : null;

    return view('reserve.confirm',[
        'company'=>$company,
        'menus'=>$menus,
        'staff'=>$staff,
        'start_at'=>$request->start_at,
        'step'=>2
    ]);
}

/*
|--------------------------------------------------------------------------
| store
|--------------------------------------------------------------------------
*/

public function store(Request $request,$company_code)
{

    $company = Company::where('company_code',$company_code)->firstOrFail();

	$request->validate([
	    'customer_name' => ['required','max:255'],
	    'customer_phone' => ['required','regex:/^[0-9\-]+$/'],
	    'customer_email' => ['nullable','email','max:255']
	],[
	    'customer_name.required' => ':attributeを入力してください',
	    'customer_phone.regex' => ':attributeは半角数字とハイフンのみ入力できます',
	],[
	    'customer_name' => 'お名前',
	    'customer_phone' => '電話番号',
	    'customer_email' => 'メールアドレス',
	]);

    $menus = Menu::where('company_id',$company->id)
        ->whereIn('id',$request->menu_ids ?? [])
        ->get();

    if($menus->isEmpty()){
        return back()->with('error','メニューを選択してください');
    }

    $start = Carbon::parse($request->start_at);

    $totalPrice = $menus->sum('price');
    $totalDuration = $menus->sum('duration');

    $end = $start->copy()->addMinutes($totalDuration);

    $limits = $this->getReservationLimits($company);

    if ($start < $limits['start']) {
        return back()->with('error','この日はまだ予約受付していません');
    }

    if ($start > $limits['end']) {
        return back()->with('error','予約可能期間を超えています');
    }

    if ($start < $limits['close']) {
        return back()->with('error','予約締切を過ぎています');
    }

    $staffId = $this->resolveStaff($company,$request->staff_id,$start,$end,$request->menu_ids ?? []);

    if(!$staffId){
        return back()->with('error','空きスタッフがいません');
    }

	$customer = Customer::firstOrCreate(

	[
	'company_id'=>$company->id,
	'phone'=>str_replace('-', '', $request->customer_phone)
	],

	[
	'name'=>$request->customer_name,
	'email'=>$request->customer_email
	]

	);

    $staff = Staff::find($staffId);

    $reservation = Reservation::create([

        'company_id'=>$company->id,
	'customer_id'=>$customer->id,
        'staff_id'=>$staffId,

        'customer_name'=>$request->customer_name,
        'customer_phone'=>str_replace('-', '', $request->customer_phone),
        'customer_email'=>$request->customer_email,

        'start_at'=>$start,
        'end_at'=>$end,

        'price'=>$totalPrice,
        'nomination_fee'=>$staff->nomination_fee,
        'total_price'=>$totalPrice + $staff->nomination_fee,

        'status'=>'reserved',
        'cancel_token'=>Str::random(6)

    ]);

	$maxCycle = $menus->max('revisit_days');

	if($maxCycle){

	$customer->next_visit_at =
	Carbon::parse($reservation->start_at)
	->addDays($maxCycle);

	$customer->save();

	}

    foreach($menus as $menu){

        ReservationMenu::create([
            'reservation_id'=>$reservation->id,
            'menu_id'=>$menu->id,
            'price'=>$menu->price,
            'duration'=>$menu->duration
        ]);

    }

    return redirect("/r/".$company_code."/complete?reservation_id=".$reservation->id);
}

/*
|--------------------------------------------------------------------------
| complete
|--------------------------------------------------------------------------
*/

public function complete($company_code, Request $request)
{

    $company = Company::where('company_code',$company_code)->firstOrFail();

    $reservation = Reservation::where('id',$request->reservation_id)
        ->where('company_id',$company->id)
        ->firstOrFail();

    $menus = \App\Models\ReservationMenu::where('reservation_id',$reservation->id)
        ->with('menu')
        ->get();

    $staff = $reservation->staff;

    return view('reserve.complete',[
        'company'=>$company,
        'reservation'=>$reservation,
        'menus'=>$menus,
        'staff'=>$staff,
        'step'=>3
    ]);

}

/*
|--------------------------------------------------------------------------
| cancel
|--------------------------------------------------------------------------
*/
public function cancel($token)
{

$reservation = Reservation::where('cancel_token',$token)
->firstOrFail();

$reservation->status = 'cancelled';
$reservation->save();

return view('reserve.cancel_complete',[
'reservation'=>$reservation
]);

}

/*
|--------------------------------------------------------------------------
| スタッフ割当
|--------------------------------------------------------------------------
*/

private function resolveStaff($company,$staffId,$start,$end,$menuIds)
{

    /* ==========================
       指名スタッフがある場合
    ========================== */

    if($staffId){

        $vacation = Vacation::where('staff_id',$staffId)
            ->where('status','approved')
            ->where(function($q) use ($start,$end){

                $q->where('start_at','<',$end)
                  ->where('end_at','>',$start);

            })
            ->exists();

        if($vacation){
            return null;
        }

        $exists = Reservation::where('company_id',$company->id)
            ->where('staff_id',$staffId)
            ->where('status','reserved')
            ->where(function($q) use ($start,$end){

                $q->where('start_at','<',$end)
                  ->where('end_at','>',$start);

            })
            ->exists();

        return $exists ? null : $staffId;
    }


    /* ==========================
       自動スタッフ割当
    ========================== */

    $staffList = Staff::where('company_id',$company->id)
        ->where('is_reservable',true)
        ->whereHas('menus', function($q) use ($menuIds){

            $q->whereIn('menus.id',$menuIds);

        })
        ->orderBy('priority_order')
        ->get();


    foreach($staffList as $staff){

        /* ==========================
           休暇チェック
        ========================== */

        $vacation = Vacation::where('staff_id',$staff->id)
            ->where('status','approved')
            ->where(function($q) use ($start,$end){

                $q->where('start_at','<',$end)
                  ->where('end_at','>',$start);

            })
            ->exists();

        if($vacation){
            continue;
        }


        /* ==========================
           予約重複チェック
        ========================== */

        $exists = Reservation::where('company_id',$company->id)
            ->where('staff_id',$staff->id)
            ->where('status','reserved')
            ->where(function($q) use ($start,$end){

                $q->where('start_at','<',$end)
                  ->where('end_at','>',$start);

            })
            ->exists();


        if(!$exists){
            return $staff->id;
        }

    }

    return null;
}
/*
|--------------------------------------------------------------------------
| 空き時間
|--------------------------------------------------------------------------
*/

public function slots(Request $request,$company_code)
{

	$company = Company::where('company_code',$company_code)->firstOrFail();

	$menuIds = $request->menu_ids ?? [];

	$menus = Menu::whereIn('id',$menuIds)->get();

	if($menus->isEmpty()){
	return [];
	}

	$duration = $menus->sum('duration');

	$date = Carbon::parse($request->date);

	$openPatterns = $company->open_patterns ?? [];

	$patterns = $openPatterns[$date->dayOfWeek] ?? [];

	$staffList = Staff::where('company_id',$company->id)
	->where('is_reservable',true)
	->whereHas('menus',function($q) use ($menuIds){
	$q->whereIn('menus.id',$menuIds);
	})
	->get();

	$staffIds = $staffList->pluck('id');

	$reservations = Reservation::where('company_id',$company->id)
	->whereDate('start_at',$date)
	->where('status','reserved')
	->get();

	$vacations = Vacation::whereIn('staff_id',$staffIds)
	->where('status','approved')
	->whereDate('start_at','<=',$date)
	->whereDate('end_at','>=',$date)
	->get();

	$shifts = StaffShift::whereIn('staff_id',$staffIds)
	->whereDate('date',$date)
	->get()
	->keyBy(fn($s)=>$s->staff_id.'_'.$s->date);

	$patternIds = $shifts->pluck('shift_pattern_id');

	$shiftPatterns = ShiftPattern::whereIn('id',$patternIds)
	->get()
	->keyBy('id');

	$slots=[];

	foreach($patterns as $p){

	$open = Carbon::parse($date->format('Y-m-d').' '.$p['open']);
	$close = Carbon::parse($date->format('Y-m-d').' '.$p['close']);

	$time=$open->copy();

	while($time < $close){

	$start=$time->copy();
	$end=$start->copy()->addMinutes($duration);

	if($end > $close){
	break;
	}

	$availableStaff=0;

	foreach($staffList as $staff){

	$key=$staff->id.'_'.$date->format('Y-m-d');

	$shift=$shifts[$key] ?? null;

	if(!$shift || !$shift->is_work){
	continue;
	}

	$pattern=$shiftPatterns[$shift->shift_pattern_id] ?? null;

	if($pattern){

	$shiftStart=Carbon::parse($date->format('Y-m-d').' '.$pattern->start_time);
	$shiftEnd=Carbon::parse($date->format('Y-m-d').' '.$pattern->end_time);

	if($start < $shiftStart || $end > $shiftEnd){
	continue;
	}

	}

	$vacation=$vacations->first(function($v) use ($staff,$start,$end){
	return $v->staff_id==$staff->id &&
	$v->start_at < $end &&
	$v->end_at > $start;
	});

	if($vacation){
	continue;
	}

	$current=$reservations->filter(function($r) use ($staff,$start,$end){

	return $r->staff_id==$staff->id &&
	$r->start_at < $end &&
	$r->end_at > $start;

	})->count();

	if($current >= ($staff->max_simultaneous ?? 1)){
	continue;
	}

	$availableStaff++;

	}

	$slots[]=[
	'time'=>$start->format('H:i'),
	'remaining'=>$availableStaff
	];

	$time->addMinutes($company->slot_minutes);

	}

	}

	return response()->json($slots);

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