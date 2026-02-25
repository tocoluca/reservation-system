@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- タイトル --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold">予約カレンダー</h1>
            <p class="text-gray-500 text-sm mt-1">予約状況の確認・登録</p>
        </div>
        <a href="{{ route('company.dashboard') }}"
           class="px-4 py-2 rounded-lg text-white shadow"
           style="background: {{ $theme }}">
            ダッシュボードへ戻る
        </a>
    </div>

    {{-- 表示切替 --}}
    <div class="flex gap-3 mb-6">
        <a href="{{ route('company.calendar',['mode'=>'week']) }}"
           class="px-4 py-2 rounded-lg shadow text-sm font-semibold"
           style="background:
           {{ request('mode','week')==='week' ? $theme : '#e5e7eb' }};
           color:
           {{ request('mode','week')==='week' ? 'white' : '#374151' }};">
            週表示
        </a>

        <a href="{{ route('company.calendar',['mode'=>'day']) }}"
           class="px-4 py-2 rounded-lg shadow text-sm font-semibold"
           style="background:
           {{ request('mode')==='day' ? $theme : '#e5e7eb' }};
           color:
           {{ request('mode')==='day' ? 'white' : '#374151' }};">
            日表示
        </a>
    </div>

    {{-- 操作バー --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-center">

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

            <select id="staffSelect"
                class="border rounded-lg px-3 py-2 shadow"
                onchange="loadCalendar()">
                <option value="">全担当者</option>
                @foreach($company->staff()->where('is_reservable',true)->orderBy('priority_order')->get() as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
            </select>

        </div>
    </div>

    <div id="messageArea" class="mb-6"></div>

    <div class="bg-white rounded-2xl shadow-lg p-6">

        @if($mode === 'day')
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <button onclick="changeDay(-1)"
                        class="px-3 py-1 rounded-lg text-white shadow"
                        style="background: {{ $theme }}">◀</button>

                    <div id="currentDateLabel" class="text-lg font-bold"></div>

                    <button onclick="changeDay(1)"
                        class="px-3 py-1 rounded-lg text-white shadow"
                        style="background: {{ $theme }}">▶</button>
                </div>

                <input type="date"
                    id="datePicker"
                    class="border rounded-lg px-3 py-2 shadow"
                    onchange="jumpToDate(this.value)">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border">
                    <thead id="day-head"></thead>
                    <tbody id="day-body"></tbody>
                </table>
            </div>
        @else
            <div id="calendar"></div>
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

function changeWeek(days) {
    currentDate.setDate(currentDate.getDate() + days);
    if (mode === 'week') loadCalendar();
}

function changeDay(diff) {
    currentDate.setDate(currentDate.getDate() + diff);
    loadDayCalendar();
}

function jumpToDate(dateStr) {
    currentDate = new Date(dateStr);
    loadDayCalendar();
}

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
            let todayStr = new Date().toISOString().split('T')[0];

            let allDates = new Set();
            Object.values(slots).forEach(timeRow => {
                Object.keys(timeRow).forEach(d => allDates.add(d));
            });

            let dates = Array.from(allDates).sort();
            let times = Object.keys(slots).sort();

            let html = '<div class="overflow-x-auto">';
            html += '<table class="w-full text-sm table-fixed border-collapse">';
            html += '<thead><tr>';
            html += '<th class="p-4 bg-gray-50 sticky left-0 z-10 text-center align-middle">時間</th>';

            dates.forEach(d => {

                let dateObj = new Date(d);
                let weekdayIndex = dateObj.getDay();
                let weekday = ['日','月','火','水','木','金','土'][weekdayIndex];

                let weekendColor = '';
                if (weekdayIndex === 0) weekendColor = 'text-red-500';
                if (weekdayIndex === 6) weekendColor = 'text-blue-500';

                let todayBg = d === todayStr ? 'bg-blue-50' : '';

                html += `
                    <th class="p-4 border-b text-center ${weekendColor} ${todayBg}">
                        <div class="font-semibold">${d}</div>
                        <div class="text-xs">${weekday}</div>
                    </th>`;
            });

            html += '</tr></thead><tbody>';

            times.forEach(time => {

                html += `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 font-medium bg-gray-50 sticky left-0 text-center align-middle">
                            ${time}
                        </td>`;

                dates.forEach(d => {

                    let cell = slots?.[time]?.[d];

                    if (!cell) {
                        html += `<td class="p-4 text-gray-200 text-center">-</td>`;
                        return;
                    }

                    if (cell.status === '×') {
                        html += `
                            <td class="p-4 text-center">
                                <div class="mx-auto w-10 h-10 flex items-center justify-center
                                            rounded-full bg-red-100 text-red-600
                                            shadow cursor-pointer hover:bg-red-200 transition"
                                    onclick='openReservationDetail(${JSON.stringify(cell)})'>
                                    ×
                                </div>
                            </td>`;
                    } else {
                        html += `
                            <td class="p-4 text-center">
                                <div class="mx-auto w-9 h-9 flex items-center justify-center
                                            rounded-full text-white shadow
                                            cursor-pointer hover:opacity-90 transition"
                                    style="background: {{ $theme }}"
                                    onclick="reserve('${d} ${time}')">
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
/* ================= DAY ================= */

function loadDayCalendar() {

    if (mode !== 'day') return;

    let dateStr = currentDate.toISOString().split('T')[0];
    let todayStr = new Date().toISOString().split('T')[0];

    document.getElementById('currentDateLabel').innerText = dateStr;
    document.getElementById('datePicker').value = dateStr;

    fetch(`/company/calendar/data?mode=day&date=${dateStr}`)
        .then(res => res.json())
        .then(data => {

            const head = document.getElementById("day-head");
            const body = document.getElementById("day-body");

            head.innerHTML = "";
            body.innerHTML = "";

            /* ===== ヘッダー（週表示と統一） ===== */

            let headerRow = `
                <tr>
                    <th class="p-4 bg-gray-50 sticky left-0 z-10 text-left">
                        時間
                    </th>`;

            data.staffs.forEach(staff => {
                headerRow += `
                    <th class="p-4 border-b text-center ${dateStr === todayStr ? 'bg-blue-50' : ''}">
                        <div class="font-semibold">${staff.name}</div>
                    </th>`;
            });

            headerRow += "</tr>";
            head.innerHTML = headerRow;

            /* ===== ボディ ===== */

            Object.keys(data.slots).forEach(time => {

                let row = `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 font-medium bg-gray-50 sticky left-0 text-center align-middle">
                            ${time}
                        </td>`;

                data.staffs.forEach(staff => {

                    let cell = data.slots[time][staff.id];

                    if (!cell) {
                        row += `<td class="p-4 text-gray-200 text-center">-</td>`;
                        return;
                    }

                    if (cell.status === '×') {

                        row += `
                            <td class="p-4 text-center">
                                <div class="mx-auto w-9 h-9 flex items-center justify-center
                                            rounded-full bg-red-100 text-red-600
                                            shadow cursor-pointer hover:bg-red-200 transition"
                                    onclick='openReservationDetail(${JSON.stringify(cell)})'>
                                    ×
                                </div>
                            </td>`;

                    } else if (cell.status === '○') {

                        row += `
                            <td class="p-4 text-center">
                                <div class="mx-auto w-9 h-9 flex items-center justify-center
                                            rounded-full text-white shadow
                                            cursor-pointer hover:opacity-90 transition"
                                    style="background: {{ $theme }}"
                                    onclick="reserve('${dateStr} ${time}', ${staff.id})">
                                    ○
                                </div>
                            </td>`;

                    } else {

                        row += `
                            <td class="p-4 text-center">
                                <div class="w-9 h-9 flex items-center justify-center
                                            rounded-full bg-gray-100 text-gray-400 shadow">
                                    ${cell.status}
                                </div>
                            </td>`;
                    }
                });

                row += "</tr>";
                body.innerHTML += row;
            });
        });
}

function reserve(datetime, staffId = null) {

    if (!confirm(datetime + ' で予約しますか？')) return;

    fetch('/company/reservation',{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body:JSON.stringify({
            start_at: datetime,
            customer_name: 'テスト予約',
            staff_id: staffId
        })
    })
    .then(res => res.json())
    .then(result => {
        if(result.success){
            if (mode === 'week') loadCalendar();
            if (mode === 'day') loadDayCalendar();
        } else {
            alert(result.message);
        }
    });
}

</script>

@endsection