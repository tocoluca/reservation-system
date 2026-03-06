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
   class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
   style="border-color: {{ $theme }}; color: {{ $theme }}">
    ← ダッシュボード
</a>
    </div>

    {{-- 表示切替 --}}
    <div class="flex gap-3 mb-6">
        <a href="{{ route('company.reserve',['mode'=>'week']) }}"
           class="px-4 py-2 rounded-lg shadow text-sm font-semibold"
           style="background:
           {{ request('mode','week')==='week' ? $theme : '#e5e7eb' }};
           color:
           {{ request('mode','week')==='week' ? 'white' : '#374151' }};">
            週表示
        </a>

        <a href="{{ route('company.reserve',['mode'=>'day']) }}"
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
<input type="hidden" id="modal_date" name="date">
<input type="hidden" id="modal_time" name="time">
<script>
let currentDate = new Date();
const mode = "{{ $mode }}";

function getLocalDateStr(date = new Date()) {
    const y = date.getFullYear();
    const m = ('0' + (date.getMonth() + 1)).slice(-2);
    const d = ('0' + date.getDate()).slice(-2);
    return `${y}-${m}-${d}`;
}

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

    let dateStr = getLocalDateStr(currentDate);
    let staffId = document.getElementById('staffSelect').value;

    fetch(`/company/reserve/data?mode=week&date=${dateStr}&staff_id=${staffId}`)
        .then(res => res.json())
        .then(data => {

            if (!data.slots || Object.keys(data.slots).length === 0) {
                document.getElementById('calendar').innerHTML =
                    '<div class="text-center p-10 text-gray-400">表示できる日がありません</div>';
                return;
            }

            let slots = data.slots;
            let todayStr = getLocalDateStr();

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

                    // ===== 予約済み =====
			if (cell.status === '×') {

			    if (cell.reservations && cell.reservations.length > 0) {

			        html += `
			            <td class="p-3 text-center">
			                <div class="mx-auto w-8 h-8 flex items-center justify-center
			                            rounded-full bg-red-100 text-red-600
			                            shadow cursor-pointer hover:bg-red-200 transition"
			                    onclick='openReservationList(${JSON.stringify(cell.reservations)})'>
			                    ×
			                </div>
			            </td>`;

			    } else if (cell.reservation_id) {

			        html += `
			            <td class="p-3 text-center">
			                <div class="mx-auto w-8 h-8 flex items-center justify-center
			                            rounded-full bg-red-100 text-red-600
			                            shadow cursor-pointer hover:bg-red-200 transition"
			                    data-id="${cell.reservation_id}"
			                    onclick="handleCancelClick(this)">
			                    ×
			                </div>
			            </td>`;

			    } else {

			        html += `
			            <td class="p-3 text-center">
			                <div class="mx-auto w-8 h-8 flex items-center justify-center
			                            rounded-full bg-gray-200 text-gray-400 shadow">
			                    ×
			                </div>
			            </td>`;
			    }
			} else if (cell.status === '○') {

			    html += `
			        <td class="p-3 text-center">
			            <div class="mx-auto w-8 h-8 flex items-center justify-center
			                        rounded-full text-white shadow
			                        cursor-pointer hover:opacity-90 transition"
			                style="background: {{ $theme }}"
			                onclick="openStaffSelector('${d} ${time}')">
			                ○
			            </div>
			        </td>`;
			}else if (cell.status === '△') {

			    html += `
			        <td class="p-3 text-center">
			            <div class="mx-auto w-8 h-8 flex items-center justify-center
			                        rounded-full bg-yellow-100 text-yellow-600
			                        shadow cursor-pointer hover:bg-yellow-200 transition"
			                onclick="openStaffSelector('${d} ${time}')">
			                △
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

    fetch(`/company/reserve/data?mode=day&date=${dateStr}`)
        .then(res => res.json())
        .then(data => {

            const head = document.getElementById("day-head");
            const body = document.getElementById("day-body");

            head.innerHTML = "";
            body.innerHTML = "";

            /* ===== ヘッダー ===== */

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
                                <div class="mx-auto w-8 h-8 flex items-center justify-center
                                            rounded-full bg-red-100 text-red-600
                                            shadow cursor-pointer hover:bg-red-200 transition"
                                    data-id="${cell.reservation_id}"
                                    onclick="handleCancelClick(this)">
                                    ×
                                </div>
                            </td>`;

                    }else if (cell.status === '△') {

			    row += `
			        <td class="p-4 text-center">
			            <div class="mx-auto w-9 h-9 flex items-center justify-center
			                        rounded-full bg-yellow-100 text-yellow-600 shadow
			                        cursor-pointer hover:bg-yellow-200 transition"
			                onclick="reserve('${dateStr} ${time}', ${staff.id})">
			                △
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

let selectedDatetime = null;
let selectedStaffId = null;

function reserve(datetime, staffId) {

    selectedDatetime = datetime;
    selectedStaffId  = staffId;

    const nameInput = document.getElementById('modal_customer_name');
    const telInput  = document.getElementById('modal_customer_phone');
    const modal     = document.getElementById('reserveModal');

    if (!modal) return; // モーダル無いページなら終了

    document.getElementById('reserveDatetime').innerText = datetime;

    if (nameInput) nameInput.value = '';
    if (telInput)  telInput.value  = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const nameInput = document.getElementById('modal_customer_name');
    const telInput  = document.getElementById('modal_customer_phone');
    const modal     = document.getElementById('reserveModal');

    if (nameInput) nameInput.value = '';
    if (telInput)  telInput.value  = '';

    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
function submitReservation() {

    const name = document.getElementById('modal_customer_name').value;
    const phone  = document.getElementById('modal_customer_phone').value;
    const phonePattern = /^[0-9\-]*$/;

    if (!name) {
        alert('お名前を入力してください');
        return;
    }

    if (!phonePattern.test(phone)) {
        alert('電話番号は数字とハイフンのみ入力できます');
        return;
    }

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
    .then(res => res.json())
    .then(result => {
        if(result.success){

            closeModal();

            if (mode === 'week') loadCalendar();
            if (mode === 'day') loadDayCalendar();

        } else {
            alert(result.message);
        }
    });
}

function cancelReservation(reservationId) {

    if (!confirm('この予約をキャンセルしますか？')) return;

    fetch(`/company/reservation/${reservationId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(result => {

        if (result.success) {

            if (mode === 'week') loadCalendar();
            if (mode === 'day') loadDayCalendar();

        } else {
            alert(result.message);
        }

    });
}
function handleCancelClick(el) {
    const reservationId = el.dataset.id;
    cancelReservation(reservationId);
}
function openReservationDetail(cell) {

    if (confirm(
        `顧客名: ${cell.customer_name}\n` +
        `日時: ${cell.start_at}\n\nキャンセルしますか？`
    )) {
        cancelReservation(cell.reservation_id);
    }
}
function handleCancelClick(el) {

    const reservationId = el.dataset.id;

    if (!reservationId || reservationId === 'undefined') {
        alert('予約IDが取得できません');
        return;
    }

    cancelReservation(reservationId);
}


function openStaffSelector(datetime) {

    const BASE_URL = "{{ url('/') }}";
    const NO_IMAGE = "{{ asset('logos/logo.png') }}";

    selectedDatetime = datetime;

    fetch(`/company/calendar/available-staff?datetime=${datetime}`)
        .then(res => res.json())
        .then(data => {

            const area = document.getElementById('staffListArea');
            area.innerHTML = '';
            data.forEach(staff => {
                area.innerHTML += `
                    <div onclick="reserveWithStaff(${staff.id})"
                        class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <img src="${staff.image_path && staff.image_path.trim() !== '' ? BASE_URL + '/' + staff.image_path : NO_IMAGE}"
                             class="w-10 h-10 rounded-full object-cover">
                        <div>${staff.name}</div>
                    </div>
                `;
            });
            document.getElementById('staffModal').classList.remove('hidden');
            document.getElementById('staffModal').classList.add('flex');
        });
}

function closeStaffModal() {
    document.getElementById('staffModal').classList.add('hidden');
}

function reserveWithStaff(staffId) {
    closeStaffModal();
    reserve(selectedDatetime, staffId);
}
function openReservationList(reservations) {

    let html = '';

    reservations.forEach(r => {
        html += `
            <div class="flex justify-between items-center border p-3 rounded-lg">
                <div>
                    <div class="font-semibold">${r.staff_name}</div>
                    <div class="text-xs text-gray-500">${r.customer_name}</div>
                </div>
                <button onclick="cancelReservation(${r.id})"
                        class="text-red-500 text-sm hover:underline">
                    キャンセル
                </button>
            </div>`;
    });

    document.getElementById('reservationListArea').innerHTML = html;
    document.getElementById('reservationModal').classList.remove('hidden');
    document.getElementById('reservationModal').classList.add('flex');
}

function closeReservationModal() {
    document.getElementById('reservationModal').classList.add('hidden');
}

function formatTel(input) {
    // 数字とハイフン以外を削除
    input.value = input.value.replace(/[^0-9\-]/g, '');
}

</script>


<div id="staffModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4">担当者を選択</h2>
        <div id="staffListArea" class="space-y-3"></div>
        <button onclick="closeStaffModal()" class="mt-4 text-sm text-gray-500">
            閉じる
        </button>
    </div>
</div>
<div id="reservationModal"
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-96 p-6">
        <h2 class="text-lg font-bold mb-4">予約一覧（キャンセルする担当者を選択）</h2>
        <div id="reservationListArea" class="space-y-2"></div>
        <button onclick="closeReservationModal()"
                class="mt-4 text-sm text-gray-500">
            閉じる
        </button>
    </div>
</div>

<!-- 予約モーダル -->
<div id="reserveModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">

        <h2 class="text-lg font-bold mb-4">予約確認</h2>

        <p class="mb-3 text-sm text-gray-600">
            <span id="reserveDatetime"></span>
        </p>

        <div class="mb-3">
            <label class="block text-sm mb-1">お名前</label>
            <input type="text" id="modal_customer_name"
                   class="border rounded-lg p-2 w-full">
        </div>

        <div class="mb-4">
            <label class="block text-sm mb-1">電話番号（数字と‐のみ入力可）</label>
		<input type="text"
		       id="modal_customer_phone"
		       class="border rounded-lg p-2 w-full"
		       oninput="formatTel(this)">
        </div>

        <div class="flex justify-end gap-2">
            <button onclick="closeModal()"
                    class="px-4 py-2 text-sm bg-gray-200 rounded-lg">
                キャンセル
            </button>

            <button onclick="submitReservation()"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">
                予約する
            </button>
        </div>

    </div>
</div>
@endsection