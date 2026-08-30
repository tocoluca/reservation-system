@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $isAvailable = $company->isSubscriptionAvailable();
    $isInGrace = method_exists($company, 'isInGracePeriod') ? $company->isInGracePeriod() : false;
    $hasCurrentStripePlan = filled($company->stripe_price_id);
    $isInBillingStartCampaign = method_exists($company, 'isInBillingStartCampaign') ? $company->isInBillingStartCampaign() : false;
    $billingCampaignEndsAt = $isInBillingStartCampaign ? $company->billing_starts_at : null;
    $billingCampaignRemainingDays = $billingCampaignEndsAt
        ? max(0, now()->startOfDay()->diffInDays($billingCampaignEndsAt->copy()->startOfDay(), false))
        : null;
    $graceUntil = optional($company->grace_until)->format('Y/m/d');
    $currentPeriodEnd = optional($company->current_period_end)->format('Y/m/d');
    $subscribedAt = optional($company->subscribed_at)->format('Y/m/d');

    $statusLabel = match($company->subscription_status) {
        'active' => '有効',
        'trialing' => 'トライアル中',
        'past_due' => $isInGrace ? '支払い失敗（猶予中）' : '支払い失敗（停止）',
        'canceled' => '解約済み',
        'incomplete' => '未完了',
        'incomplete_expired' => '期限切れ',
        'unpaid' => '未払い',
        default => '未契約',
    };

    $planLabel = match($company->plan_code) {
        'standard' => 'スタンダード',
        'platinum' => 'プラチナ',
        default => '未契約',
    };
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        BILLING MANAGEMENT
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-white tracking-tight">
                        契約・お支払い管理
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85 leading-relaxed">
                        プラン申込、カード情報の変更、請求情報の確認、解約手続きを行えます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-semibold text-white">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

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
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="rounded-[1.5rem] bg-amber-50/60 border border-amber-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">契約状態</div>
                    <div class="mt-2 text-lg font-bold text-gray-900">
                        {{ $statusLabel }}
                    </div>
                </div>

                <div class="rounded-[1.5rem] bg-gray-50 border border-gray-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">現在のプラン</div>
                    <div class="mt-2 text-base font-semibold text-gray-900">
                        {{ $planLabel }}
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
                        {{ $subscribedAt ?: '-' }}
                    </div>
                </div>

                <div class="rounded-[1.5rem] bg-gray-50 border border-gray-100 p-4">
                    <div class="text-xs font-semibold tracking-wide text-gray-500">利用終了予定日</div>
                    <div class="mt-2 text-base font-semibold text-gray-900">
                        {{ $currentPeriodEnd ?: '-' }}
                    </div>
                </div>
            </div>

            @if($billingCampaignEndsAt)
                <div class="mt-5 rounded-[1.5rem] border border-sky-200 bg-sky-50 text-sky-900 px-5 py-4 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold">キャンペーン期間中です</div>
                            <div class="mt-1 text-sm leading-6">
                                {{ $billingCampaignEndsAt->format('Y/m/d') }} までサービスをご利用いただけます。
                            </div>
                        </div>
                        <div class="shrink-0 rounded-2xl bg-white/80 border border-sky-100 px-4 py-3 text-center">
                            <div class="text-xs font-semibold text-sky-700">残り</div>
                            <div class="mt-1 text-2xl font-black text-sky-900">{{ $billingCampaignRemainingDays }}日</div>
                        </div>
                    </div>
                </div>
            @endif

            @if($isInGrace)
                <div class="mt-5 rounded-[1.5rem] border border-amber-200 bg-amber-50 text-amber-800 px-5 py-4 shadow-sm">
                    <div class="font-bold text-sm mb-1">お支払い更新のお願い</div>
                    <div class="text-sm leading-6">
                        お支払いの更新が確認できていません。<br>
                        利用停止予定日：{{ $graceUntil ?: '-' }}<br>
                        停止を避けるため、カード情報の更新をお願いいたします。
                    </div>
                </div>
            @endif

            @if(!$isAvailable && $company->subscription_status === 'past_due' && !$isInGrace)
                <div class="mt-5 rounded-[1.5rem] border border-red-200 bg-red-50 text-red-700 px-5 py-4 shadow-sm">
                    <div class="font-bold text-sm mb-1">システム利用を停止しています</div>
                    <div class="text-sm leading-6">
                        お支払いの更新が確認できないため、現在システムの利用を停止しています。<br>
                        カード情報をご確認のうえ、再度お支払い状態の更新をお願いいたします。
                    </div>
                </div>
            @endif

            @if(!$isAvailable && in_array($company->subscription_status, ['unpaid', 'canceled', 'incomplete_expired']))
                <div class="mt-5 rounded-[1.5rem] border border-red-200 bg-red-50 text-red-700 px-5 py-4 shadow-sm">
                    <div class="font-bold text-sm mb-1">ご契約状況をご確認ください</div>
                    <div class="text-sm leading-6">
                        現在の契約状態ではシステムをご利用いただけません。<br>
                        再開する場合は、カード情報または契約状況をご確認ください。
                    </div>
                </div>
            @endif

            @if($company->stripe_id)
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

    <div class="mb-4">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900">プラン一覧</h2>
        <p class="text-sm text-gray-500 mt-1">
            ご希望のプランを選んでお申し込みいただけます。
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($plans as $plan)
            @php
                $isCurrentPlan = $hasCurrentStripePlan
                    && filled($plan['price_id'])
                    && $company->stripe_price_id === $plan['price_id']
                    && $company->isSubscriptionAvailable();
            @endphp

            <div class="bg-white rounded-[2rem] shadow-sm p-6 border {{ $isCurrentPlan ? 'border-amber-300 ring-2 ring-amber-100' : 'border-gray-100' }} hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold" style="color: {{ $theme }}">
                            SUBSCRIPTION
                        </div>

                        <h3 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900">
                            {{ $plan['name'] }}
                        </h3>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if($isCurrentPlan)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                現在ご利用中
                            </span>
                        @endif

                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-xl">
                            💳
                        </div>
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

                @if($isCurrentPlan)
                    <div class="mt-6 w-full px-4 py-3.5 rounded-2xl bg-gray-100 text-gray-500 text-center font-bold">
                        現在ご利用中のプランです
                    </div>
                @else
                    <form action="{{ route('company.billing.checkout') }}" method="POST" class="mt-6">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan['key'] }}">

                        <button type="submit"
                                class="w-full px-4 py-3.5 rounded-2xl text-white font-bold shadow-sm hover:opacity-90 transition"
                                style="background: {{ $theme }};">
                            このプランで申し込む
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
