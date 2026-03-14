@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">

{{-- タイトル --}}
<div class="flex justify-between items-center mb-8">

    <div>
        <h1 class="text-2xl font-bold">
            メニュー対応スタッフ設定
        </h1>

        <p class="text-gray-500 text-sm mt-1">
            各メニューを担当できるスタッフを設定します
        </p>
    </div>

    <a href="{{ route('company.dashboard') }}"
       class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
       style="border-color: {{ $theme }}; color: {{ $theme }}">
       ← ダッシュボード
    </a>

</div>


<form method="POST" action="{{ route('company.menu-staff.update') }}">
@csrf

<div class="bg-white shadow rounded-2xl p-6 sm:p-8">


{{-- 全チェック --}}
<div class="mb-4">

<button
type="button"
onclick="toggleAll()"
class="text-white px-3 py-1 text-sm rounded"
style="background: {{ $theme }}">
全チェック
</button>

</div>


<div class="overflow-x-auto">

<table class="min-w-full text-sm border rounded-lg overflow-hidden">

<thead style="background: {{ $theme }}; color:white">

<tr>

<th class="p-3 text-left">
メニュー
</th>

@foreach($staffs as $staff)

<th class="p-3 text-center">

<div class="flex flex-col items-center">

<span>{{ $staff->name }}</span>

<input
type="checkbox"
class="staff-toggle mt-1"
data-staff="{{ $staff->id }}"
onclick="toggleStaff(this)"
>

</div>

</th>

@endforeach

</tr>

</thead>


<tbody class="divide-y">

@foreach($menus as $menu)

<tr class="hover:bg-gray-50">

<td class="p-3 font-semibold">

<div class="flex items-center gap-3">

<input
type="checkbox"
class="menu-toggle"
data-menu="{{ $menu->id }}"
onclick="toggleMenu(this)"
>

<span>{{ $menu->name }}</span>

</div>

</td>

@foreach($staffs as $staff)

@php
$checked = $relations
->where('menu_id',$menu->id)
->where('staff_id',$staff->id)
->count();
@endphp

<td class="p-3 text-center">

<input
type="checkbox"
name="relations[{{ $menu->id }}][]"
value="{{ $staff->id }}"
class="relation-checkbox w-5 h-5"
data-menu="{{ $menu->id }}"
data-staff="{{ $staff->id }}"
@if($checked) checked @endif
>

</td>

@endforeach

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- 保存ボタン --}}
<div class="flex justify-end mt-6">

<button
type="submit"
class="text-white px-6 py-2 rounded-lg shadow"
style="background: {{ $theme }}">
保存する
</button>

</div>

</div>

</form>

</div>


<script>

/* 全チェック */

function toggleAll(){

let boxes=document.querySelectorAll('.relation-checkbox')

let checked=[...boxes].every(b=>b.checked)

boxes.forEach(b=>b.checked=!checked)

}


/* スタッフ列チェック */

function toggleStaff(el){

let staff=el.dataset.staff

document.querySelectorAll('.relation-checkbox')
.forEach(box=>{

if(box.dataset.staff==staff){
box.checked=el.checked
}

})

}


/* メニュー行チェック */

function toggleMenu(el){

let menu=el.dataset.menu

document.querySelectorAll('.relation-checkbox')
.forEach(box=>{

if(box.dataset.menu==menu){
box.checked=el.checked
}

})

}

</script>

@endsection