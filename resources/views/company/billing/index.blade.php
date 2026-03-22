@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">契約・お支払い管理</h1>
            <p class="text-sm text-gray-500 mt-2">
                プラン申込、カード情報の変更、請求情報の確認、解約手続きができます。
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-xl border text-sm font-semibold"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ダッシュボードへ戻る
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 shadow-sm">
            <ul class="space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-6 mb-8">
        <h2 class="text-lg font-bold mb-4">現在の契約状況</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-gray-50 p-4">
                <div class="text-xs text-gray-500 mb-1">契約状態</div>
                <div class="text-lg font-bold text-gray-900">
                    {{ $company->subscription_status_label }}
                </div>
            </div>

            <div class="rounded-2xl bg-gray-50 p-4">
                <div class="text-xs text-gray-500 mb-1">サービス利用</div>
                <div class="text-lg font-bold {{ $company->isSubscriptionAvailable() ? 'text-green-600' : 'text-red-600' }}">
                    {{ $company->isSubscriptionAvailable() ? '利用可能' : '停止中 / 要確認' }}
                </div>
            </div>

            <div class="rounded-2xl bg-gray-50 p-4">
                <div class="text-xs text-gray-500 mb-1">契約開始日</div>
                <div class="text-base font-semibold text-gray-900">
                    {{ optional($company->subscription_started_at)->format('Y/m/d') ?: '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-gray-50 p-4">
                <div class="text-xs text-gray-500 mb-1">利用終了予定日</div>
                <div class="text-base font-semibold text-gray-900">
                    {{ optional($company->subscription_ends_at)->format('Y/m/d') ?: '-' }}
                </div>
            </div>
        </div>

        @if($company->grace_until)
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
                猶予期限：{{ $company->grace_until->format('Y/m/d H:i') }}
            </div>
        @endif

        @if($company->stripe_customer_id)
            <div class="mt-5">
                <a href="{{ route('company.billing.portal') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-xl text-white font-semibold shadow hover:opacity-90 transition"
                   style="background: {{ $theme }};">
                    カード情報・請求情報を管理する
                </a>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                <div class="text-sm font-semibold mb-2" style="color: {{ $theme }}">
                    SUBSCRIPTION
                </div>

                <h3 class="text-xl font-bold text-gray-900">
                    {{ $plan['name'] }}
                </h3>

                <p class="text-gray-500 text-sm mt-2 leading-6">
                    {{ $plan['description'] }}
                </p>

                <div class="mt-5 text-3xl font-extrabold text-gray-900">
                    ¥{{ number_format($plan['amount']) }}
                    <span class="text-sm font-normal text-gray-500">/ 月</span>
                </div>

                <form action="{{ route('company.billing.checkout') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan['key'] }}">

                    <button type="submit"
                            class="w-full px-4 py-3 rounded-xl text-white font-bold shadow hover:opacity-90 transition"
                            style="background: {{ $theme }};">
                        このプランで申し込む
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection