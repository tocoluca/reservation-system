<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>企業個別編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

@php
    $theme = old('theme_color', $company->theme_color ?? '#3b82f6');
    $days = [0 => '日', 1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土'];
    $patterns = old('open_patterns', $company->open_patterns ?? []);
    $regularHolidays = old('regular_holidays', $company->regular_holidays ?? []);
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 md:py-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">企業個別編集</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $company->name }}（{{ $company->company_code }}）</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.company.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50">
                企業一覧へ戻る
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.company.update', $company->id) }}">
        @csrf

        <div class="bg-white shadow rounded-2xl p-6 md:p-8 space-y-12">

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
                    基本情報
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold mb-2">企業名</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">企業コード</label>
                        <input type="text" name="company_code" value="{{ old('company_code', $company->company_code) }}"
                               maxlength="8"
                               class="w-full border rounded-lg p-3 uppercase">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">業種</label>
                        <select name="industry_type" class="w-full border rounded-lg p-3">
                            <option value="beauty" {{ old('industry_type', $company->industry_type) === 'beauty' ? 'selected' : '' }}>美容院</option>
                            <option value="dental" {{ old('industry_type', $company->industry_type) === 'dental' ? 'selected' : '' }}>歯科</option>
                            <option value="clinic" {{ old('industry_type', $company->industry_type) === 'clinic' ? 'selected' : '' }}>クリニック</option>
                            <option value="other" {{ old('industry_type', $company->industry_type) === 'other' ? 'selected' : '' }}>その他</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">電話番号</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">住所</label>
                        <input type="text" name="address" value="{{ old('address', $company->address) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">ホームページ</label>
                        <input type="text" name="homepage" value="{{ old('homepage', $company->homepage) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">テーマカラー</label>
                        <input type="color" name="theme_color" value="{{ old('theme_color', $company->theme_color ?? '#3b82f6') }}"
                               class="w-full h-12 border rounded-lg p-2">
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="font-semibold">利用中にする</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="is_initialized" value="0">
                        <input type="checkbox" id="is_initialized" name="is_initialized" value="1"
                               {{ old('is_initialized', $company->is_initialized) ? 'checked' : '' }}>
                        <label for="is_initialized" class="font-semibold">初期設定完了</label>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
                    LINE設定
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 flex items-center gap-3">
                        <input type="hidden" name="line_login_enabled" value="0">
                        <input type="checkbox" id="line_login_enabled" name="line_login_enabled" value="1"
                               {{ old('line_login_enabled', $company->line_login_enabled) ? 'checked' : '' }}
                               onchange="toggleLineFields()">
                        <label for="line_login_enabled" class="font-semibold">LINEログインを有効にする</label>
                    </div>

                    <div id="lineChannelIdWrap">
                        <label class="block font-semibold mb-2">LINE Channel ID</label>
                        <input type="text" name="line_channel_id"
                               value="{{ old('line_channel_id', $company->line_channel_id) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div id="lineChannelSecretWrap">
                        <label class="block font-semibold mb-2">LINE Channel Secret</label>
                        <input type="text" name="line_channel_secret"
                               value="{{ old('line_channel_secret', $company->line_channel_secret) }}"
                               class="w-full border rounded-lg p-3">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
                    予約設定
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold mb-2">時間刻み（分）</label>
                        <input type="number" name="slot_minutes"
                               value="{{ old('slot_minutes', $company->slot_minutes) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">同時予約数</label>
                        <input type="number" name="max_simultaneous_reservations"
                               value="{{ old('max_simultaneous_reservations', $company->max_simultaneous_reservations) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="menu_time_priority_flag" value="0">
                        <input type="checkbox" id="menu_time_priority_flag" name="menu_time_priority_flag" value="1"
                               {{ old('menu_time_priority_flag', $company->menu_time_priority_flag) ? 'checked' : '' }}>
                        <label for="menu_time_priority_flag" class="font-semibold">メニュー所要時間で予約</label>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">予約可能期間（月）</label>
                        <input type="number" name="reservation_month_limit"
                               value="{{ old('reservation_month_limit', $company->reservation_month_limit) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">予約受付開始（日）</label>
                        <input type="number" name="reservation_open_days"
                               value="{{ old('reservation_open_days', $company->reservation_open_days) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">予約締切（時間前）</label>
                        <input type="number" name="reservation_close_hours"
                               value="{{ old('reservation_close_hours', $company->reservation_close_hours) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <input type="hidden" name="holiday_is_closed" value="0">
                        <input type="checkbox" id="holiday_is_closed" name="holiday_is_closed" value="1"
                               {{ old('holiday_is_closed', $company->holiday_is_closed) ? 'checked' : '' }}>
                        <label for="holiday_is_closed" class="font-semibold">祝日を休業日にする</label>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
                    定休日
                </h2>

                <div class="flex flex-wrap gap-4">
                    @foreach($days as $key => $label)
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox"
                                   name="regular_holidays[]"
                                   value="{{ $key }}"
                                   {{ in_array((string)$key, array_map('strval', (array)$regularHolidays), true) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
                    曜日別営業時間
                </h2>

                <div class="space-y-6">
                    @foreach($days as $weekday => $label)
                        <div class="border rounded-xl p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="font-bold">{{ $label }}</div>
                                <button type="button"
                                        onclick="addTimeSlot({{ $weekday }})"
                                        class="px-3 py-2 rounded-lg text-white"
                                        style="background: {{ $theme }}">
                                    ＋枠追加
                                </button>
                            </div>

                            <div id="day-{{ $weekday }}" class="space-y-3">
                                @if(!empty($patterns[$weekday]))
                                    @foreach($patterns[$weekday] as $i => $pattern)
                                        <div class="flex flex-col md:flex-row gap-3 md:items-center time-row">
                                            <input type="time"
                                                   name="open_patterns[{{ $weekday }}][{{ $i }}][open]"
                                                   value="{{ $pattern['open'] ?? '' }}"
                                                   class="border rounded-lg p-2 w-full md:w-auto">

                                            <span>〜</span>

                                            <input type="time"
                                                   name="open_patterns[{{ $weekday }}][{{ $i }}][close]"
                                                   value="{{ $pattern['close'] ?? '' }}"
                                                   class="border rounded-lg p-2 w-full md:w-auto">

                                            <button type="button"
                                                    onclick="removeRow(this)"
                                                    class="px-3 py-2 rounded-lg bg-red-500 text-white">
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

            <div>
                <h2 class="text-lg font-bold mb-6 border-l-4 pl-3" style="border-color: {{ $theme }}">
                    契約管理
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold mb-2">猶予期限</label>
                        <input type="date" name="grace_until"
                               value="{{ old('grace_until', optional($company->grace_until)->format('Y-m-d')) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Stripe Customer ID</label>
                        <input type="text" name="stripe_customer_id"
                               value="{{ old('stripe_customer_id', $company->stripe_customer_id) }}"
                               class="w-full border rounded-lg p-3">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-semibold mb-2">Stripe Subscription ID</label>
                        <input type="text" name="stripe_subscription_id"
                               value="{{ old('stripe_subscription_id', $company->stripe_subscription_id) }}"
                               class="w-full border rounded-lg p-3">
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="px-6 py-3 rounded-xl text-white font-semibold shadow"
                        style="background: {{ $theme }}">
                    保存する
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleLineFields() {
    const enabled = document.getElementById('line_login_enabled').checked;
    document.getElementById('lineChannelIdWrap').style.display = enabled ? '' : 'none';
    document.getElementById('lineChannelSecretWrap').style.display = enabled ? '' : 'none';
}

function addTimeSlot(weekday) {
    const container = document.getElementById('day-' + weekday);
    const index = container.querySelectorAll('.time-row').length;

    const row = document.createElement('div');
    row.className = 'flex flex-col md:flex-row gap-3 md:items-center time-row';

    row.innerHTML = `
        <input type="time"
               name="open_patterns[${weekday}][${index}][open]"
               class="border rounded-lg p-2 w-full md:w-auto">

        <span>〜</span>

        <input type="time"
               name="open_patterns[${weekday}][${index}][close]"
               class="border rounded-lg p-2 w-full md:w-auto">

        <button type="button"
                onclick="removeRow(this)"
                class="px-3 py-2 rounded-lg bg-red-500 text-white">
            削除
        </button>
    `;

    container.appendChild(row);
}

function removeRow(button) {
    button.closest('.time-row').remove();
}

toggleLineFields();
</script>

</body>
</html>