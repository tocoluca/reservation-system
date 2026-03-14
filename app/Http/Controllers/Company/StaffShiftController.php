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
use Illuminate\Support\Facades\Log;

class StaffShiftController extends Controller
{

/*
月シフト画面
*/

public function index(Request $request)
{

$company = auth()->guard('company')->user()->company;

$month = $request->month ?? now()->format('Y-m');

$start = Carbon::parse($month.'-01');
$end = $start->copy()->endOfMonth();

$staffs = Staff::where('company_id',$company->id)
->orderBy('priority_order')
->get();

$patterns = ShiftPattern::where('company_id',$company->id)->get();


$vacations = Vacation::where('status','approved')
->where(function($q) use ($start,$end){

$q->whereBetween('start_at',[$start,$end])
  ->orWhereBetween('end_at',[$start,$end])
  ->orWhere(function($q2) use ($start,$end){
      $q2->where('start_at','<=',$start)
         ->where('end_at','>=',$end);
  });

})
->get()
->groupBy('staff_id');

$businessDays = CompanyBusinessCalendar::where('company_id',$company->id)
    ->whereBetween('date',[$start,$end])
    ->get()
    ->keyBy('date');

$shifts = StaffShift::whereBetween('date',[$start,$end])
    ->get()
    ->groupBy(['staff_id','date']);

return view('company.staff_shifts',[

'month'=>$month,
'staffs'=>$staffs,
'patterns'=>$patterns,
'shifts'=>$shifts,
'vacations'=>$vacations,
'businessDays'=>$businessDays
]);

}


/*
基本シフトから月シフト生成
*/

public function generate(Request $request)
{

$company = auth()->guard('company')->user()->company;

$month = $request->month;

$start = Carbon::parse($month.'-01');

$days = $start->daysInMonth;

$staffs = Staff::where('company_id',$company->id)->get();

foreach($staffs as $staff){

for($d=1;$d<=$days;$d++){

$date = Carbon::parse("$month-$d");

$weekday = $date->dayOfWeek;

$default = StaffDefaultShift::where('staff_id',$staff->id)
->where('weekday',$weekday)
->first();

if(!$default) continue;

StaffShift::updateOrCreate(

[
'staff_id'=>$staff->id,
'date'=>$date
],

[
'shift_pattern_id'=>$default->shift_pattern_id,
'is_work'=>$default->is_work
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

foreach($request->shifts as $staffId=>$dates){

foreach($dates as $date=>$pattern){

StaffShift::updateOrCreate(

[
'staff_id'=>$staffId,
'date'=>$date
],

[
'shift_pattern_id'=>$pattern ?: null,
'is_work'=>$pattern ? 1 : 0
]

);

}

}

    return back()->with('success','シフトを保存しました');

}

public function copy(Request $request)
{

$company = auth()->guard('company')->user()->company;

$month = $request->month;

$currentStart = Carbon::parse($month.'-01');
$prevStart = $currentStart->copy()->subMonth();

$currentEnd = $currentStart->copy()->endOfMonth();
$prevEnd = $prevStart->copy()->endOfMonth();

$prevShifts = StaffShift::whereBetween('date',[$prevStart,$prevEnd])->get();

foreach($prevShifts as $shift){

$newDate = Carbon::parse($shift->date)->addMonth();

StaffShift::updateOrCreate(

[
'staff_id'=>$shift->staff_id,
'date'=>$newDate
],

[
'shift_pattern_id'=>$shift->shift_pattern_id,
'is_work'=>$shift->is_work
]

);

}

return back()->with('success','前月シフトをコピーしました');

}

}