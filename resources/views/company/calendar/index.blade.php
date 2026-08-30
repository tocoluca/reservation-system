@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

    $weekdayLabels = ['日','月','火','水','木','金','土'];

    $monthOpenCount = 0;
    $monthClosedCount = 0;
    $monthTimeChangedCount = 0;

    for ($summaryDay = 1; $summaryDay <= $daysInMonth; $summaryDay++) {
        $summaryDateObj = \Carbon\Carbon::create($year, $month, $summaryDay);
        $summaryDate = $summaryDateObj->format('Y-m-d');
        $summaryCalendar = $calendars[$summaryDate] ?? null;
        $summaryIsHoliday = $company->holiday_is_closed && in_array($summaryDate, $holidayDates ?? []);
        $summaryIsOpen = $summaryCalendar ? (bool) $summaryCalendar->is_open : !$summaryIsHoliday;

        if ($summaryIsOpen) {
            $monthOpenCount++;
        } else {
            $monthClosedCount++;
        }

        if ($summaryCalendar && ($summaryCalendar->open_time || $summaryCalendar->close_time)) {
            $monthTimeChangedCount++;
        }
    }
@endphp

<style>
    .calendar-day-cell {
        min-width: 0;
    }

    .calendar-day-cell.is-selected-calendar-day {
        outline: 3px solid {{ $theme }};
        outline-offset: 2px;
    }

    .business-calendar-selected-panel {
        position: sticky;
        top: calc(var(--company-topbar-height, 6rem) + .75rem);
        z-index: 39;
    }

    @media (max-width: 639px) {
        .business-calendar-page {
            padding-left: .25rem !important;
            padding-right: .25rem !important;
            padding-top: .75rem !important;
        }

        .business-calendar-page.has-selected-day-panel {
            padding-bottom: 16rem !important;
        }

        .business-calendar-hero,
        .business-calendar-month-card,
        .business-calendar-guide,
        .business-calendar-secondary-nav,
        .business-calendar-legend {
            display: none !important;
        }

        .business-calendar-month-nav {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: .5rem !important;
        }

        .business-calendar-summary {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: .4rem !important;
        }

        .business-calendar-summary > div {
            padding: .65rem .35rem !important;
            text-align: center;
        }

        .business-calendar-summary > div > div:first-child {
            font-size: 10px !important;
        }

        .business-calendar-summary > div > div:last-child {
            font-size: 1.1rem !important;
        }

        .business-calendar-mobile-toolbar {
            position: sticky;
            top: 5.25rem;
            z-index: 35;
        }

        .business-calendar-selected-panel {
            position: fixed;
            top: auto;
            left: .5rem;
            right: .5rem;
            bottom: 5.5rem;
            z-index: 70;
            max-height: calc(100vh - 8rem);
            overflow-y: auto;
            margin: 0 !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .25) !important;
        }

        .calendar-day-cell {
            padding: .25rem !important;
            border-radius: .85rem;
        }

        .calendar-day-cell [data-status-label] {
            padding: .2rem .05rem;
            font-size: 9px;
            line-height: 1.15;
        }

        .calendar-reservation-count {
            padding: .15rem .05rem !important;
            font-size: 8px !important;
            line-height: 1.1;
        }

        .calendar-cell-secondary,
        .calendar-cell-action {
            display: none !important;
        }
    }
</style>

<div class="business-calendar-page max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="business-calendar-hero relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        BUSINESS CALENDAR
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-white tracking-tight">
                        営業日カレンダー
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85 leading-relaxed">
                        営業日・休業日・営業時間変更を、月単位で直感的に管理できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.calendar.year', ['year' => $year]) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-semibold text-white">
                        年間表示へ
                    </a>

                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-semibold text-white">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 月ナビ --}}
    <div class="business-calendar-month-card bg-white rounded-[1.75rem] shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 sm:px-6 py-5 bg-gradient-to-r from-white to-gray-50 border-b">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold tracking-wide text-gray-500">表示中の月</div>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-bold" style="color: {{ $theme }}">
                        {{ $year }}年 {{ $month }}月
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        日付を選び、詳細パネルから営業日 / 休業日や営業時間を変更できます。
                    </p>
                </div>

                <div class="business-calendar-month-nav grid grid-cols-1 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                    <a href="{{ route('company.calendar.index',['year'=>$prev->year,'month'=>$prev->month]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        ◀ 前月
                    </a>

                    <a href="{{ route('company.calendar.index',['year'=>now()->year,'month'=>now()->month]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl font-semibold text-white shadow-sm transition hover:opacity-90"
                       style="background: {{ $theme }};">
                        今月
                    </a>

                    <a href="{{ route('company.calendar.year',['year'=>$year]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        年間へ
                    </a>

                    <a href="{{ route('company.calendar.index',['year'=>$next->year,'month'=>$next->month]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        次月 ▶
                    </a>
                </div>
            </div>
        </div>

        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-white">
            <div class="business-calendar-summary grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-2xl border border-green-100 bg-green-50 px-4 py-4">
                    <div class="text-xs font-bold text-green-700">今月の営業日</div>
                    <div class="mt-1 text-2xl font-black text-green-900">{{ number_format($monthOpenCount) }}日</div>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50 px-4 py-4">
                    <div class="text-xs font-bold text-red-700">今月の休業日</div>
                    <div class="mt-1 text-2xl font-black text-red-900">{{ number_format($monthClosedCount) }}日</div>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4">
                    <div class="text-xs font-bold text-amber-700">営業時間変更</div>
                    <div class="mt-1 text-2xl font-black text-amber-900">{{ number_format($monthTimeChangedCount) }}日</div>
                </div>
            </div>
        </div>

        {{-- 操作ガイド --}}
        <div class="business-calendar-guide px-5 sm:px-6 py-4">
            <div class="grid grid-cols-1 xl:grid-cols-[1.2fr_0.8fr] gap-4">
                <div class="rounded-2xl bg-amber-50/60 border border-amber-100 px-4 py-4">
                    <div class="text-sm font-bold text-gray-900">この画面でできること</div>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-gray-600">
                        <div class="rounded-xl bg-white/80 border border-white px-3 py-3">
                            <div class="font-semibold text-gray-800">日付を押す</div>
                            <div class="mt-1 text-xs leading-relaxed">選択日の状態と予約件数を確認</div>
                        </div>
                        <div class="rounded-xl bg-white/80 border border-white px-3 py-3">
                            <div class="font-semibold text-gray-800">時間変更</div>
                            <div class="mt-1 text-xs leading-relaxed">その日の営業時間だけ個別変更</div>
                        </div>
                        <div class="rounded-xl bg-white/80 border border-white px-3 py-3">
                            <div class="font-semibold text-gray-800">年間設定</div>
                            <div class="mt-1 text-xs leading-relaxed">曜日ごとに1年分まとめて設定</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                    <div class="text-sm font-bold text-gray-900">表示の見方</div>
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-green-200 border border-green-300"></div>
                            <span>営業日</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-red-200 border border-red-300"></div>
                            <span>休業日</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-yellow-200 border border-yellow-300"></div>
                            <span>営業時間変更</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 年間一括設定 --}}
            <div class="mt-4">
                <button type="button"
                        onclick="toggleBulkPanel()"
                        id="bulkToggleBtn"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border font-semibold text-sm hover:bg-gray-50 transition"
                        style="border-color: {{ $theme }}22; color: {{ $theme }};">
                    年間設定を開く ▼
                </button>

                <div id="bulkSettingPanel" class="hidden mt-4">
                    <div class="rounded-[1.5rem] border bg-gray-50 p-4 sm:p-5">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-lg font-bold" style="color: {{ $theme }}">
                                    年間一括休業日 / 営業日設定
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    毎週同じ曜日を、対象年の1年分まとめて設定できます。個別の日付変更は下のカレンダーから行ってください。
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <label for="bulkYearInput" class="text-sm font-semibold text-gray-600">対象年</label>
                                <input type="number"
                                       id="bulkYearInput"
                                       value="{{ $year }}"
                                       min="2000"
                                       max="2100"
                                       class="border rounded-xl px-3 py-2 w-28 text-sm bg-white">
                                <span class="text-sm text-gray-500">年</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl border border-red-100 p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <h4 class="font-bold text-gray-800">年間で休業日にする</h4>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">
                                    例：毎週月曜日を1年分まとめて休業日に設定
                                </p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-2">
                                    @foreach($weekdayLabels as $i => $w)
                                        <button type="button"
                                                onclick="bulkYearHoliday({{ $i }}, this)"
                                                class="py-2 px-2 rounded-xl border text-sm font-semibold transition hover:bg-red-50 active:scale-95"
                                                style="border-color:#fca5a5; color:#dc2626;">
                                            <span data-busy-text>{{ $w }}曜を休業</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl border border-green-100 p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    <h4 class="font-bold text-gray-800">年間で営業日に戻す</h4>
                                </div>
                                <p class="text-xs text-gray-500 mb-3">
                                    例：毎週火曜日を1年分まとめて営業日に戻す
                                </p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-2">
                                    @foreach($weekdayLabels as $i => $w)
                                        <button type="button"
                                                onclick="bulkYearOpen({{ $i }}, this)"
                                                class="py-2 px-2 rounded-xl border text-sm font-semibold transition hover:bg-green-50 active:scale-95"
                                                style="border-color:#86efac; color:#15803d;">
                                            <span data-busy-text>{{ $w }}曜を営業</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div>

    <div class="business-calendar-secondary-nav mb-6 rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Calendar Navigation</p>
                <h2 class="mt-1 text-lg font-black text-gray-900">営業日管理の表示切替</h2>
                <p class="mt-1 text-sm text-gray-500">月ごとの編集と年間確認を切り替えできます。</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:min-w-[520px]">
                <a href="{{ route('company.calendar.index', ['year' => now()->year, 'month' => now()->month]) }}"
                   class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-700 transition hover:bg-gray-100">
                    <div class="text-sm font-black">今月</div>
                    <div class="mt-1 text-xs text-gray-500">今日の月へ戻る</div>
                </a>
                <div class="rounded-2xl border px-4 py-3 text-white shadow-sm"
                     style="background: {{ $theme }}; border-color: {{ $theme }};">
                    <div class="text-sm font-black">月間カレンダー</div>
                    <div class="mt-1 text-xs text-white/80">{{ $year }}年 {{ $month }}月を編集中</div>
                </div>
                <a href="{{ route('company.calendar.year', ['year' => $year]) }}"
                   class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-700 transition hover:bg-gray-100">
                    <div class="text-sm font-black">年間カレンダー</div>
                    <div class="mt-1 text-xs text-gray-500">{{ $year }}年を一覧確認</div>
                </a>
            </div>
        </div>
    </div>

    <div class="business-calendar-mobile-toolbar sm:hidden mb-3 rounded-2xl border border-gray-100 bg-white/95 p-2 shadow-lg backdrop-blur">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ route('company.calendar.index',['year'=>$prev->year,'month'=>$prev->month]) }}"
               class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-lg font-bold text-gray-600">
                ‹
            </a>
            <div class="min-w-0 text-center">
                <div class="text-xs font-bold text-gray-400">営業日カレンダー</div>
                <div class="truncate text-base font-black" style="color: {{ $theme }};">{{ $year }}年 {{ $month }}月</div>
            </div>
            <a href="{{ route('company.calendar.index',['year'=>$next->year,'month'=>$next->month]) }}"
               class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-lg font-bold text-gray-600">
                ›
            </a>
        </div>
    </div>

    <div id="selectedDayPanel" class="business-calendar-selected-panel hidden mb-4 rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm" aria-live="polite">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-xs font-bold tracking-wide text-slate-400">選択中の日付</div>
                    <button type="button"
                            onclick="closeSelectedDayPanel()"
                            class="sm:hidden inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-lg font-bold text-slate-500"
                            aria-label="選択日の操作を閉じる">
                        ×
                    </button>
                </div>
                <div id="selectedDayLabel" class="mt-1 text-lg font-black text-slate-900">-</div>
                <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold">
                    <span id="selectedDayStatus" class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">-</span>
                    <span id="selectedDayReservations" class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">予約 -件</span>
                    <span id="selectedDayHoliday" class="hidden rounded-full bg-red-50 px-3 py-1 text-red-700"></span>
                </div>
                <div id="selectedDayTime" class="mt-2 text-sm text-slate-500">個別の営業時間変更なし</div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 lg:min-w-[360px]">
                <button type="button"
                        id="selectedDayToggleButton"
                        onclick="toggleSelectedDayStatus()"
                        class="inline-flex items-center justify-center rounded-2xl border px-4 py-3 text-sm font-bold transition hover:bg-gray-50"
                        style="border-color: {{ $theme }}44; color: {{ $theme }};">
                    営業 / 休業を切り替える
                </button>
                <button type="button"
                        onclick="openSelectedDayModal()"
                        class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90"
                        style="background: {{ $theme }};">
                    営業時間を変更
                </button>
            </div>
        </div>
    </div>

    <div class="business-calendar-legend mb-4 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-bold text-gray-900">カレンダー凡例</div>
                <div class="business-calendar-legend-description text-xs text-gray-500 mt-1">日付を選択し、詳細パネルから営業/休業や営業時間を変更できます。</div>
            </div>
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="inline-flex items-center gap-2 rounded-2xl bg-green-50 px-3 py-2 font-bold text-green-700"><span class="w-3 h-3 rounded bg-green-300"></span>営業日</span>
                <span class="inline-flex items-center gap-2 rounded-2xl bg-red-50 px-3 py-2 font-bold text-red-700"><span class="w-3 h-3 rounded bg-red-300"></span>休業日</span>
                <span class="inline-flex items-center gap-2 rounded-2xl bg-yellow-50 px-3 py-2 font-bold text-yellow-700"><span class="w-3 h-3 rounded bg-yellow-300"></span>時間変更</span>
            </div>
        </div>
    </div>

    {{-- 曜日 --}}
    <div class="grid grid-cols-7 gap-2 mb-2">
        <div class="text-center text-sm font-bold text-red-500 py-2">日</div>
        <div class="text-center text-sm font-bold text-gray-700 py-2">月</div>
        <div class="text-center text-sm font-bold text-gray-700 py-2">火</div>
        <div class="text-center text-sm font-bold text-gray-700 py-2">水</div>
        <div class="text-center text-sm font-bold text-gray-700 py-2">木</div>
        <div class="text-center text-sm font-bold text-gray-700 py-2">金</div>
        <div class="text-center text-sm font-bold text-blue-500 py-2">土</div>
    </div>

    {{-- カレンダー --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-2 sm:p-3">
        <div class="grid grid-cols-7 gap-2">
            @for($i=0;$i<$startDayOfWeek;$i++)
                <div class="aspect-square rounded-2xl bg-transparent"></div>
            @endfor

            @for($day=1;$day<=$daysInMonth;$day++)
                @php
                    $dateObj = \Carbon\Carbon::create($year, $month, $day);
                    $date = $dateObj->format('Y-m-d');
                    $calendar = $calendars[$date] ?? null;
                    $isToday = $dateObj->isToday();
                    $weekday = $dateObj->dayOfWeek;

                    $isHoliday = false;
                    if ($company->holiday_is_closed) {
                        $isHoliday = in_array($date, $holidayDates ?? []);
                    }

                    if ($calendar) {
                        $isOpen = (bool) $calendar->is_open;
                    } else {
                        $isOpen = !$isHoliday;
                    }

                    $isTimeChanged = false;
                    if ($calendar && ($calendar->open_time || $calendar->close_time)) {
                        $isTimeChanged = true;
                    }

                    if (!$isOpen) {
                        $bgClass = $isHoliday ? 'bg-red-300' : 'bg-red-100';
                    } elseif ($isTimeChanged) {
                        $bgClass = 'bg-yellow-100';
                    } else {
                        $bgClass = 'bg-green-100';
                    }

                    $textClass = 'text-gray-800';
                    if ($weekday == 0) {
                        $textClass = 'text-red-600';
                    } elseif ($weekday == 6) {
                        $textClass = 'text-blue-600';
                    }

                    $reservationCount = $reservationCounts[$date] ?? 0;
                @endphp

                <div onclick="selectCalendarDay('{{ $date }}', this)"
                     data-date-cell="1"
                     data-date="{{ $date }}"
                     data-is-open="{{ $isOpen ? '1' : '0' }}"
                     data-reservation-count="{{ $reservationCount }}"
                     data-date-label="{{ $dateObj->format('Y年n月j日') }}"
                     data-holiday-name="{{ e($holidayNames[$date] ?? '') }}"
                     data-time-range="{{ e($calendar && $calendar->open_time ? substr($calendar->open_time, 0, 5).' - '.substr($calendar->close_time, 0, 5) : '') }}"
                     class="calendar-day-cell aspect-square rounded-2xl p-2 sm:p-3 flex flex-col justify-between border border-white shadow-sm cursor-pointer transition hover:shadow-md active:scale-[0.98] {{ $bgClass }} {{ $textClass }} {{ $isToday ? 'ring-4' : '' }}"
                     style="{{ $isToday ? 'ring-color: '.$theme.';' : '' }}">

                    <div class="flex items-start justify-between gap-2">
                        <div class="text-sm sm:text-base font-bold">{{ $day }}</div>

                        @if($isToday)
                            <span class="calendar-cell-secondary hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/80 text-gray-700">
                                今日
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1.5 mt-2">
                        <div data-status-label class="rounded-lg bg-white/70 px-2 py-1 text-[10px] sm:text-[11px] text-center font-black {{ $isOpen ? 'text-green-700' : 'text-red-700' }}">
                            <span class="sm:hidden">{{ $isOpen ? '営' : '休' }}</span>
                            <span class="hidden sm:inline">{{ $isOpen ? '営業日' : '休業日' }}</span>
                        </div>

                        @if(isset($reservationCounts[$date]))
                            <div class="calendar-reservation-count rounded-lg bg-white/70 px-2 py-1 text-[10px] sm:text-[11px] text-center font-bold text-gray-700">
                                <span class="sm:hidden">{{ $reservationCounts[$date] }}件</span>
                                <span class="hidden sm:inline">予約 {{ $reservationCounts[$date] }}件</span>
                            </div>
                        @endif

                        @if(isset($holidayNames[$date]))
                            <div class="calendar-cell-secondary text-[9px] sm:text-[10px] leading-tight text-center text-red-600 font-semibold">
                                {{ $holidayNames[$date] }}
                            </div>
                        @endif

                        @if($calendar && $calendar->open_time)
                            <div data-time-label class="calendar-cell-secondary text-[9px] sm:text-[10px] text-center leading-tight font-semibold text-gray-700">
                                {{ substr($calendar->open_time,0,5) }} - {{ substr($calendar->close_time,0,5) }}
                            </div>
                        @endif
                    </div>

                    <button type="button"
                            class="calendar-cell-action mt-2 text-[10px] sm:text-[11px] underline text-center font-semibold"
                            onclick="event.stopPropagation(); openModal('{{ $date }}')"
                            style="color: {{ $theme }};">
                        時間変更
                    </button>
                </div>
            @endfor
        </div>
    </div>

</div>

<script>
let selectedCalendarDate = null;
let selectedCalendarCell = null;

function selectCalendarDay(date, el) {
    const dayCell = el || document.querySelector(`[data-date-cell="1"][data-date="${date}"]`);
    const panel = document.getElementById('selectedDayPanel');
    if (!dayCell || !panel) return;

    selectedCalendarDate = date;
    selectedCalendarCell = dayCell;
    document.querySelectorAll('[data-date-cell="1"]').forEach(cell => {
        cell.classList.toggle('is-selected-calendar-day', cell === dayCell);
    });
    panel.classList.remove('hidden');
    document.querySelector('.business-calendar-page')?.classList.add('has-selected-day-panel');

    const isOpen = dayCell.dataset.isOpen === '1';
    const reservationCount = Number(dayCell.dataset.reservationCount || 0);
    const holidayName = dayCell.dataset.holidayName || '';
    const timeRange = dayCell.dataset.timeRange || '';

    document.getElementById('selectedDayLabel').textContent = dayCell.dataset.dateLabel || date;
    const status = document.getElementById('selectedDayStatus');
    status.textContent = isOpen ? '営業日' : '休業日';
    status.className = `rounded-full px-3 py-1 ${isOpen ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`;
    document.getElementById('selectedDayReservations').textContent = `予約 ${reservationCount}件`;

    const holiday = document.getElementById('selectedDayHoliday');
    holiday.textContent = holidayName;
    holiday.classList.toggle('hidden', !holidayName);
    document.getElementById('selectedDayTime').textContent = timeRange
        ? `個別営業時間：${timeRange}`
        : '個別の営業時間変更なし';

    const toggleButton = document.getElementById('selectedDayToggleButton');
    if (toggleButton) {
        toggleButton.textContent = isOpen ? '休業日に変更' : '営業日に変更';
    }
}

function closeSelectedDayPanel() {
    document.getElementById('selectedDayPanel')?.classList.add('hidden');
    document.querySelector('.business-calendar-page')?.classList.remove('has-selected-day-panel');
}

function toggleSelectedDayStatus() {
    if (selectedCalendarDate && selectedCalendarCell) {
        toggleDay(selectedCalendarDate, selectedCalendarCell);
    }
}

function openSelectedDayModal() {
    if (selectedCalendarDate) {
        openModal(selectedCalendarDate);
    }
}

function toggleBulkPanel() {
    const panel = document.getElementById('bulkSettingPanel');
    const btn = document.getElementById('bulkToggleBtn');

    panel.classList.toggle('hidden');
    btn.innerHTML = panel.classList.contains('hidden')
        ? '年間設定を開く ▼'
        : '年間設定を閉じる ▲';
}

function toggleDay(date, el) {
    selectCalendarDay(date, el);
    const isOpen = el.dataset.isOpen === '1';
    const nextStatus = isOpen ? '休業日' : '営業日';
    const currentStatus = isOpen ? '営業日' : '休業日';
    const dateLabel = el.dataset.dateLabel || date;
    const reservationCount = Number(el.dataset.reservationCount || 0);

    let message = `${dateLabel}を「${currentStatus}」から「${nextStatus}」に変更しますか？`;
    if (isOpen && reservationCount > 0) {
        message += `\n\nこの日は予約が${reservationCount}件あります。休業日にすると予約変更連絡の対象になる場合があります。`;
    }

    if (!confirm(message)) {
        return;
    }

    el.style.pointerEvents = 'none';
    el.classList.add('opacity-70');

    fetch("{{ route('company.calendar.toggle') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ date: date })
    })
    .then(res => res.json())
    .then(data => {
        if (!data || typeof data.is_open === 'undefined') {
            throw new Error('営業日設定の変更に失敗しました');
        }
        el.classList.remove('bg-green-100','bg-red-100','bg-red-300','bg-yellow-100');
        el.dataset.isOpen = data.is_open ? '1' : '0';

        if (data.is_open) {
            el.classList.add('bg-green-100');
        } else {
            el.classList.add('bg-red-100');
        }
        
        const statusLabel = el.querySelector('[data-status-label]');
        if (statusLabel) {
            statusLabel.innerHTML = data.is_open
                ? '<span class="sm:hidden">営</span><span class="hidden sm:inline">営業日</span>'
                : '<span class="sm:hidden">休</span><span class="hidden sm:inline">休業日</span>';
            statusLabel.classList.toggle('text-green-700', data.is_open);
            statusLabel.classList.toggle('text-red-700', !data.is_open);
        }
        selectCalendarDay(date, el);
        if (window.showCompanyToast) {
            showCompanyToast(data.is_open ? '営業日に変更しました' : '休業日に変更しました');
        }
    })
    .catch(() => {
        if (window.showCompanyToast) {
            showCompanyToast('営業日設定の変更に失敗しました。', 'error');
        } else {
            alert('営業日設定の変更に失敗しました。時間をおいて再度お試しください。');
        }
    })
    .finally(() => {
        el.style.pointerEvents = '';
        el.classList.remove('opacity-70');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const today = @json(now()->format('Y-m-d'));
    const initialCell = document.querySelector(`[data-date-cell="1"][data-date="${today}"]`)
        || document.querySelector('[data-date-cell="1"]');

    if (initialCell && !window.matchMedia('(max-width: 639px)').matches) {
        selectCalendarDay(initialCell.dataset.date, initialCell);
    }
});

function openModal(date) {
    selectCalendarDay(date);
    document.getElementById('modalDate').value = date;
    const timeRange = selectedCalendarCell?.dataset.timeRange || '';
    const [openTime = '', closeTime = ''] = timeRange.split(' - ');
    document.getElementById('openTime').value = openTime;
    document.getElementById('closeTime').value = closeTime;

    const modal = document.getElementById('timeModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('timeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function updateSelectedCalendarTime(date, openTime, closeTime, isOpen = true) {
    const dayCell = document.querySelector(`[data-date-cell="1"][data-date="${date}"]`);
    if (!dayCell) return;

    const normalizedOpen = openTime ? String(openTime).slice(0, 5) : '';
    const normalizedClose = closeTime ? String(closeTime).slice(0, 5) : '';
    const timeRange = normalizedOpen && normalizedClose ? `${normalizedOpen} - ${normalizedClose}` : '';

    dayCell.dataset.timeRange = timeRange;
    dayCell.dataset.isOpen = isOpen ? '1' : '0';
    dayCell.classList.remove('bg-green-100', 'bg-red-100', 'bg-red-300', 'bg-yellow-100');
    dayCell.classList.add(timeRange ? 'bg-yellow-100' : (isOpen ? 'bg-green-100' : 'bg-red-100'));

    const statusLabel = dayCell.querySelector('[data-status-label]');
    if (statusLabel) {
        statusLabel.innerHTML = isOpen
            ? '<span class="sm:hidden">営</span><span class="hidden sm:inline">営業日</span>'
            : '<span class="sm:hidden">休</span><span class="hidden sm:inline">休業日</span>';
        statusLabel.classList.toggle('text-green-700', isOpen);
        statusLabel.classList.toggle('text-red-700', !isOpen);
    }

    let timeLabel = dayCell.querySelector('[data-time-label]');
    if (timeRange && !timeLabel) {
        timeLabel = document.createElement('div');
        timeLabel.dataset.timeLabel = '1';
        timeLabel.className = 'calendar-cell-secondary text-[9px] sm:text-[10px] text-center leading-tight font-semibold text-gray-700';
        dayCell.querySelector('.space-y-1.5')?.appendChild(timeLabel);
    }

    if (timeLabel) {
        if (timeRange) {
            timeLabel.textContent = timeRange;
        } else {
            timeLabel.remove();
        }
    }

    selectCalendarDay(date, dayCell);
}

function saveTime() {
    const date = document.getElementById('modalDate').value;
    const open_time = document.getElementById('openTime').value;
    const close_time = document.getElementById('closeTime').value;
    const button = document.getElementById('saveTimeButton');
    if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, true, '保存中…');

    fetch("{{ route('company.calendar.updateTime') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            date: date,
            open_time: open_time,
            close_time: close_time
        })
    }).then(res => {
        if (!res.ok) throw new Error('営業時間の保存に失敗しました');
        return res.json();
    }).then(data => {
        updateSelectedCalendarTime(date, data.open_time ?? open_time, data.close_time ?? close_time, Boolean(data.is_open ?? true));
        if (window.showCompanyToast) showCompanyToast('営業時間を保存しました');
        closeModal();
        if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, false);
    }).catch(() => {
        if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, false);
        if (window.showCompanyToast) showCompanyToast('営業時間の保存に失敗しました。', 'error');
    });
}

function deleteTime() {
    const date = document.getElementById('modalDate').value;

    if (!confirm("営業時間変更を削除しますか？")) {
        return;
    }

    const button = document.getElementById('deleteTimeButton');
    if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, true, '削除中…');

    fetch("{{ route('company.calendar.deleteTime') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ date: date })
    }).then(res => {
        if (!res.ok) throw new Error('営業時間変更の削除に失敗しました');
        return res.json();
    }).then(data => {
        updateSelectedCalendarTime(date, null, null, Boolean(data.is_open ?? true));
        if (window.showCompanyToast) showCompanyToast('営業時間の変更を削除しました');
        closeModal();
        if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, false);
    }).catch(() => {
        if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, false);
        if (window.showCompanyToast) showCompanyToast('営業時間変更の削除に失敗しました。', 'error');
    });
}

function bulkYearHoliday(weekday, button) {
    const year = document.getElementById('bulkYearInput').value;

    if (!year) {
        alert("年を入力してください");
        return;
    }

    if (!confirm(year + "年の選択曜日をすべて休業日にしますか？")) {
        return;
    }

    if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, true, '処理中…');

    fetch("{{ route('company.calendar.bulkYearWeekday') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            year: year,
            weekday: weekday
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('年間休業日設定に失敗しました');
        return res.json();
    })
    .then(() => {
        if (window.showCompanyToast) showCompanyToast('年間休業日設定が完了しました');
        setTimeout(() => location.reload(), 650);
    }).catch(() => {
        if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, false);
        if (window.showCompanyToast) showCompanyToast('年間休業日設定に失敗しました。', 'error');
    });
}

function bulkYearOpen(weekday, button) {
    const year = document.getElementById('bulkYearInput').value;

    if (!year) {
        alert("年を入力してください");
        return;
    }

    if (!confirm(year + "年の選択曜日をすべて営業日に戻しますか？")) {
        return;
    }

    if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, true, '処理中…');

    fetch("{{ route('company.calendar.bulkYearOpenWeekday') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            year: year,
            weekday: weekday
        })
    })
    .then(res => {
        if (!res.ok) throw new Error('年間営業日設定に失敗しました');
        return res.json();
    })
    .then(() => {
        if (window.showCompanyToast) showCompanyToast('年間営業日設定が完了しました');
        setTimeout(() => location.reload(), 650);
    }).catch(() => {
        if (window.setCompanyButtonBusy) setCompanyButtonBusy(button, false);
        if (window.showCompanyToast) showCompanyToast('年間営業日設定に失敗しました。', 'error');
    });
}
</script>

<div id="timeModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 px-4">

    <div class="w-full max-w-md bg-white rounded-[1.75rem] shadow-xl overflow-hidden">
        <div class="px-5 py-4 border-b bg-gradient-to-r from-white to-gray-50">
            <h3 class="text-lg font-bold text-gray-900">営業時間変更</h3>
            <p class="text-sm text-gray-500 mt-1">この日だけ営業時間を個別設定できます。</p>
        </div>

        <div class="p-5">
            <input type="hidden" id="modalDate">

            <div class="mb-4">
                <label class="text-sm font-semibold text-gray-700">開始時間</label>
                <input type="time"
                       id="openTime"
                       class="w-full border rounded-xl px-3 py-3 mt-1.5">
            </div>

            <div class="mb-5">
                <label class="text-sm font-semibold text-gray-700">終了時間</label>
                <input type="time"
                       id="closeTime"
                       class="w-full border rounded-xl px-3 py-3 mt-1.5">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <button type="button"
                        id="deleteTimeButton"
                        onclick="deleteTime()"
                        class="px-4 py-3 text-sm rounded-xl font-semibold text-white shadow-sm"
                        style="background:#ef4444">
                    <span data-busy-text>時間変更削除</span>
                </button>

                <button type="button"
                        onclick="closeModal()"
                        class="px-4 py-3 text-sm rounded-xl font-semibold border"
                        style="border-color: {{ $theme }}22; color: {{ $theme }}">
                    閉じる
                </button>

                <button type="button"
                        id="saveTimeButton"
                        onclick="saveTime()"
                        class="px-4 py-3 text-sm rounded-xl font-semibold text-white shadow-sm"
                        style="background: {{ $theme }}">
                    <span data-busy-text>保存</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
