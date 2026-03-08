@extends('layouts.app')

@section('content')

@php
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-md mx-auto p-6 text-center">

<h1 class="text-xl font-bold mb-6 text-green-600">
予約が完了しました
</h1>

<div class="bg-white shadow rounded-xl p-5 mb-6 text-left space-y-2">

<div>
店舗  
<strong>{{ $company->name }}</strong>
</div>

<div>
メニュー  
<strong>{{ $menu->name }}</strong>
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
{{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}
</strong>

</div>

<div>
料金  

<strong>
{{ number_format($reservation->total_price) }}円
</strong>

</div>

</div>

<a
href="https://calendar.google.com/calendar/render?action=TEMPLATE
&text={{ urlencode($company->name.' 予約') }}
&dates={{ \Carbon\Carbon::parse($reservation->start_at)->format('Ymd\THis') }}/{{ \Carbon\Carbon::parse($reservation->end_at)->format('Ymd\THis') }}
&details={{ urlencode($menu->name) }}"
target="_blank"
class="block text-white py-4 rounded-xl mb-4"
style="background: {{ $theme }}">

Googleカレンダーに追加

</a>

<a
href="{{ url('/cancel/'.$reservation->cancel_token) }}"
class="text-red-500 underline block mb-4">
予約をキャンセル
</a>

<button
onclick="closePage()"
class="border py-3 rounded-xl w-full">

画面を閉じる

</button>

<script>

function closePage(){

window.close()

setTimeout(function(){
location.href="/r/{{ $company->company_code }}"
},200)

}

</script>

</div>

@endsection