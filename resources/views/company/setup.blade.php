@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#7c3aed';

    $stepStaff       = collect($setupSteps)->firstWhere('key', 'staff');
    $stepCompanyInfo = collect($setupSteps)->firstWhere('key', 'company_info');
    $stepMenu        = collect($setupSteps)->firstWhere('key', 'menu');
    $stepShift       = collect($setupSteps)->firstWhere('key', 'shift');
    $stepReserve     = collect($setupSteps)->firstWhere('key', 'reserve');
    $requiredPercent = $requiredTotalCount > 0
        ? (int) floor(($requiredDoneCount / $requiredTotalCount) * 100)
        : 0;

    $nextStep = collect($setupSteps)->first(function ($step) {
        return !($step['done'] ?? false) && ($step['required'] ?? false);
    });
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        SETUP GUIDE
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold text-white tracking-tight">
                        はじめての設定ガイド
                    </h1>

                    <p class="mt-3 text-sm sm:text-base lg:text-lg text-white/85 leading-7">
                        予約受付を始めるために、最初に行う設定を順番にまとめています。<br class="hidden sm:block">
                        上から順に進めれば大丈夫です。設定はあとから変更できます。
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

    {{-- 1. 利用プラン --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 shadow-sm"
                     style="background: {{ $theme }}">
                    1
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">利用プランを選択する</h2>
                    <p class="mt-2 text-gray-600 leading-7">
                        最初に利用するプランを選択してください。選択したプランに合わせて必要な設定だけをご案内します。
                    </p>
                </div>
            </div>

            <span class="shrink-0 text-sm px-4 py-2 rounded-full font-bold
                {{ $planSelected ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                {{ $planSelected ? '選択済み' : '未選択' }}
            </span>
        </div>

        @if($canChangePlan)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    @php($selected = $company->plan_code === $plan['key'])
                    <form method="POST" action="{{ route('company.setup.plan') }}"
                          class="rounded-2xl border p-5 flex flex-col {{ $selected ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        @csrf
                        <input type="hidden" name="plan_code" value="{{ $plan['key'] }}">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $plan['name'] }}</h3>
                                <p class="mt-1 text-lg font-extrabold" style="color: {{ $theme }}">
                                    ¥{{ number_format($plan['amount']) }}<span class="text-xs font-semibold text-gray-500"> / 月（{{ ($plan['tax_included'] ?? false) ? '税込' : '税抜' }}）</span>
                                </p>
                            </div>
                            @if($selected)
                                <span class="shrink-0 text-xs px-3 py-1 rounded-full bg-green-100 text-green-700 font-bold">選択中</span>
                            @endif
                        </div>

                        <p class="mt-4 text-sm text-gray-600 leading-6 flex-1">{{ $plan['description'] }}</p>

                        @if($plan['key'] === 'light')
                            <p class="mt-3 text-xs text-gray-500 leading-5">担当者・シフト設定なしで、営業時間とメニューの設定だけで始められます。</p>
                        @endif

                        <button type="submit"
                                class="mt-5 w-full px-5 py-3 rounded-xl font-semibold {{ $selected ? 'border bg-white' : 'text-white shadow-sm' }}"
                                style="{{ $selected ? 'border-color: '.$theme.'; color: '.$theme : 'background: '.$theme }}">
                            {{ $selected ? 'このプランを選択中' : 'このプランを選択' }}
                        </button>
                    </form>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4">
                <div class="text-xs font-bold text-green-700">選択中のプラン</div>
                <div class="mt-1 text-xl font-bold text-gray-900">{{ $selectedPlanName }}</div>
                <p class="mt-2 text-sm text-gray-600">初期設定完了後または契約中のプラン変更は、契約管理画面から行ってください。</p>
            </div>
        @endif
    </div>

    @if(!$planSelected)
        <div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 sm:p-6 text-amber-800 font-semibold">
            プランを選択すると、そのプランに必要な初期設定が表示されます。
        </div>
    @else

    {{-- 進捗 --}}
    <div class="rounded-[2rem] border p-5 sm:p-6 lg:p-8 mb-6 shadow-sm
        {{ $allRequiredCompleted ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200' }}">
        <div class="grid grid-cols-1 xl:grid-cols-[1.2fr_0.8fr] gap-6">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold {{ $allRequiredCompleted ? 'text-green-700' : 'text-amber-700' }}">
                    初期設定の進捗
                </h2>

                <div class="mt-4 flex items-end gap-3">
                    <div class="text-gray-700 text-base lg:text-lg">
                        必須設定
                    </div>
                    <div class="text-3xl lg:text-4xl font-extrabold text-gray-900">
                        {{ $requiredDoneCount }} / {{ $requiredTotalCount }}
                    </div>
                    <div class="text-sm font-semibold text-gray-500 mb-1">
                        完了
                    </div>
                </div>

                <div class="mt-4">
                    <div class="w-full h-3 rounded-full bg-white/80 overflow-hidden border border-white/70">
                        <div class="h-full rounded-full transition-all duration-300"
                             style="width: {{ $requiredPercent }}%; background: {{ $theme }};"></div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        進捗率 {{ $requiredPercent }}%
                    </div>
                </div>

                @if($allRequiredCompleted)
                    <div class="mt-4 rounded-2xl bg-white/70 border border-green-200 px-4 py-3">
                        <p class="text-sm lg:text-base text-green-700 font-semibold">
                            必須の初期設定は完了しています。予約受付を開始できます。
                        </p>
                    </div>
                @else
                    <div class="mt-4 rounded-2xl bg-white/70 border border-amber-200 px-4 py-3">
                        <p class="text-sm lg:text-base text-amber-800 font-semibold">
                            まだ未完了の設定があります。下の「未完了」項目から進めてください。
                        </p>
                    </div>
                @endif

                @if($nextStep)
                    <div class="mt-4 rounded-2xl bg-white border border-gray-200 px-4 py-4">
                        <div class="text-xs font-semibold text-gray-500">次に進めるおすすめ</div>
                        <div class="mt-1 text-lg font-bold text-gray-900">{{ $nextStep['label'] }}</div>
                    </div>
                @endif
            </div>

            <div>
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-2 gap-3">
                    @foreach($setupSteps as $step)
                        @if($step['required'])
                            <div class="px-4 py-4 rounded-2xl border text-center shadow-sm
                                {{ $step['done'] ? 'bg-green-50 border-green-200' : 'bg-white border-red-200' }}">
                                <div class="text-xs font-bold {{ $step['done'] ? 'text-green-700' : 'text-red-600' }}">
                                    {{ $step['done'] ? '完了' : '未完了' }}
                                </div>
                                <div class="mt-2 text-sm text-gray-800 font-semibold">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 進め方 --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow-sm"
                 style="background: {{ $theme }}">
                ★
            </div>
            <h2 class="text-xl lg:text-2xl font-bold text-gray-900">
                まずはこの順番で進めてください
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 {{ $isLight ? 'xl:grid-cols-4' : 'xl:grid-cols-6' }} gap-4">
            @foreach(collect($setupSteps)->where('required', true) as $card)
                <div class="rounded-2xl border px-5 py-5 shadow-sm
                    {{ $card['done'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-bold" style="color: {{ $theme }}">STEP {{ $card['step'] }}</div>
                        <span class="text-xs px-3 py-1 rounded-full font-bold
                            {{ $card['done'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $card['done'] ? '完了' : '未完了' }}
                        </span>
                    </div>

                    <div class="text-lg font-bold text-gray-900 mb-2">{{ $card['label'] }}</div>
                    <p class="text-sm text-gray-600 leading-6">{{ $card['description'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
            <p class="text-sm lg:text-base text-amber-800 leading-7">
                @if($isLight)
                    <span class="font-bold">ライトプランでは担当者・シフトの設定は不要です。</span><br>
                    企業情報と営業時間を設定してから、予約メニューを登録してください。
                @else
                    <span class="font-bold">迷ったときは、まず「担当者」から進めてください。</span><br>
                    先に担当者を登録しておくと、その後のメニュー設定やシフト設定が進めやすくなります。
                @endif
            </p>
        </div>
    </div>

    <div class="space-y-6">

        @unless($isLight)
        {{-- 2. 担当者 --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 shadow-sm"
                         style="background: {{ $theme }}">
                        2
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">担当者を設定する</h2>
                        <p class="mt-2 text-gray-600 leading-7">
                            担当者一覧を確認し、必要に応じて予約を受けるスタッフを登録します。1名で利用する場合は、一覧の確認だけで完了です。
                        </p>
                    </div>
                </div>

                <span class="shrink-0 text-sm px-4 py-2 rounded-full font-bold
                    {{ $stepStaff['done'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ $stepStaff['done'] ? '完了' : '未完了' }}
                </span>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">担当者管理</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        スタッフの登録、編集、権限設定を行います。この画面を開くと初期設定の確認が完了します。
                    </p>

                    <div class="mt-5">
                        <a href="{{ route('company.staff.index') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl text-white font-semibold shadow-sm"
                           style="background: {{ $theme }}">
                            担当者管理を開く
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900">マイプロフィール</h3>
                        <span class="text-xs px-3 py-1 rounded-full bg-gray-200 text-gray-700 font-bold">
                            任意
                        </span>
                    </div>

                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        自分のプロフィール情報を設定できます。初期設定の完了には含まれません。
                    </p>

                    <div class="mt-4">
                        <span class="inline-flex text-xs px-3 py-1 rounded-full font-bold bg-gray-200 text-gray-700">
                            あとから設定できます
                        </span>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('company.my-profile') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl border font-semibold"
                           style="border-color: {{ $theme }}; color: {{ $theme }}">
                            マイプロフィールを開く
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        {{-- 企業情報 --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 shadow-sm"
                         style="background: {{ $theme }}">
                        {{ $isLight ? 2 : 3 }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">企業情報を設定する</h2>
                        <p class="mt-2 text-gray-600 leading-7">
                            予約を受け付けるための基本設定です。
                        </p>
                    </div>
                </div>

                <span class="shrink-0 text-sm px-4 py-2 rounded-full font-bold
                    {{ $stepCompanyInfo['done'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ $stepCompanyInfo['done'] ? '完了' : '未完了' }}
                </span>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">企業情報編集</h3>
                    <ul class="space-y-2 text-sm lg:text-base text-gray-700 leading-7">
                        <li>・予約可能期間</li>
                        <li>・予約受付開始日</li>
                        <li>・予約締切時間</li>
                        <li class="font-semibold text-red-600">・曜日ごとの営業時間</li>
                        <li>・予約カレンダーの刻み時間</li>
                    </ul>

                    <div class="mt-4 rounded-xl bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600 leading-6">
                        予約カレンダーに正しく反映するため、曜日ごとの営業時間を設定してください。
                        <span class="font-semibold text-red-600">定休日の曜日でも、祝日なら営業する場合は営業時間の設定が必要です。</span>
                        祝日も休みの場合は、その曜日の営業時間を設定する必要はありません。
                        営業時間が未設定の曜日は、営業日カレンダーで営業日にしても予約を受け付けできません。
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('company.info.edit') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl text-white font-semibold shadow-sm"
                           style="background: {{ $theme }}">
                            企業情報編集を開く
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">営業日カレンダー</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        営業日や営業時間の変更を行う画面です。
                    </p>

                    <div class="mt-4 rounded-xl bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600 leading-6">
                        年末年始やＧＷなど臨時休業や営業時間の変更ができます。
                        また、定休日が祝日となった場合に営業日に変更することができますが、
                        企業情報編集の曜日ごとの営業時間で、定休日の曜日にも営業時間を設定しておく必要があります。
                        設定していない場合、このカレンダーで営業日にしても予約カレンダーに表示されません。
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('company.calendar.index') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl border font-semibold"
                           style="border-color: {{ $theme }}; color: {{ $theme }}">
                            営業日カレンダーを開く
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- メニュー --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 shadow-sm"
                         style="background: {{ $theme }}">
                        {{ $isLight ? 3 : 4 }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">メニューを設定する</h2>
                        <p class="mt-2 text-gray-600 leading-7">
                            お客様が選ぶメニューの内容を登録します。
                        </p>
                    </div>
                </div>

                <span class="shrink-0 text-sm px-4 py-2 rounded-full font-bold
                    {{ $stepMenu['done'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ $stepMenu['done'] ? '完了' : '未完了' }}
                </span>
            </div>

            <div class="grid {{ $isLight ? 'lg:grid-cols-2' : 'lg:grid-cols-3' }} gap-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">カテゴリー・タグ管理</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        メニューの種類分けを登録します。
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('company.menu.settings') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl border font-semibold"
                           style="border-color: {{ $theme }}; color: {{ $theme }}">
                            カテゴリー・タグ管理を開く
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">メニュー管理</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        メニュー名、カテゴリ、時間、金額を設定します。
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('company.menu.index') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl text-white font-semibold shadow-sm"
                           style="background: {{ $theme }}">
                            メニュー管理を開く
                        </a>
                    </div>
                </div>

                @unless($isLight)
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">メニュー対応スタッフ</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        どのスタッフがそのメニューを担当できるか設定します。
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('company.menu-staff.index') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl border font-semibold"
                           style="border-color: {{ $theme }}; color: {{ $theme }}">
                            メニュー対応スタッフを開く
                        </a>
                    </div>
                </div>
                @endunless
            </div>
        </div>

        @unless($isLight)
        {{-- 5. シフト --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 shadow-sm"
                         style="background: {{ $theme }}">
                        5
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">シフトを設定する</h2>
                        <p class="mt-2 text-gray-600 leading-7">
                            スタッフが対応できる時間を設定します。
                        </p>
                    </div>
                </div>

                <span class="shrink-0 text-sm px-4 py-2 rounded-full font-bold
                    {{ $stepShift['done'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ $stepShift['done'] ? '完了' : '未完了' }}
                </span>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">シフトパターン</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        早番・通常・遅番などの種類を設定します。
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('company.shift-patterns') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl border font-semibold"
                           style="border-color: {{ $theme }}; color: {{ $theme }}">
                            シフトパターンを開く
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">基本シフト</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        シフトパターンを基に毎週の基本的な勤務予定を設定します。
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('company.staff-default-shifts') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl border font-semibold"
                           style="border-color: {{ $theme }}; color: {{ $theme }}">
                            基本シフトを開く
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">月シフト</h3>
                    <p class="text-sm lg:text-base text-gray-700 leading-7">
                        月ごとの勤務予定を設定します。基本シフトをもとに作成できます。
                    </p>
                    <div class="mt-5">
                        <a href="{{ route('company.staff-shifts') }}"
                           class="inline-flex items-center px-5 py-3 rounded-xl text-white font-semibold shadow-sm"
                           style="background: {{ $theme }}">
                            月シフトを開く
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endunless

        {{-- 最後の確認 --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 sm:p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold shrink-0 shadow-sm"
                         style="background: {{ $theme }}">
                        {{ $isLight ? 4 : 6 }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">最後に予約カレンダーを確認する</h2>
                        <p class="mt-2 text-gray-600 leading-7">
                            設定後に予約カレンダーを開いて、予約受付の準備ができているか確認してください。
                        </p>
                    </div>
                </div>

                <span class="shrink-0 text-sm px-4 py-2 rounded-full font-bold
                    {{ $stepReserve['done'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ $stepReserve['done'] ? '完了' : '未完了' }}
                </span>
            </div>

            <div class="rounded-2xl bg-blue-50 border border-blue-100 px-5 py-4 mb-5">
                <p class="text-sm lg:text-base text-blue-800 leading-7">
                    予約カレンダーに表示されない場合は、<br>
                    @if($isLight)
                        <span class="font-bold">「企業情報・営業時間」→「メニュー」</span>
                        の順で見直すと確認しやすいです。
                    @else
                        <span class="font-bold">「担当者」→「企業情報」→「シフト」</span>
                        の順で見直すと確認しやすいです。
                    @endif
                </p>
            </div>

            @if($allRequiredCompleted)
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 mb-5">
                    <p class="text-sm lg:text-base text-green-700 font-semibold leading-7">
                        必須の初期設定は完了しています。予約受付を開始できます。
                    </p>
                </div>
            @else
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 mb-5">
                    <p class="text-sm lg:text-base text-amber-800 font-semibold leading-7">
                        まだ未完了の設定があります。上の「未完了」表示の項目から進めてください。
                    </p>
                </div>
            @endif

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('company.reserve') }}"
                   class="inline-flex items-center px-6 py-3 rounded-xl text-white font-semibold shadow-sm"
                   style="background: {{ $theme }}">
                    予約カレンダーを開く
                </a>

                <form method="POST" action="{{ route('company.setup.complete') }}">
                    @csrf
                    <button type="submit" @disabled(!$allRequiredCompleted)
                            class="px-6 py-3 rounded-xl border font-semibold bg-white disabled:opacity-50 disabled:cursor-not-allowed"
                            style="border-color: {{ $theme }}; color: {{ $theme }}">
                        ガイド確認完了
                    </button>
                </form>
            </div>
        </div>

    </div>
    @endif
</div>
@endsection
