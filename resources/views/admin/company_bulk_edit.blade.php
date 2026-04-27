<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>企業一括編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="max-w-5xl mx-auto px-4 py-6 md:py-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">企業一括編集</h1>
            <p class="text-sm text-gray-500 mt-1">
                対象企業：
                {{ $companies->pluck('name')->implode('、') }}
            </p>
        </div>

        <a href="{{ route('admin.company.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50">
            企業一覧へ戻る
        </a>
    </div>

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.company.bulk-update') }}">
        @csrf

        @foreach($companies as $company)
            <input type="hidden" name="company_ids[]" value="{{ $company->id }}">
        @endforeach

        <div class="bg-white shadow rounded-2xl p-6 md:p-8 space-y-8">

            <div>
                <h2 class="text-lg font-bold mb-4">反映する項目を選んでください</h2>
                <p class="text-sm text-gray-500">
                    チェックを入れた項目だけ、選択した企業すべてに反映します。
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_theme_color" value="0">
                        <input type="checkbox" name="apply_theme_color" value="1">
                        テーマカラーを一括更新
                    </label>
                    <input type="color" name="theme_color" value="#3b82f6" class="w-full h-12 border rounded-lg p-2">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_is_active" value="0">
                        <input type="checkbox" name="apply_is_active" value="1">
                        利用状態を一括更新
                    </label>
                    <select name="is_active" class="w-full border rounded-lg p-3">
                        <option value="1">利用中</option>
                        <option value="0">停止</option>
                    </select>
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_is_initialized" value="0">
                        <input type="checkbox" name="apply_is_initialized" value="1">
                        初期設定完了フラグを一括更新
                    </label>
                    <select name="is_initialized" class="w-full border rounded-lg p-3">
                        <option value="1">完了</option>
                        <option value="0">未完了</option>
                    </select>
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_line_login_enabled" value="0">
                        <input type="checkbox" name="apply_line_login_enabled" value="1" onchange="toggleBulkLineArea()">
                        LINEログイン有効を一括更新
                    </label>
                    <select id="bulk_line_login_enabled" name="line_login_enabled" class="w-full border rounded-lg p-3" onchange="toggleBulkLineArea()">
                        <option value="1">有効</option>
                        <option value="0">無効</option>
                    </select>
                </div>

                <div id="bulkLineChannelIdWrap" class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_line_channel_id" value="0">
                        <input type="checkbox" name="apply_line_channel_id" value="1">
                        LINE Channel ID を一括更新
                    </label>
                    <input type="text" name="line_channel_id" class="w-full border rounded-lg p-3">
                </div>

                <div id="bulkLineChannelSecretWrap" class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_line_channel_secret" value="0">
                        <input type="checkbox" name="apply_line_channel_secret" value="1">
                        LINE Channel Secret を一括更新
                    </label>
                    <input type="text" name="line_channel_secret" class="w-full border rounded-lg p-3">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_slot_minutes" value="0">
                        <input type="checkbox" name="apply_slot_minutes" value="1">
                        時間刻みを一括更新
                    </label>
                    <input type="number" name="slot_minutes" class="w-full border rounded-lg p-3" value="30">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_max_simultaneous_reservations" value="0">
                        <input type="checkbox" name="apply_max_simultaneous_reservations" value="1">
                        同時予約数を一括更新
                    </label>
                    <input type="number" name="max_simultaneous_reservations" class="w-full border rounded-lg p-3" value="1">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_menu_time_priority_flag" value="0">
                        <input type="checkbox" name="apply_menu_time_priority_flag" value="1">
                        メニュー所要時間予約を一括更新
                    </label>
                    <select name="menu_time_priority_flag" class="w-full border rounded-lg p-3">
                        <option value="1">有効</option>
                        <option value="0">無効</option>
                    </select>
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_reservation_month_limit" value="0">
                        <input type="checkbox" name="apply_reservation_month_limit" value="1">
                        予約可能期間を一括更新
                    </label>
                    <input type="number" name="reservation_month_limit" class="w-full border rounded-lg p-3" value="3">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_reservation_open_days" value="0">
                        <input type="checkbox" name="apply_reservation_open_days" value="1">
                        予約受付開始日を一括更新
                    </label>
                    <input type="number" name="reservation_open_days" class="w-full border rounded-lg p-3" value="0">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_reservation_close_hours" value="0">
                        <input type="checkbox" name="apply_reservation_close_hours" value="1">
                        予約締切時間を一括更新
                    </label>
                    <input type="number" name="reservation_close_hours" class="w-full border rounded-lg p-3" value="1">
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_holiday_is_closed" value="0">
                        <input type="checkbox" name="apply_holiday_is_closed" value="1">
                        祝日休業を一括更新
                    </label>
                    <select name="holiday_is_closed" class="w-full border rounded-lg p-3">
                        <option value="1">休業</option>
                        <option value="0">営業</option>
                    </select>
                </div>

                <div class="border rounded-xl p-4 md:col-span-2">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_regular_holidays" value="0">
                        <input type="checkbox" name="apply_regular_holidays" value="1">
                        定休日を一括更新
                    </label>

                    <div class="flex flex-wrap gap-4">
                        @php $days = [0 => '日', 1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土']; @endphp
                        @foreach($days as $key => $label)
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="regular_holidays[]" value="{{ $key }}">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_grace_until" value="0">
                        <input type="checkbox" name="apply_grace_until" value="1">
                        猶予期限を一括更新
                    </label>
                    <input type="date" name="grace_until" class="w-full border rounded-lg p-3">
                </div>
                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_subscription_status" value="0">
                        <input type="checkbox" name="apply_subscription_status" value="1">
                        Subscription Status を一括更新
                    </label>
                    <select name="subscription_status" class="w-full border rounded-lg p-3">
                        <option value="incomplete">incomplete</option>
                        <option value="incomplete_expired">incomplete_expired</option>
                        <option value="trialing">trialing</option>
                        <option value="active">active</option>
                        <option value="past_due">past_due</option>
                        <option value="canceled">canceled</option>
                        <option value="unpaid">unpaid</option>
                    </select>
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_is_billing_active" value="0">
                        <input type="checkbox" name="apply_is_billing_active" value="1">
                        Billing Active を一括更新
                    </label>
                    <select name="is_billing_active" class="w-full border rounded-lg p-3">
                        <option value="1">ON</option>
                        <option value="0">OFF</option>
                    </select>
                </div>

                <div class="border rounded-xl p-4">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_stripe_customer_id" value="0">
                        <input type="checkbox" name="apply_stripe_customer_id" value="1">
                        Stripe Customer ID を一括更新
                    </label>
                    <input type="text" name="stripe_customer_id" class="w-full border rounded-lg p-3">
                </div>

                <div class="border rounded-xl p-4 md:col-span-2">
                    <label class="flex items-center gap-2 font-semibold mb-3">
                        <input type="hidden" name="apply_stripe_subscription_id" value="0">
                        <input type="checkbox" name="apply_stripe_subscription_id" value="1">
                        Stripe Subscription ID を一括更新
                    </label>
                    <input type="text" name="stripe_subscription_id" class="w-full border rounded-lg p-3">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                        class="px-6 py-3 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700">
                    一括更新する
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleBulkLineArea() {
    const applyEnabled = document.querySelector('input[name="apply_line_login_enabled"]').checked;
    const enabledValue = document.getElementById('bulk_line_login_enabled').value;

    const visible = !(applyEnabled && enabledValue === '0');

    document.getElementById('bulkLineChannelIdWrap').style.display = visible ? '' : 'none';
    document.getElementById('bulkLineChannelSecretWrap').style.display = visible ? '' : 'none';
}

toggleBulkLineArea();
</script>

</body>
</html>
