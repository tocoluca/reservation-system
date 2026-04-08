@extends('layouts.app')

@section('content')
@php
    $theme = $company->theme_color ?? '#b7875c';
    $selectedStaff = $staff ?? null;
    $lineName = $lineCustomer->name ?? ($lineProfile['name'] ?? null);
    $lineEmail = $lineCustomer->email ?? ($lineProfile['email'] ?? null);

    $startAtObj = \Carbon\Carbon::parse($start_at);
    $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
    $dateLabel = $startAtObj->format('Y年n月j日') . '（' . $weekdayMap[$startAtObj->dayOfWeek] . '）';
    $timeLabel = $startAtObj->format('H:i');

    $totalPrice = 0;
    $totalDuration = 0;
    foreach ($menus as $menu) {
        $totalPrice += (int) ($menu->price ?? 0);
        $totalDuration += (int) ($menu->duration ?? 0);
    }

    $nominationFee = (int) ($selectedStaff->nomination_fee ?? 0);
    $grandTotal = $totalPrice + $nominationFee;
@endphp

<div class="min-h-screen bg-[#f7f3ee] py-8 px-4">
    <div class="max-w-5xl mx-auto">

        {{-- ヘッダー --}}
        <div class="bg-white rounded-[24px] overflow-hidden border border-[#eadfd3] shadow-sm">
            <div class="bg-gradient-to-br from-[#c9a27e] to-[#b7875c] px-6 sm:px-8 py-8 text-white text-center">
                <div class="text-[12px] tracking-[0.12em] font-bold opacity-90">RESERVATION CONFIRM</div>
                <h1 class="mt-3 text-2xl sm:text-3xl font-bold leading-tight">ご予約内容の確認</h1>
                <p class="mt-3 text-sm sm:text-base leading-7 opacity-95">
                    内容をご確認のうえ、お客様情報をご入力ください。
                </p>
            </div>

            <div class="px-5 sm:px-8 py-6 border-b border-[#efe4d8] bg-[#fcf8f4]">
                <div class="grid grid-cols-3 gap-2 text-center text-xs sm:text-sm">
                    <div class="rounded-2xl bg-emerald-50 py-3 font-semibold text-emerald-700 border border-emerald-100">
                        1 条件選択
                    </div>
                    <div class="rounded-2xl py-3 font-semibold text-white"
                         style="background: {{ $theme }}">
                        2 内容確認・入力
                    </div>
                    <div class="rounded-2xl bg-gray-50 py-3 font-semibold text-gray-500">
                        3 完了
                    </div>
                </div>
            </div>

            <div class="px-5 sm:px-8 py-8 text-[#4b3f35]">
                @if(session('error'))
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <div class="font-bold mb-2">入力内容をご確認ください</div>
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>・{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/r/' . $company->company_code . '/store') }}">
                    @csrf

                    <input type="hidden" name="start_at" value="{{ $start_at }}">
                    @if($selectedStaff)
                        <input type="hidden" name="staff_id" value="{{ $selectedStaff->id }}">
                    @endif

                    @foreach($menus as $menu)
                        <input type="hidden" name="menu_ids[]" value="{{ $menu->id }}">
                    @endforeach

                    <div class="grid lg:grid-cols-[1.35fr_0.85fr] gap-6">

                        {{-- 左カラム --}}
                        <div class="space-y-6">

                            {{-- 予約内容 --}}
                            <section class="rounded-2xl border border-[#eadfd3] bg-[#fcf8f4] p-5 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                                    <div>
                                        <div class="text-[12px] text-[#9a7d63] font-bold tracking-[0.08em]">ご予約内容</div>
                                        <div class="mt-1 text-[14px] text-[#7b6654]">
                                            内容をご確認ください
                                        </div>
                                    </div>

                                    <a href="{{ url('/r/' . $company->company_code) }}"
                                       class="inline-flex items-center justify-center rounded-full px-4 py-2 border border-[#d6c5b5] text-[#6b533f] font-bold text-sm bg-white">
                                        戻って修正する
                                    </a>
                                </div>

                                <div class="space-y-4">
                                    <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                        <div class="grid sm:grid-cols-2 gap-3">
                                            <div>
                                                <div class="text-[12px] text-[#8a7665] font-bold mb-1">日付</div>
                                                <div class="text-[15px] font-bold text-[#4b3f35]">
                                                    {{ $dateLabel }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-[12px] text-[#8a7665] font-bold mb-1">時間</div>
                                                <div class="text-[15px] font-bold text-[#4b3f35]">
                                                    {{ $timeLabel }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                        <div class="text-[12px] text-[#8a7665] font-bold mb-2">担当者</div>

                                        @if($selectedStaff)
                                            <div class="flex items-start gap-3">
                                                <div class="w-12 h-12 rounded-full overflow-hidden bg-[#f3ece4] shrink-0">
                                                    <img
                                                        src="{{ $selectedStaff->image_path ? asset($selectedStaff->image_path) : asset('images/noimage.png') }}"
                                                        class="w-full h-full object-cover"
                                                        alt="{{ $selectedStaff->name }}">
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <div class="font-bold text-[15px] text-[#4b3f35]">
                                                            {{ $selectedStaff->name }}
                                                        </div>

                                                        @if($nominationFee > 0)
                                                            <span class="text-[11px] px-2 py-1 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-100">
                                                                指名料 +{{ number_format($nominationFee) }}円
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if(!empty($selectedStaff->comment))
                                                        <div class="mt-2 text-[13px] leading-6 text-[#7b6654]">
                                                            {{ $selectedStaff->comment }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="font-bold text-[15px] text-[#4b3f35]">
                                                指名しない
                                            </div>
                                            <div class="mt-1 text-[13px] leading-6 text-[#7b6654]">
                                                空いている担当者をご案内します。
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </section>

                            {{-- 選択メニュー --}}
                            <section class="rounded-2xl border border-[#f0e2d4] bg-[#fffaf5] p-5 sm:p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-[14px] font-bold text-[#6b533f]">選択メニュー</div>
                                    <div class="text-[12px] text-[#9a7d63]">
                                        {{ $menus->count() }}件
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    @foreach($menus as $menu)
                                        <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-bold text-[15px] text-[#4b3f35]">
                                                        {{ $menu->name }}
                                                    </div>
                                                    @if(!empty($menu->description))
                                                        <div class="mt-1 text-[13px] leading-6 text-[#7b6654]">
                                                            {{ $menu->description }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="text-right shrink-0">
                                                    @if(!empty($menu->price))
                                                        <div class="text-[14px] font-bold text-[#4b3f35]">
                                                            {{ number_format((int) $menu->price) }}円
                                                        </div>
                                                    @endif

                                                    @if(!empty($menu->duration))
                                                        <div class="mt-1 text-[12px] text-[#8a7665]">
                                                            {{ (int) $menu->duration }}分
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 rounded-xl bg-[#f8f2eb] p-4 text-[14px] text-[#6b5b4d] space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span>メニュー合計</span>
                                        <span class="font-bold text-[#4b3f35]">{{ number_format($totalPrice) }}円</span>
                                    </div>

                                    @if($nominationFee > 0)
                                        <div class="flex items-center justify-between">
                                            <span>指名料</span>
                                            <span class="font-bold text-[#4b3f35]">{{ number_format($nominationFee) }}円</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <span>目安時間</span>
                                        <span class="font-bold text-[#4b3f35]">{{ $totalDuration }}分</span>
                                    </div>

                                    <div class="pt-3 border-t border-[#e7d8ca] flex items-center justify-between text-[15px]">
                                        <span class="font-bold text-[#4b3f35]">料金目安</span>
                                        <span class="font-bold text-[#4b3f35]">{{ number_format($grandTotal) }}円</span>
                                    </div>
                                </div>
                            </section>

                            {{-- 担当について --}}
                            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
                                <div class="text-[14px] font-bold mb-2 text-amber-900">担当について</div>
                                <p class="text-[14px] leading-8 text-amber-900">
                                    複数メニューをご予約の場合、内容に応じてメニューごとに担当者が分かれる場合があります。<br>
                                    ご希望担当が対応できるメニューは優先して割り当てられ、対応できないメニューは他の担当者が対応することがあります。
                                </p>

                                @if($selectedStaff)
                                    <p class="mt-3 text-[13px] leading-7 text-amber-800">
                                        現在のご希望担当：{{ $selectedStaff->name }}
                                    </p>
                                @endif
                            </section>

                            {{-- お客様情報 --}}
                            <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                                <div class="text-[14px] font-bold mb-4 text-[#6b533f]">お客様情報</div>

                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-[13px] font-bold text-[#7a614d] mb-2">
                                            お名前 <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="customer_name"
                                            value="{{ old('customer_name', $lineCustomer->name ?? '') }}"
                                            class="w-full rounded-xl border border-[#d9cabb] px-4 py-3 text-[14px] focus:outline-none focus:ring-2"
                                            style="--tw-ring-color: {{ $theme }};"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-[13px] font-bold text-[#7a614d] mb-2">
                                            電話番号 <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="customer_phone"
                                            value="{{ old('customer_phone', $lineCustomer->phone ?? '') }}"
                                            class="w-full rounded-xl border border-[#d9cabb] px-4 py-3 text-[14px] focus:outline-none focus:ring-2"
                                            style="--tw-ring-color: {{ $theme }};"
                                            placeholder="090-1234-5678"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-[13px] font-bold text-[#7a614d] mb-2">
                                            メールアドレス
                                            <span class="ml-1 text-[11px] font-normal text-[#9a8878]">（任意）</span>
                                        </label>

                                        <div class="mb-3 rounded-xl border border-[#e8dccf] bg-[#fcf8f4] px-4 py-3 text-[13px] leading-7 text-[#6b5b4d]">
                                            メールアドレスをご入力いただくと、予約内容の確認やキャンセル用URLをお送りします。<br>
                                            予約内容の再確認やキャンセル手続きがスムーズに行えますので、変更やキャンセルの可能性がある場合は入力をおすすめします。<br>
                                            未入力の場合、キャンセルの際はお電話でのご連絡をお願いいたします。
                                        </div>

                                        <input
                                            type="email"
                                            name="customer_email"
                                            value="{{ old('customer_email', $lineEmail ?? '') }}"
                                            class="w-full rounded-xl border border-[#d9cabb] px-4 py-3 text-[14px] focus:outline-none focus:ring-2"
                                            style="--tw-ring-color: {{ $theme }};"
                                            placeholder="example@example.com"
                                        >
                                    </div>

                                    @if($lineName)
                                        <div class="rounded-xl bg-[#f8f2eb] px-4 py-3 text-[13px] leading-7 text-[#6b5b4d]">
                                            LINEログイン情報が利用されています。必要に応じて上記内容を修正してください。
                                        </div>
                                    @endif
                                </div>
                            </section>
                        </div>

                        {{-- 右カラム --}}
                        <div class="space-y-6">
                            <div class="lg:sticky lg:top-6 space-y-6">

                                <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                                    <div class="text-[14px] font-bold mb-4 text-[#6b533f]">最終確認</div>

                                    <div class="space-y-3 text-[14px] text-[#6b5b4d]">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>ご予約日時</span>
                                            <span class="font-bold text-[#4b3f35] text-right">{{ $dateLabel }} {{ $timeLabel }}</span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <span>メニュー数</span>
                                            <span class="font-bold text-[#4b3f35]">{{ $menus->count() }}件</span>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <span>施術時間</span>
                                            <span class="font-bold text-[#4b3f35]">{{ $totalDuration }}分</span>
                                        </div>

                                        <div class="pt-3 border-t border-[#e7d8ca] flex items-center justify-between gap-3">
                                            <span class="font-bold text-[#4b3f35]">料金目安</span>
                                            <span class="font-bold text-[#4b3f35] text-lg">{{ number_format($grandTotal) }}円</span>
                                        </div>
                                    </div>
                                </section>

                                <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                                    <div class="text-[13px] font-bold mb-3 text-[#7a614d]">ご案内</div>
                                    <div class="space-y-3 text-[13px] leading-7 text-[#6b5b4d]">
                                        <div class="rounded-xl bg-[#f8f2eb] px-4 py-3">
                                            ご予約完了後、予約内容の確認画面が表示されます。
                                        </div>
                                        <div class="rounded-xl bg-[#f8f2eb] px-4 py-3">
                                            当日の状況により、担当やご案内順が調整される場合があります。
                                        </div>
                                    </div>
                                </section>

                                <div class="hidden lg:flex flex-col gap-3">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center rounded-full px-6 py-4 text-white font-bold text-sm shadow-sm"
                                        style="background:#b7875c;">
                                        この内容で予約する
                                    </button>

                                    <a
                                        href="{{ url('/r/' . $company->company_code) }}"
                                        class="inline-flex items-center justify-center rounded-full px-6 py-4 border border-[#d6c5b5] text-[#6b533f] font-bold text-sm bg-white"
                                    >
                                        戻って修正する
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-6 sm:px-8 py-5 bg-[#f3ece4] text-center text-[12px] leading-7 text-[#9a8878]">
                © {{ date('Y') }} {{ $company->name }}
            </div>
        </div>
    </div>
</div>

{{-- 下部固定バー --}}
<div class="fixed bottom-0 inset-x-0 z-40 pointer-events-none border-t border-[#e5d9cd] bg-white/95 backdrop-blur">
    <div class="max-w-5xl mx-auto px-4 py-3 pointer-events-auto">
        <div class="flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="text-xs sm:text-sm text-[#8a7665] mb-1">
                    {{ $dateLabel }} {{ $timeLabel }} ／ {{ $menus->count() }}メニュー
                </div>
                <div class="flex items-end gap-2">
                    <div class="text-xs sm:text-sm text-[#9a8878]">料金目安</div>
                    <div class="text-xl sm:text-2xl font-bold text-[#4b3f35]">
                        {{ number_format($grandTotal) }}円
                    </div>
                </div>
            </div>

            <button
                type="button"
                onclick="document.querySelector('form').requestSubmit();"
                class="shrink-0 text-white px-5 sm:px-7 py-3.5 rounded-full text-sm sm:text-base font-bold shadow-lg"
                style="background:#b7875c;">
                予約を確定する
            </button>
        </div>
    </div>
</div>
@endsection