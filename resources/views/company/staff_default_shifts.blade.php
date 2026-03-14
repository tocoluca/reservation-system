@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto">

<div class="flex justify-between mb-6">

<h1 class="text-xl font-bold">
基本シフト
</h1>

<a href="{{ route('company.dashboard') }}"
class="px-3 py-1 border rounded"
style="border-color:{{ $theme }};color:{{ $theme }}">
← ダッシュボード
</a>

</div>


<form method="POST" action="{{ route('company.staff-default-shifts') }}">
@csrf

<div class="bg-white shadow rounded-xl p-6 overflow-x-auto">

<table class="min-w-full border text-sm">

<thead style="background:{{ $theme }};color:white">

<tr>

<th class="p-3">スタッフ</th>

@foreach(['月','火','水','木','金','土','日'] as $i=>$d)

<th class="p-3 text-center">{{ $d }}</th>

@endforeach

</tr>

</thead>

<tbody>

@foreach($staffs as $staff)

<tr>

<td class="p-3 font-semibold">
{{ $staff->name }}
</td>

@for($w=1;$w<=7;$w++)

@php

$shift = $shifts
->where('staff_id',$staff->id)
->where('weekday',$w%7)
->first();

@endphp

<td class="p-2">

<select
name="shifts[{{ $staff->id }}][{{ $w%7 }}]"
class="border rounded p-1 w-full">

<option value="">
休
</option>

@foreach($patterns as $p)

<option
value="{{ $p->id }}"
@if($shift && $shift->shift_pattern_id==$p->id) selected @endif
>

{{ $p->name }}

</option>

@endforeach

</select>

</td>

@endfor

</tr>

@endforeach

</tbody>

</table>


<div class="mt-6 text-right">

<button
class="px-6 py-2 text-white rounded"
style="background:{{ $theme }}">
保存
</button>

</div>

</div>

</form>

</div>

@endsection