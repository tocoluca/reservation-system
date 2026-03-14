@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';

$days = \Carbon\Carbon::parse($month)->daysInMonth;

use Yasumi\Yasumi;
$holidays = Yasumi::create('Japan', \Carbon\Carbon::parse($month)->year);
@endphp

<div class="max-w-full mx-auto px-6 py-6">

<div class="flex justify-between items-center mb-6">

<div>
<h1 class="text-2xl font-bold">月シフト管理</h1>
<p class="text-gray-500 text-sm">{{ $month }}</p>
</div>

<div class="flex gap-3">

<a href="{{ route('company.dashboard') }}"
class="px-4 py-2 border rounded text-sm"
style="border-color:{{ $theme }};color:{{ $theme }}">
ダッシュボード
</a>

<button
form="shiftForm"
class="px-6 py-2 text-white rounded shadow"
style="background:{{ $theme }}">
保存
</button>

</div>

</div>

<div class="flex gap-3 mb-6">

<form method="GET">
<input type="month" name="month" value="{{ $month }}" class="border p-2 rounded">

<button class="px-4 py-2 text-white rounded" style="background:{{ $theme }}">
表示
</button>

</form>


<form method="POST" action="{{ route('company.staff-shifts.generate') }}">
@csrf
<input type="hidden" name="month" value="{{ $month }}">

<button class="px-4 py-2 bg-gray-700 text-white rounded">
基本シフト生成
</button>

</form>


<form method="POST" action="{{ route('company.staff-shifts.copy') }}">
@csrf
<input type="hidden" name="month" value="{{ $month }}">

<button class="px-4 py-2 bg-indigo-600 text-white rounded">
前月コピー
</button>

</form>

</div>


<div class="flex gap-4 mb-6 flex-wrap">

<button onclick="setAllShift('早番')" class="px-4 py-2 bg-blue-500 text-white rounded">
早番一括
</button>

<button onclick="setAllShift('遅番')" class="px-4 py-2 bg-purple-500 text-white rounded">
遅番一括
</button>


<select id="weekdaySelect" class="border p-2 rounded">
<option value="">曜日一括設定</option>
<option value="0">日曜</option>
<option value="1">月曜</option>
<option value="2">火曜</option>
<option value="3">水曜</option>
<option value="4">木曜</option>
<option value="5">金曜</option>
<option value="6">土曜</option>
</select>


<select id="weekdayShift" class="border p-2 rounded">
<option value="">シフト</option>

@foreach($patterns as $p)
<option value="{{ $p->id }}">{{ $p->name }}</option>
@endforeach

</select>


<button onclick="applyWeekdayShift()" class="px-4 py-2 bg-green-600 text-white rounded">
適用
</button>

</div>


<form id="shiftForm" method="POST" action="{{ route('company.staff-shifts.update') }}">
@csrf

<div class="border rounded-xl shadow">

<div class="overflow-x-auto max-h-[70vh]">

<table class="min-w-max text-sm">

<thead class="sticky top-0 z-10" style="background:{{ $theme }};color:white">

<tr>

<th class="p-3 sticky left-0 bg-white text-black z-20 w-[260px]">
スタッフ
</th>


@for($d=1;$d<=$days;$d++)

@php
$dateObj = \Carbon\Carbon::parse("$month-$d");

$isHoliday = $holidays->isHoliday($dateObj);
$dayOfWeek = $dateObj->dayOfWeek;

$color = "";

if($isHoliday){
$color = "bg-red-200 text-red-900";
}elseif($dayOfWeek == 0){
$color = "text-red-600";
}elseif($dayOfWeek == 6){
$color = "text-blue-600";
}
@endphp


<th class="p-2 text-center min-w-[90px] {{ $color }}" data-day="{{ $d }}" data-weekday="{{ $dayOfWeek }}">

<div class="font-bold">{{ $d }}</div>

<div class="text-xs mb-1">
{{ ['日','月','火','水','木','金','土'][$dayOfWeek] }}
</div>

<div class="flex justify-center gap-1">

<button type="button"
onclick="setDayShift({{ $d }},'早番')"
class="text-[10px] bg-blue-500 text-white px-1 rounded">
早
</button>

<button type="button"
onclick="setDayShift({{ $d }},'遅番')"
class="text-[10px] bg-purple-500 text-white px-1 rounded">
遅
</button>

<button type="button"
onclick="setDayShift({{ $d }},'')"
class="text-[10px] bg-gray-500 text-white px-1 rounded">
休
</button>

</div>

</th>

@endfor

</tr>

</thead>

<tbody>

@foreach($staffs as $staff)

<tr class="border-b hover:bg-gray-50">

<td class="p-3 font-semibold sticky left-0 bg-white w-[260px]">

<div class="flex items-center gap-2">

<span class="w-24">{{ $staff->name }}</span>

<button type="button"
onclick="setStaffShift({{ $staff->id }},'早番')"
class="text-xs bg-blue-500 text-white px-2 py-1 rounded">早</button>

<button type="button"
onclick="setStaffShift({{ $staff->id }},'遅番')"
class="text-xs bg-purple-500 text-white px-2 py-1 rounded">遅</button>

<button type="button"
onclick="setStaffShift({{ $staff->id }},'')"
class="text-xs bg-gray-500 text-white px-2 py-1 rounded">休</button>

</div>

</td>


@for($d=1;$d<=$days;$d++)

@php
$date = $month.'-'.str_pad($d,2,'0',STR_PAD_LEFT);

$shift = $shifts[$staff->id][$date][0] ?? null;

$staffVacations = $vacations[$staff->id] ?? collect();

$isVacation = $staffVacations->first(function($v) use ($date){

return $date >= \Carbon\Carbon::parse($v->start_at)->toDateString()
&& $date <= \Carbon\Carbon::parse($v->end_at)->toDateString();

});

$business = $businessDays[\Carbon\Carbon::parse($date)->format('Y-m-d H:i:s')] ?? null;

$isClosed = false;

if($business){

if(
$business->is_open === false &&
is_null($business->open_time) &&
is_null($business->close_time)
){
$isClosed = true;
}

}

@endphp


<td class="p-1 min-w-[70px]">

@if($isClosed)

<div class="text-gray-400 text-center font-semibold">
休業
</div>

@elseif($isVacation)

<div class="text-red-500 text-center font-bold">
休
</div>

@else

<select
name="shifts[{{ $staff->id }}][{{ $date }}]"
class="border rounded text-xs w-full p-1 shift-select"
data-staff="{{ $staff->id }}"
data-day="{{ $d }}"
data-date="{{ $date }}">

<option value="">休</option>

@foreach($patterns as $p)

<option value="{{ $p->id }}"
data-name="{{ $p->name }}"
@if($shift && $shift->shift_pattern_id==$p->id) selected @endif>

{{ $p->name }}

</option>

@endforeach

</select>

@endif

</td>

@endfor

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</form>

</div>



<script>

function setAllShift(name){

document.querySelectorAll('.shift-select').forEach(select=>{

for(let opt of select.options){

if(opt.dataset.name===name){
select.value=opt.value
}

}

})

}



function setStaffShift(staffId,name){

document.querySelectorAll('.shift-select').forEach(select=>{

if(select.dataset.staff==staffId){

for(let opt of select.options){

if(opt.dataset.name===name){
select.value=opt.value
}

}

if(name===''){
select.value=''
}

}

})

}



function setDayShift(day,name){

document.querySelectorAll('.shift-select').forEach(select=>{

if(select.dataset.day==day){

for(let opt of select.options){

if(opt.dataset.name===name){
select.value=opt.value
}

}

if(name===''){
select.value=''
}

}

})

}



function applyWeekdayShift(){

let weekday=document.getElementById('weekdaySelect').value
let shift=document.getElementById('weekdayShift').value

if(!weekday||!shift)return

let headers=document.querySelectorAll('th[data-weekday]')

headers.forEach((th,index)=>{

if(th.dataset.weekday==weekday){

document.querySelectorAll('tbody tr').forEach(row=>{

let select=row.children[index+1].querySelector('select')

if(select){
select.value=shift
}

})

}

})

}

</script>

@endsection