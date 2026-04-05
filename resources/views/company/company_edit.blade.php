@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<style>
.tooltip{position:relative;display:inline-block;cursor:pointer}
.tooltip .tooltip-text{
visibility:hidden;
width:260px;
background:#333;
color:#fff;
text-align:left;
padding:8px 10px;
border-radius:6px;
position:absolute;
z-index:50;
bottom:130%;
left:50%;
transform:translateX(-50%);
font-size:12px;
line-height:1.5;
opacity:0;
transition:.2s;
}
.tooltip.active .tooltip-text{visibility:visible;opacity:1}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">

<div class="flex justify-between items-center mb-8">
<div>
<h1 class="text-2xl font-bold">企業情報設定</h1>
<p class="text-gray-500 text-sm mt-1">企業情報の設定・変更</p>
</div>

<a href="{{ route('company.dashboard') }}"
class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
style="border-color: {{ $theme }}; color: {{ $theme }}">
← ダッシュボード
</a>
</div>

<form method="POST" action="{{ route('company.info.update') }}">
@csrf

<div class="bg-white shadow rounded-2xl p-6 sm:p-8 space-y-12">

<div>
<h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
基本情報
</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>
<label class="block text-sm text-gray-500 mb-1">企業コード</label>
<div class="font-semibold">{{ $company->company_code }}</div>
</div>

<div>
<label class="block font-semibold mb-2 flex items-center gap-2">
メールアドレス
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
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

@error('email')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">電話番号</label>
<input type="text"
name="phone"
value="{{ old('phone',$company->phone) }}"
class="w-full border rounded-lg p-3 focus:ring-2"
style="--tw-ring-color: {{ $theme }}">

@error('phone')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">住所</label>
<input type="text"
name="address"
value="{{ old('address',$company->address) }}"
class="w-full border rounded-lg p-3 focus:ring-2"
style="--tw-ring-color: {{ $theme }}">

@error('address')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div class="md:col-span-2">
<label class="block font-semibold mb-2 flex items-center gap-2">
口コミ機能
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
<span class="tooltip-text">
ONにすると、口コミ投稿・口コミ管理機能を利用できます。<br>
OFFにすると、企業管理画面の口コミ管理メニューや口コミ画面は表示されません。
</span>
</span>
</label>

<div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
    <label class="flex items-start gap-3">
        <input type="hidden" name="review_enabled" value="0">
        <input type="checkbox"
               name="review_enabled"
               value="1"
               class="mt-1 h-5 w-5 rounded border-stone-300"
               {{ old('review_enabled', $company->review_enabled ?? false) ? 'checked' : '' }}>
        <div>
            <div class="font-semibold text-stone-800">口コミ機能を利用する</div>
            <div class="text-sm text-stone-500 mt-1 leading-6">
                ON にすると、口コミ投稿の受付や口コミ管理が利用できます。<br>
                OFF にすると、口コミ関連機能は非表示になります。
            </div>
        </div>
    </label>
</div>

@error('review_enabled')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

@if((int) $company->line_login_enabled === 1)
    <div class="md:col-span-2">
        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <h3 class="font-bold text-green-800 mb-2">LINEログイン設定</h3>
            <p class="text-sm text-green-700 leading-6">
                LINEログインが有効になっているため、Channel ID と Channel Secret を設定できます。
            </p>
        </div>
    </div>

    <div>
        <label class="block font-semibold mb-2">LINE Channel ID</label>
        <input type="text"
               name="line_channel_id"
               value="{{ old('line_channel_id', $company->line_channel_id) }}"
               class="w-full border rounded-lg p-3 focus:ring-2"
               style="--tw-ring-color: {{ $theme }}">
        @error('line_channel_id')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold mb-2">LINE Channel Secret</label>
        <input type="text"
               name="line_channel_secret"
               value="{{ old('line_channel_secret', $company->line_channel_secret) }}"
               class="w-full border rounded-lg p-3 focus:ring-2"
               style="--tw-ring-color: {{ $theme }}">
        @error('line_channel_secret')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif

<div>
<label class="block font-semibold mb-2">
時間刻み（分）
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
<span class="tooltip-text">
予約を行う単位時間。１つの予約をこの時間で
予約を受け付けます。またここで設定した時間で
予約表を作成します。メニューで設定した所要時間で
予約を受付たい場合は「メニュー所要時間で予約」に
チェックを入れてください。
</span>
</span>
</label>
<input type="number"
name="slot_minutes"
value="{{ old('slot_minutes',$company->slot_minutes) }}"
class="w-full border rounded-lg p-3 focus:ring-2"
style="--tw-ring-color: {{ $theme }}">

@error('slot_minutes')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">
メニュー所要時間で予約（分）
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
<span class="tooltip-text">
メニューで設定した所要時間で予約を行う場合
チェックしてください。チェックしない場合は
時間刻み（分）で設定した時間で予約を行います。
</span>
</span>
</label>
<input type="checkbox"
name="menu_time_priority_flag"
value="1"
{{ old('menu_time_priority_flag', $company->menu_time_priority_flag) ? 'checked' : '' }}>
<span class="text-sm">メニューの所要時間を予約時間にする</span>
</div>

<div>
<label class="block font-semibold mb-2">
同時予約数
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
<span class="tooltip-text">
１人当たり重複していくつ予約を受けるかを設定する項目です。
同一時間帯で受け付ける予約数を記載して下さい。
１人２つの予約を受け付ける場合は２を設定して下さい。
</span>
</span>
</label>

<input type="number"
name="max_simultaneous_reservations"
value="{{ old('max_simultaneous_reservations',$company->max_simultaneous_reservations) }}"
class="w-full border rounded-lg p-3 focus:ring-2"
style="--tw-ring-color: {{ $theme }}">

@error('max_simultaneous_reservations')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

</div>
</div>


<div>
<h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
予約設定
</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>
<label class="block font-semibold mb-2">予約可能期間（月）</label>
<select name="reservation_month_limit" class="w-full border rounded-lg p-3">
<option value="1" {{ old('reservation_month_limit', $company->reservation_month_limit)==1?'selected':'' }}>1ヶ月</option>
<option value="2" {{ old('reservation_month_limit', $company->reservation_month_limit)==2?'selected':'' }}>2ヶ月</option>
<option value="3" {{ old('reservation_month_limit', $company->reservation_month_limit)==3?'selected':'' }}>3ヶ月</option>
<option value="4" {{ old('reservation_month_limit', $company->reservation_month_limit)==4?'selected':'' }}>4ヶ月</option>
<option value="5" {{ old('reservation_month_limit', $company->reservation_month_limit)==5?'selected':'' }}>5ヶ月</option>
<option value="6" {{ old('reservation_month_limit', $company->reservation_month_limit)==6?'selected':'' }}>6ヶ月</option>
<option value="7" {{ old('reservation_month_limit', $company->reservation_month_limit)==7?'selected':'' }}>7ヶ月</option>
<option value="8" {{ old('reservation_month_limit', $company->reservation_month_limit)==8?'selected':'' }}>8ヶ月</option>
<option value="9" {{ old('reservation_month_limit', $company->reservation_month_limit)==9?'selected':'' }}>9ヶ月</option>
<option value="10" {{ old('reservation_month_limit', $company->reservation_month_limit)==10?'selected':'' }}>10ヶ月</option>
<option value="11" {{ old('reservation_month_limit', $company->reservation_month_limit)==11?'selected':'' }}>11ヶ月</option>
<option value="12" {{ old('reservation_month_limit', $company->reservation_month_limit)==12?'selected':'' }}>12ヶ月</option>
</select>
<p class="text-xs text-gray-500 mt-1">指定した月数の月末まで予約可能</p>
@error('reservation_month_limit')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">予約受付開始（日）</label>
<input type="number"
name="reservation_open_days"
value="{{ old('reservation_open_days',$company->reservation_open_days) }}"
class="w-full border rounded-lg p-3">
<p class="text-xs text-gray-500 mt-1">例：0 → 当日予約可能、1 → 翌日分から予約可能</p>
@error('reservation_open_days')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">予約締切（時間前）</label>
<input type="number"
name="reservation_close_hours"
value="{{ old('reservation_close_hours',$company->reservation_close_hours) }}"
class="w-full border rounded-lg p-3">
<p class="text-xs text-gray-500 mt-1">例：2 → 2時間前まで予約可能</p>
@error('reservation_close_hours')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">再来店促進メール送信日数</label>
<input type="number"
name="revisit_reminder_days"
value="{{ old('revisit_reminder_days', $company->revisit_reminder_days ?? 45) }}"
class="w-full border rounded-lg p-3">
<p class="text-xs text-gray-500 mt-1">例：45 → 最終来店日から45日後に案内対象</p>
@error('revisit_reminder_days')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

<div>
<label class="block font-semibold mb-2">
Webキャンセル締切（時間前）
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
<span class="tooltip-text">
予約時間の何時間前まで、予約完了メールからキャンセルできるかを設定します。
それ以降はお電話でのキャンセル案内に切り替わります。
</span>
</span>
</label>

<input type="number"
name="web_cancel_deadline_hours"
value="{{ old('web_cancel_deadline_hours', $company->web_cancel_deadline_hours ?? 24) }}"
class="w-full border rounded-lg p-3">

<p class="text-xs text-gray-500 mt-1">
例：24 → 24時間前までWebでキャンセル可能
</p>

@error('web_cancel_deadline_hours')
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
</div>

</div>
</div>

@php
$days = [0=>'日',1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土'];
$patterns = old('open_patterns', $company->open_patterns ?? []);
@endphp

<div>
<h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
曜日別営業時間
<span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
<span class="tooltip-text">
曜日別に営業時間を設定することができます。
毎週火曜日が休業日の場合でも祝日は営業する場合は
設定して下さい。ここでは営業する可能性がある曜日について
営業時間を設定して下さい。年末年始、ＧＷ、その他臨時休業や
営業時間変更については、ダッシュボードにある営業日カレンダーで
設定して下さい。
</span>
</span>
</h2>

<div class="space-y-6">
@foreach($days as $weekday=>$label)
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
@foreach($patterns[$weekday] as $i=>$pattern)
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

@error("open_patterns.$weekday.$i.open")
<p class="text-sm text-red-500 mt-1">{{ $message }}</p>
@enderror
@endforeach
@endif
</div>
</div>
@endforeach
</div>
</div>

<div class="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t">
<button type="submit"
class="w-full sm:w-auto text-white px-6 py-3 rounded-lg shadow"
style="background: {{ $theme }}">
保存する
</button>
</div>

</div>

<input type="hidden" name="theme_color" value="{{ $company->theme_color }}">
</form>

</div>

<script>
function toggleTooltip(el){
    el.classList.toggle('active')
}

function addTimeSlot(weekday){
    const container = document.getElementById('day-' + weekday)
    const index = container.children.length
    const row = document.createElement('div')

    row.className = 'flex flex-col sm:flex-row gap-3 sm:items-center time-row'

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
`

    container.appendChild(row)
}

function removeTimeSlot(btn){
    btn.closest('.time-row').remove()
}
</script>

@endsection