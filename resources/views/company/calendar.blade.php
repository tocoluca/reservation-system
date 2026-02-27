@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- ================= タイトル ================= --}}
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6 mb-8">

        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">
                予約カレンダー
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                予約状況の確認・登録
            </p>
        </div>

        {{-- ダッシュボードボタン --}}
        <a href="{{ route('company.dashboard') }}"
           class="group inline-flex items-center justify-center gap-2
                  w-full sm:w-auto px-6 py-3
                  rounded-xl text-white font-semibold
                  shadow-lg hover:shadow-xl
                  transition-all duration-200 hover:-translate-y-0.5"
           style="background: {{ $theme }}">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5 transition-transform group-hover:-translate-x-1"
                 fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>

            ダッシュボードに戻る
        </a>

    </div>


    {{-- ================= 表示切替 ================= --}}
    <div class="flex gap-3 mb-6 overflow-x-auto pb-2">

        @foreach(['week'=>'週表示','day'=>'日表示'] as $key => $label)
            <a href="{{ route('company.calendar',['mode'=>$key]) }}"
               class="px-5 py-2 rounded-xl shadow text-sm font-semibold whitespace-nowrap
                      transition"
               style="background:
               {{ request('mode','week')===$key ? $theme : '#e5e7eb' }};
               color:
               {{ request('mode','week')===$key ? 'white' : '#374151' }};">
                {{ $label }}
            </a>
        @endforeach

    </div>


    {{-- ================= 操作バー ================= --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 mb-6">

        <div class="flex flex-col lg:flex-row gap-4 lg:items-center">

            <div class="flex gap-3 w-full sm:w-auto">

                <button onclick="changeWeek(-7)"
                    class="flex-1 sm:flex-none px-4 py-3 rounded-xl text-white
                           shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    ◀ 前へ
                </button>

                <button onclick="changeWeek(7)"
                    class="flex-1 sm:flex-none px-4 py-3 rounded-xl text-white
                           shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    次へ ▶
                </button>
            </div>

            <select id="staffSelect"
                class="border rounded-xl px-4 py-3 shadow w-full lg:w-auto"
                onchange="loadCalendar()">
                <option value="">全担当者</option>
                @foreach($company->staff()->where('is_reservable',true)->orderBy('priority_order')->get() as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
            </select>

        </div>
    </div>


    {{-- ================= カレンダー ================= --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">

        @if($mode === 'day')

            <div class="flex flex-col sm:flex-row sm:justify-between gap-4 mb-4">

                <div class="flex items-center justify-between sm:justify-start gap-4">

                    <button onclick="changeDay(-1)"
                        class="px-4 py-3 rounded-xl text-white shadow"
                        style="background: {{ $theme }}">◀</button>

                    <div id="currentDateLabel"
                         class="text-lg font-bold min-w-[140px] text-center">
                    </div>

                    <button onclick="changeDay(1)"
                        class="px-4 py-3 rounded-xl text-white shadow"
                        style="background: {{ $theme }}">▶</button>
                </div>

                <input type="date"
                       id="datePicker"
                       class="border rounded-xl px-4 py-3 shadow w-full sm:w-auto"
                       onchange="jumpToDate(this.value)">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[700px] w-full text-sm text-center border-collapse">
                    <thead id="day-head"></thead>
                    <tbody id="day-body"></tbody>
                </table>
            </div>

        @else

            <div id="calendar" class="overflow-x-auto"></div>

        @endif

    </div>

</div>