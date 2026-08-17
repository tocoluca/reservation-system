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
    $initialMobileDate = $month . '-' . str_pad(
        $month === now()->format('Y-m') ? now()->day : 1,
        2,
        '0',
        STR_PAD_LEFT
    );
@endphp

<style>
    .mobile-shift-date-button[aria-selected="true"] {
        color: #fff;
        background: {{ $theme }};
        border-color: {{ $theme }};
        box-shadow: 0 10px 24px {{ $theme }}38;
    }

    .shift-save-toolbar {
        border-color: {{ $theme }}45 !important;
        background:
            linear-gradient(135deg, rgba(255,255,255,.98), rgba(248,250,252,.94)),
            radial-gradient(circle at left, {{ $theme }}28, transparent 22rem) !important;
        box-shadow: 0 18px 42px rgba(15,23,42,.12), 0 8px 20px {{ $theme }}1f, inset 0 1px 0 rgba(255,255,255,.95) !important;
        position: sticky;
        top: 6rem;
        overflow: hidden;
    }

    .shift-save-toolbar::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: linear-gradient(180deg, {{ $theme }}, #111827);
    }

    .shift-save-toolbar > div {
        position: relative;
        z-index: 1;
    }

    .shift-save-button {
        min-height: 48px;
        background: linear-gradient(135deg, {{ $theme }}, #111827) !important;
        box-shadow: 0 12px 24px {{ $theme }}38, 0 6px 14px rgba(15,23,42,.12);
    }

    .shift-save-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 30px {{ $theme }}4d, 0 8px 18px rgba(15,23,42,.16);
    }

    .shift-save-button-label-mobile {
        display: none;
    }

    #shiftDirtyBadge {
        border: 1px solid rgba(245,158,11,.35);
        box-shadow: 0 6px 14px rgba(245,158,11,.14);
    }

    @media (max-width: 1023px) {
        .shift-management-page {
            padding-bottom: 7rem !important;
        }

        .shift-desktop-layout {
            display: none !important;
        }

        .shift-mobile-layout {
            display: block !important;
        }

        .shift-mobile-toolbar {
            position: sticky;
            top: calc(var(--company-topbar-height, 6rem) + .5rem);
            z-index: 35;
        }

        .shift-save-toolbar {
            position: fixed !important;
            left: .75rem;
            right: .75rem;
            bottom: 5.5rem;
            top: auto !important;
            z-index: 60;
            margin: 0 !important;
            padding: .5rem !important;
            border-radius: 1rem !important;
            border-color: rgba(148, 163, 184, .35);
        }

        .shift-save-toolbar > div {
            flex-direction: row !important;
            align-items: center;
            gap: .5rem !important;
        }

        .shift-save-toolbar > div > div:first-child {
            min-width: 0;
            flex: 1 1 auto;
        }

        .shift-save-toolbar .shift-save-help {
            display: none;
        }

        .shift-save-toolbar .shift-save-button {
            width: auto;
            min-height: 40px;
            flex: 0 0 auto;
            padding: .6rem .8rem;
            border-radius: .8rem;
            font-size: .75rem;
        }

        .shift-save-button-label-desktop {
            display: none;
        }

        .shift-save-button-label-mobile {
            display: inline;
        }

        .shift-save-toolbar .shift-legend {
            display: none;
        }

        .shift-save-button {
            width: 100%;
        }
    }
</style>

<div class="shift-management-page max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="shift-desktop-layout relative overflow-hidden rounded-3xl shadow-lg mb-6">
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

    <div class="shift-desktop-layout mb-6">
        @include('company._shift_setup_nav', [
            'currentStep' => 3,
            'links' => [
                ['label' => '基本シフトへ', 'route' => 'company.staff-default-shifts', 'icon' => 'arrow-left'],
            ],
        ])
    </div>

    {{-- ガイド --}}
    <div class="shift-desktop-layout mb-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
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

    <div class="shift-save-toolbar sticky top-24 z-30 mb-6 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span id="shiftDirtyBadge" class="hidden rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">未保存の変更あり</span>
                    <i data-lucide="pencil-line" class="h-4 w-4" style="color: {{ $theme }};"></i>
                    <span class="text-base font-black text-gray-950">シフト編集</span>
                </div>
                <p class="shift-save-help mt-1 text-xs text-gray-500">シフトを変更したら、最後にこのボタンで保存してください。</p>
                <div class="shift-legend mt-2 flex flex-wrap gap-2">
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
                    class="shift-save-button inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span class="shift-save-button-label-desktop" data-busy-text>変更内容を保存</span>
                <span class="shift-save-button-label-mobile" data-busy-text-mobile>保存</span>
            </button>
        </div>
    </div>

    <form id="shiftForm" method="POST" action="{{ route('company.staff-shifts.update') }}" data-busy-form="true" data-busy-label="保存中…">
        @csrf

        <div class="shift-mobile-layout lg:hidden mb-4">
            <div class="shift-mobile-toolbar rounded-[1.5rem] border border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur">
                <div class="flex items-center justify-between gap-2">
                    <button type="button" id="mobileShiftPrevDay"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-xl font-black text-gray-600 disabled:opacity-30"
                            aria-label="前日">‹</button>
                    <div class="min-w-0 text-center">
                        <div class="text-[11px] font-bold text-gray-400">勤務管理</div>
                        <div id="mobileShiftSelectedLabel" class="truncate text-lg font-black" style="color: {{ $theme }};"></div>
                    </div>
                    <button type="button" id="mobileShiftNextDay"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-xl font-black text-gray-600 disabled:opacity-30"
                            aria-label="翌日">›</button>
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
                                data-date-label="{{ $mobileDateObj->format('n月j日') }}（{{ ['日','月','火','水','木','金','土'][$mobileDayOfWeek] }}）"
                            class="mobile-shift-date-button snap-center shrink-0 whitespace-nowrap rounded-xl border border-gray-200 bg-white px-1.5 py-1.5 text-center text-xs font-black {{ $mobileDateColor }}"
                            style="min-width: min(4.25rem, calc((100vw - 3rem) / 7));"
                            aria-selected="false">
                            {{ $d }}（{{ ['日','月','火','水','木','金','土'][$mobileDayOfWeek] }}）
                        </button>
                    @endfor
                </div>
            </div>

            <div id="mobileShiftDayPanels" class="mt-3 space-y-2">
                @for($d = 1; $d <= $days; $d++)
                    @php
                        $mobileDate = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $mobileDateObj = \Carbon\Carbon::parse($mobileDate);
                        $mobileBusiness = $businessDays[$mobileDateObj->format('Y-m-d H:i:s')] ?? null;
                        $mobileIsClosed = $mobileBusiness
                            && $mobileBusiness->is_open === false
                            && is_null($mobileBusiness->open_time)
                            && is_null($mobileBusiness->close_time);
                    @endphp

                    <section data-mobile-shift-day="{{ $mobileDate }}" class="hidden space-y-2">
                        @forelse($staffs as $staff)
                            @php
                                $mobileShift = $shifts[$staff->id][$mobileDate][0] ?? null;
                                $mobileShiftId = $mobileShift->shift_pattern_id ?? '';
                                $mobileStaffVacations = $vacations[$staff->id] ?? collect();
                                $mobileIsVacation = $mobileStaffVacations->first(function ($v) use ($mobileDate) {
                                    return $mobileDate >= \Carbon\Carbon::parse($v->start_at)->toDateString()
                                        && $mobileDate <= \Carbon\Carbon::parse($v->end_at)->toDateString();
                                });
                                $mobileShiftName = $mobileShift?->shiftPattern->name ?? ($mobileShiftId ? ($patternMap[$mobileShiftId]->name ?? '') : '');
                                $mobileShiftColor = $mobileShift?->shiftPattern->color ?? ($mobileShiftId ? ($patternMap[$mobileShiftId]->color ?? '#64748b') : '');
                            @endphp

                            <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm"
                                 data-shift-cell
                                 data-staff="{{ $staff->id }}"
                                 data-day="{{ $d }}">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate font-black text-gray-900">{{ $staff->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500">この日のシフト</div>
                                    </div>
                                    @if($mobileIsClosed)
                                        <span class="shrink-0 rounded-full bg-gray-100 px-3 py-2 text-xs font-black text-gray-500">休業</span>
                                    @elseif($mobileIsVacation)
                                        <span class="shrink-0 rounded-full bg-red-100 px-3 py-2 text-xs font-black text-red-600">休暇</span>
                                    @else
                                        <span class="mobile-shift-state shift-state shrink-0 rounded-full px-3 py-2 text-xs font-black {{ $mobileShiftId ? 'text-white' : 'bg-gray-100 text-gray-700' }}"
                                              data-state-staff="{{ $staff->id }}"
                                              data-state-day="{{ $d }}"
                                              style="{{ $mobileShiftId ? 'background:' . ($mobileShiftColor ?: '#64748b') : '' }}">
                                            {{ $mobileShiftName ?: '休み' }}
                                        </span>
                                    @endif
                                </div>

                                @if(!$mobileIsClosed && !$mobileIsVacation)
                                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        <button type="button"
                                                class="shift-btn rounded-xl bg-gray-100 px-2 py-2.5 text-xs font-bold text-gray-700"
                                                data-target-staff="{{ $staff->id }}"
                                                data-target-day="{{ $d }}"
                                                data-value=""
                                                data-label="休"
                                                data-color="">
                                            休み
                                        </button>
                                        @foreach($patterns as $p)
                                            <button type="button"
                                                    class="shift-btn rounded-xl px-2 py-2.5 text-xs font-bold text-white"
                                                    data-target-staff="{{ $staff->id }}"
                                                    data-target-day="{{ $d }}"
                                                    data-value="{{ $p->id }}"
                                                    data-label="{{ $p->name }}"
                                                    data-color="{{ $p->color ?: '#64748b' }}"
                                                    style="background: {{ $p->color ?: '#64748b' }}">
                                                {{ $p->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-400">スタッフが登録されていません</div>
                        @endforelse
                    </section>
                @endfor
            </div>
        </div>

        <div class="shift-desktop-layout hidden lg:block bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-bold text-gray-900">スタッフ別 シフト表</h2>
                <p class="text-sm text-gray-500 mt-1">
                    各マスのボタンを押すだけで変更できます。表示されるシフトは登録済みパターンです。
                </p>
            </div>

            <div class="overflow-auto max-h-[72vh]">
                <table class="min-w-max text-sm w-full">
                    <thead>
                        <tr style="background: {{ $theme }}; color: white;">
                            <th class="sticky top-0 left-0 z-40 min-w-[240px] border-r border-b bg-white px-3 py-1.5 text-xs leading-tight text-black">
                                スタッフ
                            </th>

                            @for($d = 1; $d <= $days; $d++)
                                @php
                                    $dateObj = \Carbon\Carbon::parse("$month-$d");
                                    $isHoliday = $holidays->isHoliday($dateObj);
                                    $dayOfWeek = $dateObj->dayOfWeek;

                                    $color = 'bg-white text-gray-900';
                                    if ($isHoliday) {
                                        $color = 'bg-red-50 text-red-700';
                                    } elseif ($dayOfWeek == 0) {
                                        $color = 'bg-rose-50 text-red-700';
                                    } elseif ($dayOfWeek == 6) {
                                        $color = 'bg-blue-50 text-blue-700';
                                    }
                                @endphp

                                <th class="sticky top-0 z-30 min-w-[156px] border-b border-r border-gray-200 px-2 py-1 text-center {{ $color }}"
                                    data-day="{{ $d }}"
                                    data-weekday="{{ $dayOfWeek }}">
                                    <div class="mb-1 whitespace-nowrap text-xs font-bold leading-none">
                                        {{ $d }}（{{ ['日','月','火','水','木','金','土'][$dayOfWeek] }}）
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
                                <td class="p-3 font-semibold sticky left-0 bg-white z-20 min-w-[240px] border-r align-top">
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

                                    <td class="p-1 min-w-[156px] align-middle"
                                        data-shift-cell
                                        data-staff="{{ $staff->id }}"
                                        data-day="{{ $d }}">
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

document.addEventListener('DOMContentLoaded', function () {
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
        dateButtons.forEach((button, index) => {
            button.setAttribute('aria-selected', index === selectedIndex ? 'true' : 'false');
        });
        dayPanels.forEach(panel => {
            panel.classList.toggle('hidden', panel.dataset.mobileShiftDay !== date);
        });

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
    const staffId = input.dataset.staff;
    const day = input.dataset.day;
    const wrappers = Array.from(document.querySelectorAll(
        `[data-shift-cell][data-staff="${staffId}"][data-day="${day}"]`
    ));

    if (wrappers.length === 0) {
        const wrapper = input.closest('td');
        if (wrapper) wrappers.push(wrapper);
    }

    const value = input.value ?? '';

    wrappers.forEach(wrapper => {
        const buttons = wrapper.querySelectorAll('.shift-btn');
        const state = wrapper.querySelector('.shift-state');

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

            state.className = state.classList.contains('mobile-shift-state')
                ? 'mobile-shift-state shift-state shrink-0 rounded-full px-3 py-2 text-xs font-black'
                : 'shift-state text-[10px] font-bold rounded-full px-2 py-1 inline-block';
            state.style.background = '';
            state.style.color = '';

            if (!selectedBtn || value === '') {
                state.textContent = '休み';
                state.classList.add('bg-gray-100', 'text-gray-700');
            } else {
                state.textContent = selectedBtn.dataset.label || '設定済み';
                state.style.background = selectedBtn.dataset.color || '#64748b';
                state.style.color = '#fff';
            }
        }
    });
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
