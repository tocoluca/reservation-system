@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================= --}}
    {{-- タイトル --}}
    {{-- ============================= --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">

        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">
                予約カレンダー
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                予約状況の確認・登録
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="w-full sm:w-auto text-center px-4 py-3 rounded-xl text-white shadow hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ダッシュボードへ戻る
        </a>
    </div>


    {{-- ============================= --}}
    {{-- 表示切替 --}}
    {{-- ============================= --}}
    <div class="flex gap-3 mb-6 overflow-x-auto">

        <a href="{{ route('company.calendar',['mode'=>'week']) }}"
           class="px-4 py-2 rounded-xl shadow text-sm font-semibold whitespace-nowrap"
           style="background:
           {{ request('mode','week')==='week' ? $theme : '#e5e7eb' }};
           color:
           {{ request('mode','week')==='week' ? 'white' : '#374151' }};">
            週表示
        </a>

        <a href="{{ route('company.calendar',['mode'=>'day']) }}"
           class="px-4 py-2 rounded-xl shadow text-sm font-semibold whitespace-nowrap"
           style="background:
           {{ request('mode')==='day' ? $theme : '#e5e7eb' }};
           color:
           {{ request('mode')==='day' ? 'white' : '#374151' }};">
            日表示
        </a>

    </div>


    {{-- ============================= --}}
    {{-- 操作バー --}}
    {{-- ============================= --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 mb-6">

        <div class="flex flex-col sm:flex-row gap-4 sm:items-center">

            <div class="flex gap-3">
                <button onclick="changeWeek(-7)"
                    class="px-4 py-2 rounded-lg text-white shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    ◀ 前へ
                </button>

                <button onclick="changeWeek(7)"
                    class="px-4 py-2 rounded-lg text-white shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    次へ ▶
                </button>
            </div>

            <select id="staffSelect"
                class="border rounded-lg px-3 py-2 shadow w-full sm:w-auto"
                onchange="loadCalendar()">
                <option value="">全担当者</option>
                @foreach($company->staff()->where('is_reservable',true)->orderBy('priority_order')->get() as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
            </select>

        </div>
    </div>


    <div id="messageArea" class="mb-6"></div>


    {{-- ============================= --}}
    {{-- カレンダー本体 --}}
    {{-- ============================= --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">

        @if($mode === 'day')

            {{-- 日表示ヘッダー --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">

                <div class="flex items-center justify-between sm:justify-start gap-3">

                    <button onclick="changeDay(-1)"
                        class="px-3 py-2 rounded-lg text-white shadow"
                        style="background: {{ $theme }}">◀</button>

                    <div id="currentDateLabel"
                         class="text-lg font-bold text-center min-w-[120px]">
                    </div>

                    <button onclick="changeDay(1)"
                        class="px-3 py-2 rounded-lg text-white shadow"
                        style="background: {{ $theme }}">▶</button>
                </div>

                <input type="date"
                    id="datePicker"
                    class="border rounded-lg px-3 py-2 shadow w-full sm:w-auto"
                    onchange="jumpToDate(this.value)">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[600px] w-full text-sm text-center border">
                    <thead id="day-head"></thead>
                    <tbody id="day-body"></tbody>
                </table>
            </div>

        @else

            <div id="calendar" class="overflow-x-auto"></div>

        @endif

    </div>
</div>


<script>

let currentDate = new Date();
const mode = "{{ $mode }}";

document.addEventListener("DOMContentLoaded", function () {
    if (mode === 'week') loadCalendar();
    if (mode === 'day') loadDayCalendar();
});

/* ================= WEEK ================= */

function loadCalendar() {

    if (mode !== 'week') return;

    let dateStr = currentDate.toISOString().split('T')[0];
    let staffId = document.getElementById('staffSelect').value;

    fetch(`/company/calendar/data?mode=week&date=${dateStr}&staff_id=${staffId}`)
        .then(res => res.json())
        .then(data => {

            if (!data.slots || Object.keys(data.slots).length === 0) {
                document.getElementById('calendar').innerHTML =
                    '<div class="text-center p-10 text-gray-400">表示できる日がありません</div>';
                return;
            }

            let slots = data.slots;
            let html = '<div class="overflow-x-auto">';
            html += '<table class="min-w-[900px] w-full text-sm table-fixed border-collapse">';
            html += '<thead><tr>';
            html += '<th class="p-3 bg-gray-50 sticky left-0 z-10">時間</th>';

            let dates = Object.keys(Object.values(slots)[0]);

            dates.forEach(d => {
                html += `<th class="p-3 border-b">${d}</th>`;
            });

            html += '</tr></thead><tbody>';

            Object.keys(slots).forEach(time => {

                html += `<tr>
                    <td class="p-3 bg-gray-50 sticky left-0 font-medium">${time}</td>`;

                dates.forEach(d => {

                    let cell = slots[time][d];

                    if (cell.status === '×') {
                        html += `
                            <td class="p-3">
                                <div class="mx-auto w-10 h-10 flex items-center justify-center
                                            rounded-full bg-red-100 text-red-600 shadow">
                                    ×
                                </div>
                            </td>`;
                    } else {
                        html += `
                            <td class="p-3">
                                <div class="mx-auto w-10 h-10 flex items-center justify-center
                                            rounded-full text-white shadow"
                                    style="background: {{ $theme }}">
                                    ○
                                </div>
                            </td>`;
                    }
                });

                html += '</tr>';
            });

            html += '</tbody></table></div>';
            document.getElementById('calendar').innerHTML = html;
        });
}

/* ================= DAY ================= */

function loadDayCalendar() {

    if (mode !== 'day') return;

    let dateStr = currentDate.toISOString().split('T')[0];

    document.getElementById('currentDateLabel').innerText = dateStr;
    document.getElementById('datePicker').value = dateStr;

    fetch(`/company/calendar/data?mode=day&date=${dateStr}`)
        .then(res => res.json())
        .then(data => {

            const head = document.getElementById("day-head");
            const body = document.getElementById("day-body");

            head.innerHTML = "";
            body.innerHTML = "";

            let headerRow = `<tr>
                <th class="p-3 bg-gray-50 sticky left-0">時間</th>`;

            data.staffs.forEach(staff => {
                headerRow += `<th class="p-3">${staff.name}</th>`;
            });

            headerRow += "</tr>";
            head.innerHTML = headerRow;

            Object.keys(data.slots).forEach(time => {

                let row = `<tr>
                    <td class="p-3 bg-gray-50 sticky left-0">${time}</td>`;

                data.staffs.forEach(staff => {

                    let cell = data.slots[time][staff.id];

                    if (cell.status === '×') {
                        row += `
                            <td class="p-3">
                                <div class="mx-auto w-10 h-10 flex items-center justify-center
                                            rounded-full bg-red-100 text-red-600 shadow">
                                    ×
                                </div>
                            </td>`;
                    } else {
                        row += `
                            <td class="p-3">
                                <div class="mx-auto w-10 h-10 flex items-center justify-center
                                            rounded-full text-white shadow"
                                    style="background: {{ $theme }}">
                                    ○
                                </div>
                            </td>`;
                    }
                });

                row += "</tr>";
                body.innerHTML += row;
            });
        });
}

function changeWeek(days){
    currentDate.setDate(currentDate.getDate() + days);
    loadCalendar();
}

function changeDay(diff){
    currentDate.setDate(currentDate.getDate() + diff);
    loadDayCalendar();
}

function jumpToDate(dateStr){
    currentDate = new Date(dateStr);
    loadDayCalendar();
}

</script>

@endsection