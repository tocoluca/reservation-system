<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vacation;
use App\Models\Company;
use App\Models\Menu;
use App\Models\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReserveController extends Controller
{

public function index($company_code)
{

$company = Company::where('company_code',$company_code)->firstOrFail();

$menus = Menu::where('company_id',$company->id)->get();

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

public function store(Request $request,$company_code)
{

$company = Company::where('company_code',$company_code)->firstOrFail();

$menu = Menu::where('company_id',$company->id)
->findOrFail($request->menu_id);

$start = Carbon::parse($request->start_at);

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
| 予約重複チェック
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
| 予約保存
|--------------------------------------------------------------------------
*/

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
'nomination_fee'=>Staff::find($staffId)->nomination_fee,
'total_price'=>$menu->price + Staff::find($staffId)->nomination_fee,

'status'=>'reserved',
'cancel_token'=>Str::uuid()

]);

return redirect("/r/".$company_code."/complete?reservation_id=".$reservation->id);

}


public function slots(Request $request,$company_code)
{

$company = Company::where('company_code',$company_code)->firstOrFail();

$date = Carbon::parse($request->date);

$menu = Menu::where('company_id',$company->id)
->findOrFail($request->menu_id);

$duration = $menu->duration;

$slotMinutes = $company->slot_minutes;

$staffId = $request->staff_id ?: null;

$open = Carbon::parse($date->format('Y-m-d').' '.$company->open_time);
$close = Carbon::parse($date->format('Y-m-d').' '.$company->close_time);

$slots = [];

$time = $open->copy();

while ($time < $close) {

$start = $time->copy();
$end = $start->copy()->addMinutes($duration);

if($end > $close){
break;
}

$available = $this->checkSlot($company,$staffId,$start,$end);

$slots[] = [
'time'=>$start->format('H:i'),
'status'=>$available ? '○':'×'
];

$time->addMinutes($slotMinutes);

}

return response()->json($slots);

}

private function checkSlot($company,$staffId,$start,$end)
{

if(!$staffId){

$staffList = Staff::where('company_id',$company->id)
->where('is_reservable',true)
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
return true;
}

}

return false;

}


/*
|--------------------------------------------------------------------------
| 指名あり
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

return !$exists;

}

}