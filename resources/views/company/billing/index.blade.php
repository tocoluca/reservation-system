@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $isAvailable = $company->isSubscriptionAvailable();
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-amber-50 via-white to-rose-50 shadow-sm mb-6">
        <div class="absolute inset-x-0 top-0 h-1.5" style="background: {{ $theme }};"></div>

        <div class="px-5 sm:px-8 py-6 sm:py-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        BILLING MANAGEMENT
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        契約・お支払い管理
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-gray-600 leading-relaxed">
                        プラン申込、カード情報の変更、請求情報の確認、解約手続きを行えます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white text-sm font-semibold shadow-sm border hover:bg-gray-50 transition"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- エラー表示 --}}
    @if($errors->any())
        <div class="mb-6 rounded-[1.5rem] border border-red-200 bg-red-50 text-red-700 px-5 py-4 shadow-sm">
            <div class="font-bold text-sm mb-2">入力内容をご確認ください</div>
            <ul class="space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 現在の契約状況 --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-white to-gray-50">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">現在の契約状況</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        現在の利用状態や契約期間を確認できます。
                    </p>
                </div>

                <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                    {{ $isAvailable ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                    {{ $isAvailable ? 'サービス利用可能' : '停止中 / 要確認' }}
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-[1.5rem] bg-amber-50/60 border border-amber-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">契約状態</div>
                    <div class="mt-2 text-lg font-bold text-gray-900">
                        {{ $company->subscription_status_label }}
                    </div>
                </div>

                <div class="rounded-[1.5rem] bg-gray-50 border border-gray-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">サービス利用</div>
                    <div class="mt-2 text-lg font-bold {{ $isAvailable ? 'text-green-600' : 'text-red-600' }}">
                        {{ $isAvailable ? '利用可能' : '停止中 / 要確認' }}
                    </div>
                </div>

                <div class="rounded-[1.5rem] bg-gray-50 border border-gray-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">契約開始日</div>
                    <div class="mt-2 text-base font-semibold text-gray-900">
                        {{ optional($company->subscription_started_at)->format('Y/m/d') ?: '-' }}
                    </div>
                </div>

                <div class="rounded-[1.5rem] bg-gray-50 border border-gray-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">利用終了予定日</div>
                    <div class="mt-2 text-base font-semibold text-gray-900">
                        {{ optional($company->subscription_ends_at)->format('Y/m/d') ?: '-' }}
                    </div>
                </div>
            </div>

            @if($company->grace_until)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
                    <span class="font-semibold">猶予期限：</span>{{ $company->grace_until->format('Y/m/d H:i') }}
                </div>
            @endif

            @if($company->stripe_customer_id)
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.billing.portal') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-semibold shadow-sm hover:opacity-90 transition"
                       style="background: {{ $theme }};">
                        カード情報・請求情報を管理する
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- プラン一覧 --}}
    <div class="mb-4">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900">プラン一覧</h2>
        <p class="text-sm text-gray-500 mt-1">
            ご希望のプランを選んでお申し込みいただけます。
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-[2rem] shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold" style="color: {{ $theme }}">
                            SUBSCRIPTION
                        </div>

                        <h3 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900">
                            {{ $plan['name'] }}
                        </h3>
                    </div>

                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-xl">
                        💳
                    </div>
                </div>

                <p class="text-gray-500 text-sm mt-3 leading-6 min-h-[72px]">
                    {{ $plan['description'] }}
                </p>

                <div class="mt-5 rounded-[1.5rem] bg-gray-50 border border-gray-100 px-4 py-4">
                    <div class="text-3xl font-extrabold text-gray-900">
                        ¥{{ number_format($plan['amount']) }}
                        <span class="text-sm font-normal text-gray-500">/ 月（税抜）</span>
                    </div>
                </div>

                <form action="{{ route('company.billing.checkout') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan['key'] }}">

                    <button type="submit"
                            class="w-full px-4 py-3.5 rounded-2xl text-white font-bold shadow-sm hover:opacity-90 transition"
                            style="background: {{ $theme }};">
                        このプランで申し込む
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection