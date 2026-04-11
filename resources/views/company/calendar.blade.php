@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Reservation Calendar</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">予約カレンダー</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        空き状況の確認、予約登録、キャンセル確認までこの画面で行えます。
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('company.reservations.index') }}"
                       class="group inline-flex items-center gap-3 rounded-2xl px-5 py-3 text-white bg-white/15 hover:bg-white/20 backdrop-blur-sm transition">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-lg font-bold">
                            予
                        </span>

                        <span class="text-left leading-tight">
                            <span class="block text-sm font-bold">予約一覧</span>
                            <span class="block text-[11px] text-white/80">顧客検索・予約確認はこちら</span>
                        </span>
                    </a>

                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 操作の流れ --}}
    <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-stone-800 mb-2">操作の流れ</h2>
                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-stone-600">
                    <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">1. 日付を選ぶ</span>
                    <span class="text-stone-300">→</span>
                    <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">2. 空き枠を押す</span>
                    <span class="text-stone-300">→</span>
                    <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">3. メニューを選ぶ</span>
                    <span class="text-stone-300">→</span>
                    <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">4. 担当パターンを選ぶ</span>
                    <span class="text-stone-300">→</span>
                    <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">5. 予約確定</span>
                </div>
            </div>

				<div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
				    <span class="inline-flex items-center gap-2 rounded-full bg-green-50 text-green-700 px-3 py-1">
				        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>予約可能
				    </span>
				    <span class="inline-flex items-center gap-2 rounded-full bg-red-50 text-red-700 px-3 py-1">
				        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>予約あり
				    </span>
				    <span class="inline-flex items-center gap-2 rounded-full bg-stone-100 text-stone-500 px-3 py-1">
				        <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>受付不可
				    </span>
				</div>
        </div>
    </div>

    {{-- 操作エリア --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 mb-6">
        <div class="xl:col-span-5 bg-white rounded-3xl shadow-sm border border-stone-200 p-5">
            <div class="text-xs font-semibold text-stone-500 mb-2">日付操作</div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex items-center gap-2">
                    <button type="button"
                            onclick="navigateCurrent(-1)"
                            class="px-4 py-2.5 rounded-2xl text-white shadow hover:opacity-90 transition"
                            style="background: {{ $theme }};">
                        ◀ 前へ
                    </button>

                    <button type="button"
                            onclick="goToday()"
                            class="px-4 py-2.5 rounded-2xl border bg-white hover:bg-gray-50 text-sm font-semibold transition"
                            style="border-color: {{ $theme }}; color: {{ $theme }};">
                        今日
                    </button>

                    <button type="button"
                            onclick="navigateCurrent(1)"
                            class="px-4 py-2.5 rounded-2xl text-white shadow hover:opacity-90 transition"
                            style="background: {{ $theme }};">
                        次へ ▶
                    </button>
                </div>

                <div class="flex-1">
                    <input type="date"
                           id="globalDatePicker"
                           class="w-full border rounded-2xl px-3 py-2.5 shadow-sm focus:outline-none focus:ring-2"
                           style="border-color: #d6d3d1; --tw-ring-color: {{ $theme }};"
                           onchange="jumpByMode(this.value)">
                </div>
            </div>

            <div class="mt-3 rounded-2xl bg-stone-50 border border-stone-200 px-4 py-3">
                <div class="text-xs text-stone-500 mb-1">選択中の日付</div>
                <div id="currentDateHero" class="text-lg sm:text-xl font-bold text-stone-800">-</div>
            </div>
        </div>

        <div class="xl:col-span-3 bg-white rounded-3xl shadow-sm border border-stone-200 p-5">
            <div class="text-xs font-semibold text-stone-500 mb-2">表示方法</div>

            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('company.reserve',['mode'=>'day']) }}"
                   class="text-center px-4 py-3 rounded-2xl shadow-sm text-sm font-semibold transition"
                   style="background: {{ request('mode') === 'day' ? $theme : '#f5f5f4' }};
                          color: {{ request('mode') === 'day' ? 'white' : '#44403c' }};">
                    日表示
                </a>

                <a href="{{ route('company.reserve',['mode'=>'week']) }}"
                   class="text-center px-4 py-3 rounded-2xl shadow-sm text-sm font-semibold transition"
                   style="background: {{ request('mode','week') === 'week' ? $theme : '#f5f5f4' }};
                          color: {{ request('mode','week') === 'week' ? 'white' : '#44403c' }};">
                    週表示
                </a>
            </div>

            <p class="text-xs text-gray-500 mt-3">
                日表示は詳細確認向け、週表示は全体把握向けです。
            </p>
        </div>

        <div class="xl:col-span-4 bg-white rounded-3xl shadow-sm border border-stone-200 p-5">
            <div class="text-xs font-semibold text-stone-500 mb-2">担当者で絞り込み</div>

            <select id="staffSelect"
                    class="w-full border rounded-2xl px-3 py-3 shadow-sm focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $theme }};"
                    onchange="reloadByMode()">
                <option value="">全担当者を表示</option>
                @foreach($company->staff()->where('is_reservable', true)->orderBy('priority_order')->get() as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
            </select>

            <p class="text-xs text-gray-500 mt-3">
                全体の空き確認なら「全担当者を表示」のままで使えます。
            </p>
        </div>
    </div>

    <div id="messageArea" class="mb-6"></div>

    {{-- カレンダー本体 --}}
    <div class="bg-white rounded-3xl shadow-lg border border-stone-200 p-4 sm:p-6">
        @if($mode === 'day')
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-stone-800">日別の空き状況</h2>
                    <p class="text-sm text-gray-500">空いている枠を押すと、メニュー選択から予約登録へ進みます。</p>
                </div>
                <div class="text-xs sm:text-sm text-stone-500">
                    予約ありの枠は内容確認後にキャンセルできます
                </div>
            </div>

			<div class="overflow-auto max-h-[70vh] rounded-2xl border border-stone-200 bg-white">
			    <table class="min-w-[720px] w-full text-sm text-center border-collapse">
			        <thead id="day-head"></thead>
			        <tbody id="day-body"></tbody>
			    </table>
			</div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-stone-800">週別の空き状況</h2>
                    <p class="text-sm text-gray-500">まずは空きのある日を探したいときに便利です。</p>
                </div>
                <div class="text-xs sm:text-sm text-stone-500">
                	「予約可能」の枠を押すとメニュー選択へ進みます
                </div>
            </div>

            <div id="calendar"></div>
        @endif
    </div>
</div>

<script>
let currentDate = new Date(getLocalDateStr());
const mode = "{{ $mode }}";
let selectedDatetime = null;
let selectedStaffId = null;
let cancelReservationId = null;
let selectedMenuIds = [];
let availableStaffCache = [];
let selectedMenuDuration = 0;
let selectedMenuPrice = 0;
let selectedAssignments = [];
let assignmentCandidatesCache = [];
let assignmentMode = 'single';
let preferLessCapableStaffForMenuAssignment = @json((bool) ($company->prefer_less_capable_staff_for_menu_assignment ?? false));

function getLocalDateStr(date = new Date()) {
    const y = date.getFullYear();
    const m = ('0' + (date.getMonth() + 1)).slice(-2);
    const d = ('0' + date.getDate()).slice(-2);
    return `${y}-${m}-${d}`;
}
function toList(value) {
    if (Array.isArray(value)) return value;
    if (value && typeof value === 'object') return Object.values(value);
    return [];
}
function getCandidateTitle(candidate) {
    const assignments = toList(candidate.assignments);

    if (assignments.length === 1) {
        return safeText(assignments[0]?.staff_name, `第${candidate.rank}候補`);
    }

    const uniqueStaffNames = [...new Set(
        assignments.map(row => safeText(row.staff_name, '')).filter(Boolean)
    )];

    if (uniqueStaffNames.length === 1) {
        return uniqueStaffNames[0];
    }

    return `組み合わせ候補 ${candidate.rank}`;
}

function getCandidateSubLabel(candidate) {
    if ((candidate.rank ?? 999) === 1) {
        return 'おすすめ';
    }

    return safeText(candidate.label, '担当者候補');
}

function renderCandidateCard(candidate, index) {
    const assignments = toList(candidate.assignments);
    const title = getCandidateTitle(candidate);
    const subLabel = getCandidateSubLabel(candidate);

    const rows = assignments.map(row => `
        <div class="flex items-center justify-between gap-3 py-2 border-b border-stone-100 last:border-b-0">
            <span class="text-sm text-stone-600">${safeText(row.menu_name, '選択メニュー')}</span>
            <span class="text-sm font-semibold text-stone-800 text-right">${safeText(row.staff_name, '担当者')}</span>
        </div>
    `).join('');

    const badgeClass = (candidate.rank ?? 999) === 1
        ? 'bg-amber-50 text-amber-700'
        : 'bg-stone-100 text-stone-600';

    return `
        <button type="button"
                id="assignment-candidate-${index}"
                onclick="selectAssignmentPatternByIndex(${index})"
                class="w-full text-left rounded-2xl border border-stone-200 bg-white p-4 shadow-sm hover:border-stone-300 hover:bg-stone-50 transition">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-base font-bold text-stone-800 truncate">${title}</div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${badgeClass}">
                            ${subLabel}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-stone-100 text-stone-600 px-2.5 py-1 text-xs font-semibold">
                            第${candidate.rank ?? (index + 1)}候補
                        </span>
                        <span class="inline-flex items-center rounded-full bg-stone-100 text-stone-600 px-2.5 py-1 text-xs font-semibold">
                            ${assignments.length}件の割り当て
                        </span>
                    </div>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-white"
                          style="background: {{ $theme }};">
                        選択する
                    </span>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-stone-50 border border-stone-200 p-3">
                ${rows}
            </div>
        </button>
    `;
}

function renderAssignmentCandidates(area, candidates) {
    const topCandidates = candidates.slice(0, 3);
    const otherCandidates = candidates.slice(3);

    area.innerHTML = `
        <div class="space-y-3">
            ${topCandidates.map((candidate, index) => renderCandidateCard(candidate, index)).join('')}
        </div>
        ${
            otherCandidates.length > 0
                ? `
                <details class="mt-4">
                    <summary class="cursor-pointer list-none rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-100 transition">
                        他の候補を表示（${otherCandidates.length}件）
                    </summary>
                    <div class="mt-3 space-y-3">
                        ${otherCandidates.map((candidate, offset) => renderCandidateCard(candidate, offset + 3)).join('')}
                    </div>
                </details>
                `
                : ''
        }
    `;
}

function highlightSelectedCandidate(index) {
    document.querySelectorAll('[id^="assignment-candidate-"]').forEach(el => {
        el.classList.remove('border-2');
        el.classList.remove('shadow-md');
        el.style.borderColor = '';
        el.style.backgroundColor = '';
    });

    const active = document.getElementById(`assignment-candidate-${index}`);
    if (!active) return;

    active.classList.add('border-2');
    active.classList.add('shadow-md');
    active.style.borderColor = '{{ $theme }}';
    active.style.backgroundColor = '{{ $theme }}08';
}
function safeText(value, fallback = '') {
    if (value === null || value === undefined) return fallback;
    const text = String(value).trim();
    return text === '' ? fallback : text;
}

function toList(value) {
    if (Array.isArray(value)) return value;
    if (value && typeof value === 'object') return Object.values(value);
    return [];
}

function formatDateLabel(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const weekNames = ['日', '月', '火', '水', '木', '金', '土'];
    const y = date.getFullYear();
    const m = ('0' + (date.getMonth() + 1)).slice(-2);
    const d = ('0' + date.getDate()).slice(-2);
    return `${y}/${m}/${d}（${weekNames[date.getDay()]}）`;
}

function updateTopDateLabel() {
    const dateStr = getLocalDateStr(currentDate);
    const hero = document.getElementById('currentDateHero');
    const picker = document.getElementById('globalDatePicker');

    if (hero) hero.innerText = formatDateLabel(dateStr);
    if (picker) picker.value = dateStr;
}

document.addEventListener("DOMContentLoaded", function () {
    updateTopDateLabel();

    if (mode === 'week') loadCalendar();
    if (mode === 'day') loadDayCalendar();
});

function goToday() {
    currentDate = new Date(getLocalDateStr() + "T00:00:00");
    updateTopDateLabel();
    reloadByMode();
}

function navigateCurrent(diff) {
    if (mode === 'week') {
        currentDate.setDate(currentDate.getDate() + (diff * 7));
    } else {
        currentDate.setDate(currentDate.getDate() + diff);
    }

    updateTopDateLabel();
    reloadByMode();
}

function jumpByMode(dateStr) {
    currentDate = new Date(dateStr + "T00:00:00");
    updateTopDateLabel();
    reloadByMode();
}

function reloadByMode() {
    if (mode === 'week') loadCalendar();
    if (mode === 'day') loadDayCalendar();
}

function getStatusBadge(cell) {
    if (!cell) {
        return `
            <div class="mx-auto inline-flex items-center justify-center rounded-xl px-2 py-2 text-xs font-semibold bg-stone-100 text-stone-400 border border-stone-200 w-[96px]">
                -
            </div>
        `;
    }

    if (cell.status === '○' || cell.status === '△') {
        return `
            <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-2 py-2 bg-green-50 text-green-700 border border-green-200 w-[96px] shadow-sm">
                <span class="text-xs font-bold whitespace-nowrap">予約可能</span>
                <span class="text-[11px] mt-0.5 whitespace-nowrap">空き ${cell.available}/${cell.total}</span>
            </div>
        `;
    }

    const reason = (cell.total > 0)
        ? 'この時間帯の受付枠が埋まっています'
        : '営業時間外・休業日・シフト外などで受付できません';

    const label = (cell.total > 0) ? '予約あり' : '受付不可';

    return `
        <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-2 py-2 bg-stone-200 text-stone-500 border border-stone-300 w-[96px] opacity-90"
             title="${reason}">
            <span class="text-xs font-bold whitespace-nowrap">${label}</span>
            <span class="text-[11px] mt-0.5 whitespace-nowrap">${Math.max(0, (cell.total ?? 0) - (cell.available ?? 0))}/${cell.total ?? 0}</span>
        </div>
    `;
}

function loadCalendar() {
    if (mode !== 'week') return;

    updateTopDateLabel();

    let dateStr = getLocalDateStr(currentDate);
    let staffId = document.getElementById('staffSelect').value;

    fetch(`/company/reserve/data?mode=week&date=${dateStr}&staff_id=${staffId}`)
        .then(res => res.json())
        .then(data => {
            if (!data || !data.slots) {
                console.error('APIエラー', data);
                return;
            }

            let slots = data.slots;
            let firstTimeKey = Object.keys(slots)[0];
            let dates = firstTimeKey ? Object.keys(slots[firstTimeKey] || {}) : [];
            let times = Object.keys(slots);

            const timeColWidth = 88;
            const dayColWidth = 132;

            let html = `
                <div class="overflow-auto max-h-[70vh] rounded-2xl border border-stone-200 bg-white">
                    <table class="border-collapse text-sm table-fixed"
                           style="width: max-content; min-width: ${timeColWidth + (dates.length * dayColWidth)}px;">
                        <colgroup>
                            <col style="width:${timeColWidth}px; min-width:${timeColWidth}px; max-width:${timeColWidth}px;">
                            ${dates.map(() => `<col style="width:${dayColWidth}px; min-width:${dayColWidth}px; max-width:${dayColWidth}px;">`).join('')}
                        </colgroup>
                        <thead>
                            <tr class="bg-stone-50">
                                <th class="sticky top-0 left-0 z-30 px-2 py-3 border-b border-r bg-stone-50 text-center font-bold text-stone-700 whitespace-nowrap"
                                    style="width:${timeColWidth}px; min-width:${timeColWidth}px; max-width:${timeColWidth}px;">
                                    時間
                                </th>
            `;

            dates.forEach(d => {
                let day = new Date(d + 'T00:00:00').getDay();
                let w = ['日','月','火','水','木','金','土'][day];
                let dayClass = day === 0 ? 'text-red-500' : (day === 6 ? 'text-blue-500' : 'text-stone-700');

                html += `
                    <th class="sticky top-0 z-20 px-2 py-3 border-b bg-white text-center align-middle"
                        style="width:${dayColWidth}px; min-width:${dayColWidth}px; max-width:${dayColWidth}px;">
                        <div class="text-sm font-bold ${dayClass} whitespace-nowrap">${d}</div>
                        <div class="text-xs ${dayClass} mt-1">${w}</div>
                    </th>
                `;
            });

            html += `</tr></thead><tbody>`;

            times.forEach(time => {
                html += `
                    <tr class="hover:bg-stone-50/70 transition">
                        <td class="sticky left-0 z-10 bg-white px-2 py-3 border-b border-r font-bold text-stone-700 text-center whitespace-nowrap align-middle"
                            style="width:${timeColWidth}px; min-width:${timeColWidth}px; max-width:${timeColWidth}px;">
                            ${time}
                        </td>
                `;

                dates.forEach(d => {
                    let cell = slots[time][d];

                    if (!cell) {
                        html += `
                            <td class="border-b px-2 py-2 text-center bg-stone-50 align-middle"
                                style="width:${dayColWidth}px; min-width:${dayColWidth}px; max-width:${dayColWidth}px;">
                                <div class="mx-auto inline-flex items-center justify-center rounded-xl px-2 py-2 text-xs font-semibold bg-stone-100 text-stone-400 border border-stone-200 w-[96px]">
                                    -
                                </div>
                            </td>
                        `;
                        return;
                    }

                    let clickable = cell.status !== '×';
                    let wrapperClass = clickable ? 'cursor-pointer transition hover:scale-[1.02]' : 'cursor-not-allowed';

                    html += `
                        <td class="border-b px-2 py-2 text-center ${clickable ? 'bg-white' : 'bg-stone-50'} align-middle"
                            style="width:${dayColWidth}px; min-width:${dayColWidth}px; max-width:${dayColWidth}px;">
                            <div class="${wrapperClass}"
                                 ${clickable ? `onclick="startReservationFlow('${d} ${time}')"` : ''}>
                                ${getStatusBadge(cell)}
                            </div>
                        </td>
                    `;
                });

                html += `</tr>`;
            });

            html += `</tbody></table></div>`;

            document.getElementById('calendar').innerHTML = html;
        })
        .catch(err => {
            console.error('通信エラー', err);
        });
}

function getDayCellContent(cell, dateStr, time, staff) {
    let now = new Date();
    let cellTime = new Date(`${dateStr}T${time}:00`);
    let isPast = cellTime < now;

    if (!cell) {
        return `
            <td class="p-3 sm:p-4 text-center bg-stone-50">
                <div class="mx-auto inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold bg-stone-100 text-stone-400 border border-stone-200">
                    -
                </div>
            </td>
        `;
    }

    const total = parseInt(cell.total ?? 0);
    const available = parseInt(cell.available ?? 0);
    const used = Math.max(0, total - available);

    if (cell.status === '×') {
        if (isPast) {
            return `
                <td class="p-3 sm:p-4 text-center bg-stone-50">
                    <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-3 py-2 bg-stone-200 text-stone-500 border border-stone-300 min-w-[104px]"
                         title="過去の時間のため受付できません">
                        <span class="text-xs font-bold">受付終了</span>
                        <span class="text-[11px] mt-0.5">${used}/${total || 0}</span>
                    </div>
                </td>
            `;
        }

        if (cell.reservation_id) {
            return `
                <td class="p-3 sm:p-4 text-center">
                    <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-3 py-2 bg-red-50 text-red-700 border border-red-200 min-w-[104px] shadow-sm cursor-pointer hover:bg-red-100 transition"
                         data-id="${cell.reservation_id}"
                         data-datetime="${cell.reservation_start ?? `${dateStr} ${time}`}"
                         data-customer-name="${cell.customer_name ?? ''}"
                         data-customer-phone="${cell.customer_phone ?? ''}"
                         data-staff-name="${cell.staff_name ?? staff.name}"
                         onclick="handleCancelClick(this)"
                         title="この担当者の同時予約枠が埋まっています。クリックでキャンセル確認">
                        <span class="text-xs font-bold">予約あり</span>
                        <span class="text-[11px] mt-0.5">${used}/${total || 0}</span>
                    </div>
                </td>
            `;
        }

        const label = (total > 0) ? '予約あり' : '受付不可';
        const title = (total > 0)
            ? 'この担当者の同時予約枠が埋まっています'
            : '営業時間外・休業日・シフト外などで受付できません';

        return `
            <td class="p-3 sm:p-4 text-center bg-stone-50">
                <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-3 py-2 bg-stone-200 text-stone-500 border border-stone-300 min-w-[104px] cursor-not-allowed"
                     title="${title}">
                    <span class="text-xs font-bold">${label}</span>
                    <span class="text-[11px] mt-0.5">${used}/${total || 0}</span>
                </div>
            </td>
        `;
    }

    if (cell.status === '○' || cell.status === '△') {
        if (isPast) {
            return `
                <td class="p-3 sm:p-4 text-center bg-stone-50">
                    <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-3 py-2 bg-stone-200 text-stone-500 border border-stone-300 min-w-[104px]">
                        <span class="text-xs font-bold">受付終了</span>
                        <span class="text-[11px] mt-0.5">${used}/${total || 0}</span>
                    </div>
                </td>
            `;
        }

        return `
            <td class="p-3 sm:p-4 text-center">
                <div class="mx-auto inline-flex flex-col items-center justify-center rounded-xl px-3 py-2 text-white min-w-[104px] shadow-sm cursor-pointer hover:opacity-90 transition"
                     style="background: {{ $theme }}"
                     onclick="startReservationFlow('${dateStr} ${time}')"
                     title="この担当者は残り ${available} 枠です">
                    <span class="text-xs font-bold">予約可能</span>
                    <span class="text-[11px] mt-0.5 text-white/90">空き ${available}/${total || 0}</span>
                </div>
            </td>
        `;
    }

    return `
        <td class="p-3 sm:p-4 text-center bg-stone-50">
            <div class="mx-auto inline-flex items-center justify-center rounded-xl px-3 py-2 text-xs font-semibold bg-stone-100 text-stone-400 border border-stone-200">
                -
            </div>
        </td>
    `;
}


function loadDayCalendar() {
    if (mode !== 'day') return;

    let dateStr = getLocalDateStr(currentDate);
    updateTopDateLabel();

    fetch(`/company/reserve/data?mode=day&date=${dateStr}`)
        .then(res => res.json())
        .then(data => {
            const head = document.getElementById("day-head");
            const body = document.getElementById("day-body");

            head.innerHTML = "";
            body.innerHTML = "";

            let headerRow = `
                <tr class="bg-stone-50">
                    <th class="p-4 sticky top-0 left-0 z-30 text-left border-b border-r bg-stone-50 text-stone-700 min-w-[90px] whitespace-nowrap">
                        時間
                    </th>`;

            data.staffs.forEach(staff => {
                headerRow += `
                    <th class="p-4 border-b text-center sticky top-0 bg-white z-20 min-w-[120px]">
                        <div class="font-semibold text-stone-800 whitespace-nowrap">${staff.name}</div>
                    </th>`;
            });

            headerRow += "</tr>";
            head.innerHTML = headerRow;

            Object.keys(data.slots).forEach(time => {
                let row = `
                    <tr class="hover:bg-stone-50 transition">
                        <td class="p-4 font-bold bg-white sticky left-0 z-10 text-center align-middle border-b border-r text-stone-700 whitespace-nowrap">
                            ${time}
                        </td>`;

                data.staffs.forEach(staff => {
                    let cell = data.slots[time][staff.id];
                    row += getDayCellContent(cell, dateStr, time, staff);
                });

                row += "</tr>";
                body.innerHTML += row;
            });
        });
}

function startReservationFlow(datetime) {
    selectedDatetime = datetime;
    selectedStaffId = null;
    selectedMenuIds = [];
    selectedAssignments = [];
    assignmentCandidatesCache = [];
    availableStaffCache = [];
    selectedMenuDuration = 0;
    selectedMenuPrice = 0;
    assignmentMode = preferLessCapableStaffForMenuAssignment ? 'multi' : 'single';

    resetMenuStep();
    document.getElementById('menuStepDatetime').innerText = datetime;
    document.getElementById('menuStepModal').classList.remove('hidden');
    document.getElementById('menuStepModal').classList.add('flex');
}

function closeMenuStepModal() {
    document.getElementById('menuStepModal').classList.add('hidden');
    document.getElementById('menuStepModal').classList.remove('flex');
}

function resetMenuStep() {
    document.querySelectorAll('.step-menu-checkbox').forEach(el => {
        el.checked = false;
    });
    document.getElementById('stepTotalDuration').innerText = 0;
    document.getElementById('stepTotalPrice').innerText = 0;
    selectedAssignments = [];
    selectedStaffId = null;
    assignmentCandidatesCache = [];
}

document.addEventListener('change', function(e){
    if (!e.target.classList.contains('step-menu-checkbox')) return;

    let totalTime = 0;
    let totalPrice = 0;

    document.querySelectorAll('.step-menu-checkbox:checked').forEach(el => {
        totalTime += parseInt(el.dataset.duration || 0);
        totalPrice += parseInt(el.dataset.price || 0);
    });

    document.getElementById('stepTotalDuration').innerText = totalTime;
    document.getElementById('stepTotalPrice').innerText = totalPrice.toLocaleString();
});

function normalizeAssignmentCandidates(data) {
    let normalizedMode = data.mode || 'single';
    let candidates = [];

    const rawCandidates = toList(data.candidates);
    if (rawCandidates.length > 0) {
        candidates = rawCandidates.map((candidate, index) => {
            const assignments = toList(candidate.assignments).map((row, rowIndex) => ({
                menu_id: row.menu_id ?? null,
                menu_name: safeText(row.menu_name, `メニュー${rowIndex + 1}`),
                staff_id: row.staff_id ?? null,
                staff_name: safeText(row.staff_name, safeText(row.staff?.name, '担当者'))
            }));

            return {
                rank: candidate.rank ?? (index + 1),
                label: safeText(
                    candidate.label,
                    selectedMenuIds.length <= 1
                        ? '担当者候補'
                        : (assignments.length > 1 ? '複数担当' : '単独担当')
                ),
                assignments
            };
        });

        return { mode: normalizedMode, candidates };
    }

    const rawPatterns = toList(data.patterns);
    if (rawPatterns.length > 0) {
        candidates = rawPatterns.map((pattern, index) => {
            const assignments = toList(pattern).map((row, rowIndex) => ({
                menu_id: row.menu_id ?? null,
                menu_name: safeText(row.menu_name, `メニュー${rowIndex + 1}`),
                staff_id: row.staff_id ?? null,
                staff_name: safeText(row.staff_name, safeText(row.staff?.name, '担当者'))
            }));

            return {
                rank: index + 1,
                label: selectedMenuIds.length <= 1
                    ? '担当者候補'
                    : (assignments.length > 1 ? '複数担当' : '単独担当'),
                assignments
            };
        });

        if (selectedMenuIds.length <= 1) {
            normalizedMode = 'single';
        } else if (!data.mode) {
            normalizedMode = 'multi';
        }

        return { mode: normalizedMode, candidates };
    }

    const rawStaff = toList(data.staff);
    if (rawStaff.length > 0) {
        candidates = rawStaff.map((staff, index) => ({
            rank: index + 1,
            label: '担当者候補',
            assignments: [{
                menu_id: selectedMenuIds[0] ?? null,
                menu_name: '選択メニュー',
                staff_id: staff.id ?? null,
                staff_name: safeText(staff.name, '担当者')
            }]
        }));

        return { mode: 'single', candidates };
    }

    return { mode: normalizedMode, candidates: [] };
}

function proceedToStaffSelection() {
    selectedMenuIds = Array.from(document.querySelectorAll('.step-menu-checkbox:checked')).map(el => parseInt(el.value));

    if (selectedMenuIds.length === 0) {
        alert('メニューを選択してください');
        return;
    }

    selectedMenuDuration = 0;
    selectedMenuPrice = 0;

    document.querySelectorAll('.step-menu-checkbox:checked').forEach(el => {
        selectedMenuDuration += parseInt(el.dataset.duration || 0);
        selectedMenuPrice += parseInt(el.dataset.price || 0);
    });

    const params = new URLSearchParams();
    params.append('datetime', selectedDatetime);
    selectedMenuIds.forEach(id => params.append('menu_ids[]', id));

    fetch(`/company/calendar/assignment-candidates?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            const area = document.getElementById('stepStaffListArea');
            area.innerHTML = '';

            const normalized = normalizeAssignmentCandidates(data);
            assignmentMode = normalized.mode || 'single';
            const candidates = normalized.candidates || [];

            assignmentCandidatesCache = candidates;

            if (!candidates.length) {
                area.innerHTML = `
                    <div class="text-sm text-stone-500 bg-stone-50 border border-stone-200 rounded-xl p-4">
                        このメニュー内容で対応できる担当パターンが見つかりませんでした。
                    </div>
                `;
            } else {
                renderAssignmentCandidates(area, candidates);
            }

            document.getElementById('stepStaffDatetime').innerText = selectedDatetime;
            document.getElementById('stepStaffDuration').innerText = selectedMenuDuration;
            document.getElementById('stepStaffPrice').innerText = selectedMenuPrice.toLocaleString();

            closeMenuStepModal();
            document.getElementById('staffStepModal').classList.remove('hidden');
            document.getElementById('staffStepModal').classList.add('flex');
        })
        .catch(() => {
            alert('担当候補の取得に失敗しました');
        });
}

function closeStaffStepModal() {
    document.getElementById('staffStepModal').classList.add('hidden');
    document.getElementById('staffStepModal').classList.remove('flex');
}

function selectAssignmentPatternByIndex(index) {
    const candidate = assignmentCandidatesCache[index];

    if (!candidate) {
        alert('担当候補の選択に失敗しました');
        return;
    }

    highlightSelectedCandidate(index);

    assignmentMode = (selectedMenuIds.length <= 1) ? 'single' : (assignmentMode || 'single');
    selectedAssignments = toList(candidate.assignments);

    if (assignmentMode === 'single' && selectedAssignments.length > 0) {
        selectedStaffId = selectedAssignments[0].staff_id;
    } else {
        selectedStaffId = null;
    }

    setTimeout(() => {
        closeStaffStepModal();
        openFinalReservationModal();
    }, 120);
}

function openFinalReservationModal() {
    document.getElementById('reserveDatetime').innerText = selectedDatetime;
    document.getElementById('finalTotalDuration').innerText = selectedMenuDuration;
    document.getElementById('finalTotalPrice').innerText = selectedMenuPrice.toLocaleString();

    const area = document.getElementById('finalAssignmentArea');

    if (area) {
        if (!Array.isArray(selectedAssignments) || selectedAssignments.length === 0) {
            area.innerHTML = `
                <div class="text-sm text-stone-500 bg-stone-50 border border-stone-200 rounded-xl p-3">
                    担当が未選択です
                </div>
            `;
        } else {
            area.innerHTML = selectedAssignments.map(row => `
                <div class="flex items-center justify-between py-2 border-b border-stone-100 last:border-b-0">
                    <span class="text-sm text-stone-700">${row.menu_name ?? '選択メニュー'}</span>
                    <span class="text-sm font-semibold text-stone-800">${row.staff_name}</span>
                </div>
            `).join('');
        }
    }

    document.getElementById('reserveModal').classList.remove('hidden');
    document.getElementById('reserveModal').classList.add('flex');
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
    const phone = document.getElementById('modal_customer_phone').value;
    const phonePattern = /^[0-9\-]*$/;

    if (!name) {
        alert('お名前を入力してください');
        return;
    }

    if (!phone) {
        alert('電話番号を入力してください');
        return;
    }

    if (!phonePattern.test(phone)) {
        alert('電話番号は数字とハイフンのみ入力できます');
        return;
    }

    if (!selectedMenuIds.length) {
        alert('メニューを選択してください');
        return;
    }

    if (!selectedAssignments.length && !selectedStaffId) {
        alert('担当パターンを選択してください');
        return;
    }

    const payload = {
        start_at: selectedDatetime,
        customer_name: name,
        customer_phone: phone,
        menu_ids: selectedMenuIds
    };

    if (assignmentMode === 'multi') {
        payload.assignments = selectedAssignments.map(row => ({
            menu_id: row.menu_id,
            staff_id: row.staff_id
        }));
    } else {
        payload.staff_id = selectedStaffId || (selectedAssignments[0] ? selectedAssignments[0].staff_id : null);
    }

    fetch('/company/reservation', {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(result => {
        if(result.success){
            closeModal();

            document.getElementById('modal_customer_name').value = '';
            document.getElementById('modal_customer_phone').value = '';
            selectedMenuIds = [];
            selectedAssignments = [];
            selectedStaffId = null;
            assignmentCandidatesCache = [];
            selectedMenuDuration = 0;
            selectedMenuPrice = 0;

            if (mode === 'week') loadCalendar();
            if (mode === 'day') loadDayCalendar();
        } else {
            alert(result.message || '予約登録に失敗しました');
        }
    })
    .catch(() => {
        alert('通信エラーが発生しました');
    });
}

function handleCancelClick(el) {
    const reservationId = el.dataset.id;

    if (!reservationId) {
        alert('予約IDが取得できません');
        return;
    }

    cancelReservationId = reservationId;

    document.getElementById('cancelReservationDatetime').innerText = el.dataset.datetime || '-';
    document.getElementById('cancelCustomerName').innerText = el.dataset.customerName || '-';
    document.getElementById('cancelCustomerPhone').innerText = el.dataset.customerPhone || '-';
    document.getElementById('cancelStaffName').innerText = el.dataset.staffName || '-';

    document.getElementById('cancelConfirmModal').classList.remove('hidden');
    document.getElementById('cancelConfirmModal').classList.add('flex');
}

function closeCancelConfirmModal() {
    document.getElementById('cancelConfirmModal').classList.add('hidden');
    document.getElementById('cancelConfirmModal').classList.remove('flex');
    cancelReservationId = null;
}

function executeCancelReservation() {
    if (!cancelReservationId) {
        alert('予約IDが取得できません');
        return;
    }

    fetch(`/company/reservation/${cancelReservationId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            closeCancelConfirmModal();

            if (mode === 'week') loadCalendar();
            if (mode === 'day') loadDayCalendar();
        } else {
            alert(result.message || 'キャンセルに失敗しました');
        }
    })
    .catch(() => {
        alert('通信エラーが発生しました');
    });
}

function formatTel(input) {
    input.value = input.value.replace(/[^0-9\-]/g, '');
}
</script>

{{-- メニュー選択 --}}
<div id="menuStepModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="mb-4">
            <div class="flex items-center gap-2 text-xs text-stone-500 mb-2">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">STEP 1</span>
                <span>メニュー選択</span>
            </div>
            <h2 class="text-lg font-bold text-stone-800">メニューを選択</h2>
        </div>

        <div class="rounded-2xl bg-stone-50 border border-stone-200 px-4 py-3 mb-4">
            <div class="text-xs text-stone-500 mb-1">選択中の日時</div>
            <div id="menuStepDatetime" class="font-bold text-stone-800"></div>
        </div>

        <div class="space-y-4 max-h-80 overflow-y-auto border border-stone-200 rounded-2xl p-4 bg-white">
            @foreach($menus as $category => $menuList)
                <div>
                    <div class="font-bold text-sm mb-2 text-stone-700">{{ $category }}</div>
                    <div class="space-y-2">
                        @foreach($menuList as $menu)
                            <label class="flex items-start gap-3 p-3 border border-stone-200 rounded-xl cursor-pointer hover:bg-stone-50 transition">
                                <input type="checkbox"
                                       class="step-menu-checkbox mt-1"
                                       value="{{ $menu->id }}"
                                       data-price="{{ $menu->price }}"
                                       data-duration="{{ $menu->duration }}">
                                <div class="flex-1">
                                    <div class="font-semibold text-stone-800">{{ $menu->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $menu->duration }}分 / ¥{{ number_format($menu->price) }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 rounded-2xl bg-stone-50 border border-stone-200 p-4 text-sm text-stone-700">
            <div class="flex items-center justify-between">
                <span>合計時間</span>
                <span><span id="stepTotalDuration">0</span>分</span>
            </div>
            <div class="flex items-center justify-between mt-2 font-semibold">
                <span>合計料金</span>
                <span>¥<span id="stepTotalPrice">0</span></span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-2 mt-5">
            <button onclick="closeMenuStepModal()"
                    class="px-4 py-3 text-sm bg-gray-200 rounded-xl hover:bg-gray-300 transition">
                キャンセル
            </button>
            <button onclick="proceedToStaffSelection()"
                    class="px-4 py-3 text-sm text-white rounded-xl hover:opacity-90 transition"
                    style="background: {{ $theme }};">
                担当パターンを選ぶ
            </button>
        </div>
    </div>
</div>

{{-- 担当選択 --}}
<div id="staffStepModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="mb-4">
            <div class="flex items-center gap-2 text-xs text-stone-500 mb-2">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">STEP 2</span>
                <span>担当パターン選択</span>
            </div>
            <h2 class="text-lg font-bold text-stone-800 mb-2">担当パターンを選択</h2>
            <p class="text-sm text-stone-500">
                上位のおすすめ候補を先に表示しています。気になる担当を選んでください。
            </p>
        </div>

        <div class="rounded-2xl bg-stone-50 border border-stone-200 px-4 py-3 mb-4 text-sm">
            <div class="text-stone-500">予約日時</div>
            <div id="stepStaffDatetime" class="font-semibold text-stone-800 mb-2"></div>
            <div class="flex justify-between text-stone-700">
                <span>合計時間</span>
                <span><span id="stepStaffDuration">0</span>分</span>
            </div>
            <div class="flex justify-between text-stone-700 mt-1">
                <span>合計料金</span>
                <span>¥<span id="stepStaffPrice">0</span></span>
            </div>
        </div>

        <div id="stepStaffListArea" class="space-y-3"></div>

        <button onclick="closeStaffStepModal()"
                class="mt-5 w-full px-4 py-3 rounded-xl bg-stone-100 text-stone-700 hover:bg-stone-200 transition">
            戻る
        </button>
    </div>
</div>

{{-- キャンセル確認 --}}
<div id="cancelConfirmModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl p-6">
        <h2 class="text-lg font-bold mb-4 text-stone-800">予約キャンセル確認</h2>

        <p class="text-sm text-gray-500 mb-4">
            以下の予約をキャンセルします。内容にお間違いがないかご確認ください。
        </p>

        <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3 text-sm">
            <div>
                <div class="text-stone-500">予約日時</div>
                <div id="cancelReservationDatetime" class="font-semibold text-stone-800">-</div>
            </div>
            <div>
                <div class="text-stone-500">顧客名</div>
                <div id="cancelCustomerName" class="font-semibold text-stone-800">-</div>
            </div>
            <div>
                <div class="text-stone-500">電話番号</div>
                <div id="cancelCustomerPhone" class="font-semibold text-stone-800">-</div>
            </div>
            <div>
                <div class="text-stone-500">担当者</div>
                <div id="cancelStaffName" class="font-semibold text-stone-800">-</div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-2 mt-6">
            <button type="button"
                    onclick="closeCancelConfirmModal()"
                    class="px-4 py-3 text-sm bg-gray-200 rounded-xl hover:bg-gray-300 transition">
                戻る
            </button>

            <button type="button"
                    onclick="executeCancelReservation()"
                    class="px-4 py-3 text-sm text-white rounded-xl hover:opacity-90 transition"
                    style="background: {{ $theme }};">
                この予約をキャンセルする
            </button>
        </div>
    </div>
</div>

{{-- 予約確定 --}}
<div id="reserveModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">

        <div class="mb-4">
            <div class="flex items-center gap-2 text-xs text-stone-500 mb-2">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1">STEP 3</span>
                <span>予約確定</span>
            </div>
            <h2 class="text-lg font-bold text-stone-800">予約内容の確認</h2>
        </div>

        <div class="rounded-2xl bg-stone-50 border border-stone-200 px-4 py-3 mb-4">
            <div class="text-xs text-stone-500 mb-1">選択中の日時</div>
            <div id="reserveDatetime" class="font-bold text-stone-800"></div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-semibold text-stone-700 mb-1">お名前</label>
            <input type="text"
                   id="modal_customer_name"
                   class="border border-stone-300 rounded-xl p-3 w-full focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $theme }};"
                   placeholder="例：山田 花子">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-stone-700 mb-1">電話番号（数字と-のみ）</label>
            <input type="text"
                   id="modal_customer_phone"
                   class="border border-stone-300 rounded-xl p-3 w-full focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $theme }};"
                   placeholder="例：090-1234-5678"
                   oninput="formatTel(this)">
        </div>

        <div class="mt-4 rounded-2xl bg-stone-50 border border-stone-200 p-4">
            <div class="text-sm font-semibold text-stone-700 mb-2">担当割り当て</div>
            <div id="finalAssignmentArea" class="space-y-1"></div>
        </div>

        <div class="mt-4 rounded-2xl bg-stone-50 border border-stone-200 p-4 text-sm text-stone-700">
            <div class="flex items-center justify-between">
                <span>合計時間</span>
                <span><span id="finalTotalDuration">0</span>分</span>
            </div>
            <div class="flex items-center justify-between mt-2 font-semibold">
                <span>合計料金</span>
                <span>¥<span id="finalTotalPrice">0</span></span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-2 mt-5">
            <button onclick="closeModal()"
                    class="px-4 py-3 text-sm bg-gray-200 rounded-xl hover:bg-gray-300 transition">
                キャンセル
            </button>

            <button onclick="submitReservation()"
                    class="px-4 py-3 text-sm text-white rounded-xl hover:opacity-90 transition"
                    style="background: {{ $theme }};">
                この内容で予約する
            </button>
        </div>
    </div>
</div>
@endsection