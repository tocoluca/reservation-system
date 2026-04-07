@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

    $businessWarning = $settingWarnings['business_calendar'] ?? [];
    $shiftWarning = $settingWarnings['staff_shifts'] ?? [];

    $hasBusinessAlert = ($businessWarning['has_alert'] ?? false) || ($businessWarning['has_warning'] ?? false);
    $hasShiftAlert = ($shiftWarning['has_alert'] ?? false) || ($shiftWarning['has_warning'] ?? false);
    $hasAnySettingAlert = $hasBusinessAlert || $hasShiftAlert;

    $todayReservationCount = $todayReservations->count();
    $todayReservationLabel = now()->format('Y年m月d日');

    $todayCustomerCount = $todayReservations->pluck('customer_name')->filter()->unique()->count();

    $changePending = (int) ($changeNoticePendingCount ?? 0);
    $changePhonePending = (int) ($changeNoticePhonePendingCount ?? 0);
    $changeConfirmed = (int) ($changeNoticeConfirmedCount ?? 0);
    $changeTotalActive = $changePending + $changePhonePending;

    $can = function ($key, $default = false) use ($dashboardPermissions) {
        return (bool) ($dashboardPermissions[$key] ?? $default);
    };
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-3 py-1 text-xs font-medium text-gray-500 shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full" style="background: {{ $theme }}"></span>
                店舗運営ダッシュボード
            </div>

            <h1 class="mt-3 text-2xl sm:text-3xl font-bold text-gray-900">
                ダッシュボード
            </h1>

            <p class="text-gray-500 mt-2 text-sm sm:text-base">
                {{ $staff->company->name }} ｜ {{ $staff->name }}（{{ $staff->role }}）
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($can('card.reserve'))
                <a href="{{ route('company.reserve') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                   style="background: {{ $theme }}">
                    予約管理
                </a>
            @endif

            @if($can('card.business_calendar'))
                <a href="{{ route('company.calendar.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 font-semibold shadow-sm hover:bg-gray-50 transition">
                    営業日管理
                </a>
            @endif
        </div>
    </div>

    <div class="space-y-4 mb-8">

        @if($showSetupGuide)
            <div class="rounded-3xl border border-amber-200 bg-amber-50 shadow-sm p-5 lg:p-6">
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                    <div class="flex-1">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0"
                                 style="background: {{ $theme }}">
                                !
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-amber-900">
                                    はじめての設定がまだ完了していません
                                </h2>
                                <p class="text-sm sm:text-base text-amber-800 mt-1">
                                    予約受付をスムーズに始めるため、初期設定を進めてください。
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm sm:text-base text-gray-700">
                                必須設定
                                <span class="font-bold text-xl">{{ $setupDoneCount }} / {{ $setupTotalCount }}</span>
                                完了
                            </p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                            @foreach($setupStatusList as $item)
                                <div class="rounded-2xl border px-4 py-3 text-center {{ $item['done'] ? 'bg-green-50 border-green-200' : 'bg-white border-red-200' }}">
                                    <div class="text-xs font-bold {{ $item['done'] ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $item['done'] ? '完了' : '未完了' }}
                                    </div>
                                    <div class="mt-1 text-sm font-semibold text-gray-800">
                                        {{ $item['label'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-4 text-sm text-amber-900 leading-6">
                            まだ終わっていない項目があります。先に設定しておくと、予約カレンダーが正しく表示されやすくなります。
                        </p>
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

        @if($hasAnySettingAlert)
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @if($hasBusinessAlert)
                    <div class="rounded-3xl border {{ ($businessWarning['has_alert'] ?? false) ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }} shadow-sm p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shrink-0 {{ ($businessWarning['has_alert'] ?? false) ? 'bg-red-500' : 'bg-amber-500' }}">
                                !
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base sm:text-lg font-bold {{ ($businessWarning['has_alert'] ?? false) ? 'text-red-900' : 'text-amber-900' }}">
                                    営業日{{ ($businessWarning['has_alert'] ?? false) ? 'の警告' : 'のワーニング' }}
                                </h3>
                                <p class="text-sm mt-1 {{ ($businessWarning['has_alert'] ?? false) ? 'text-red-800' : 'text-amber-800' }}">
                                    {{ ($businessWarning['has_alert'] ?? false)
                                        ? '営業日設定した最終日が予約可能期間内です。顧客が予約を行えなくなるため、至急、営業日を設定してください。'
                                        : '営業日設定した最終日が予約可能期間内に近づいています。余裕のあるうちに営業日を設定してください。' }}
                                </p>

                                <div class="mt-4 text-sm text-gray-700 leading-6">
                                    本日：<span class="font-semibold">{{ $settingWarnings['today'] ?? '-' }}</span><br>
                                    予約可能期間の末日：<span class="font-semibold">{{ $settingWarnings['alert_end'] ?? '-' }}</span><br>
                                    次の1か月末：<span class="font-semibold">{{ $settingWarnings['warning_end'] ?? '-' }}</span><br>
                                    登録済み最終日：
                                    <span class="font-bold {{ ($businessWarning['has_alert'] ?? false) ? 'text-red-700' : 'text-amber-700' }}">
                                        {{ $businessWarning['last_date'] ?? '未登録' }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('company.calendar.index') }}"
                                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                       style="background: {{ $theme }}">
                                        営業日を設定する
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($hasShiftAlert)
                    <div class="rounded-3xl border {{ ($shiftWarning['has_alert'] ?? false) ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }} shadow-sm p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shrink-0 {{ ($shiftWarning['has_alert'] ?? false) ? 'bg-red-500' : 'bg-amber-500' }}">
                                !
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base sm:text-lg font-bold {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-900' : 'text-amber-900' }}">
                                    勤務日程{{ ($shiftWarning['has_alert'] ?? false) ? 'の警告' : 'のワーニング' }}
                                </h3>
                                <p class="text-sm mt-1 {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-800' : 'text-amber-800' }}">
                                    {{ ($shiftWarning['has_alert'] ?? false)
                                        ? '勤務日程の最終日が予約可能期間内です。顧客が予約を行えなくなるため、至急、従業員の勤務日程を設定してください。'
                                        : '勤務日程の最終日が予約可能期間に近づいています。余裕のあるうちに従業員の勤務日程を設定してください。' }}
                                </p>

                                <div class="mt-4 text-sm text-gray-700 leading-6">
                                    本日：<span class="font-semibold">{{ $settingWarnings['today'] ?? '-' }}</span><br>
                                    予約可能期間の末日：<span class="font-semibold">{{ $settingWarnings['alert_end'] ?? '-' }}</span><br>
                                    次の1か月末：<span class="font-semibold">{{ $settingWarnings['warning_end'] ?? '-' }}</span><br>
                                    登録済み最終日：
                                    <span class="font-bold {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-700' : 'text-amber-700' }}">
                                        {{ $shiftWarning['last_date'] ?? '未登録' }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('company.staff-shifts') }}"
                                       class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                       style="background: {{ $theme }}">
                                        従業員の勤務日程を設定する
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($hasChangeNoticeAlert ?? false)
            <div class="rounded-3xl border border-rose-200 bg-rose-50 shadow-sm p-5 lg:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="flex-1">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-lg shrink-0">!</div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-rose-900">予約変更連絡の未対応があります</h2>
                                <p class="text-sm text-rose-800 mt-1">
                                    店都合で変更が必要な予約のうち、まだ確認や連絡が完了していないものがあります。
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                            <div class="rounded-2xl bg-white border border-rose-100 px-4 py-3">
                                <div class="text-xs text-gray-500">確認待ち</div>
                                <div class="mt-1 text-2xl font-bold text-rose-700">
                                    {{ number_format($changePending) }}件
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white border border-amber-100 px-4 py-3">
                                <div class="text-xs text-gray-500">電話対応待ち</div>
                                <div class="mt-1 text-2xl font-bold text-amber-700">
                                    {{ number_format($changePhonePending) }}件
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white border border-green-100 px-4 py-3">
                                <div class="text-xs text-gray-500">確認済み</div>
                                <div class="mt-1 text-2xl font-bold text-green-700">
                                    {{ number_format($changeConfirmed) }}件
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <a href="{{ route('company.reservation_change_notices.index') }}"
                           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                           style="background: {{ $theme }}">
                            予約変更連絡管理を開く
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500">今日の予約</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($todayReservationCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">{{ $todayReservationLabel }}</div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500">本日の来店予定人数</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($todayCustomerCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">同一名義をまとめて集計</div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between gap-3">
                <div class="text-xs font-semibold text-gray-500">予約変更連絡</div>
                @if($changeTotalActive > 0)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                        要対応
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                        対応なし
                    </span>
                @endif
            </div>
            <div class="mt-2 text-3xl font-bold {{ $changeTotalActive > 0 ? 'text-rose-700' : 'text-gray-900' }}">
                {{ number_format($changeTotalActive) }}
            </div>
            <div class="mt-2 text-sm text-gray-500">確認待ち + 電話対応待ち</div>
        </div>

        @if($can('dashboard.sales'))
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
                <div class="text-xs font-semibold text-gray-500">今日売上</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">¥{{ number_format($todaySales) }}</div>
                <div class="mt-2 text-sm text-gray-500">当日分の集計</div>
            </div>
        @else
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
                <div class="text-xs font-semibold text-gray-500">現在のご案内</div>
                <div class="mt-2 text-lg font-bold text-gray-900">
                    {{ $notices->count() > 0 ? $notices->count().'件のお知らせ' : 'お知らせはありません' }}
                </div>
                <div class="mt-2 text-sm text-gray-500">必要な情報だけ下で確認できます</div>
            </div>
        @endif
    </div>

    <div class="mb-10">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">よく使う操作</h2>
                <p class="text-sm text-gray-500 mt-1">まずここから操作すると迷いにくくなります。</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @if($can('card.reserve'))
                <a href="{{ route('company.reserve') }}"
                   class="group rounded-3xl p-6 text-white shadow-lg hover:-translate-y-0.5 transition"
                   style="background: linear-gradient(135deg, {{ $theme }} 0%, #1f2937 100%);">
                    <div class="flex items-center justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 7V3M16 7V3M4 11H20M5 5H19A2 2 0 0121 7V19A2 2 0 0119 21H5A2 2 0 013 19V7A2 2 0 015 5Z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold bg-white/15 px-3 py-1 rounded-full">最優先</span>
                    </div>

                    <div class="mt-6 text-2xl font-bold">予約管理</div>
                    <div class="mt-2 text-sm text-white/85 leading-6">
                        予約の確認、登録、キャンセル、検索をすぐ行えます。
                    </div>

                    <div class="mt-6 inline-flex items-center text-sm font-semibold">
                        開く
                        <svg class="w-4 h-4 ml-2 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5L16 12L9 19"/>
                        </svg>
                    </div>
                </a>
            @endif

            @if($can('card.customers'))
                <a href="{{ route('company.customers') }}"
                   class="group bg-white rounded-3xl p-6 border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-lime-50 text-lime-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M17 21V19A4 4 0 0013 15H5A4 4 0 001 19V21M23 21V19A4 4 0 0019 15.13M16 3.13A4 4 0 0116 11"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-lime-700 bg-lime-50 px-3 py-1 rounded-full">顧客</span>
                    </div>

                    <div class="mt-6 text-xl font-bold text-gray-900">顧客管理</div>
                    <div class="mt-2 text-sm text-gray-500 leading-6">
                        来店履歴や顧客情報を確認し、次の提案につなげられます。
                    </div>

                    <div class="mt-6 text-sm font-semibold text-lime-700">開く</div>
                </a>
            @endif

            @if($can('card.month_shift'))
                <a href="{{ route('company.staff-shifts') }}"
                   class="group bg-white rounded-3xl p-6 border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-fuchsia-50 text-fuchsia-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 8V12L15 15M3 12A9 9 0 1021 12A9 9 0 003 12Z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-fuchsia-700 bg-fuchsia-50 px-3 py-1 rounded-full">勤務</span>
                    </div>

                    <div class="mt-6 text-xl font-bold text-gray-900">勤務管理</div>
                    <div class="mt-2 text-sm text-gray-500 leading-6">
                        従業員の勤務予定について日単位での変更や月単位での登録ができます。
                    </div>

                    <div class="mt-6 text-sm font-semibold text-fuchsia-700">開く</div>
                </a>
            @endif

            @if($can('card.business_calendar'))
                <a href="{{ route('company.calendar.index') }}"
                   class="group bg-white rounded-3xl p-6 border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 7V3M16 7V3M4 11H20M5 5H19A2 2 0 0121 7V19A2 2 0 0119 21H5A2 2 0 013 19V7A2 2 0 015 5Z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">営業日</span>
                    </div>

                    <div class="mt-6 text-xl font-bold text-gray-900">営業日管理</div>
                    <div class="mt-2 text-sm text-gray-500 leading-6">
                        営業日・営業時間・予約状況を確認しながら調整できます。
                    </div>

                    <div class="mt-6 text-sm font-semibold text-emerald-700">開く</div>
                </a>
            @endif
        </div>
    </div>

    <div class="mb-10">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">その他のよく使うメニュー</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @if($can('card.reservation_change_notices'))
                <a href="{{ route('company.reservation_change_notices.index') }}"
                   class="bg-white shadow-sm hover:shadow-md transition rounded-2xl p-5 border border-gray-200">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-rose-500 text-xs font-semibold">予約変更連絡管理</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">未対応確認</div>
                        </div>

                        @if($changeTotalActive > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700 whitespace-nowrap">
                                {{ $changeTotalActive }}件
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 whitespace-nowrap">
                                なし
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 text-sm text-gray-500 leading-6">
                        店都合で変更が必要な予約の確認・連絡状況を管理します。
                    </div>
                </a>
            @endif

            @if($can('card.reviews') && ($company->review_enabled ?? false))
                <a href="{{ route('company.reviews.index') }}"
                   class="bg-white shadow-sm hover:shadow-md transition rounded-2xl p-5 border border-gray-200">
                    <div class="text-amber-500 text-xs font-semibold">口コミ管理</div>
                    <div class="text-lg font-bold mt-2 text-gray-900">口コミの確認・返信</div>
                    <div class="mt-3 text-sm text-gray-500 leading-6">
                        投稿された口コミの確認、公開、返信を行えます。
                    </div>
                </a>
            @endif

            @if($can('card.vacation'))
                <a href="{{ route('company.vacation.index') }}"
                   class="bg-white shadow-sm hover:shadow-md transition rounded-2xl p-5 border border-gray-200">
                    <div class="text-green-500 text-xs font-semibold">休暇管理</div>
                    <div class="text-lg font-bold mt-2 text-gray-900">休暇申請・承認</div>
                    <div class="mt-3 text-sm text-gray-500 leading-6">
                        従業員の休暇管理を行えます。
                    </div>
                </a>
            @endif

            @if($can('card.my_profile'))
                <a href="{{ route('company.my-profile') }}"
                   class="bg-white shadow-sm hover:shadow-md transition rounded-2xl p-5 border border-gray-200">
                    <div class="text-teal-500 text-xs font-semibold">マイプロフィール</div>
                    <div class="text-lg font-bold mt-2 text-gray-900">個人設定</div>
                    <div class="mt-3 text-sm text-gray-500 leading-6">
                        自分のプロフィールや基本情報を変更できます。
                    </div>
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 2xl:grid-cols-3 gap-6 mb-10">
        <div class="2xl:col-span-2 bg-white shadow-sm rounded-3xl border border-gray-100 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-5">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">今日の予約</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ now()->format('Y年m月d日') }}</p>
                </div>

                @if($can('card.reserve'))
                    <a href="{{ route('company.reservations.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-2xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">
                        予約一覧を見る
                    </a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-gray-600">
                            <th class="p-3 text-left whitespace-nowrap">時間</th>
                            <th class="p-3 text-left whitespace-nowrap">顧客</th>
                            <th class="p-3 text-left whitespace-nowrap">メニュー</th>
                            <th class="p-3 text-left whitespace-nowrap">担当</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayReservations as $r)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}</td>
                                <td class="p-3 whitespace-nowrap">{{ $r->customer_name }}</td>
                                <td class="p-3 min-w-[180px]">{{ $r->menus->pluck('name')->join(', ') ?: '-' }}</td>
                                <td class="p-3 whitespace-nowrap">{{ $r->staff->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-400 py-10">本日の予約はありません</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900">企業向けお知らせ</h2>
                <span class="text-xs text-gray-400">{{ $notices->count() }}件</span>
            </div>

            <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                @forelse($notices as $notice)
                    <a href="{{ route('company.dashboard-notices.show', $notice) }}"
                       class="block rounded-2xl border border-gray-200 hover:bg-gray-50 transition p-4">
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
                        </div>

                        <div class="font-bold text-gray-800 leading-6">{{ $notice->title }}</div>
                        <div class="text-sm text-gray-500 mt-1 line-clamp-2">
                            {{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 80) }}
                        </div>
                    </a>
                @empty
                    <div class="text-sm text-gray-400 py-10 text-center">現在表示中のお知らせはありません</div>
                @endforelse
            </div>
        </div>
    </div>

    @if($can('dashboard.sales'))
        <div class="mb-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">売上ダッシュボード</h2>
                    <p class="text-sm text-gray-500 mt-1">必要な数字だけ見やすく確認できます。</p>
                </div>
            </div>

            <form method="GET" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <select name="period" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">
                        <option value="month" {{ $period=='month' ? 'selected':'' }}>月別</option>
                        <option value="year" {{ $period=='year' ? 'selected':'' }}>年別</option>
                    </select>

                    <select name="year" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">
                        @for($y = now()->year; $y >= now()->year-5; $y--)
                            <option value="{{ $y }}" {{ $year==$y ? 'selected':'' }}>{{ $y }}年</option>
                        @endfor
                    </select>

                    <select name="month" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>{{ $m }}月</option>
                        @endfor
                    </select>

                    <button class="inline-flex items-center justify-center rounded-2xl text-white font-bold px-4 py-2.5 hover:opacity-90 transition"
                            style="background: {{ $theme }}">
                        表示
                    </button>
                </div>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <div class="text-gray-500 text-sm">今日売上</div>
                    <div class="text-3xl font-bold mt-2">¥{{ number_format($todaySales) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <div class="text-gray-500 text-sm">今月売上</div>
                    <div class="text-3xl font-bold mt-2">¥{{ number_format($monthlySales) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <div class="text-gray-500 text-sm">今年売上</div>
                    <div class="text-3xl font-bold mt-2">¥{{ number_format($yearlySales) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <div class="text-gray-500 text-sm">客単価</div>
                    <div class="text-3xl font-bold mt-2">¥{{ number_format($averagePrice) }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5 sm:p-6 mb-5">
                <h3 class="font-bold text-gray-900 mb-4">売上推移（{{ $year }}年）</h3>
                <div class="w-full overflow-x-auto">
                    <div class="min-w-[560px]">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <h3 class="font-bold mb-4 text-gray-900">従業員売上ランキング</h3>
                    <div class="space-y-2">
                        @foreach($staffRanking as $i => $row)
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2">
                                <span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->staff->name ?? '未設定' }}</span>
                                <span class="text-sm font-bold text-gray-900 whitespace-nowrap">¥{{ number_format($row->total) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <h3 class="font-bold mb-4 text-gray-900">指名ランキング</h3>
                    <div class="space-y-2">
                        @foreach($nominationRanking as $i => $row)
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2">
                                <span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->staff->name ?? '未設定' }}</span>
                                <span class="text-sm font-bold text-gray-900 whitespace-nowrap">{{ $row->nomination_count }}回</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5">
                    <h3 class="font-bold mb-4 text-gray-900">人気メニュー</h3>
                    <div class="space-y-2">
                        @foreach($menuRanking as $i => $row)
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2">
                                <span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->name }}</span>
                                <span class="text-sm font-bold text-gray-900 whitespace-nowrap">{{ $row->total }}回</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">設定メニュー</h2>
                <p class="text-sm text-gray-500 mt-1">初期設定や店舗運営の各種管理はこちらから行えます。</p>
            </div>
        </div>

        <div class="space-y-8">
            <div>
                <h3 class="text-sm font-bold tracking-wide text-gray-400 uppercase mb-3">基本設定</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @if($can('card.company_info'))
                        <a href="{{ route('company.info.edit') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-orange-500 text-xs font-semibold">COMPANY</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">企業情報編集</div>
                            <div class="text-sm text-gray-500 mt-2">会社情報・営業時間変更</div>
                        </a>
                    @endif

                    @if($can('card.business_calendar'))
                        <a href="{{ route('company.calendar.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-emerald-500 text-xs font-semibold">BUSINESS</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">営業日管理</div>
                            <div class="text-sm text-gray-500 mt-2">営業日の確認・登録・管理</div>
                        </a>
                    @endif

                    @if($can('card.staff'))
                        <a href="{{ route('company.staff.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-indigo-500 text-xs font-semibold">STAFF</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">担当者管理</div>
                            <div class="text-sm text-gray-500 mt-2">担当者の登録・編集</div>
                        </a>
                    @endif

                    @if($can('card.logo'))
                        <a href="{{ route('company.logo') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-gray-500 text-xs font-semibold">BRAND</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">ロゴ設定</div>
                            <div class="text-sm text-gray-500 mt-2">企業ロゴ変更</div>
                        </a>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-wide text-gray-400 uppercase mb-3">メニュー設定</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @if($can('card.menu_category_tag'))
                        <a href="{{ route('company.menu.settings') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-cyan-500 text-xs font-semibold">CATEGORY & TAG</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">カテゴリー・タグ管理</div>
                            <div class="text-sm text-gray-500 mt-2">メニューの分類を管理</div>
                        </a>
                    @endif

                    @if($can('card.menu'))
                        <a href="{{ route('company.menu.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-lime-500 text-xs font-semibold">MENU</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">メニュー管理</div>
                            <div class="text-sm text-gray-500 mt-2">メニューの管理・施工時間</div>
                        </a>
                    @endif

                    @if($can('card.menu_staff'))
                        <a href="{{ route('company.menu-staff.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-lime-500 text-xs font-semibold">SKILL</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">メニュー対応担当者設定</div>
                            <div class="text-sm text-gray-500 mt-2">施工可能な担当者を管理</div>
                        </a>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-wide text-gray-400 uppercase mb-3">シフト・運営設定</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @if($can('card.shift_patterns'))
                        <a href="{{ route('company.shift-patterns') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-pink-500 text-xs font-semibold">SHIFT</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">シフトパターン</div>
                            <div class="text-sm text-gray-500 mt-2">早番・遅番・通しなどの勤務設定</div>
                        </a>
                    @endif

                    @if($can('card.default_shift'))
                        <a href="{{ route('company.staff-default-shifts') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-rose-500 text-xs font-semibold">SHIFT</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">基本シフト</div>
                            <div class="text-sm text-gray-500 mt-2">曜日ごとの基本シフト設定</div>
                        </a>
                    @endif

                    @if($can('card.month_shift'))
                        <a href="{{ route('company.staff-shifts') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-fuchsia-500 text-xs font-semibold">SHIFT</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">勤務管理</div>
                            <div class="text-sm text-gray-500 mt-2">従業員の勤怠を管理</div>
                        </a>
                    @endif

                    @if($can('card.notices'))
                        <a href="{{ route('company.notices.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-emerald-500 text-xs font-semibold">INFORMATION</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">お知らせ情報管理</div>
                            <div class="text-sm text-gray-500 mt-2">予約画面に表示するお知らせ情報を管理</div>
                        </a>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold tracking-wide text-gray-400 uppercase mb-3">システム管理</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @if($can('card.billing'))
                        <a href="{{ route('company.billing.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-yellow-500 text-xs font-semibold">BILLING</div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $subscriptionAvailable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $subscriptionAvailable ? '利用可能' : '要確認' }}
                                </span>
                            </div>
                            <div class="text-lg font-bold mt-2 text-gray-900">契約管理</div>
                            <div class="text-sm text-gray-700 mt-2">現在の状態：{{ $subscriptionStatusLabel }}</div>
                            <div class="text-sm text-gray-500 mt-2 leading-6">プラン申込、カード情報の変更、請求情報の確認、解約手続き</div>
                            @if($billingWarning)
                                <div class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                                    {{ $billingWarning }}
                                </div>
                            @endif
                        </a>
                    @endif

                    @if($can('card.theme'))
                        <a href="{{ route('company.theme') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-purple-500 text-xs font-semibold">DESIGN</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">テーマ設定</div>
                            <div class="text-sm text-gray-500 mt-2">顧客画面のカラー変更</div>
                        </a>
                    @endif

                    @if($can('dashboard.manage'))
                        <a href="{{ route('company.dashboard-settings.index') }}"
                           class="bg-gray-50 hover:bg-gray-100 transition rounded-2xl p-5 border border-gray-200">
                            <div class="text-violet-500 text-xs font-semibold">DASHBOARD</div>
                            <div class="text-lg font-bold mt-2 text-gray-900">ダッシュボード管理</div>
                            <div class="text-sm text-gray-500 mt-2">役職ごとの表示権限を設定</div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const salesLabels = @json($monthlyChart->pluck('month')->values());
const salesData = @json($monthlyChart->pluck('total')->values());

@if($can('dashboard.sales'))
const salesCanvas = document.getElementById('salesChart');
if (salesCanvas) {
    new Chart(salesCanvas, {
        type: 'bar',
        data: {
            labels: salesLabels.map(m => m + '月'),
            datasets: [{
                label: '売上',
                data: salesData,
                backgroundColor: '#3b82f6',
                borderRadius: 8,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '¥' + Number(value).toLocaleString();
                        }
                    }
                }
            }
        }
    });
}
@endif
</script>
@endsection