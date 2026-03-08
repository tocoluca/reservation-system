<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vacation;
use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class ReserveController extends Controller
{

/*
|--------------------------------------------------------------------------
| 予約制御
|--------------------------------------------------------------------------
*/

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
        'start'=>$startReservableDate,
        'end'=>$lastReservableDate,
        'close'=>$closeLimit
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

/*$menus = Menu::where('company_id',$company->id)->get();*/
$menus = Menu::with(['tags','category'])
    ->where('company_id',$company->id)
    ->where('is_active',1)
    ->orderByDesc('is_popular')
    ->orderBy('sort_order')
    ->get()
    ->groupBy(function($menu){
        return $menu->category->name ?? 'その他';
    });

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

public function confirm(Request $request,$company_code)
{

$company = Company::where('company_code',$company_code)->firstOrFail();

$menu = Menu::where('company_id',$company->id)
->findOrFail($request->menu_id);

$staff = null;

if($request->staff_id){

$staff = Staff::find($request->staff_id);

}

return view('reserve.confirm',[

'company'=>$company,
'menu'=>$menu,
'staff'=>$staff,
'start_at'=>$request->start_at,
'step'=>2

]);

}

public function complete($company_code, Request $request)
{
Log::debug('ログ');

$company = Company::where('company_code',$company_code)->firstOrFail();

$reservation = Reservation::where('id',$request->reservation_id)
->where('company_id',$company->id)
->firstOrFail();

$menu = $reservation->menu;
$staff = $reservation->staff;

return view('reserve.complete',[

'company'=>$company,
'reservation'=>$reservation,
'menu'=>$menu,
'staff'=>$staff,
'step'=>3

]);

}

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
| 予約確定
|--------------------------------------------------------------------------
*/

public function store(Request $request,$company_code)
{

$company = Company::where('company_code',$company_code)->firstOrFail();

$menu = Menu::where('company_id',$company->id)
->findOrFail($request->menu_id);

$start = Carbon::parse($request->start_at);

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

$end = $start->copy()->addMinutes($menu->duration);

$staffId = $request->staff_id;

/*
|--------------------------------------------------------------------------
| 指名なし自動割当
|--------------------------------------------------------------------------
*/

if(!$staffId){

$staffList = Staff::where('company_id',$company->id)
->where('is_reservable',true)
->orderBy('priority_order')
->get();

foreach($staffList as $staff){

$exists = Reservation::where('company_id',$company->id)
->where('staff_id',$staff->id)
->where('status','reserved')
->where(function($q) use ($start,$end){

$q->where('start_at','<',$end)
->where('end_at','>',$start);

})
->exists();

if(!$exists){

$staffId = $staff->id;
break;

}

}

if(!$staffId){
return back()->with('error','空きスタッフがいません');
}

}

/*
|--------------------------------------------------------------------------
| 重複チェック
|--------------------------------------------------------------------------
*/

$exists = Reservation::where('company_id',$company->id)
->where('staff_id',$staffId)
->where('status','reserved')
->where(function($q) use ($start,$end){

$q->where('start_at','<',$end)
->where('end_at','>',$start);

})
->exists();

if($exists){
return back()->with('error','この時間は予約済みです');
}

/*
|--------------------------------------------------------------------------
| 同時予約数
|--------------------------------------------------------------------------
*/

$overlapCount = Reservation::where('company_id',$company->id)
->where('status','reserved')
->where(function ($q) use ($start,$end) {
$q->where('start_at','<',$end)
->where('end_at','>',$start);
})
->count();

if ($overlapCount >= $company->max_simultaneous_reservations) {
return back()->with('error','この時間は予約上限です');
}

/*
|--------------------------------------------------------------------------
| 保存
|--------------------------------------------------------------------------
*/

$staff = Staff::find($staffId);
$reservation = Reservation::create([

'company_id'=>$company->id,
'staff_id'=>$staffId,
'menu_id'=>$menu->id,

'customer_name'=>$request->customer_name,
'customer_phone'=>$request->customer_phone,
'customer_email'=>$request->customer_email,

'start_at'=>$start,
'end_at'=>$end,

'price'=>$menu->price,
'nomination_fee'=>$staff->nomination_fee,
'total_price'=>$menu->price + $staff->nomination_fee,

'status'=>'reserved',
'cancel_token'=>Str::random(6)

]);

return redirect("/r/".$company_code."/complete?reservation_id=".$reservation->id);

}

/*
|--------------------------------------------------------------------------
| 空き時間
|--------------------------------------------------------------------------
*/

public function slots(Request $request,$company_code)
{

$cacheKey = 'slots_'.
$company_code.'_'.
$request->date.'_'.
($request->menu_id ?? 0).'_'.
($request->staff_id ?? 0);

return Cache::remember($cacheKey,60,function() use ($request,$company_code){

$company = Company::where('company_code',$company_code)->firstOrFail();

$date = Carbon::parse($request->date);

$limits = $this->getReservationLimits($company);

if ($date->startOfDay() < $limits['start'] || $date->startOfDay() > $limits['end']) {
return [];
}

$menu = Menu::where('company_id',$company->id)
->findOrFail($request->menu_id);

$duration = $menu->duration;

$slotMinutes = $company->slot_minutes;

$staffId = $request->staff_id ?: null;

$weekday = $date->dayOfWeek;

$patterns = (array) ($company->open_patterns ?? []);
$patterns = $patterns[$weekday] ?? [];

/*
|--------------------------------------------------------------------------
| 予約取得
|--------------------------------------------------------------------------
*/

$reservations = Reservation::where('company_id',$company->id)
->whereDate('start_at',$date)
->where('status','reserved')
->get();

$staffList = Cache::remember(
'staff_'.$company->id,
600,
function() use ($company){
    return Staff::where('company_id',$company->id)
        ->where('is_reservable',true)
        ->get();
});

$totalStaff = $staffList->count();

$slots = [];

foreach($patterns as $p){

if(empty($p['open']) || empty($p['close'])){
continue;
}

$open = Carbon::parse($date->format('Y-m-d').' '.$p['open']);
$close = Carbon::parse($date->format('Y-m-d').' '.$p['close']);

$time = $open->copy();

while($time < $close){

$start = $time->copy();
$end = $start->copy()->addMinutes($duration);

if($end > $close){
break;
}

$reservedCount = 0;

if(!$staffId){

foreach($staffList as $staff){

$exists = $reservations
->where('staff_id',$staff->id)
->first(function($r) use ($start,$end){

return $r->start_at < $end && $r->end_at > $start;

});

if($exists){
$reservedCount++;
}

}

}else{

$exists = $reservations
->where('staff_id',$staffId)
->first(function($r) use ($start,$end){

return $r->start_at < $end && $r->end_at > $start;

});

$reservedCount = $exists ? 1 : 0;
$totalStaff = 1;

}

if($reservedCount >= $totalStaff){

$status='×';

}
elseif($reservedCount>0){

$status='△';

}
else{

$status='○';

}

$slots[]=[
'time'=>$start->format('H:i'),
'status'=>$status
];

$time->addMinutes($slotMinutes);

}

}

return $slots;

});
}
}