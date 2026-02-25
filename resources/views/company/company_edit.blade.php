@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp
<style>
.tooltip {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.tooltip .tooltip-text {
    visibility: hidden;
    width: 260px;
    background-color: #333;
    color: #fff;
    text-align: left;
    padding: 8px 10px;
    border-radius: 6px;
    position: absolute;
    z-index: 10;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    line-height: 1.5;
    opacity: 0;
    transition: opacity 0.2s;
}

.tooltip:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}
</style>
<div class="max-w-5xl mx-auto">

    {{-- ヘッダー --}}
    <div class="rounded-xl shadow mb-8 p-8 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl font-bold">
            企業情報設定
        </h1>

        <p class="mt-2 text-sm opacity-90">
            ブランドカラーと予約設定を管理します
        </p>
    </div>
    <form method="POST" action="{{ route('company.info.update') }}">
        @csrf

        <div class="bg-white shadow rounded-xl p-8 space-y-10">

            {{-- 基本情報 --}}
            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3"
                    style="border-color: {{ $theme }}">
                    基本情報
                </h2>

                <div class="grid grid-cols-2 gap-6">

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

			    <span class="tooltip text-gray-400 text-sm">❓
			        <span class="tooltip-text">
			            企業の連絡用メールです。<br>
			            ログイン用ではありません。
			        </span>
			    </span>
			</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email',$company->email) }}"
                               class="w-full border rounded p-2 focus:ring-2 focus:outline-none"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">電話番号</label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone',$company->phone) }}"
                               class="w-full border rounded p-2 focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">住所</label>
                        <input type="text"
                               name="address"
                               value="{{ old('address',$company->address) }}"
                               class="w-full border rounded p-2 focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>

                </div>
            </div>

	@php
	    $days = [0=>'日',1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土'];
	    $patterns = old('open_patterns', $company->open_patterns ?? []);
	@endphp

	<div>
	    <h2 class="text-lg font-bold mb-6 border-l-4 pl-3"
	        style="border-color: {{ $theme }}">
	        曜日別営業時間（複数枠対応）
	    </h2>

	    <div class="space-y-8">

	        @foreach($days as $weekday => $label)

	        <div class="border rounded-xl p-4">

	            <div class="flex justify-between items-center mb-4">
	                <div class="font-bold text-lg">{{ $label }}</div>

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

	                        <div class="flex gap-3 items-center time-row">

				<input type="time"
				       name="open_patterns[{{ $weekday }}][{{ $i }}][open]"
				       value="{{ $pattern['open'] ?? '' }}"
				       class="border rounded p-2
				       @error("open_patterns.$weekday.$i.open")
				           bg-red-50 border-red-400 text-red-700
				       @enderror">

	                            <span>〜</span>

				<input type="time"
				       name="open_patterns[{{ $weekday }}][{{ $i }}][close]"
				       value="{{ $pattern['close'] ?? '' }}"
				       class="border rounded p-2
				       @error("open_patterns.$weekday.$i.open")
				           bg-red-50 border-red-400 text-red-700
				       @enderror">
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
            {{-- 予約設定 --}}
            <div>
		<h2 class="text-lg font-bold mb-6 border-l-4 pl-3 flex items-center gap-3"
		    style="border-color: {{ $theme }}">
		    予約設定

		    <span class="tooltip text-gray-400 text-sm">❓
		        <span class="tooltip-text">
		            左は予約枠の時間（分）です。右は重複して予約を受け付ける件数となります。重複受付件数を2とした場合、2件まで同一時間帯で予約が可能となります。
		        </span>
		    </span>
                </h2>

                <div class="grid grid-cols-2 gap-6">
                    <input type="number"
                           name="slot_minutes"
                           value="{{ old('slot_minutes',$company->slot_minutes) }}"
                           class="border rounded p-2 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }}">
                    <input type="number"
                           name="max_simultaneous_reservations"
                           value="{{ old('max_simultaneous_reservations',$company->max_simultaneous_reservations) }}"
                           class="border rounded p-2 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }}">
                </div>
            </div>

            {{-- 定休日 --}}
            @php
                $selectedHolidays = old(
                    'regular_holidays',
                    $company->regular_holidays ?? []
                );
                $days = [0=>'日',1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土'];
            @endphp

            <div>
		<h2 class="text-lg font-bold mb-6 border-l-4 pl-3 flex items-center gap-3"
		    style="border-color: {{ $theme }}">
		    定休日

		    <span class="tooltip text-gray-400 text-sm">❓
		        <span class="tooltip-text">
		            チェックした曜日は予約受付を行いません。
		        </span>
		    </span>
                </h2>

                <div class="flex gap-4 flex-wrap">
                    @foreach($days as $num => $label)
                        <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox"
                                   name="regular_holidays[]"
                                   value="{{ $num }}"
                                   {{ in_array($num,$selectedHolidays)?'checked':'' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- スイッチ --}}
            <div class="space-y-4">

                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           name="holiday_is_closed"
                           value="1"
                           {{ $company->holiday_is_closed ? 'checked' : '' }}>
                    祝日は休業にする
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           name="menu_time_priority_flag"
                           value="1"
                           {{ $company->menu_time_priority_flag ? 'checked' : '' }}>
                    メニュー時間を優先する
		    <span class="tooltip text-gray-400 text-sm">❓
		        <span class="tooltip-text">
		            予約設定で指定した時間帯よりもメニューに記載している時間を優先する。メニューで90分となっていれば90分枠で予約をします。
		        </span>
		    </span>
                </label>

            </div>

            {{-- 保存ボタン --}}

		<div class="flex justify-between items-center mt-6">

		<a href="{{ route('company.dashboard') }}"
		   class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200"
		   style="color: {{ $theme }}">

		    <svg xmlns="http://www.w3.org/2000/svg"
		         class="w-4 h-4 transition-transform duration-200"
		         fill="none" viewBox="0 0 24 24" stroke="currentColor">
		        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
		              d="M15 19l-7-7 7-7" />
		    </svg>

		    ダッシュボードへ戻る
		</a>

		    <button type="submit"
		            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
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
function addTimeSlot(weekday) {

    const container = document.getElementById('day-' + weekday);

    const index = container.children.length;

    const row = document.createElement('div');
    row.className = 'flex gap-3 items-center time-row';

    row.innerHTML = `
        <input type="time"
               name="open_patterns[${weekday}][${index}][open]"
               class="border rounded p-2">

        <span>〜</span>

        <input type="time"
               name="open_patterns[${weekday}][${index}][close]"
               class="border rounded p-2">

        <button type="button"
                onclick="removeTimeSlot(this)"
                class="text-red-500 text-sm">
            削除
        </button>
    `;

    container.appendChild(row);
}

function removeTimeSlot(button) {
    button.closest('.time-row').remove();
}
</script>
@endsection