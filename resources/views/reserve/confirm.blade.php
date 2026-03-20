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
合計料金（目安）

<strong>
{{ number_format($totalPrice + $staffFee) }}円～
</strong>
<p class="text-xs text-gray-500 mt-1">
※事前に目安料金をご案内しておりますが、当日の施術内容や髪の状態により変動する場合がございます。
</p>
</div>

</div>

<div id="errorArea" class="mb-4 hidden">
    <div class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm"></div>
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
pattern="[0-9\-]+"
inputmode="numeric"
class="border rounded-lg p-3 w-full mb-3"
oninput="formatTel(this)"
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
<script>
function formatTel(input) {
    input.value = input.value.replace(/[^0-9\-]/g, '');
}
function submitReservation() {

    clearErrors(); // 🔥 追加

    const name  = document.getElementById('modal_customer_name').value;
    const phone = document.getElementById('modal_customer_phone').value;

    fetch('/company/reservation', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({
            start_at: selectedDatetime,
            customer_name: name,
            customer_phone: phone,
            staff_id: selectedStaffId
        })
    })
    .then(async res => {

        const data = await res.json();

        if (!res.ok) {
            showErrors(data.errors);
            return;
        }

        if(data.success){
            closeModal();
            loadCalendar();
        }

    });
}
function showErrors(errors) {

    const area = document.getElementById('errorArea');
    const box  = area.firstElementChild;

    let html = '';

    Object.keys(errors).forEach(key => {

        errors[key].forEach(msg => {
            html += `<div>${msg}</div>`;
        });

        // 🔥 該当input赤くする
        const input = document.getElementById('modal_' + key.replace('customer_', 'customer_'));
        if (input) {
            input.classList.add('border-red-500');
        }
    });

    box.innerHTML = html;
    area.classList.remove('hidden');
}
function clearErrors() {

    // エリア消す
    document.getElementById('errorArea').classList.add('hidden');

    // 赤枠リセット
    document.querySelectorAll('#reserveModal input').forEach(el => {
        el.classList.remove('border-red-500');
    });
}
</script>

@endsection