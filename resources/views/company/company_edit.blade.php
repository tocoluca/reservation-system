@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
    $isPlatinumPlan = ($company->plan_code ?? null) === 'platinum';
    $notificationChannel = old('customer_notification_channel', $company->customerNotificationChannel());

    $days = [0=>'日',1=>'月',2=>'火',3=>'水',4=>'木',5=>'金',6=>'土'];
    $patterns = old('open_patterns', $company->open_patterns ?? []);
@endphp

<style>
.tooltip {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    z-index: 30;
}
.tooltip .tooltip-text {
    visibility: hidden;
    width: 280px;
    background: #292524;
    color: #fff;
    text-align: left;
    padding: 10px 12px;
    border-radius: 12px;
    position: absolute;
    z-index: 9999;
    bottom: 135%;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    line-height: 1.6;
    opacity: 0;
    transition: .2s;
    box-shadow: 0 12px 30px rgba(0,0,0,.15);
    pointer-events: none;
    white-space: normal;
}
.tooltip.active .tooltip-text {
    visibility: visible;
    opacity: 1;
}

.tooltip:focus-visible {
    outline: 2px solid {{ $theme }};
    outline-offset: 3px;
    border-radius: 9999px;
}

.company-settings-form input:not([type="checkbox"]):not([type="hidden"]),
.company-settings-form select,
.company-settings-form textarea {
    min-height: 48px;
    border-color: #d6d3d1;
    background-color: #fff;
    transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
}

.company-settings-form input:not([type="checkbox"]):not([type="hidden"]):hover,
.company-settings-form select:hover,
.company-settings-form textarea:hover {
    border-color: #a8a29e;
}

.company-settings-form input:not([type="checkbox"]):not([type="hidden"]):focus,
.company-settings-form select:focus,
.company-settings-form textarea:focus {
    border-color: {{ $theme }};
    box-shadow: 0 0 0 3px {{ $themeSoft }};
    outline: none;
}

.settings-anchor-nav {
    scrollbar-width: none;
}
.settings-anchor-nav::-webkit-scrollbar {
    display: none;
}
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
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
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Company Settings</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">企業情報設定</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        予約受付に必要な基本情報、予約設定、営業時間をまとめて管理できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._storefront_settings_nav', ['current' => 'info'])
    </div>

    {{-- ガイド --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">設定の流れ</h2>
                <p class="text-sm text-gray-500 mt-1">
                    連絡先、予約画面の掲載内容、機能設定、予約条件、営業時間の順に確認するとスムーズです。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">1. 基本情報</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">2. 掲載情報</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">3. 機能設定</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">4. 予約設定</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">5. 営業時間</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">入力内容をご確認ください。</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="sticky top-24 z-30 mb-6 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="settings-anchor-nav -mx-1 flex gap-2 overflow-x-auto px-1 pb-1 lg:pb-0" aria-label="設定項目へ移動">
                <a href="#company-basic" class="shrink-0 rounded-2xl bg-stone-100 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-200">基本情報</a>
                <a href="#company-public" class="shrink-0 rounded-2xl bg-stone-100 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-200">掲載情報</a>
                <a href="#company-features" class="shrink-0 rounded-2xl bg-stone-100 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-200">機能・通知</a>
                <a href="#company-reserve" class="shrink-0 rounded-2xl bg-stone-100 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-200">予約設定</a>
                <a href="#company-hours" class="shrink-0 rounded-2xl bg-stone-100 px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-200">営業時間</a>
            </div>

            <div class="flex items-center gap-3">
                <span id="unsavedNotice" class="hidden text-xs font-bold text-amber-700" role="status" aria-live="polite">
                    ● 未保存の変更あり
                </span>
                <button type="submit"
                        form="companyInfoForm"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-white shadow-sm sm:flex-none"
                        style="background: var(--company-theme-gradient);">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    変更を保存
                </button>
            </div>
        </div>
    </div>

    <form id="companyInfoForm" method="POST" action="{{ route('company.info.update') }}" class="company-settings-form space-y-6">
        @csrf

        {{-- 基本情報 --}}
        <section id="company-basic" class="scroll-mt-40 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-visible relative">
            <div class="px-6 py-4 border-b border-gray-100 relative z-10"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900">基本情報</h2>
                <p class="text-sm text-gray-500 mt-1">企業の連絡先や公開設定に関わる基本項目です。</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-500 mb-2">企業コード</label>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 font-semibold text-stone-800">
                        {{ $company->company_code }}
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-2 flex items-center gap-2">
                        メールアドレス
                        <span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                tocolucaから貴社へメールにて連絡する際のアドレスです。	
                            </span>
                        </span>
                    </label>
                    <input type="email"
                           name="email"
                           value="{{ old('email',$company->email) }}"
                           class="w-full border rounded-2xl p-3 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('email')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">電話番号</label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone',$company->phone) }}"
                           class="w-full border rounded-2xl p-3 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('phone')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">住所</label>
                    <input type="text"
                           name="address"
                           value="{{ old('address',$company->address) }}"
                           class="w-full border rounded-2xl p-3 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('address')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </section>

        {{-- 予約画面掲載情報 --}}
        <section id="company-public" class="scroll-mt-40 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-visible relative">
            <div class="px-6 py-4 border-b border-gray-100 relative z-10"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">予約画面の掲載情報</h2>
                        <p class="text-sm text-gray-500 mt-1">お客様が予約前に確認するサロン紹介・来店案内です。</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">予約画面に公開</span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

				<div class="md:col-span-2">
				    <label class="block font-semibold mb-2">サロンからのメッセージ</label>
				    <textarea
				        name="salon_message"
				        rows="4"
				        class="w-full border rounded-2xl p-3 focus:ring-2"
				        style="--tw-ring-color: {{ $theme }};"
				        placeholder="例：落ち着いた空間で、ゆっくりお過ごしいただけるサロンです。髪のお悩みやご希望を丁寧に伺います。"
				    >{{ old('salon_message', $company->salon_message) }}</textarea>
				    <p class="text-xs text-gray-500 mt-2">予約画面のサロン紹介として表示します。</p>
				    @error('salon_message')
				        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
				    @enderror
				</div>

				<div class="md:col-span-2">
				    <label class="block font-semibold mb-2">営業時間（表示用）</label>
				    <textarea
				        name="business_hours_text"
				        rows="3"
				        class="w-full border rounded-2xl p-3 focus:ring-2"
				        style="--tw-ring-color: {{ $theme }};"
				        placeholder="例：平日 9:00〜18:00&#10;土日祝 9:00〜17:00&#10;定休日：毎週火曜日"
				    >{{ old('business_hours_text', $company->business_hours_text) }}</textarea>
				    <p class="text-xs text-gray-500 mt-2">予約画面に表示する案内文です。曜日別営業時間の内部設定とは別です。</p>
				    @error('business_hours_text')
				        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
				    @enderror
				</div>

				<div>
				    <label class="block font-semibold mb-2">駐車場案内</label>
				    <textarea
				        name="parking_info"
				        rows="3"
				        class="w-full border rounded-2xl p-3 focus:ring-2"
				        style="--tw-ring-color: {{ $theme }};"
				        placeholder="例：店舗前に2台ございます。P-22、P-23 をご利用ください。"
				    >{{ old('parking_info', $company->parking_info) }}</textarea>
				    @error('parking_info')
				        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
				    @enderror
				</div>

				<div>
				    <label class="block font-semibold mb-2">支払い方法</label>
				    <textarea
				        name="payment_methods"
				        rows="3"
				        class="w-full border rounded-2xl p-3 focus:ring-2"
				        style="--tw-ring-color: {{ $theme }};"
				        placeholder="例：現金／Visa／Mastercard／PayPay／楽天Pay／交通系IC"
				    >{{ old('payment_methods', $company->payment_methods) }}</textarea>
				    @error('payment_methods')
				        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
				    @enderror
				</div>

				<div>
				    <label class="block font-semibold mb-2">アクセス案内</label>
				    <textarea
				        name="access_info"
				        rows="3"
				        class="w-full border rounded-2xl p-3 focus:ring-2"
				        style="--tw-ring-color: {{ $theme }};"
				        placeholder="例：〇〇駅から徒歩5分です。店舗前に看板がございます。"
				    >{{ old('access_info', $company->access_info) }}</textarea>
				    @error('access_info')
				        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
				    @enderror
				</div>

				<div>
				    <label class="block font-semibold mb-2">メモ</label>
				    <textarea
				        name="salon_note"
				        rows="3"
				        class="w-full border rounded-2xl p-3 focus:ring-2"
				        style="--tw-ring-color: {{ $theme }};"
				        placeholder="例：妊娠中や体調が優れないお客様に関しましては薬剤等使用する施術はご希望に添えない場合がございますのでご了承下さい。"
				    >{{ old('salon_note', $company->salon_note) }}</textarea>
				    @error('salon_note')
				        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
				    @enderror
				</div>

            </div>
        </section>

        {{-- 機能・通知設定 --}}
        <section id="company-features" class="scroll-mt-40 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-visible relative">
            <div class="px-6 py-4 border-b border-gray-100 relative z-10"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900">機能・通知設定</h2>
                <p class="text-sm text-gray-500 mt-1">口コミ、自動割当、LINE通知などの利用状態を管理します。</p>
            </div>

            <div class="p-6 grid grid-cols-1 gap-6">
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2 flex items-center gap-2">
                        口コミ機能
                        <span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                ONにすると、口コミ投稿・口コミ管理機能を利用できます。OFFにすると口コミ関連機能は非表示になります。
                            </span>
                        </span>
                    </label>

                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
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
                                    ON にすると、口コミ投稿の受付や口コミ管理が利用できます。OFF にすると、口コミ関連機能は非表示になります。
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('review_enabled')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2 flex items-center gap-2">
                        複数メニュー予約時の担当自動割当
                        <span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                ONにすると、複数メニュー予約時に担当できるメニュー数が少ないスタッフを優先して自動割当します。
                            </span>
                        </span>
                    </label>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <label class="flex items-start gap-3">
                            <input type="hidden" name="prefer_less_capable_staff_for_menu_assignment" value="0">
                            <input type="checkbox"
                                   name="prefer_less_capable_staff_for_menu_assignment"
                                   value="1"
                                   class="mt-1 h-5 w-5 rounded border-amber-300"
                                   {{ old('prefer_less_capable_staff_for_menu_assignment', $company->prefer_less_capable_staff_for_menu_assignment ?? false) ? 'checked' : '' }}>
                            <div>
                                <div class="font-semibold text-amber-900">担当可能メニュー数が少ないスタッフを優先して自動割当する</div>
                                <div class="text-sm text-amber-700 mt-1 leading-6">
                                    特定メニューしか対応できないスタッフを先に活かし、幅広く対応できるスタッフは後順位にします。
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('prefer_less_capable_staff_for_menu_assignment')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2 flex items-center gap-2">
                        LINE機能の利用状況
                        <span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                LINEログイン・LINE通知の設定は管理者が行います。この画面では現在の利用状況のみ確認できます。
                            </span>
                        </span>
                    </label>

                    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-semibold text-violet-950">LINEログイン</span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-bold {{ $isPlatinumPlan ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}">
                                {{ $isPlatinumPlan ? '利用中' : '利用していません' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="font-semibold text-violet-950">LINE通知機能</span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-bold {{ $isPlatinumPlan ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600' }}">
                                {{ $isPlatinumPlan ? '利用中' : '利用していません' }}
                            </span>
                        </div>

                        @if($isPlatinumPlan)
                            <div class="border-t border-violet-200 pt-3">
                                <label for="customer_notification_channel" class="block font-semibold text-violet-950">
                                    顧客宛て通知方法
                                </label>
                                <select id="customer_notification_channel"
                                        name="customer_notification_channel"
                                        class="mt-2 w-full rounded-xl border border-violet-200 bg-white px-3 py-2 text-sm text-violet-950 focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-200">
                                    <option value="both" {{ $notificationChannel === 'both' ? 'selected' : '' }}>
                                        EメールとLINE
                                    </option>
                                    <option value="email" {{ $notificationChannel === 'email' ? 'selected' : '' }}>
                                        Eメールのみ
                                    </option>
                                    <option value="line" {{ $notificationChannel === 'line' ? 'selected' : '' }}>
                                        LINEのみ
                                    </option>
                                </select>
                                <p class="mt-2 text-xs leading-5 text-violet-700">
                                    EメールとLINEの両方で通知可能な顧客に対する設定です。初期設定はEメールとLINEです。
                                    Eメールのみ登録済みの顧客にはEメールのみ、Eメール未登録でLINEのみ通知可能な顧客にはLINEのみ送信されます。
                                </p>
                            </div>
                        @endif

                        @unless($isPlatinumPlan)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
                                LINEログイン・LINE通知機能を利用するには、プラチナプランへの変更が必要です。
                                <a href="{{ route('company.billing.index') }}" class="ml-1 font-bold underline hover:no-underline">プランを確認する</a>
                            </div>
                        @else
                            <p class="text-sm leading-6 text-violet-700">
                                LINE関連の設定変更が必要な場合は、管理者へお問い合わせください。
                            </p>
                        @endunless
                    </div>
                </div>

            </div>
        </section>

        {{-- 予約設定 --}}
        <section id="company-reserve" class="scroll-mt-40 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-visible relative">
            <div class="px-6 py-4 border-b border-gray-100 relative z-10"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900">予約設定</h2>
                <p class="text-sm text-gray-500 mt-1">予約受付の条件や締切時間を設定します。</p>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-semibold mb-2">
                        時間刻み（分）
                        <span class="rounded bg-red-50 px-2 py-0.5 text-xs font-bold text-red-600">必須</span>
                        <span class="tooltip text-gray-400 text-sm ml-2" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                予約を行う単位時間です。メニューの所要時間で予約を受け付けたい場合は「メニュー所要時間で予約」にチェックを入れてください。
                            </span>
                        </span>
                    </label>
                    <input type="number"
                           name="slot_minutes"
                           min="5"
                           max="120"
                           step="5"
                           required
                           value="{{ old('slot_minutes',$company->slot_minutes) }}"
                           class="w-full border rounded-2xl p-3 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('slot_minutes')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">
                        同時予約数
                        <span class="rounded bg-red-50 px-2 py-0.5 text-xs font-bold text-red-600">必須</span>
                        <span class="tooltip text-gray-400 text-sm ml-2" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                1人当たり同一時間帯でいくつ予約を受けるかを設定します。
                            </span>
                        </span>
                    </label>
                    <input type="number"
                           name="max_simultaneous_reservations"
                           min="1"
                           max="10"
                           required
                           value="{{ old('max_simultaneous_reservations',$company->max_simultaneous_reservations) }}"
                           class="w-full border rounded-2xl p-3 focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('max_simultaneous_reservations')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2">
                        メニュー所要時間で予約
                        <span class="tooltip text-gray-400 text-sm ml-2" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                チェックすると、メニューで設定した所要時間を予約時間として使用します。
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <input type="checkbox"
                               name="menu_time_priority_flag"
                               value="1"
                               class="mt-1 h-5 w-5 rounded border-stone-300"
                               {{ old('menu_time_priority_flag', $company->menu_time_priority_flag) ? 'checked' : '' }}>
                        <div>
                            <div class="font-semibold text-stone-800">メニューの所要時間を予約時間にする</div>
                            <div class="text-sm text-stone-500 mt-1">未チェックの場合は、上で設定した時間刻みを使います。</div>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block font-semibold mb-2">予約可能期間（月）</label>
                    <select name="reservation_month_limit" class="w-full border rounded-2xl p-3">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('reservation_month_limit', $company->reservation_month_limit)==$i?'selected':'' }}>
                                {{ $i }}ヶ月
                            </option>
                        @endfor
                    </select>
                    <p class="text-xs text-gray-500 mt-2">指定した月数の月末まで予約可能です。</p>
                    @error('reservation_month_limit')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">予約受付開始（日）</label>
                    <input type="number"
                           name="reservation_open_days"
                           min="0"
                           max="30"
                           value="{{ old('reservation_open_days',$company->reservation_open_days) }}"
                           class="w-full border rounded-2xl p-3">
                    <p class="text-xs text-gray-500 mt-2">例：0 → 当日予約可能、1 → 翌日分から予約可能</p>
                    @error('reservation_open_days')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">予約締切（時間前）</label>
                    <input type="number"
                           name="reservation_close_hours"
                           min="0"
                           max="48"
                           value="{{ old('reservation_close_hours',$company->reservation_close_hours) }}"
                           class="w-full border rounded-2xl p-3">
                    <p class="text-xs text-gray-500 mt-2">例：2 → 2時間前まで予約可能</p>
                    @error('reservation_close_hours')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">再来店促進メール送信日数</label>
                    <input type="number"
                           name="revisit_reminder_days"
                           min="1"
                           max="365"
                           value="{{ old('revisit_reminder_days', $company->revisit_reminder_days ?? 45) }}"
                           class="w-full border rounded-2xl p-3">
                    <p class="text-xs text-gray-500 mt-2">例：45 → 最終来店日から45日後に案内対象</p>
                    @error('revisit_reminder_days')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">
                        Webキャンセル締切
                        <span class="tooltip text-gray-400 text-sm ml-2" onclick="toggleTooltip(this)">❓
                            <span class="tooltip-text">
                                予約完了メールからキャンセルできる期限です。それ以降はお電話案内に切り替わります。
                            </span>
                        </span>
                    </label>
                    @php
                        $cancelDeadlineType = old('web_cancel_deadline_type', $company->web_cancel_deadline_type ?? 'hours');
                    @endphp
                    <select id="web_cancel_deadline_type" name="web_cancel_deadline_type"
                            class="w-full border rounded-2xl p-3 mb-3">
                        <option value="hours" @selected($cancelDeadlineType === 'hours')>予約時間の指定時間前まで</option>
                        <option value="business_open_minus_1_hour" @selected($cancelDeadlineType === 'business_open_minus_1_hour')>予約当日の営業開始1時間前まで</option>
                    </select>
                    <div id="webCancelHoursField">
                        <label for="web_cancel_deadline_hours" class="mb-2 block text-sm font-semibold text-stone-700">締切時間</label>
                        <div class="relative">
                            <input id="web_cancel_deadline_hours"
                                   type="number"
                                   min="0"
                                   max="168"
                                   name="web_cancel_deadline_hours"
                                   value="{{ old('web_cancel_deadline_hours', $company->web_cancel_deadline_hours ?? 24) }}"
                                   class="w-full border rounded-2xl p-3 pr-16">
                            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm text-stone-500">時間前</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">例：24 → 24時間前までWebでキャンセル可能</p>
                    </div>
                    @error('web_cancel_deadline_hours')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                    @error('web_cancel_deadline_type')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">予約後の自動ステータス処理</label>
                    @php
                        $autoStatusMode = old('reservation_auto_status_mode', $company->reservation_auto_status_mode ?? 'no_show');
                        $autoStatusHours = old('reservation_auto_status_hours', $company->reservation_auto_status_hours ?? 1);
                    @endphp
                    <select id="reservation_auto_status_mode" name="reservation_auto_status_mode"
                            class="w-full border rounded-2xl p-3 mb-3">
                        <option value="manual" @selected($autoStatusMode === 'manual')>自分で操作する</option>
                        <option value="completed" @selected($autoStatusMode === 'completed')>指定時間後に自動で来店済にする</option>
                        <option value="no_show" @selected($autoStatusMode === 'no_show')>指定時間後に自動で無断キャンセルにする</option>
                    </select>
                    <div id="autoStatusHoursField">
                        <label for="reservation_auto_status_hours" class="mb-2 block text-sm font-semibold text-stone-700">処理するタイミング</label>
                        <select id="reservation_auto_status_hours" name="reservation_auto_status_hours"
                                class="w-full border rounded-2xl p-3">
                            @for($hour = 1; $hour <= 3; $hour++)
                                <option value="{{ $hour }}" @selected((int) $autoStatusHours === $hour)>
                                    予約開始から{{ $hour }}時間後
                                </option>
                            @endfor
                        </select>
                        <p class="text-xs text-gray-500 mt-2">自動処理を選んだ場合のみ設定できます。</p>
                    </div>
                    @error('reservation_auto_status_mode')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                    @error('reservation_auto_status_hours')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- 曜日別営業時間 --}}
        <section id="company-hours" class="scroll-mt-40 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-visible relative z-20">
            <div class="px-6 py-4 border-b border-gray-100 relative z-30"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    曜日別営業時間
                    <span class="tooltip text-gray-400 text-sm" onclick="toggleTooltip(this)">❓
                        <span class="tooltip-text">
                            曜日別に営業時間を設定できます。臨時休業や時間変更は営業日カレンダーで管理してください。
                        </span>
                    </span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">営業する可能性がある曜日だけ時間枠を設定してください。</p>
            </div>

            <div class="grid grid-cols-1 gap-5 p-6 xl:grid-cols-2">
                @foreach($days as $weekday => $label)
                    <div class="rounded-2xl border border-stone-200 overflow-hidden">
                        <div class="px-4 py-4 bg-stone-50 border-b border-stone-200 flex items-center justify-between gap-3">
                            <div class="font-bold text-stone-800">{{ $label }}</div>
                            <button type="button"
                                    onclick="addTimeSlot({{ $weekday }})"
                                    class="text-sm px-4 py-2 rounded-xl text-white shadow-sm hover:opacity-90 transition"
                                    style="background: {{ $theme }};">
                                ＋枠追加
                            </button>
                        </div>

                        <div id="day-{{ $weekday }}" class="p-4 space-y-3">
                            @if(!empty($patterns[$weekday]))
                                @foreach($patterns[$weekday] as $i => $pattern)
                                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center time-row">
                                        <input type="time"
                                               name="open_patterns[{{ $weekday }}][{{ $i }}][open]"
                                               value="{{ $pattern['open'] ?? '' }}"
                                               aria-label="{{ $label }}曜日の開始時間"
                                               class="border rounded-xl p-3 w-full sm:w-auto">

                                        <span class="hidden sm:inline text-stone-500">〜</span>

                                        <input type="time"
                                               name="open_patterns[{{ $weekday }}][{{ $i }}][close]"
                                               value="{{ $pattern['close'] ?? '' }}"
                                               aria-label="{{ $label }}曜日の終了時間"
                                               class="border rounded-xl p-3 w-full sm:w-auto">

                                        <button type="button"
                                                onclick="removeTimeSlot(this)"
                                                class="text-red-500 text-sm font-medium px-2 py-2">
                                            削除
                                        </button>
                                    </div>

                                    @error("open_patterns.$weekday.$i.open")
                                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                @endforeach
                            @else
                                <div class="empty-time-slot rounded-xl border border-dashed border-stone-200 px-4 py-5 text-center text-sm text-gray-400">営業時間は未設定です。「枠追加」から登録できます。</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex flex-col items-stretch justify-between gap-4 rounded-3xl border border-stone-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:p-5">
            <p class="text-sm leading-6 text-stone-500">変更内容は、保存後すぐに予約画面と予約受付設定へ反映されます。</p>
            <button type="submit"
                    class="w-full shrink-0 sm:w-auto text-white px-8 py-3 rounded-2xl shadow-lg hover:opacity-90 transition"
                    style="background: var(--company-theme-gradient);">
                変更を保存
            </button>
        </div>

        <input type="hidden" name="theme_color" value="{{ $company->theme_color }}">
    </form>
</div>

<script>
function toggleTooltip(el){
    document.querySelectorAll('.tooltip.active').forEach(t => {
        if (t !== el) t.classList.remove('active');
    });
    el.classList.toggle('active');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.tooltip')) {
        document.querySelectorAll('.tooltip.active').forEach(t => t.classList.remove('active'));
    }
});

function addTimeSlot(weekday){
    const container = document.getElementById('day-' + weekday)
    const emptyState = container.querySelector('.empty-time-slot')
    if (emptyState) emptyState.remove()

    const index = container.querySelectorAll('.time-row').length
    const dayLabels = ['日', '月', '火', '水', '木', '金', '土']
    const row = document.createElement('div')

    row.className = 'flex flex-col sm:flex-row gap-3 sm:items-center time-row'
    row.innerHTML = `
        <input type="time"
               name="open_patterns[${weekday}][${index}][open]"
               aria-label="${dayLabels[weekday]}曜日の開始時間"
               class="border rounded-xl p-3 w-full sm:w-auto">

        <span class="hidden sm:inline text-stone-500">〜</span>

        <input type="time"
               name="open_patterns[${weekday}][${index}][close]"
               aria-label="${dayLabels[weekday]}曜日の終了時間"
               class="border rounded-xl p-3 w-full sm:w-auto">

        <button type="button"
                onclick="removeTimeSlot(this)"
                class="text-red-500 text-sm font-medium px-2 py-2">
            削除
        </button>
    `
    container.appendChild(row)
    reindexTimeSlots(container, weekday)
}

function removeTimeSlot(btn){
    const container = btn.closest('[id^="day-"]')
    const weekday = container.id.replace('day-', '')
    btn.closest('.time-row').remove()
    reindexTimeSlots(container, weekday)

    if (!container.querySelector('.time-row')) {
        container.insertAdjacentHTML('beforeend', '<div class="empty-time-slot rounded-xl border border-dashed border-stone-200 px-4 py-5 text-center text-sm text-gray-400">営業時間は未設定です。「枠追加」から登録できます。</div>')
    }
}

function reindexTimeSlots(container, weekday) {
    container.querySelectorAll('.time-row').forEach(function (row, index) {
        const openInput = row.querySelector('input[name$="[open]"]')
        const closeInput = row.querySelector('input[name$="[close]"]')
        openInput.name = `open_patterns[${weekday}][${index}][open]`
        closeInput.name = `open_patterns[${weekday}][${index}][close]`
    })
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('companyInfoForm')
    const unsavedNotice = document.getElementById('unsavedNotice')
    let isDirty = false

    document.querySelectorAll('.tooltip').forEach(function (tooltip) {
        tooltip.setAttribute('role', 'button')
        tooltip.setAttribute('tabindex', '0')
        tooltip.setAttribute('aria-label', '補足説明を表示')
        tooltip.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault()
                toggleTooltip(tooltip)
            }
        })
    })

    function markDirty() {
        if (isDirty) return
        isDirty = true
        unsavedNotice.classList.remove('hidden')
    }

    function updateConditionalFields() {
        const cancelType = document.getElementById('web_cancel_deadline_type')
        const cancelHours = document.getElementById('webCancelHoursField')
        const autoStatusMode = document.getElementById('reservation_auto_status_mode')
        const autoStatusHours = document.getElementById('autoStatusHoursField')

        cancelHours.classList.toggle('hidden', cancelType.value !== 'hours')
        autoStatusHours.classList.toggle('hidden', autoStatusMode.value === 'manual')
    }

    form.addEventListener('input', markDirty)
    form.addEventListener('change', function () {
        markDirty()
        updateConditionalFields()
    })
    form.addEventListener('submit', function () { isDirty = false })
    window.addEventListener('beforeunload', function (event) {
        if (!isDirty) return
        event.preventDefault()
        event.returnValue = ''
    })

    updateConditionalFields()
})
</script>

@endsection
