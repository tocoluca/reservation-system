@extends('layouts.app')

@section('content')

@php
$theme = $company->theme_color ?? '#3b82f6';

$totalPrice = $menus->sum('price');
$staffFee = $staff->nomination_fee ?? 0;
@endphp

<div class="max-w-md mx-auto p-6">

<h1 class="text-xl font-bold mb-6 text-center">
予約確認
</h1>

<div class="bg-white shadow rounded-xl p-5 mb-6 space-y-3">

<div>
メニュー
<div class="font-bold mt-1">

@foreach($menus as $menu)
<div>
{{ $menu->name }}
<span class="text-sm text-gray-500">
({{ $menu->duration }}分)
</span>
</div>
@endforeach

</div>
</div>

<div>
担当  

<strong>
@if($staff)
{{ $staff->name }}
@else
指名なし
@endif
</strong>

</div>

<div>
日時  

<strong>
{{ \Carbon\Carbon::parse($start_at)->format('Y年m月d日 H:i') }}
</strong>

</div>

<div>
料金  

<strong>
{{ number_format($totalPrice + $staffFee) }}円
</strong>

</div>

</div>


<form method="POST" action="/r/{{ $company->company_code }}/store">
@csrf

@foreach($menus as $menu)
<input type="hidden" name="menu_ids[]" value="{{ $menu->id }}">
@endforeach

<input type="hidden" name="staff_id" value="{{ $staff->id ?? '' }}">
<input type="hidden" name="start_at" value="{{ $start_at }}">

<h2 class="font-bold mb-3">
お客様情報
</h2>

<input
type="text"
name="customer_name"
placeholder="お名前"
class="border rounded-lg p-3 w-full mb-3"
required>

<input
type="tel"
name="customer_phone"
placeholder="電話番号"
class="border rounded-lg p-3 w-full mb-3"
required>

<input
type="email"
name="customer_email"
placeholder="メール（任意）"
class="border rounded-lg p-3 w-full mb-4">

<button
style="background: {{ $theme }}"
class="text-white w-full py-4 rounded-xl font-bold text-lg">

予約を確定

</button>

</form>

<button
onclick="history.back()"
class="text-center w-full mt-4 text-blue-500">

← 予約内容を変更

</button>

</div>

@endsection