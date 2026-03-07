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
class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-blue-500">

<div class="flex items-center gap-2 mb-2">

<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
<path d="M8 7V3M16 7V3M4 11H20M5 5H19A2 2 0 0121 7V19A2 2 0 0119 21H5A2 2 0 013 19V7A2 2 0 015 5Z"/>
</svg>

<div class="text-blue-500 text-xs font-semibold mb-2">
RESERVATION
</div>

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
class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-emerald-500">

<div class="text-emerald-500 text-xs font-semibold mb-2">
BUSINESS
</div>

<div class="text-lg font-bold mb-2">
営業日カレンダー
</div>

<div class="text-gray-500 text-sm">
営業日の確認・登録・管理
</div>

</a>


<a href="{{ route('company.staff.index') }}"
class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-indigo-500">

<div class="text-indigo-500 text-xs font-semibold mb-2">
STAFF
</div>

<div class="text-lg font-bold mb-2">
担当者管理
</div>

<div class="text-gray-500 text-sm">
担当者の登録・編集
</div>

</a>
            <a href="{{ route('company.menu.settings') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-cyan-500">

<div class="text-cyan-500 text-xs font-semibold mb-2">
CATEGORY & TAG
</div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    カテゴリー・タグ管理
                </div>

                <div class="text-gray-500 text-sm">
                    メニューのカテゴリー・タグの管理
                </div>
            </a>

            <a href="{{ route('company.menu.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-lime-500">

<div class="text-lime-500 text-xs font-semibold mb-2">
MENU
</div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    メニュー管理
                </div>

                <div class="text-gray-500 text-sm">
                    メニューの管理・施工時間
                </div>
            </a>
@endif


<a href="{{ route('company.vacation.index') }}"
class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-green-500">

<div class="text-green-500 text-xs font-semibold mb-2">
VACATION
</div>

<div class="text-lg font-bold mb-2">
休暇管理
</div>

<div class="text-gray-500 text-sm">
休暇申請・承認管理
</div>

</a>

            {{-- テーマ設定 --}}
            @if($staff->role === 'master')
            <a href="{{ route('company.theme') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-purple-500">

<div class="text-purple-500 text-xs font-semibold mb-2">
DESIGN
</div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    テーマ設定
                </div>

                <div class="text-gray-500 text-sm">
                    顧客画面のカラー変更
                </div>
            </a>
            @endif


            {{-- 企業情報編集 --}}
            @if($staff->role === 'master')
            <a href="{{ route('company.info.edit') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-orange-500">

<div class="text-orange-500 text-xs font-semibold mb-2">
COMPANY
</div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    企業情報編集
                </div>

                <div class="text-gray-500 text-sm">
                    会社情報・営業時間変更
                </div>
            </a>
            @endif


            {{-- ロゴ設定 --}}
            @if($staff->role === 'master')
            <a href="{{ route('company.logo') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-gray-500">

<div class="text-gray-500 text-xs font-semibold mb-2">
BRAND
</div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    ロゴ設定
                </div>

                <div class="text-gray-500 text-sm">
                    企業ロゴ変更
                </div>
            </a>
            @endif



<a href="{{ route('company.my-profile') }}"
class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-teal-500">

<div class="text-teal-500 text-xs font-semibold mb-2">
MYPAGE
</div>

<div class="text-lg font-bold mb-2">
マイプロフィール
</div>

<div class="text-gray-500 text-sm">
プロフィール変更
</div>

</a>

</div>

</div>


{{-- ============================= --}}
{{-- 今日の予約 --}}
{{-- ============================= --}}

<div class="bg-white shadow-lg rounded-2xl p-6 mb-12">

    {{-- タイトル --}}
    <div class="flex items-center justify-between mb-6">

        <h2 class="text-lg font-bold flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full"
                style="background: {{ $theme }}"></span>
            今日の予約
        </h2>

        <span class="text-xs text-gray-400">
            {{ now()->format('Y年m月d日') }}
        </span>

    </div>


    {{-- テーブル --}}
    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead>

                <tr class="border-b bg-gray-50 text-gray-600">

                    <th class="p-3 text-left font-semibold">
                        時間
                    </th>

                    <th class="p-3 text-left font-semibold">
                        顧客
                    </th>

                    <th class="p-3 text-left font-semibold">
                        メニュー
                    </th>

                    <th class="p-3 text-left font-semibold">
                        担当
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($todayReservations as $r)

                <tr class="border-b hover:bg-gray-50 transition">

                    <td class="p-3 font-medium">
                        {{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}
                    </td>

                    <td class="p-3">
                        {{ $r->customer_name }}
                    </td>

                    <td class="p-3 text-gray-600">
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

{{-- ============================= --}}
{{-- サマリー --}}
{{-- ============================= --}}

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

<div class="bg-white shadow rounded-xl p-6 border-l-4 border-blue-500">

<div class="text-gray-500 text-sm">
本日の予約数
</div>

<div class="text-3xl font-bold mt-2">
{{ $todayCount }}
</div>

</div>


<div class="bg-white shadow rounded-xl p-6 border-l-4 border-purple-500">

<div class="text-gray-500 text-sm">
今月の予約数
</div>

<div class="text-3xl font-bold mt-2">
{{ $monthlyCount }}
</div>

</div>


<div class="bg-white shadow rounded-xl p-6 border-l-4 border-rose-500">

<div class="text-gray-500 text-sm">
稼働率
</div>

<div class="text-3xl font-bold mt-2">
{{ $utilizationRate }}%
</div>

<div class="text-gray-400 text-xs mt-2">
総予約時間 ÷ (営業時間合計 × スタッフ数)
</div>

</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

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

</div>

<div class="bg-white shadow rounded-xl p-6 mb-8">

<h2 class="font-bold mb-4">
売上推移（{{ now()->year }}年）
</h2>

<canvas id="salesChart"></canvas>

</div>


<div class="bg-white shadow rounded-xl p-6 mb-8">

<h2 class="font-bold mb-4">
スタッフ売上ランキング
</h2>

<table class="w-full text-sm">

@foreach($staffRanking as $i=>$row)

<tr class="border-b">

<td class="p-2">{{ $i+1 }}</td>

<td class="p-2">
{{ $row->staff->name ?? '-' }}
</td>

<td class="p-2 text-right">
¥{{ number_format($row->total) }}
</td>

</tr>

@endforeach

</table>

</div>


<div class="bg-white shadow rounded-xl p-6">

<h2 class="font-bold mb-4">
人気メニュー
</h2>

<table class="w-full text-sm">

@foreach($menuRanking as $i=>$row)

<tr class="border-b">

<td class="p-2">{{ $i+1 }}</td>

<td class="p-2">
{{ $row->menu->name ?? '-' }}
</td>

<td class="p-2 text-right">
{{ $row->total }}回
</td>

</tr>

@endforeach

</table>

</div>



</div>

</div>
<script>

const salesLabels = @json($monthlyChart->pluck('month'));
const salesData = @json($monthlyChart->pluck('total'));

new Chart(document.getElementById('salesChart'),{

type:'bar',

data:{
labels:salesLabels.map(m=>m+"月"),
datasets:[{
label:'売上',
data:salesData,
borderWidth:1
}]
}

});

</script>
@endsection