@extends('layouts.app')

@section('content')

@php
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-md mx-auto px-4 py-6">

<h1 class="text-xl font-bold text-center mb-6">
{{ $company->name }} 予約
</h1>

<form method="POST" action="/r/{{ $company->company_code }}/confirm">
@csrf

<input type="hidden" name="start_at" id="start_at">

{{-- メニュー --}}
<div class="mb-8">

<h2 class="font-bold mb-3 text-lg">
STEP1 メニュー
</h2>

@foreach($menus as $categoryName => $categoryMenus)

<div class="mb-6">

<div class="font-bold text-gray-700 mb-3">
{{ $categoryName }}
</div>

<div class="space-y-3">

@foreach($categoryMenus as $menu)

<label class="block bg-white border rounded-xl p-4 shadow-sm cursor-pointer hover:border-gray-300">

<input
type="radio"
name="menu_id"
value="{{ $menu->id }}"
data-price="{{ $menu->price }}"
data-duration="{{ $menu->duration }}"
class="mr-2"
required>

<div class="flex gap-3">

{{-- メニュー画像 
@if($menu->image)

<img
src="{{ $menu->image_url }}"
class="w-16 h-16 rounded-lg object-cover">

@else

<img
src="{{ asset('images/noimage.png') }}"
class="w-16 h-16 rounded-lg object-cover">

@endif
--}}

<div class="flex-1">

{{-- メニュー名 --}}
<div class="font-semibold">

{{ $menu->name }}

@if($menu->is_popular)

<span class="text-xs px-2 py-1 ml-1 rounded-full text-white"
style="background: {{ $theme }}">
人気
</span>

@endif

</div>


{{-- タグ --}}
@if($menu->tags->count())

<div class="flex flex-wrap gap-1 mt-1">

@foreach($menu->tags as $tag)

<span class="text-xs px-2 py-1 rounded-full text-white"
style="background: {{ $theme }}">
{{ $tag->name }}
</span>

@endforeach

</div>

@endif


{{-- 価格 --}}
<div class="text-sm text-gray-500 mt-1">

{{ number_format($menu->price) }}円  
・{{ $menu->duration }}分

</div>

</div>

</div>

</label>

@endforeach

</div>

</div>

@endforeach

</div>

{{-- スタッフ --}}
<div class="mb-8">

<h2 class="font-bold mb-3 text-lg">
STEP2 スタッフ
</h2>

<div class="space-y-3">

{{-- 指名なし --}}
<label class="block bg-white border rounded-xl p-4 shadow-sm cursor-pointer">

<input type="radio"
name="staff_id"
value=""
data-fee="0"
class="mr-2">

<div class="flex items-center gap-3">

<img
src="{{ asset('images/noimage.png') }}"
class="w-14 h-14 rounded-full object-cover">

<div>

<div class="font-semibold">
指名なし
</div>

<div class="text-sm text-gray-500">
担当者を指定しません
</div>

</div>

</div>

</label>

{{-- スタッフ一覧 --}}
@foreach($staff as $s)

<label class="block bg-white border rounded-xl p-4 shadow-sm cursor-pointer">

<input
type="radio"
name="staff_id"
value="{{ $s->id }}"
data-fee="{{ $s->nomination_fee }}"
class="mr-2">

<div class="flex gap-3">

<img
src="{{ $s->image_url }}"
class="w-14 h-14 rounded-full object-cover">

<div class="flex-1">

<div class="font-semibold">

{{ $s->name }}

@if($s->nomination_fee)
<span class="text-sm text-gray-500">
(+{{ $s->nomination_fee }}円)
</span>
@endif

</div>

@if($s->comment)

<div class="text-sm text-gray-500 mt-1">
{{ $s->comment }}
</div>

@endif

</div>

</div>

</label>

@endforeach

</div>

</div>

{{-- 日付 --}}
<div class="mb-8">

<h2 class="font-bold mb-3 text-lg">
STEP3 日付
</h2>

<input
type="text"
id="date"
class="border rounded-lg p-3 w-full">

</div>

{{-- 空き時間 --}}
<div class="mb-8">

<h2 class="font-bold mb-3 text-lg">
STEP4 時間
</h2>

<div id="slots" class="grid grid-cols-3 gap-2"></div>

</div>

{{-- 料金 --}}
<div class="mb-6 text-lg font-bold text-center">

合計料金  
<span id="price">0</span>円

</div>

<button
style="background: {{ $theme }}"
class="text-white w-full py-4 rounded-xl text-lg font-bold">

予約確認へ

</button>

</form>

</div>

@endsection


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>


<script>

document.addEventListener("DOMContentLoaded", function(){

document.querySelectorAll('[name=menu_id]').forEach(el=>{
el.addEventListener('change',updatePrice)
})

document.querySelectorAll('[name=staff_id]').forEach(el=>{
el.addEventListener('change',function(){
updatePrice()
loadSlots()
})
})

flatpickr("#date",{
locale:"ja",
minDate:"today",
dateFormat:"Y-m-d",
onChange:function(){
loadSlots()
}
})

})

function updatePrice(){

let menu = document.querySelector('[name=menu_id]:checked')
let staff = document.querySelector('[name=staff_id]:checked')

let menuPrice = menu ? Number(menu.dataset.price || 0) : 0
let staffFee = staff ? Number(staff.dataset.fee || 0) : 0

document.getElementById('price').innerText = menuPrice + staffFee

}

function loadSlots(){

let date = document.getElementById('date').value
if(!date){
return
}
let menuEl = document.querySelector('[name=menu_id]:checked')

if(!menuEl){
return
}

let menu = menuEl.value
let staffEl = document.querySelector('[name=staff_id]:checked')
let staff = staffEl ? staffEl.value : ''

fetch(`/r/{{ $company->company_code }}/slots?date=${date}&menu_id=${menu}&staff_id=${staff}`)
.then(r=>r.json())
.then(data=>{

let html=''

data.forEach(slot=>{

if(slot.status=='○'){

html+=`<button type="button"
data-time="${slot.time}"
class="border rounded-lg py-2 text-center hover:bg-gray-100 slot-btn">
${slot.time}
</button>`

}else{

html+=`<div class="border rounded-lg py-2 text-center text-gray-400">
${slot.time}
</div>`

}

})

document.getElementById('slots').innerHTML=html

document.querySelectorAll('.slot-btn').forEach(btn=>{
btn.addEventListener('click',function(){

let datetime = date + ' ' + this.dataset.time
document.getElementById('start_at').value = datetime

document.querySelectorAll('.slot-btn').forEach(el=>{
el.classList.remove('text-white')
el.style.background=''
})

this.style.background="{{ $theme }}"
this.classList.add('text-white')

})
})

})

}

document.addEventListener("DOMContentLoaded", function(){

document.querySelector("form").addEventListener("submit",function(e){

let start = document.getElementById("start_at").value

if(!start){

alert("日時を選択してください")
e.preventDefault()

}

})

})

</script>