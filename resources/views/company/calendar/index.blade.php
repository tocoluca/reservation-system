@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- ============================= --}}
    {{-- ヘッダー --}}
    {{-- ============================= --}}
    <div class="flex flex-col gap-4 mb-6">

        <a href="{{ route('company.dashboard') }}"
           class="w-full text-center py-3 rounded-xl text-white font-semibold shadow-md"
           style="background: {{ $theme }}">
            ← ダッシュボードに戻る
        </a>

        <h2 class="text-xl font-bold text-center"
            style="color: {{ $theme }}">
            {{ $year }}年 {{ $month }}月
        </h2>

        {{-- 月移動 --}}
        <div class="flex justify-between gap-3">
            <a href="{{ route('company.calendar.index',['year'=>$prev->year,'month'=>$prev->month]) }}"
               class="flex-1 text-center py-2 rounded-xl border font-semibold"
               style="border-color: {{ $theme }}; color: {{ $theme }}">
                ◀ 前月
            </a>

            <a href="{{ route('company.calendar.index',['year'=>$next->year,'month'=>$next->month]) }}"
               class="flex-1 text-center py-2 rounded-xl border font-semibold"
               style="border-color: {{ $theme }}; color: {{ $theme }}">
                次月 ▶
            </a>
        </div>
    </div>


    {{-- ============================= --}}
    {{-- 年間一括休日設定 --}}
    {{-- ============================= --}}
<div class="bg-white rounded-2xl shadow p-4 mb-6 border-l-4"
     style="border-color: {{ $theme }}">

    <div class="font-semibold mb-3 text-base"
         style="color: {{ $theme }}">
        年間一括休業日/営業日設定
    </div>

    {{-- 年入力 --}}
    <div class="flex items-center gap-2 mb-3">
        <input type="number"
               id="bulkYearInput"
               value="{{ $year }}"
               min="2000"
               max="2100"
               class="border rounded-lg px-2 py-1 w-24 text-sm">
        <span class="text-xs text-gray-500">年</span>
    </div>

    {{-- 休日設定 --}}
    <div class="grid grid-cols-3 sm:grid-cols-7 gap-2 text-xs">
        @foreach(['日','月','火','水','木','金','土'] as $i => $w)
            <button onclick="bulkYearHoliday({{ $i }})"
                    class="py-1.5 rounded-lg border transition active:scale-95"
                    style="border-color: {{ $theme }}; color: {{ $theme }}">
                {{ $w }}休
            </button>
        @endforeach
    </div>

    {{-- 営業日に戻す --}}
    <div class="grid grid-cols-3 sm:grid-cols-7 gap-2 mt-2 text-xs">
        @foreach(['日','月','火','水','木','金','土'] as $i => $w)
            <button onclick="bulkYearOpen({{ $i }})"
                    class="py-1.5 rounded-lg border bg-green-50 transition active:scale-95"
                    style="border-color: {{ $theme }}; color: {{ $theme }}">
                {{ $w }}営
            </button>
        @endforeach
    </div>

</div>

    {{-- ============================= --}}
    {{-- カレンダー説明 --}}
    {{-- ============================= --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 mb-4 border-l-4"
         style="border-color: {{ $theme }}">

        <div class="font-bold mb-3"
             style="color: {{ $theme }}">
            カレンダー表示説明
        </div>

        <div class="flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-200 rounded"></div> 営業日
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-200 rounded"></div> 休日
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 border-2 rounded"
                     style="border-color: {{ $theme }}"></div> 本日
            </div>
        </div>
    </div>


    {{-- 曜日 --}}
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

		if ($calendar) {
		    // 🔥 カレンダーが最優先
		    $isOpen = (bool)$calendar->is_open;
		}
		else {
		    // 🔥 holiday_is_closed が有効なら祝日は休業
		    if ($isHoliday) {
		        $isOpen = false;
		    } else {
		        $isOpen = true;
		    }
		}


		$bgClass = !$isOpen
		    ? ($isHoliday ? 'bg-red-300' : 'bg-red-200')
		    : 'bg-green-200';
        @endphp

        <div onclick="toggleDay('{{ $date }}', this)"
             class="aspect-square rounded-2xl p-2 text-xs font-semibold
                    flex flex-col justify-between shadow-md
                    transition active:scale-95 cursor-pointer
                    {{ $bgClass }}
                    {{ $weekday==0?'text-red-600':'' }}
                    {{ $weekday==6?'text-blue-600':'' }}
                    {{ $isToday?'ring-2':'' }}"
             style="{{ $isToday?'ring-color:'.$theme:'' }}">

            <div class="text-sm font-bold">{{ $day }}</div>

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
        el.classList.remove('bg-green-200','bg-red-200','hover:bg-green-300','hover:bg-red-300');

        if (data.is_open) {
            el.classList.add('bg-green-200','hover:bg-green-300');
        } else {
            el.classList.add('bg-red-200','hover:bg-red-300');
        }
    });
}

function openModal(date) {
    document.getElementById('modalDate').value = date;
    document.getElementById('timeModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('timeModal').classList.add('hidden');
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

    if (!confirm("選択した曜日を年間すべて休日にしますか？")) {
        return;
    }

    fetch("{{ route('company.calendar.bulkYearWeekday') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            year: {{ $year }},
            weekday: weekday
        })
    })
    .then(res => res.json())
    .then(() => {
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

        <div class="flex justify-between">
            <button onclick="closeModal()"
                    class="px-4 py-2 text-sm text-gray-500">
                キャンセル
            </button>

            <button onclick="saveTime()"
                    class="px-4 py-2 text-white rounded-lg"
                    style="background: {{ $theme }}">
                保存
            </button>
        </div>

    </div>
</div>
@endsection