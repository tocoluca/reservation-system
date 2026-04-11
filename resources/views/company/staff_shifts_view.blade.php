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
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
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

    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-5 mb-6">
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

    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-5 mb-6">
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

    <div class="bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden">
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
@endsection