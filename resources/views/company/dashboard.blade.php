@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">
            ダッシュボード
        </h1>

        <p class="text-gray-500 mt-2 text-sm sm:text-base">
            {{ $staff->company->name }} ｜ {{ $staff->name }}（{{ $staff->role }}）
        </p>
    </div>

    @if($dashboardPermissions['dashboard.manage'] ?? false)
        <div class="shrink-0">
            <a href="{{ route('company.dashboard-settings.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-white font-bold shadow hover:opacity-90 transition"
               style="background: {{ $theme }};">
                ダッシュボード管理
            </a>
        </div>
    @endif
</div>

@if($showSetupGuide)
    <div class="mb-8 rounded-3xl border border-amber-200 bg-amber-50 shadow-sm p-6 lg:p-7">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-lg"
                         style="background: {{ $theme }}">
                        !
                    </div>
                    <div>
                        <h2 class="text-xl lg:text-2xl font-bold text-amber-900">
                            はじめての設定がまだ完了していません
                        </h2>
                        <p class="text-sm lg:text-base text-amber-800 mt-1">
                            予約受付をスムーズに始めるため、初期設定を進めてください。
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-sm lg:text-base text-gray-700">
                        必須設定
                        <span class="font-bold text-xl">{{ $setupDoneCount }} / {{ $setupTotalCount }}</span>
                        完了
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    @foreach($setupStatusList as $item)
                        <div class="rounded-2xl border px-4 py-3 text-center
                            {{ $item['done'] ? 'bg-green-50 border-green-200' : 'bg-white border-red-200' }}">
                            <div class="text-xs font-bold {{ $item['done'] ? 'text-green-700' : 'text-red-600' }}">
                                {{ $item['done'] ? '完了' : '未完了' }}
                            </div>
                            <div class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $item['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 text-sm text-amber-900 leading-6">
                    まだ終わっていない項目があります。先に設定しておくと、予約カレンダーが正しく表示されやすくなります。
                </div>
            </div>

            <div class="shrink-0">
                <a href="{{ url('/company/setup') }}"
                   class="inline-flex items-center justify-center px-6 py-4 rounded-2xl text-white font-bold shadow-lg hover:opacity-90 transition"
                   style="background: {{ $theme }}">
                    はじめての設定ガイドへ
                </a>
            </div>
        </div>
    </div>
@endif

@if(!empty($settingWarnings))
    @php
        $businessWarning = $settingWarnings['business_calendar'] ?? [];
        $shiftWarning = $settingWarnings['staff_shifts'] ?? [];
    @endphp

    @if(
        ($businessWarning['has_alert'] ?? false) ||
        ($businessWarning['has_warning'] ?? false) ||
        ($shiftWarning['has_alert'] ?? false) ||
        ($shiftWarning['has_warning'] ?? false)
    )
        <div class="mb-8 space-y-4">

            @if($businessWarning['has_alert'] ?? false)
                <div class="rounded-3xl border border-red-200 bg-red-50 shadow-sm p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-11 h-11 rounded-full bg-red-500 text-white flex items-center justify-center font-bold text-lg">!</div>
                                <div>
                                    <h2 class="text-xl font-bold text-red-900">営業日カレンダーの警告</h2>
                                    <p class="text-sm text-red-800 mt-1">登録済みの最終日が予約可能期間内です。早めに先の日付まで設定してください。</p>
                                </div>
                            </div>

                            <div class="text-sm text-gray-700 leading-6">
                                本日：<span class="font-semibold">{{ $settingWarnings['today'] }}</span><br>
                                予約可能期間の末日：<span class="font-semibold">{{ $settingWarnings['alert_end'] }}</span><br>
                                次の1か月末：<span class="font-semibold">{{ $settingWarnings['warning_end'] }}</span><br>
                                登録済み最終日：
                                <span class="font-bold text-red-700">{{ $businessWarning['last_date'] ?? '未登録' }}</span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ route('company.calendar.index') }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                               style="background: {{ $theme }};">
                                営業日カレンダーを設定する
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($businessWarning['has_warning'] ?? false)
                <div class="rounded-3xl border border-amber-200 bg-amber-50 shadow-sm p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-11 h-11 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-lg">!</div>
                                <div>
                                    <h2 class="text-xl font-bold text-amber-900">営業日カレンダーのワーニング</h2>
                                    <p class="text-sm text-amber-800 mt-1">そろそろ設定が切れる時期です。余裕のあるうちに先の日付まで設定してください。</p>
                                </div>
                            </div>

                            <div class="text-sm text-gray-700 leading-6">
                                本日：<span class="font-semibold">{{ $settingWarnings['today'] }}</span><br>
                                予約可能期間の末日：<span class="font-semibold">{{ $settingWarnings['alert_end'] }}</span><br>
                                次の1か月末：<span class="font-semibold">{{ $settingWarnings['warning_end'] }}</span><br>
                                登録済み最終日：
                                <span class="font-bold text-amber-700">{{ $businessWarning['last_date'] ?? '未登録' }}</span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ route('company.calendar.index') }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                               style="background: {{ $theme }};">
                                営業日カレンダーを設定する
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if($shiftWarning['has_alert'] ?? false)
                <div class="rounded-3xl border border-red-200 bg-red-50 shadow-sm p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-11 h-11 rounded-full bg-red-500 text-white flex items-center justify-center font-bold text-lg">!</div>
                                <div>
                                    <h2 class="text-xl font-bold text-red-900">月シフトの警告</h2>
                                    <p class="text-sm text-red-800 mt-1">登録済みの最終日が予約可能期間内です。早めに先の日付まで設定してください。</p>
                                </div>
                            </div>

                            <div class="text-sm text-gray-700 leading-6">
                                本日：<span class="font-semibold">{{ $settingWarnings['today'] }}</span><br>
                                予約可能期間の末日：<span class="font-semibold">{{ $settingWarnings['alert_end'] }}</span><br>
                                次の1か月末：<span class="font-semibold">{{ $settingWarnings['warning_end'] }}</span><br>
                                登録済み最終日：
                                <span class="font-bold text-red-700">{{ $shiftWarning['last_date'] ?? '未登録' }}</span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ route('company.staff-shifts') }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                               style="background: {{ $theme }};">
                                月シフトを設定する
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($shiftWarning['has_warning'] ?? false)
                <div class="rounded-3xl border border-amber-200 bg-amber-50 shadow-sm p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-11 h-11 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-lg">!</div>
                                <div>
                                    <h2 class="text-xl font-bold text-amber-900">月シフトのワーニング</h2>
                                    <p class="text-sm text-amber-800 mt-1">そろそろ設定が切れる時期です。余裕のあるうちに先の日付まで設定してください。</p>
                                </div>
                            </div>

                            <div class="text-sm text-gray-700 leading-6">
                                本日：<span class="font-semibold">{{ $settingWarnings['today'] }}</span><br>
                                予約可能期間の末日：<span class="font-semibold">{{ $settingWarnings['alert_end'] }}</span><br>
                                次の1か月末：<span class="font-semibold">{{ $settingWarnings['warning_end'] }}</span><br>
                                登録済み最終日：
                                <span class="font-bold text-amber-700">{{ $shiftWarning['last_date'] ?? '未登録' }}</span>
                            </div>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ route('company.staff-shifts') }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                               style="background: {{ $theme }};">
                                月シフトを設定する
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif
@endif

<div class="bg-white shadow-lg rounded-2xl p-6 mb-12">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg sm:text-xl font-bold">企業向けお知らせ</h2>
    </div>

    <div class="space-y-3">
        @forelse($notices as $notice)
            <a href="{{ route('company.dashboard-notices.show', $notice) }}"
               class="block rounded-xl border border-gray-200 hover:bg-gray-50 transition p-4">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    @if($notice->is_important)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">重要</span>
                    @endif
                    @if($notice->is_new)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">NEW</span>
                    @endif
                    <span class="text-xs text-gray-500">
                        {{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }}
                        @if($notice->end_date)
                            〜 {{ optional($notice->end_date)->format('Y/m/d') }}
                        @endif
                    </span>
                    <span class="text-xs text-gray-400">{{ $notice->target_label }}</span>
                </div>

                <div class="font-bold text-gray-800">{{ $notice->title }}</div>
                <div class="text-sm text-gray-500 mt-1 line-clamp-2">
                    {{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 100) }}
                </div>
            </a>
        @empty
            <div class="text-sm text-gray-400 py-6 text-center">現在表示中のお知らせはありません</div>
        @endforelse
    </div>
</div>

<div class="mb-12">
    <h2 class="text-lg sm:text-xl font-bold mb-6">管理メニュー</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        @if($dashboardPermissions['card.reserve'] ?? false)
            <a href="{{ route('company.reserve') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-blue-500">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 7V3M16 7V3M4 11H20M5 5H19A2 2 0 0121 7V19A2 2 0 0119 21H5A2 2 0 013 19V7A2 2 0 015 5Z"/>
                    </svg>
                    <div class="text-blue-500 text-xs font-semibold mb-2">RESERVATION</div>
                </div>
                <div class="text-lg font-bold mb-2">予約カレンダー</div>
                <div class="text-gray-500 text-sm">予約の確認・登録・管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.business_calendar'] ?? false)
            <a href="{{ route('company.calendar.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-emerald-500">
                <div class="text-emerald-500 text-xs font-semibold mb-2">BUSINESS</div>
                <div class="text-lg font-bold mb-2">営業日カレンダー</div>
                <div class="text-gray-500 text-sm">営業日の確認・登録・管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.staff'] ?? false)
            <a href="{{ route('company.staff.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-indigo-500">
                <div class="text-indigo-500 text-xs font-semibold mb-2">STAFF</div>
                <div class="text-lg font-bold mb-2">担当者管理</div>
                <div class="text-gray-500 text-sm">担当者の登録・編集</div>
            </a>
        @endif

        @if($dashboardPermissions['card.menu_category_tag'] ?? false)
            <a href="{{ route('company.menu.settings') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-cyan-500">
                <div class="text-cyan-500 text-xs font-semibold mb-2">CATEGORY & TAG</div>
                <div class="text-lg sm:text-xl font-bold mb-2">カテゴリー・タグ管理</div>
                <div class="text-gray-500 text-sm">メニューのカテゴリー・タグの管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.menu'] ?? false)
            <a href="{{ route('company.menu.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-lime-500">
                <div class="text-lime-500 text-xs font-semibold mb-2">MENU</div>
                <div class="text-lg sm:text-xl font-bold mb-2">メニュー管理</div>
                <div class="text-gray-500 text-sm">メニューの管理・施工時間</div>
            </a>
        @endif

        @if($dashboardPermissions['card.menu_staff'] ?? false)
            <a href="{{ route('company.menu-staff.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-lime-500">
                <div class="text-lime-500 text-xs font-semibold mb-2">SKILL</div>
                <div class="text-lg sm:text-xl font-bold mb-2">メニュー対応スタッフ設定</div>
                <div class="text-gray-500 text-sm">メニューを施工できるスタッフを管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.shift_patterns'] ?? false)
            <a href="{{ route('company.shift-patterns') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-pink-500">
                <div class="text-pink-500 text-xs font-semibold mb-2">SHIFT</div>
                <div class="text-lg sm:text-xl font-bold mb-2">シフトパターン</div>
                <div class="text-gray-500 text-sm">早番・遅番・通しなどの勤務パターン設定</div>
            </a>
        @endif

        @if($dashboardPermissions['card.default_shift'] ?? false)
            <a href="{{ route('company.staff-default-shifts') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-rose-500">
                <div class="text-rose-500 text-xs font-semibold mb-2">SHIFT</div>
                <div class="text-lg sm:text-xl font-bold mb-2">基本シフト</div>
                <div class="text-gray-500 text-sm">曜日ごとの基本シフト設定</div>
            </a>
        @endif

        @if($dashboardPermissions['card.month_shift'] ?? false)
            <a href="{{ route('company.staff-shifts') }}"
               class="bg-white shadow hover:shadow-lg transition rounded-xl p-6 border-l-4 border-fuchsia-500">
                <div class="text-fuchsia-500 text-xs font-semibold mb-2">SHIFT</div>
                <div class="text-lg sm:text-xl font-bold mb-2">月シフト</div>
                <div class="text-gray-500 text-sm">月ごとの勤務シフト作成</div>
            </a>
        @endif

        @if($dashboardPermissions['card.customers'] ?? false)
            <a href="{{ route('company.customers') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-lime-500">
                <div class="text-lime-500 text-xs font-semibold mb-2">COSTOMER</div>
                <div class="text-lg sm:text-xl font-bold mb-2">顧客管理</div>
                <div class="text-gray-500 text-sm">顧客の管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.notices'] ?? false)
            <a href="{{ route('company.notices.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-emerald-500">
                <div class="text-emerald-500 text-xs font-semibold mb-2">INFOMATION</div>
                <div class="text-lg sm:text-xl font-bold mb-2">お知らせ情報管理</div>
                <div class="text-gray-500 text-sm">ＷＥＢ予約画面に表示する顧客宛てお知らせ情報の管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.vacation'] ?? false)
            <a href="{{ route('company.vacation.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-green-500">
                <div class="text-green-500 text-xs font-semibold mb-2">VACATION</div>
                <div class="text-lg font-bold mb-2">休暇管理</div>
                <div class="text-gray-500 text-sm">休暇申請・承認管理</div>
            </a>
        @endif

        @if($dashboardPermissions['card.theme'] ?? false)
            <a href="{{ route('company.theme') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-purple-500">
                <div class="text-purple-500 text-xs font-semibold mb-2">DESIGN</div>
                <div class="text-lg sm:text-xl font-bold mb-2">テーマ設定</div>
                <div class="text-gray-500 text-sm">顧客画面のカラー変更</div>
            </a>
        @endif

        @if($dashboardPermissions['card.company_info'] ?? false)
            <a href="{{ route('company.info.edit') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-orange-500">
                <div class="text-orange-500 text-xs font-semibold mb-2">COMPANY</div>
                <div class="text-lg sm:text-xl font-bold mb-2">企業情報編集</div>
                <div class="text-gray-500 text-sm">会社情報・営業時間変更</div>
            </a>
        @endif

        @if($dashboardPermissions['card.logo'] ?? false)
            <a href="{{ route('company.logo') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-gray-500">
                <div class="text-gray-500 text-xs font-semibold mb-2">BRAND</div>
                <div class="text-lg sm:text-xl font-bold mb-2">ロゴ設定</div>
                <div class="text-gray-500 text-sm">企業ロゴ変更</div>
            </a>
        @endif

        @if($dashboardPermissions['card.billing'] ?? false)
            <a href="{{ route('company.billing.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="text-yellow-500 text-xs font-semibold">BILLING</div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $subscriptionAvailable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $subscriptionAvailable ? '利用可能' : '要確認' }}
                    </span>
                </div>
                <div class="text-lg sm:text-xl font-bold mb-2">契約管理</div>
                <div class="text-sm text-gray-700 mb-2">現在の状態：{{ $subscriptionStatusLabel }}</div>
                <div class="text-gray-500 text-sm leading-6">プラン申込、カード情報の変更、請求情報の確認、解約手続きができます。</div>
                @if($billingWarning)
                    <div class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                        {{ $billingWarning }}
                    </div>
                @endif
            </a>
        @endif

        @if($dashboardPermissions['card.my_profile'] ?? false)
            <a href="{{ route('company.my-profile') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-teal-500">
                <div class="text-teal-500 text-xs font-semibold mb-2">MYPAGE</div>
                <div class="text-lg font-bold mb-2">マイプロフィール</div>
                <div class="text-gray-500 text-sm">プロフィール変更</div>
            </a>
        @endif

        @if($dashboardPermissions['dashboard.manage'] ?? false)
            <a href="{{ route('company.dashboard-settings.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-violet-500">
                <div class="text-violet-500 text-xs font-semibold mb-2">DASHBOARD</div>
                <div class="text-lg sm:text-xl font-bold mb-2">ダッシュボード管理</div>
                <div class="text-gray-500 text-sm">役職ごとの表示権限を設定</div>
            </a>
        @endif

    </div>
</div>

<div class="bg-white shadow-lg rounded-2xl p-6 mb-12">
    <div class="flex justify-between mb-6">
        <h2 class="text-lg font-bold">今日の予約</h2>
        <span class="text-xs text-gray-400">{{ now()->format('Y年m月d日') }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600">
                    <th class="p-3 text-left">時間</th>
                    <th class="p-3 text-left">顧客</th>
                    <th class="p-3 text-left">メニュー</th>
                    <th class="p-3 text-left">担当</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayReservations as $r)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}</td>
                        <td class="p-3">{{ $r->customer_name }}</td>
                        <td class="p-3">{{ $r->menus->pluck('name')->join(', ') ?: '-' }}</td>
                        <td class="p-3">{{ $r->staff->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-400 py-8">本日の予約はありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($dashboardPermissions['dashboard.sales'] ?? false)
<div class="mb-12">
    <h2 class="text-xl font-bold mb-6">売上ダッシュボード</h2>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="period" class="border rounded px-3 py-2">
            <option value="month" {{ $period=='month' ? 'selected':'' }}>月別</option>
            <option value="year" {{ $period=='year' ? 'selected':'' }}>年別</option>
        </select>

        <select name="year" class="border rounded px-3 py-2">
            @for($y = now()->year; $y >= now()->year-5; $y--)
                <option value="{{ $y }}" {{ $year==$y ? 'selected':'' }}>{{ $y }}年</option>
            @endfor
        </select>

        <select name="month" class="border rounded px-3 py-2">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>{{ $m }}月</option>
            @endfor
        </select>

        <button class="bg-gray-600 text-white px-4 py-2 rounded">表示</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">今日売上</div>
            <div class="text-3xl font-bold mt-2">¥{{ number_format($todaySales) }}</div>
        </div>
        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">今月売上</div>
            <div class="text-3xl font-bold mt-2">¥{{ number_format($monthlySales) }}</div>
        </div>
        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">今年売上</div>
            <div class="text-3xl font-bold mt-2">¥{{ number_format($yearlySales) }}</div>
        </div>
        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">客単価</div>
            <div class="text-3xl font-bold mt-2">¥{{ number_format($averagePrice) }}</div>
        </div>
    </div>

    <div class="bg-white shadow rounded-xl p-6 mb-8">
        <h3 class="font-bold mb-4">売上推移（{{ $year }}年）</h3>
        <canvas id="salesChart"></canvas>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="font-bold mb-4">スタッフ売上ランキング</h3>
            @foreach($staffRanking as $i=>$row)
                <div class="flex justify-between border-b py-2">
                    <span>{{ $i+1 }}. {{ $row->staff->name ?? '未設定' }}</span>
                    <span>¥{{ number_format($row->total) }}</span>
                </div>
            @endforeach
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="font-bold mb-4">指名ランキング</h3>
            @foreach($nominationRanking as $i=>$row)
                <div class="flex justify-between border-b py-2">
                    <span>{{ $i+1 }}. {{ $row->staff->name ?? '未設定' }}</span>
                    <span>{{ $row->nomination_count }}回</span>
                </div>
            @endforeach
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="font-bold mb-4">人気メニュー</h3>
            @foreach($menuRanking as $i=>$row)
                <div class="flex justify-between border-b py-2">
                    <span>{{ $i+1 }}. {{ $row->name }}</span>
                    <span>{{ $row->total }}回</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

</div>

<script>
const salesLabels = @json($monthlyChart->pluck('month')->values());
const salesData = @json($monthlyChart->pluck('total')->values());

@if($dashboardPermissions['dashboard.sales'] ?? false)
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: salesLabels.map(m => m + "月"),
        datasets: [{
            label: '売上',
            data: salesData,
            backgroundColor: '#3b82f6'
        }]
    }
});
@endif
</script>

@endsection