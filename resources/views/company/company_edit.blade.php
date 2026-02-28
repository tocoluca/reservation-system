@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<style>
.tooltip { position: relative; display: inline-block; cursor: pointer; }
.tooltip .tooltip-text {
    visibility: hidden;
    width: 260px;
    background: #333;
    color: #fff;
    text-align: left;
    padding: 8px 10px;
    border-radius: 6px;
    position: absolute;
    z-index: 50;
    bottom: 130%;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    line-height: 1.5;
    opacity: 0;
    transition: 0.2s;
}
.tooltip.active .tooltip-text { visibility: visible; opacity: 1; }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">

    {{-- ヘッダー --}}
    <div class="rounded-2xl shadow mb-8 p-6 sm:p-8 text-white"
         style="background: {{ $theme }}">
        <h1 class="text-2xl sm:text-3xl font-bold">
            企業情報設定
        </h1>
        <p class="mt-2 text-sm opacity-90">
            ブランドカラーと予約設定を管理します
        </p>
    </div>

    <form method="POST" action="{{ route('company.info.update') }}">
        @csrf

        <div class="bg-white shadow rounded-2xl p-6 sm:p-8 space-y-12">

            {{-- ================= 基本情報 ================= --}}
            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3"
                    style="border-color: {{ $theme }}">
                    基本情報
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm text-gray-500 mb-1">
                            企業コード
                        </label>
                        <div class="font-semibold">
                            {{ $company->company_code }}
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2 flex items-center gap-2">
                            メールアドレス
                            <span class="tooltip text-gray-400 text-sm"
                                  onclick="toggleTooltip(this)">❓
                                <span class="tooltip-text">
                                    企業の連絡用メールです。<br>
                                    ログイン用ではありません。
                                </span>
                            </span>
                        </label>
                        <input type="email"
                               name="email"
                               value="{{ old('email',$company->email) }}"
                               class="w-full border rounded-lg p-3 focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">電話番号</label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone',$company->phone) }}"
                               class="w-full border rounded-lg p-3 focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">住所</label>
                        <input type="text"
                               name="address"
                               value="{{ old('address',$company->address) }}"
                               class="w-full border rounded-lg p-3 focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>
        {{-- 時間刻み --}}
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                時間刻み（分）
            </label>
            <input type="number"
                   name="slot_minutes"
                   value="{{ old('address',$company->slot_minutes) }}"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        {{-- 同時予約数 --}}
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                同時予約数
            </label>

            <input type="number"
                   name="max_simultaneous_reservations"
                   value="{{ old('address',$company->max_simultaneous_reservations) }}"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        {{-- 営業時間 --}}
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                営業時間（開始）
            </label>

            <input type="time"
                   name="open_time"
                   value="{{ old('address',$company->open_time) }}"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                営業時間（終了）
            </label>

            <input type="time"
                   name="close_time"
                   value="{{ old('address',$company->close_time) }}"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        {{-- 休業日 --}}
        <div class="mb-6">
            <label class="block mb-3 font-semibold text-sm">
                休業日
            </label>

            @php
                $days = ['日','月','火','水','木','金','土'];
    $selected = old('regular_holidays', $company->regular_holidays ?? []);
            @endphp

<div class="grid grid-cols-4 gap-2 text-sm">
    @foreach($days as $i => $day)
        <label class="flex items-center gap-2 border rounded-lg px-2 py-2 cursor-pointer hover:bg-gray-50">
            <input type="checkbox"
                   name="regular_holidays[]"
                   value="{{ $i }}"
                   @checked(in_array($i, $selected))>
            <span>{{ $day }}</span>
        </label>
    @endforeach
</div>
        </div>

        {{-- 祝日設定 --}}
        <div class="mb-8">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox"
                       name="holiday_is_closed"
                       value="1"       
			{{ old('active', $company->holiday_is_closed) ? 'checked' : '' }}>
                祝日を休業日にする
            </label>
        </div>

                </div>
            </div>


            {{-- ================= 曜日別営業時間 ================= --}}
            @php
                $days = [0=>'日',1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土'];
                $patterns = old('open_patterns', $company->open_patterns ?? []);
            @endphp

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3"
                    style="border-color: {{ $theme }}">
                    曜日別営業時間
                </h2>

                <div class="space-y-6">

                    @foreach($days as $weekday => $label)

                    <div class="border rounded-xl p-4">

                        <div class="flex justify-between items-center mb-4">
                            <div class="font-bold">{{ $label }}</div>

                            <button type="button"
                                    onclick="addTimeSlot({{ $weekday }})"
                                    class="text-sm px-3 py-1 rounded text-white"
                                    style="background: {{ $theme }}">
                                ＋枠追加
                            </button>
                        </div>

                        <div id="day-{{ $weekday }}" class="space-y-3">

                            @if(!empty($patterns[$weekday]))
                                @foreach($patterns[$weekday] as $i => $pattern)

                                <div class="flex flex-col sm:flex-row gap-3 sm:items-center time-row">

                                    <input type="time"
                                           name="open_patterns[{{ $weekday }}][{{ $i }}][open]"
                                           value="{{ $pattern['open'] ?? '' }}"
                                           class="border rounded-lg p-2 w-full sm:w-auto">

                                    <span class="hidden sm:inline">〜</span>

                                    <input type="time"
                                           name="open_patterns[{{ $weekday }}][{{ $i }}][close]"
                                           value="{{ $pattern['close'] ?? '' }}"
                                           class="border rounded-lg p-2 w-full sm:w-auto">

                                    <button type="button"
                                            onclick="removeTimeSlot(this)"
                                            class="text-red-500 text-sm">
                                        削除
                                    </button>

                                </div>

                                @endforeach
                            @endif

                        </div>

                    </div>

                    @endforeach

                </div>
            </div>


            {{-- ================= 保存ボタン ================= --}}
            <div class="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t">

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<a href="{{ route('company.dashboard') }}"
   class="group inline-flex items-center justify-center gap-2
          w-full sm:w-auto
          px-6 py-3
          rounded-xl
          text-white font-semibold
          shadow-lg
          transition-all duration-200
          hover:shadow-xl hover:-translate-y-0.5"
   style="background: {{ $theme }}">

    {{-- 左矢印アイコン --}}
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 transition-transform duration-200 group-hover:-translate-x-1"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"/>
    </svg>

    ダッシュボードに戻る

</a>

                <button type="submit"
                        class="w-full sm:w-auto text-white px-6 py-3 rounded-lg shadow"
                        style="background: {{ $theme }}">
                    保存する
                </button>

            </div>

        </div>

        <input type="hidden"
               name="theme_color"
               value="{{ $company->theme_color }}">

    </form>
</div>


<script>
function toggleTooltip(el){
    el.classList.toggle('active');
}

function addTimeSlot(weekday){
    const container = document.getElementById('day-' + weekday);
    const index = container.children.length;

    const row = document.createElement('div');
    row.className = 'flex flex-col sm:flex-row gap-3 sm:items-center time-row';

    row.innerHTML = `
        <input type="time"
               name="open_patterns[${weekday}][${index}][open]"
               class="border rounded-lg p-2 w-full sm:w-auto">
        <span class="hidden sm:inline">〜</span>
        <input type="time"
               name="open_patterns[${weekday}][${index}][close]"
               class="border rounded-lg p-2 w-full sm:w-auto">
        <button type="button"
                onclick="removeTimeSlot(this)"
                class="text-red-500 text-sm">
            削除
        </button>
    `;

    container.appendChild(row);
}

function removeTimeSlot(btn){
    btn.closest('.time-row').remove();
}
</script>

@endsection