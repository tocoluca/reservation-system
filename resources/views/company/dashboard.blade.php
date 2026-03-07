@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

{{-- ============================= --}}
{{-- ヘッダー --}}
{{-- ============================= --}}

<div class="mb-8">

<h1 class="text-2xl sm:text-3xl font-bold">
ダッシュボード
</h1>

<p class="text-gray-500 mt-2 text-sm sm:text-base">
{{ $staff->company->name }} ｜ {{ $staff->name }}（{{ $staff->role }}）
</p>

</div>


{{-- ============================= --}}
{{-- 管理メニュー --}}
{{-- ============================= --}}

<div class="mb-12">

<h2 class="text-lg sm:text-xl font-bold mb-6">
管理メニュー
</h2>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

<a href="{{ route('company.reserve') }}"
class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-blue-500">

<div class="text-blue-500 text-xs font-semibold mb-2">
RESERVATION
</div>

<div class="text-lg font-bold mb-2">
予約カレンダー
</div>

<div class="text-gray-500 text-sm">
予約の確認・登録・管理
</div>

</a>

@if(in_array($staff->role,['master','area_leader','leader']))

<a href="{{ route('company.calendar.index') }}"
class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-emerald-500">

<div class="text-emerald-500 text-xs font-semibold mb-2">
BUSINESS
</div>

<div class="text-lg font-bold mb-2">
営業日カレンダー
</div>

</a>

<a href="{{ route('company.staff.index') }}"
class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-indigo-500">

<div class="text-indigo-500 text-xs font-semibold mb-2">
STAFF
</div>

<div class="text-lg font-bold mb-2">
担当者管理
</div>

</a>

<a href="{{ route('company.menu.index') }}"
class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-lime-500">

<div class="text-lime-500 text-xs font-semibold mb-2">
MENU
</div>

<div class="text-lg font-bold mb-2">
メニュー管理
</div>

</a>

@endif

<a href="{{ route('company.vacation.index') }}"
class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-green-500">

<div class="text-green-500 text-xs font-semibold mb-2">
VACATION
</div>

<div class="text-lg font-bold mb-2">
休暇管理
</div>

</a>

<a href="{{ route('company.my-profile') }}"
class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-teal-500">

<div class="text-teal-500 text-xs font-semibold mb-2">
MYPAGE
</div>

<div class="text-lg font-bold mb-2">
マイプロフィール
</div>

</a>

</div>

</div>


{{-- ============================= --}}
{{-- 今日の予約 --}}
{{-- ============================= --}}

<div class="bg-white shadow-lg rounded-2xl p-6 mb-12">

<div class="flex justify-between mb-6">

<h2 class="text-lg font-bold">
今日の予約
</h2>

<span class="text-xs text-gray-400">
{{ now()->format('Y年m月d日') }}
</span>

</div>

<div class="overflow-x-auto">

<table class="w-full text-sm">

<thead>

<tr class="border-b bg-gray-50 text-gray-600">

<th class="p-3 text-left">時間</th>
<th class="p-3 text-left">顧客</th>
<th class="p-3 text-left">メニュー</th>
<th class="p-3 text-left">担当</th>

</tr>

</thead>

<tbody>

@forelse($todayReservations as $r)

<tr class="border-b hover:bg-gray-50">

<td class="p-3">
{{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}
</td>

<td class="p-3">
{{ $r->customer_name }}
</td>

<td class="p-3">
{{ $r->menu->name ?? '-' }}
</td>

<td class="p-3">
{{ $r->staff->name ?? '-' }}
</td>

</tr>

@empty

<tr>
<td colspan="4" class="text-center text-gray-400 py-8">
本日の予約はありません
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>


@if(in_array($staff->role,['master','area_leader','leader']))

{{-- ============================= --}}
{{-- 売上ダッシュボード --}}
{{-- ============================= --}}

<div class="mb-12">

<h2 class="text-xl font-bold mb-6">
売上ダッシュボード
</h2>


{{-- 期間選択 --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">

<select name="period" class="border rounded px-3 py-2">
<option value="month" {{ $period=='month' ? 'selected':'' }}>月別</option>
<option value="year" {{ $period=='year' ? 'selected':'' }}>年別</option>
</select>

<select name="year" class="border rounded px-3 py-2">
@for($y = now()->year; $y >= now()->year-5; $y--)
<option value="{{ $y }}" {{ $year==$y ? 'selected':'' }}>
{{ $y }}年
</option>
@endfor
</select>

<select name="month" class="border rounded px-3 py-2">
@for($m = 1; $m <= 12; $m++)
<option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>
{{ $m }}月
</option>
@endfor
</select>

<button class="bg-gray-600 text-white px-4 py-2 rounded">
表示
</button>

</form>


{{-- KPIカード --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

<div class="bg-white shadow rounded-xl p-6">
<div class="text-gray-500 text-sm">今日売上</div>
<div class="text-3xl font-bold mt-2">
¥{{ number_format($todaySales) }}
</div>
</div>

<div class="bg-white shadow rounded-xl p-6">
<div class="text-gray-500 text-sm">今月売上</div>
<div class="text-3xl font-bold mt-2">
¥{{ number_format($monthlySales) }}
</div>
</div>

<div class="bg-white shadow rounded-xl p-6">
<div class="text-gray-500 text-sm">今年売上</div>
<div class="text-3xl font-bold mt-2">
¥{{ number_format($yearlySales) }}
</div>
</div>

<div class="bg-white shadow rounded-xl p-6">
<div class="text-gray-500 text-sm">客単価</div>
<div class="text-3xl font-bold mt-2">
¥{{ number_format($averagePrice) }}
</div>
</div>

</div>


{{-- 売上グラフ --}}
<div class="bg-white shadow rounded-xl p-6 mb-8">

<h3 class="font-bold mb-4">
売上推移（{{ $year }}年）
</h3>

<canvas id="salesChart"></canvas>

</div>


{{-- ランキング --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<div class="bg-white shadow rounded-xl p-6">

<h3 class="font-bold mb-4">
スタッフ売上ランキング
</h3>

@foreach($staffRanking as $i=>$row)

<div class="flex justify-between border-b py-2">
<span>{{ $i+1 }}. {{ $row->staff->name }}</span>
<span>¥{{ number_format($row->total) }}</span>
</div>

@endforeach

</div>


<div class="bg-white shadow rounded-xl p-6">

<h3 class="font-bold mb-4">
指名ランキング
</h3>

@foreach($nominationRanking as $i=>$row)

<div class="flex justify-between border-b py-2">
<span>{{ $i+1 }}. {{ $row->staff->name }}</span>
<span>{{ $row->nomination_count }}回</span>
</div>

@endforeach

</div>


<div class="bg-white shadow rounded-xl p-6">

<h3 class="font-bold mb-4">
人気メニュー
</h3>

@foreach($menuRanking as $i=>$row)

<div class="flex justify-between border-b py-2">
<span>{{ $i+1 }}. {{ $row->menu->name }}</span>
<span>{{ $row->total }}回</span>
</div>

@endforeach

</div>

</div>

</div>

@endif

</div>


<script>

const salesLabels = @json($monthlyChart->pluck('month')->values());
const salesData = @json($monthlyChart->pluck('total')->values());

new Chart(document.getElementById('salesChart'),{

type:'bar',

data:{
labels:salesLabels.map(m=>m+"月"),
datasets:[{
label:'売上',
data:salesData,
backgroundColor:'#3b82f6'
}]
}

});

</script>

@endsection