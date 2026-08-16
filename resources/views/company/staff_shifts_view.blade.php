@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $days = \Carbon\Carbon::parse($month)->daysInMonth;

    use Yasumi\Yasumi;
    $holidays = Yasumi::create('Japan', \Carbon\Carbon::parse($month)->year);

    $prevMonth = \Carbon\Carbon::parse($month . '-01')->subMonth()->format('Y-m');
    $nextMonth = \Carbon\Carbon::parse($month . '-01')->addMonth()->format('Y-m');

    $patternMap = collect($patterns)->keyBy('id');
    $weekdayLabels = ['日', '月', '火', '水', '木', '金', '土'];
    $isCurrentMonth = $month === now()->format('Y-m');
    $initialMobileDate = $month . '-' . str_pad($isCurrentMonth ? now()->day : 1, 2, '0', STR_PAD_LEFT);
@endphp

<style>
    .mobile-shift-date-button[aria-selected="true"] {
        color: #fff;
        background: {{ $theme }};
        border-color: {{ $theme }};
        box-shadow: 0 10px 24px {{ $theme }}38;
    }

    .mobile-shift-date-button[aria-selected="true"] .mobile-shift-date-weekday {
        color: rgba(255, 255, 255, .8);
    }

    @media (max-width: 639px) {
        .staff-shift-view-page {
            padding: .75rem .25rem 1.5rem !important;
        }

        .mobile-shift-toolbar {
            position: sticky;
            top: calc(var(--company-topbar-height, 6rem) + .5rem);
            z-index: 35;
        }
    }
</style>

<div class="staff-shift-view-page max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <div class="hidden sm:block relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Shift View</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">スタッフ別シフト表</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        月別で勤務表を確認し、PDFでダウンロードできます。
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ダッシュボード
                    </a>

                    <a href="{{ route('company.staff-shifts.pdf', ['month' => $month, 'top_staff_id' => $topStaffId]) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-sm font-bold text-white bg-white/15 hover:bg-white/20 backdrop-blur-sm transition">
                        PDFダウンロード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden sm:block bg-white border border-gray-200 rounded-3xl shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('company.staff-shifts.view') }}"
              class="grid grid-cols-1 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">表示する月</label>
                <input type="month" name="month" value="{{ $month }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-3">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">先頭に表示するスタッフ</label>
                <select name="top_staff_id" class="w-full border border-gray-300 rounded-2xl px-4 py-3">
                    @foreach($staffs as $s)
                        <option value="{{ $s->id }}" {{ (int)$topStaffId === (int)$s->id ? 'selected' : '' }}>
                            {{ $s->roleLabel() }} / {{ $s->staff_code ?: '-' }} / {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('company.staff-shifts.view', ['month' => $prevMonth, 'top_staff_id' => $topStaffId]) }}"
                   class="px-4 py-3 rounded-2xl border border-gray-300 bg-white text-sm font-semibold">
                    前月
                </a>
                <a href="{{ route('company.staff-shifts.view', ['month' => $nextMonth, 'top_staff_id' => $topStaffId]) }}"
                   class="px-4 py-3 rounded-2xl border border-gray-300 bg-white text-sm font-semibold">
                    翌月
                </a>
            </div>

            <div>
                <button class="w-full px-5 py-3 text-white rounded-2xl font-semibold hover:opacity-90 transition"
                        style="background: {{ $theme }}">
                    表示
                </button>
            </div>
        </form>
    </div>

    <div class="hidden sm:block bg-white border border-gray-200 rounded-3xl shadow-sm p-5 mb-6">
        <div class="flex flex-col gap-3">
            <div class="text-sm font-bold text-gray-900">シフトカテゴリ</div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach($patterns as $pattern)
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold text-white shadow-sm"
                        style="background: {{ $pattern->color ?: '#64748b' }}">
                        {{ $pattern->name }}
                    </span>
                @endforeach

                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    休業
                </span>

                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    休み
                </span>

                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-600 border border-red-200">
                    休暇
                </span>
            </div>
        </div>
    </div>

    <div class="sm:hidden space-y-3">
        <div class="mobile-shift-toolbar rounded-[1.5rem] border border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur">
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('company.staff-shifts.view', ['month' => $prevMonth, 'top_staff_id' => $topStaffId]) }}"
                   class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-xl font-black text-gray-600"
                   aria-label="前月を表示">‹</a>
                <div class="min-w-0 text-center">
                    <div class="text-[11px] font-bold text-gray-400">スタッフ別 シフト表</div>
                    <div class="truncate text-lg font-black" style="color: {{ $theme }};">
                        {{ \Carbon\Carbon::parse($month . '-01')->format('Y年n月') }}
                    </div>
                </div>
                <a href="{{ route('company.staff-shifts.view', ['month' => $nextMonth, 'top_staff_id' => $topStaffId]) }}"
                   class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-xl font-black text-gray-600"
                   aria-label="翌月を表示">›</a>
            </div>

            <div class="mt-3 flex items-center gap-2">
                <button type="button" id="mobileShiftPrevDay"
                        class="h-10 shrink-0 rounded-xl border border-gray-200 px-3 text-xs font-black text-gray-600 disabled:opacity-30">
                    前日
                </button>
                <div id="mobileShiftSelectedLabel" class="min-w-0 flex-1 text-center text-sm font-black text-gray-900"></div>
                <button type="button" id="mobileShiftNextDay"
                        class="h-10 shrink-0 rounded-xl border border-gray-200 px-3 text-xs font-black text-gray-600 disabled:opacity-30">
                    翌日
                </button>
            </div>

            <div id="mobileShiftDateStrip" class="mt-3 flex gap-1.5 overflow-x-auto pb-1 snap-x">
                @for($d = 1; $d <= $days; $d++)
                    @php
                        $mobileDateObj = \Carbon\Carbon::parse($month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT));
                        $mobileDate = $mobileDateObj->format('Y-m-d');
                        $mobileDayOfWeek = $mobileDateObj->dayOfWeek;
                        $mobileDateColor = $mobileDayOfWeek === 0 ? 'text-red-600' : ($mobileDayOfWeek === 6 ? 'text-blue-600' : 'text-gray-700');
                    @endphp
                    <button type="button"
                            data-mobile-shift-date-button="{{ $mobileDate }}"
                            data-date-label="{{ $mobileDateObj->format('n月j日') }}（{{ $weekdayLabels[$mobileDayOfWeek] }}）"
                            class="mobile-shift-date-button snap-center shrink-0 rounded-xl border border-gray-200 bg-white px-1 py-2 text-center {{ $mobileDateColor }}"
                            style="min-width: min(3.5rem, calc((100vw - 3rem) / 7));"
                            aria-selected="false">
                        <span class="block text-sm font-black">{{ $d }}</span>
                        <span class="mobile-shift-date-weekday block text-[10px] text-gray-400">{{ $weekdayLabels[$mobileDayOfWeek] }}</span>
                    </button>
                @endfor
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('company.staff-shifts.view', ['month' => now()->format('Y-m'), 'top_staff_id' => $topStaffId]) }}"
               class="inline-flex flex-1 items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-black text-gray-700">
                今日を表示
            </a>
            <a href="{{ route('company.staff-shifts.pdf', ['month' => $month, 'top_staff_id' => $topStaffId]) }}"
               class="inline-flex flex-1 items-center justify-center rounded-2xl px-4 py-3 text-sm font-black text-white"
               style="background: {{ $theme }};">
                PDF
            </a>
        </div>

        <details class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-black text-gray-800">表示条件を変更</summary>
            <form method="GET" action="{{ route('company.staff-shifts.view') }}" class="space-y-3 border-t border-gray-100 p-4">
                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-500">表示する月</label>
                    <input type="month" name="month" value="{{ $month }}" class="w-full rounded-xl border border-gray-300 px-3 py-3">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-500">先頭に表示するスタッフ</label>
                    <select name="top_staff_id" class="w-full rounded-xl border border-gray-300 px-3 py-3">
                        @foreach($staffs as $s)
                            <option value="{{ $s->id }}" {{ (int)$topStaffId === (int)$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="w-full rounded-xl px-4 py-3 font-black text-white" style="background: {{ $theme }};">表示する</button>
            </form>
        </details>

        <details class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-black text-gray-800">シフト区分の見方</summary>
            <div class="flex flex-wrap gap-2 border-t border-gray-100 p-4">
                @foreach($patterns as $pattern)
                    <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-bold text-white"
                          style="background: {{ $pattern->color ?: '#64748b' }};">{{ $pattern->name }}</span>
                @endforeach
                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600">休業</span>
                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">休み</span>
                <span class="inline-flex rounded-full bg-red-100 px-3 py-1.5 text-xs font-bold text-red-600">休暇</span>
            </div>
        </details>

        <div id="mobileShiftDayPanels">
            @for($d = 1; $d <= $days; $d++)
                @php
                    $mobileDate = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                    $mobileDateObj = \Carbon\Carbon::parse($mobileDate);
                    $mobileWeekday = $mobileDateObj->dayOfWeek;
                    $mobileBusiness = $businessDays[$mobileDateObj->format('Y-m-d H:i:s')] ?? null;
                    $mobileIsClosed = $mobileBusiness
                        && $mobileBusiness->is_open === false
                        && is_null($mobileBusiness->open_time)
                        && is_null($mobileBusiness->close_time);
                @endphp

                <section data-mobile-shift-day="{{ $mobileDate }}" class="hidden space-y-2">
                    @forelse($staffs as $staff)
                        @php
                            $mobileIsTop = (int)$staff->id === (int)$topStaffId;
                            $mobileIsMe = (int)$staff->id === (int)$loginStaffId;
                            $mobileShift = $shifts[$staff->id][$mobileDate][0] ?? null;
                            $mobileDefaultShift = $defaultShifts[$staff->id][$mobileWeekday][0] ?? null;
                            $mobilePatternId = null;

                            if ($mobileShift && (int)($mobileShift->is_work ?? 0) === 1 && !empty($mobileShift->shift_pattern_id)) {
                                $mobilePatternId = (int)$mobileShift->shift_pattern_id;
                            } elseif (!$mobileShift && $mobileDefaultShift && (int)($mobileDefaultShift->is_work ?? 0) === 1 && !empty($mobileDefaultShift->shift_pattern_id)) {
                                $mobilePatternId = (int)$mobileDefaultShift->shift_pattern_id;
                            }

                            $mobilePattern = $mobilePatternId ? ($patternMap[$mobilePatternId] ?? null) : null;
                            $mobileStaffVacations = $vacations[$staff->id] ?? collect();
                            $mobileIsVacation = $mobileStaffVacations->first(function($vacation) use ($mobileDate) {
                                return $mobileDate >= \Carbon\Carbon::parse($vacation->start_at)->toDateString()
                                    && $mobileDate <= \Carbon\Carbon::parse($vacation->end_at)->toDateString();
                            });
                        @endphp

                        <div class="flex items-center justify-between gap-3 rounded-2xl border bg-white p-4 shadow-sm {{ $mobileIsTop ? 'border-amber-300 bg-amber-50' : 'border-gray-200' }}">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <div class="truncate font-black text-gray-900">{{ $staff->name }}</div>
                                    @if($mobileIsTop)<span class="shrink-0 text-[10px] font-black text-amber-700">先頭</span>@endif
                                    @if($mobileIsMe)<span class="shrink-0 text-[10px] font-black text-rose-600">あなた</span>@endif
                                </div>
                                <div class="mt-1 truncate text-xs text-gray-500">{{ $staff->roleLabel() }} / {{ $staff->staff_code ?: '-' }}</div>
                            </div>

                            @if($mobileIsClosed)
                                <span class="shrink-0 rounded-full bg-gray-100 px-3 py-2 text-xs font-black text-gray-500">休業</span>
                            @elseif($mobileIsVacation)
                                <span class="shrink-0 rounded-full bg-red-100 px-3 py-2 text-xs font-black text-red-600">休暇</span>
                            @elseif($mobilePattern)
                                <span class="shrink-0 rounded-full px-3 py-2 text-xs font-black text-white"
                                      style="background: {{ $mobilePattern->color ?: '#64748b' }};">{{ $mobilePattern->name }}</span>
                            @else
                                <span class="shrink-0 rounded-full bg-gray-100 px-3 py-2 text-xs font-black text-gray-700">休み</span>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">スタッフが登録されていません</div>
                    @endforelse
                </section>
            @endfor
        </div>
    </div>

    <div class="hidden sm:block bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b bg-amber-50">
            <h2 class="text-lg font-bold text-gray-900">月別 シフト一覧</h2>
            <p class="text-sm text-gray-600 mt-1">
                並び順：先頭表示スタッフ → マスター → エリアリーダー → リーダー → スタッフ → ユーザー番号順
            </p>
        </div>

        <div class="overflow-x-auto max-h-[72vh]">
            <table class="min-w-max text-sm w-full border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr style="background: {{ $theme }}; color: white;">
                        <th class="p-3 sticky left-0 bg-white text-black z-20 min-w-[240px] border-r">スタッフ</th>

                        @for($d = 1; $d <= $days; $d++)
                            @php
                                $dateObj = \Carbon\Carbon::parse("$month-" . str_pad($d, 2, '0', STR_PAD_LEFT));
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
                            <th class="p-2 text-center min-w-[110px] {{ $color }}">
                                <div class="font-bold">{{ $d }}</div>
                                <div class="text-xs">{{ ['日','月','火','水','木','金','土'][$dayOfWeek] }}</div>
                            </th>
                        @endfor
                    </tr>
                </thead>

                <tbody>
                    @foreach($staffs as $staff)
                        @php
                            $isTop = (int)$staff->id === (int)$topStaffId;
                            $isMe = (int)$staff->id === (int)$loginStaffId;
                        @endphp

                        <tr class="border-b {{ $isTop ? 'bg-amber-50' : 'hover:bg-gray-50' }}">
                            <td class="p-3 sticky left-0 z-10 min-w-[240px] border-r align-top {{ $isTop ? 'bg-amber-100' : 'bg-white' }}">
                                <div class="space-y-2">
                                    <div class="font-semibold text-gray-900">
                                        {{ $staff->name }}
                                        @if($isTop)
                                            <span class="ml-1 text-xs text-amber-700">（先頭表示）</span>
                                        @endif
                                        @if($isMe)
                                            <span class="ml-1 text-xs text-rose-600">（あなた）</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $staff->roleLabel() }} / ユーザー番号: {{ $staff->staff_code ?: '-' }}
                                    </div>
                                </div>
                            </td>

                            @for($d = 1; $d <= $days; $d++)
                                @php
                                    $date = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                    $dateObj = \Carbon\Carbon::parse($date);
                                    $weekday = $dateObj->dayOfWeek;

                                    $shift = $shifts[$staff->id][$date][0] ?? null;
                                    $defaultShift = $defaultShifts[$staff->id][$weekday][0] ?? null;

                                    $effectivePatternId = null;

                                    if ($shift && (int)($shift->is_work ?? 0) === 1 && !empty($shift->shift_pattern_id)) {
                                        $effectivePatternId = (int) $shift->shift_pattern_id;
                                    } elseif (!$shift && $defaultShift && (int)($defaultShift->is_work ?? 0) === 1 && !empty($defaultShift->shift_pattern_id)) {
                                        $effectivePatternId = (int) $defaultShift->shift_pattern_id;
                                    }

                                    $effectivePattern = $effectivePatternId ? ($patternMap[$effectivePatternId] ?? null) : null;
                                    $currentShiftName = $effectivePattern->name ?? '休み';
                                    $currentShiftColor = $effectivePattern->color ?? '#e5e7eb';

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

                                <td class="p-2 min-w-[110px] text-center align-middle {{ $isTop ? 'bg-amber-50' : '' }}">
                                    @if($isClosed)
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold bg-gray-100 text-gray-500">
                                            休業
                                        </span>
                                    @elseif($isVacation)
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold bg-red-100 text-red-600">
                                            休暇
                                        </span>
                                    @elseif($effectivePattern)
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold text-white"
                                              style="background: {{ $currentShiftColor ?: '#64748b' }}">
                                            {{ $currentShiftName }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold bg-gray-100 text-gray-700">
                                            休み
                                        </span>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateButtons = Array.from(document.querySelectorAll('[data-mobile-shift-date-button]'));
    const dayPanels = Array.from(document.querySelectorAll('[data-mobile-shift-day]'));
    const selectedLabel = document.getElementById('mobileShiftSelectedLabel');
    const previousButton = document.getElementById('mobileShiftPrevDay');
    const nextButton = document.getElementById('mobileShiftNextDay');
    let selectedIndex = 0;

    const showMobileShiftDate = (date, centerButton = true) => {
        const targetIndex = dateButtons.findIndex(button => button.dataset.mobileShiftDateButton === date);
        if (targetIndex < 0) return;

        selectedIndex = targetIndex;
        dateButtons.forEach((button, index) => button.setAttribute('aria-selected', index === selectedIndex ? 'true' : 'false'));
        dayPanels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.mobileShiftDay !== date));

        const selectedButton = dateButtons[selectedIndex];
        if (selectedLabel) selectedLabel.textContent = selectedButton.dataset.dateLabel || '';
        if (previousButton) previousButton.disabled = selectedIndex === 0;
        if (nextButton) nextButton.disabled = selectedIndex === dateButtons.length - 1;

        if (centerButton) {
            selectedButton.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    };

    dateButtons.forEach(button => {
        button.addEventListener('click', () => showMobileShiftDate(button.dataset.mobileShiftDateButton));
    });
    previousButton?.addEventListener('click', () => {
        if (selectedIndex > 0) showMobileShiftDate(dateButtons[selectedIndex - 1].dataset.mobileShiftDateButton);
    });
    nextButton?.addEventListener('click', () => {
        if (selectedIndex < dateButtons.length - 1) showMobileShiftDate(dateButtons[selectedIndex + 1].dataset.mobileShiftDateButton);
    });

    showMobileShiftDate(@json($initialMobileDate), false);
    requestAnimationFrame(() => dateButtons[selectedIndex]?.scrollIntoView({ block: 'nearest', inline: 'center' }));
});
</script>
@endsection
