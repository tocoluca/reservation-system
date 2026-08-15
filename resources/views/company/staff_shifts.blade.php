@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';

    $days = \Carbon\Carbon::parse($month)->daysInMonth;

    use Yasumi\Yasumi;
    $holidays = Yasumi::create('Japan', \Carbon\Carbon::parse($month)->year);

    $patternMap = collect($patterns)->keyBy('id');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Shift Management</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">勤務管理</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        {{ $month }} の勤務表を、スタッフごと・日付ごとにまとめて調整できます。
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ダッシュボード
                    </a>

                    <button form="shiftForm"
                            data-busy-button
                            class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-sm font-bold text-white bg-white/15 hover:bg-white/20 backdrop-blur-sm transition">
                        <span data-busy-text>保存する</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._shift_setup_nav', [
            'currentStep' => 3,
            'links' => [
                ['label' => '基本シフトへ', 'route' => 'company.staff-default-shifts', 'icon' => 'arrow-left'],
            ],
        ])
    </div>

    {{-- ガイド --}}
    <div class="mb-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">操作の流れ</h2>
                <p class="text-sm text-gray-500 mt-1">
                    まず自動生成で土台を作り、そのあと必要な日だけ個別調整すると効率的です。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">1. 月を選ぶ</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">2. 自動生成</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">3. 微調整</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
        <div class="xl:col-span-2 bg-white border border-gray-200 rounded-3xl shadow-sm p-5">
            <div class="flex flex-col lg:flex-row lg:items-end gap-3">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">表示する月</label>
                        <input type="month"
                               name="month"
                               value="{{ $month }}"
                               class="w-full border border-gray-300 rounded-2xl px-4 py-3">
                    </div>

                    <div class="sm:self-end">
                        <button class="w-full sm:w-auto px-5 py-3 text-white rounded-2xl font-semibold hover:opacity-90 transition"
                                style="background: {{ $theme }}">
                            表示
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-5">
            <div class="text-xs font-semibold text-gray-500 mb-3">初期反映</div>

            <div class="flex flex-col gap-3">
                <form method="POST" action="{{ route('company.staff-shifts.generate') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button class="w-full px-4 py-3 bg-gray-800 text-white rounded-2xl font-semibold hover:bg-gray-700 transition">
                        基本シフト生成
                    </button>
                </form>

                <form method="POST" action="{{ route('company.staff-shifts.copy') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button class="w-full px-4 py-3 bg-indigo-600 text-white rounded-2xl font-semibold hover:bg-indigo-500 transition">
                        前月コピー
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="sticky top-24 z-30 mb-6 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span id="shiftDirtyBadge" class="hidden rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">未保存の変更あり</span>
                    <span class="text-sm font-bold text-gray-900">シフト編集</span>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">休み</span>
                    @foreach($patterns as $p)
                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">
                            <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $p->color ?: '#64748b' }}"></span>
                            {{ $p->name }}
                        </span>
                    @endforeach
                </div>
            </div>

            <button form="shiftForm"
                    data-busy-button
                    class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span data-busy-text>保存する</span>
            </button>
        </div>
    </div>

    <form id="shiftForm" method="POST" action="{{ route('company.staff-shifts.update') }}" data-busy-form="true" data-busy-label="保存中…">
        @csrf

        <div class="bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-bold text-gray-900">スタッフ別 シフト表</h2>
                <p class="text-sm text-gray-500 mt-1">
                    各マスのボタンを押すだけで変更できます。表示されるシフトは登録済みパターンです。
                </p>
            </div>

            <div class="overflow-x-auto max-h-[72vh]">
                <table class="min-w-max text-sm w-full">
                    <thead class="sticky top-0 z-10">
                        <tr style="background: {{ $theme }}; color: white;">
                            <th class="p-3 sticky left-0 bg-white text-black z-20 min-w-[240px] border-r">
                                スタッフ
                            </th>

                            @for($d = 1; $d <= $days; $d++)
                                @php
                                    $dateObj = \Carbon\Carbon::parse("$month-$d");
                                    $isHoliday = $holidays->isHoliday($dateObj);
                                    $dayOfWeek = $dateObj->dayOfWeek;

                                    $color = '';
                                    if ($isHoliday) {
                                        $color = 'bg-red-200 text-red-900';
                                    } elseif ($dayOfWeek == 0) {
                                        $color = 'text-red-600';
                                    } elseif ($dayOfWeek == 6) {
                                        $color = 'text-blue-600';
                                    }
                                @endphp

                                <th class="p-2 text-center min-w-[156px] {{ $color }}" data-day="{{ $d }}" data-weekday="{{ $dayOfWeek }}">
                                    <div class="font-bold">{{ $d }}</div>
                                    <div class="text-xs mb-2">
                                        {{ ['日','月','火','水','木','金','土'][$dayOfWeek] }}
                                    </div>

                                    <div class="flex flex-wrap justify-center gap-1">
                                        <button type="button"
                                                onclick="setDayShift({{ $d }}, '')"
                                                class="text-[10px] bg-gray-500 text-white px-2 py-1 rounded-md hover:bg-gray-400 transition">
                                            休
                                        </button>

                                        @foreach($patterns as $p)
                                            <button type="button"
                                                    onclick="setDayShift({{ $d }}, '{{ $p->id }}')"
                                                    class="text-[10px] text-white px-2 py-1 rounded-md hover:opacity-85 transition"
                                                    style="background: {{ $p->color ?: '#64748b' }}">
                                                {{ \Illuminate\Support\Str::limit($p->name, 4, '') }}
                                            </button>
                                        @endforeach
                                    </div>
                                </th>
                            @endfor
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($staffs as $staff)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-semibold sticky left-0 bg-white z-10 min-w-[240px] border-r align-top">
                                    <div class="space-y-3">
                                        <div class="font-semibold text-gray-900">{{ $staff->name }}</div>

                                        <div class="flex flex-wrap gap-2">
                                            <button type="button"
                                                    onclick="setStaffShift({{ $staff->id }}, '')"
                                                    class="text-xs bg-gray-500 text-white px-3 py-1.5 rounded-lg hover:bg-gray-400 transition">
                                                休み
                                            </button>

                                            @foreach($patterns as $p)
                                                <button type="button"
                                                        onclick="setStaffShift({{ $staff->id }}, '{{ $p->id }}')"
                                                        class="text-xs text-white px-3 py-1.5 rounded-lg hover:opacity-85 transition"
                                                        style="background: {{ $p->color ?: '#64748b' }}">
                                                    {{ $p->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>

                                @for($d = 1; $d <= $days; $d++)
                                    @php
                                        $date = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);

                                        $shift = $shifts[$staff->id][$date][0] ?? null;
                                        $currentShiftId = $shift->shift_pattern_id ?? '';
                                        $currentShiftName = $shift?->shiftPattern->name ?? ($currentShiftId ? ($patternMap[$currentShiftId]->name ?? '') : '');
                                        $currentShiftColor = $shift?->shiftPattern->color ?? ($currentShiftId ? ($patternMap[$currentShiftId]->color ?? '#64748b') : null);

                                        $staffVacations = $vacations[$staff->id] ?? collect();

                                        $isVacation = $staffVacations->first(function($v) use ($date) {
                                            return $date >= \Carbon\Carbon::parse($v->start_at)->toDateString()
                                                && $date <= \Carbon\Carbon::parse($v->end_at)->toDateString();
                                        });

                                        $business = $businessDays[\Carbon\Carbon::parse($date)->format('Y-m-d H:i:s')] ?? null;

                                        $isClosed = false;

                                        if ($business) {
                                            if (
                                                $business->is_open === false &&
                                                is_null($business->open_time) &&
                                                is_null($business->close_time)
                                            ) {
                                                $isClosed = true;
                                            }
                                        }
                                    @endphp

                                    <td class="p-1 min-w-[156px] align-middle">
                                        @if($isClosed)
                                            <div class="text-gray-400 text-center font-semibold text-xs py-3">休業</div>
                                        @elseif($isVacation)
                                            <div class="text-red-500 text-center font-bold text-xs py-3">休暇</div>
                                        @else
                                            <input type="hidden"
                                                   name="shifts[{{ $staff->id }}][{{ $date }}]"
                                                   value="{{ $currentShiftId }}"
                                                   class="shift-input"
                                                   data-staff="{{ $staff->id }}"
                                                   data-day="{{ $d }}"
                                                   data-date="{{ $date }}"
                                                   data-current-name="{{ $currentShiftName }}"
                                                   data-current-color="{{ $currentShiftColor }}">

                                            <div class="rounded-xl border border-gray-200 p-1.5 bg-white">
                                                <div class="grid gap-1" style="grid-template-columns: repeat({{ count($patterns) + 1 }}, minmax(0, 1fr));">
                                                    <button type="button"
                                                            class="shift-btn text-[11px] px-0 py-1.5 rounded-md font-semibold"
                                                            data-target-staff="{{ $staff->id }}"
                                                            data-target-day="{{ $d }}"
                                                            data-value=""
                                                            data-label="休"
                                                            data-color="">
                                                        休
                                                    </button>

                                                    @foreach($patterns as $p)
                                                        <button type="button"
                                                                class="shift-btn text-[11px] px-1 py-1.5 rounded-md font-semibold text-white"
                                                                data-target-staff="{{ $staff->id }}"
                                                                data-target-day="{{ $d }}"
                                                                data-value="{{ $p->id }}"
                                                                data-label="{{ $p->name }}"
                                                                data-color="{{ $p->color ?: '#64748b' }}">
                                                            {{ \Illuminate\Support\Str::limit($p->name, 4, '') }}
                                                        </button>
                                                    @endforeach
                                                </div>

                                                <div class="mt-1.5 text-center">
                                                    <span class="shift-state text-[10px] font-bold rounded-full px-2 py-1 inline-block">
                                                        {{ $currentShiftName ?: '休み' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

</div>

<script>
let shiftHasUnsavedChanges = false;

function markShiftDirty() {
    shiftHasUnsavedChanges = true;
    const badge = document.getElementById('shiftDirtyBadge');
    if (badge) {
        badge.classList.remove('hidden');
    }
}

function setButtonInactiveStyle(btn) {
    btn.style.background = '';
    btn.style.color = '';
    btn.classList.remove('ring-2', 'ring-offset-1', 'ring-gray-400', 'text-white');

    const label = btn.dataset.label ?? '';
    const color = btn.dataset.color ?? '';

    if (label === '休') {
        btn.classList.add('bg-gray-100', 'text-gray-600');
        return;
    }

    btn.classList.remove('bg-gray-100', 'text-gray-600');
    btn.style.background = color || '#64748b';
    btn.style.opacity = '0.22';
    btn.style.color = '#111827';
}

function setButtonActiveStyle(btn) {
    btn.style.opacity = '1';
    btn.classList.remove('bg-gray-100', 'text-gray-600');

    const label = btn.dataset.label ?? '';
    const color = btn.dataset.color ?? '';

    if (label === '休') {
        btn.style.background = '';
        btn.style.color = '';
        btn.classList.add('bg-gray-800', 'text-white', 'ring-2', 'ring-offset-1', 'ring-gray-400');
        return;
    }

    btn.style.background = color || '#64748b';
    btn.style.color = '#ffffff';
    btn.classList.add('ring-2', 'ring-offset-1', 'ring-gray-400');
}

function updateCellUI(input) {
    const wrapper = input.closest('td');
    if (!wrapper) return;

    const buttons = wrapper.querySelectorAll('.shift-btn');
    const state = wrapper.querySelector('.shift-state');
    const value = input.value ?? '';

    buttons.forEach(btn => {
        btn.style.opacity = '';
        setButtonInactiveStyle(btn);

        const btnValue = btn.dataset.value ?? '';
        if (btnValue === value) {
            setButtonActiveStyle(btn);
        }
    });

    if (state) {
        const selectedBtn = Array.from(buttons).find(btn => (btn.dataset.value ?? '') === value);

        state.className = 'shift-state text-[10px] font-bold rounded-full px-2 py-1 inline-block';

        if (!selectedBtn || value === '') {
            state.textContent = '休み';
            state.classList.add('bg-gray-100', 'text-gray-700');
        } else {
            state.textContent = selectedBtn.dataset.label || '設定済み';
            state.style.background = selectedBtn.dataset.color || '#64748b';
            state.style.color = '#fff';
        }
    }
}

function setCellValue(staffId, day, value) {
    const input = document.querySelector(`.shift-input[data-staff="${staffId}"][data-day="${day}"]`);
    if (!input) return;
    if (input.value !== value) {
        markShiftDirty();
    }
    input.value = value;
    updateCellUI(input);
}

function setStaffShift(staffId, value) {
    document.querySelectorAll(`.shift-input[data-staff="${staffId}"]`).forEach(input => {
        if (input.value !== value) {
            markShiftDirty();
        }
        input.value = value;
        updateCellUI(input);
    });
}

function setDayShift(day, value) {
    document.querySelectorAll(`.shift-input[data-day="${day}"]`).forEach(input => {
        if (input.value !== value) {
            markShiftDirty();
        }
        input.value = value;
        updateCellUI(input);
    });
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.shift-btn');
    if (!btn) return;

    const staffId = btn.dataset.targetStaff;
    const day = btn.dataset.targetDay;
    const value = btn.dataset.value ?? '';

    setCellValue(staffId, day, value);
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.shift-input').forEach(updateCellUI);
});

document.getElementById('shiftForm')?.addEventListener('submit', function () {
    shiftHasUnsavedChanges = false;
});

window.addEventListener('beforeunload', function (event) {
    if (!shiftHasUnsavedChanges) return;
    event.preventDefault();
    event.returnValue = '';
});
</script>

@endsection
