@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- ============================= --}}
    {{-- タイトル --}}
    {{-- ============================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">営業日カレンダー</h1>
            <p class="text-gray-500 text-sm mt-1">営業日の登録・確認・営業時間変更ができます</p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2 text-sm rounded-xl border font-semibold hover:bg-gray-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ← ダッシュボード
        </a>
    </div>

    {{-- ============================= --}}
    {{-- 上部操作エリア --}}
    {{-- ============================= --}}
    <div class="bg-white rounded-3xl shadow-lg border overflow-hidden mb-6">
        {{-- 月ナビ --}}
        <div class="px-4 md:px-6 py-5 border-b bg-gradient-to-r from-white to-gray-50">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-gray-500 uppercase">表示中の月</p>
                    <h2 class="text-2xl md:text-3xl font-bold mt-1" style="color: {{ $theme }}">
                        {{ $year }}年 {{ $month }}月
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        この月の営業日・休業日・営業時間変更を確認できます
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
                    <a href="{{ route('company.calendar.index',['year'=>$prev->year,'month'=>$prev->month]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                       style="border-color: {{ $theme }}; color: {{ $theme }};">
                        ◀ 前月
                    </a>

                    <a href="{{ route('company.calendar.index',['year'=>now()->year,'month'=>now()->month]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl font-semibold text-white shadow-md transition hover:opacity-90"
                       style="background: {{ $theme }};">
                        今月へ戻る
                    </a>

                    <a href="{{ route('company.calendar.index',['year'=>$next->year,'month'=>$next->month]) }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                       style="border-color: {{ $theme }}; color: {{ $theme }};">
                        次月 ▶
                    </a>
                </div>
            </div>
        </div>

        {{-- 補助操作 --}}
        <div class="px-4 md:px-6 py-4">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            onclick="toggleBulkPanel()"
                            id="bulkToggleBtn"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl border font-semibold text-sm hover:bg-gray-50 transition"
                            style="border-color: {{ $theme }}; color: {{ $theme }};">
                        年間設定を開く ▼
                    </button>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-600">
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
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2" style="border-color: {{ $theme }}"></div>
                        <span>本日</span>
                    </div>
                </div>
            </div>

            {{-- 年間一括設定パネル --}}
            <div id="bulkSettingPanel" class="hidden mt-4">
                <div class="rounded-2xl border bg-gray-50 p-4 md:p-5">
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
                        {{-- 休業設定 --}}
                        <div class="bg-white rounded-2xl border p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <h4 class="font-bold text-gray-800">年間で休業日にする</h4>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">
                                例：毎週月曜日を1年分まとめて休業日に設定
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-2">
                                @foreach(['日','月','火','水','木','金','土'] as $i => $w)
                                    <button type="button"
                                            onclick="bulkYearHoliday({{ $i }})"
                                            class="py-2 px-2 rounded-xl border text-sm font-semibold transition hover:bg-red-50 active:scale-95"
                                            style="border-color:#fca5a5; color:#dc2626;">
                                        {{ $w }}曜を休業
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- 営業日に戻す --}}
                        <div class="bg-white rounded-2xl border p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <h4 class="font-bold text-gray-800">年間で営業日に戻す</h4>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">
                                例：毎週火曜日を1年分まとめて営業日に戻す
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-2">
                                @foreach(['日','月','火','水','木','金','土'] as $i => $w)
                                    <button type="button"
                                            onclick="bulkYearOpen({{ $i }})"
                                            class="py-2 px-2 rounded-xl border text-sm font-semibold transition hover:bg-green-50 active:scale-95"
                                            style="border-color:#86efac; color:#15803d;">
                                        {{ $w }}曜を営業
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- 曜日 --}}
    {{-- ============================= --}}
    <div class="grid grid-cols-7 text-center text-sm font-bold mb-2">
        <div class="text-red-500">日</div>
        <div>月</div>
        <div>火</div>
        <div>水</div>
        <div>木</div>
        <div>金</div>
        <div class="text-blue-500">土</div>
    </div>

    {{-- ============================= --}}
    {{-- カレンダー本体 --}}
    {{-- ============================= --}}
    <div class="grid grid-cols-7 gap-2">
        @for($i=0;$i<$startDayOfWeek;$i++)
            <div></div>
        @endfor

        @for($day=1;$day<=$daysInMonth;$day++)
            @php
                $dateObj = \Carbon\Carbon::create($year,$month,$day);
                $date = $dateObj->format('Y-m-d');
                $calendar = $calendars[$date] ?? null;
                $isToday = $dateObj->isToday();
                $weekday = $dateObj->dayOfWeek;

                $isHoliday = false;

                if ($company->holiday_is_closed) {
                    $isHoliday = in_array($date, $holidayDates ?? []);
                }

                $holidayRing = $isHoliday ? 'ring-2 ring-red-400' : '';

                if ($calendar) {
                    $isOpen = (bool)$calendar->is_open;
                } else {
                    if ($isHoliday) {
                        $isOpen = false;
                    } else {
                        $isOpen = true;
                    }
                }

                $isTimeChanged = false;

                if ($calendar && ($calendar->open_time || $calendar->close_time)) {
                    $isTimeChanged = true;
                }

                if (!$isOpen) {
                    $bgClass = $isHoliday ? 'bg-red-300' : 'bg-red-200';
                } elseif ($isTimeChanged) {
                    $bgClass = 'bg-yellow-200';
                } else {
                    $bgClass = 'bg-green-200';
                }
            @endphp

            <div onclick="toggleDay('{{ $date }}', this)"
                 class="aspect-square rounded-2xl p-2 text-xs font-semibold
                        flex flex-col justify-between shadow-md
                        transition active:scale-95 cursor-pointer
                        {{ $bgClass }}
                        {{ $weekday==0?'text-red-600':'' }}
                        {{ $weekday==6?'text-blue-600':'' }}
                        {{ $isToday ? 'ring-4' : '' }}
                        {{ $holidayRing }}"
                 style="
                    {{ $isToday ? 'ring-color:'.$theme.';' : '' }}
                    {{ $isHoliday ? 'ring-color:#f87171;' : '' }}
                 ">
                <div class="text-sm font-bold">{{ $day }}</div>

                @if(isset($reservationCounts[$date]))
                    <div class="text-[10px] text-center font-bold text-gray-700">
                        予約 {{ $reservationCounts[$date] }}件
                    </div>
                @endif

                @if(isset($holidayNames[$date]))
                    <div class="text-[9px] text-red-600 text-center leading-tight">
                        {{ $holidayNames[$date] }}
                    </div>
                @endif

                @if($calendar && $calendar->open_time)
                    <div class="text-[9px] text-center leading-tight">
                        {{ substr($calendar->open_time,0,5) }} - {{ substr($calendar->close_time,0,5) }}
                    </div>
                @endif

                <div class="text-[10px] underline text-center"
                     onclick="event.stopPropagation(); openModal('{{ $date }}')"
                     style="color: {{ $theme }}">
                    時間変更
                </div>
            </div>
        @endfor
    </div>
</div>

{{-- ============================= --}}
{{-- JS --}}
{{-- ============================= --}}
<script>
function toggleBulkPanel() {
    const panel = document.getElementById('bulkSettingPanel');
    const btn = document.getElementById('bulkToggleBtn');

    panel.classList.toggle('hidden');

    if (panel.classList.contains('hidden')) {
        btn.innerHTML = '年間設定を開く ▼';
    } else {
        btn.innerHTML = '年間設定を閉じる ▲';
    }
}

function toggleDay(date, el) {
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
        el.classList.remove('bg-green-200','bg-red-200','bg-red-300','bg-yellow-200','hover:bg-green-300','hover:bg-red-300');

        if (data.is_open) {
            el.classList.add('bg-green-200','hover:bg-green-300');
        } else {
            el.classList.add('bg-red-200','hover:bg-red-300');
        }
    });
}

function openModal(date) {
    document.getElementById('modalDate').value = date;
    const modal = document.getElementById('timeModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('timeModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function saveTime() {
    const date = document.getElementById('modalDate').value;
    const open_time = document.getElementById('openTime').value;
    const close_time = document.getElementById('closeTime').value;

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
    }).then(() => {
        closeModal();
        location.reload();
    });
}

function deleteTime() {
    const date = document.getElementById('modalDate').value;

    if (!confirm("営業時間変更を削除しますか？")) {
        return;
    }

    fetch("{{ route('company.calendar.deleteTime') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            date: date
        })
    }).then(() => {
        closeModal();
        location.reload();
    });
}

function bulkHoliday(weekday) {
    if (!confirm("選択した曜日をすべて休日にしますか？")) {
        return;
    }

    fetch("{{ route('company.calendar.bulkWeekday') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            year: {{ $year }},
            month: {{ $month }},
            weekday: weekday
        })
    })
    .then(res => res.json())
    .then(() => {
        location.reload();
    });
}

function bulkYearHoliday(weekday) {
    const year = document.getElementById('bulkYearInput').value;

    if (!year) {
        alert("年を入力してください");
        return;
    }

    if (!confirm(year + "年の選択曜日をすべて休業日にしますか？")) {
        return;
    }

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
    .then(res => res.json())
    .then(() => {
        alert("年間休業日設定が完了しました");
        location.reload();
    });
}

function bulkYearOpen(weekday) {
    const year = document.getElementById('bulkYearInput').value;

    if (!year) {
        alert("年を入力してください");
        return;
    }

    if (!confirm(year + "年の選択曜日をすべて営業日に戻しますか？")) {
        return;
    }

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
    .then(res => res.json())
    .then(() => {
        alert("年間営業日設定が完了しました");
        location.reload();
    });
}
</script>

{{-- ============================= --}}
{{-- 時間変更モーダル --}}
{{-- ============================= --}}
<div id="timeModal"
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl w-80 p-6">
        <h3 class="text-lg font-bold mb-4">営業時間変更</h3>

        <input type="hidden" id="modalDate">

        <div class="mb-3">
            <label class="text-sm font-semibold">開始時間</label>
            <input type="time"
                   id="openTime"
                   class="w-full border rounded-lg px-3 py-2 mt-1">
        </div>

        <div class="mb-4">
            <label class="text-sm font-semibold">終了時間</label>
            <input type="time"
                   id="closeTime"
                   class="w-full border rounded-lg px-3 py-2 mt-1">
        </div>

        <div class="flex gap-2 mt-4">
            <button onclick="deleteTime()"
                    class="flex-1 px-4 py-2 text-sm rounded-lg font-semibold text-white shadow"
                    style="background:#ef4444">
                時間変更削除
            </button>

            <button onclick="closeModal()"
                    class="flex-1 px-4 py-2 text-sm rounded-lg font-semibold border"
                    style="border-color: {{ $theme }}; color: {{ $theme }}">
                閉じる
            </button>

            <button onclick="saveTime()"
                    class="flex-1 px-4 py-2 text-sm rounded-lg font-semibold text-white shadow"
                    style="background: {{ $theme }}">
                保存
            </button>
        </div>
    </div>
</div>
@endsection