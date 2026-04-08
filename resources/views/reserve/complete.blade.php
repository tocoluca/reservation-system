@extends('layouts.app')

@section('content')
@php
    $theme = $company->theme_color ?? '#b7875c';
    $selectedStaff = $staff ?? null;

    $startAtObj = \Carbon\Carbon::parse($start_at ?? $reservation->start_at);
    $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
    $dateLabel = $startAtObj->format('Y年n月j日') . '（' . $weekdayMap[$startAtObj->dayOfWeek] . '）';
    $timeLabel = $startAtObj->format('H:i');

    $menuRows = collect($menus)->map(function ($row) {
        return $row->menu ?? $row;
    })->filter();

    $totalPrice = (int) ($reservation->price ?? 0);
    $nominationFee = (int) ($reservation->nomination_fee ?? 0);
    $grandTotal = (int) ($reservation->total_price ?? ($totalPrice + $nominationFee));

    $totalDuration = 0;
    if (!empty($details) && collect($details)->count() > 0) {
        $totalDuration = (int) collect($details)->sum('duration');
    } else {
        $totalDuration = (int) $menuRows->sum(function ($menu) {
            return (int) ($menu->duration ?? 0);
        });
    }
@endphp

<div class="min-h-screen bg-[#f7f3ee] py-8 px-4 pb-24">
    <div class="max-w-5xl mx-auto">

        <div class="bg-white rounded-[24px] overflow-hidden border border-[#eadfd3] shadow-sm">
            {{-- ヘッダー --}}
            <div class="bg-gradient-to-br from-[#c9a27e] to-[#b7875c] px-6 sm:px-8 py-10 text-white text-center">
                <div class="text-[12px] tracking-[0.12em] font-bold opacity-90">RESERVATION COMPLETE</div>
                <h1 class="mt-3 text-2xl sm:text-3xl font-bold leading-tight">ご予約が完了しました</h1>
                <p class="mt-3 text-sm sm:text-base leading-7 opacity-95">
                    ご予約ありがとうございます。<br class="sm:hidden">
                    当日のご来店を心よりお待ちしております。
                </p>
            </div>

            {{-- ステップ --}}
            <div class="px-5 sm:px-8 py-6 border-b border-[#efe4d8] bg-[#fcf8f4]">
                <div class="grid grid-cols-3 gap-2 text-center text-xs sm:text-sm">
                    <div class="rounded-2xl bg-emerald-50 py-3 font-semibold text-emerald-700 border border-emerald-100">
                        1 条件選択
                    </div>
                    <div class="rounded-2xl bg-emerald-50 py-3 font-semibold text-emerald-700 border border-emerald-100">
                        2 内容確認・入力
                    </div>
                    <div class="rounded-2xl py-3 font-semibold text-white"
                         style="background: {{ $theme }};">
                        3 完了
                    </div>
                </div>
            </div>

            <div class="px-5 sm:px-8 py-8 text-[#4b3f35]">
                <div class="grid lg:grid-cols-[1.35fr_0.85fr] gap-6">

                    {{-- 左カラム --}}
                    <div class="space-y-6">

                        {{-- 完了メッセージ --}}
                        <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 sm:p-6">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold">
                                    ✓
                                </div>
                                <div>
                                    <div class="text-[16px] sm:text-[18px] font-bold text-emerald-900">
                                        予約受付が完了しました
                                    </div>
                                    <p class="mt-2 text-[14px] leading-7 text-emerald-900">
                                        内容は以下の通りです。<br>
                                        メールアドレスをご入力いただいた場合は、予約確認メールをご確認ください。
                                    </p>
                                </div>
                            </div>
                        </section>

                        {{-- 予約内容 --}}
                        <section class="rounded-2xl border border-[#eadfd3] bg-[#fcf8f4] p-5 sm:p-6">
                            <div class="mb-4">
                                <div class="text-[12px] text-[#9a7d63] font-bold tracking-[0.08em]">RESERVATION DETAIL</div>
                                <div class="mt-1 text-[18px] font-bold text-[#4b3f35]">ご予約内容</div>
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
                                    <div class="text-[12px] text-[#8a7665] font-bold mb-2">お名前</div>
                                    <div class="text-[15px] font-bold text-[#4b3f35]">
                                        {{ $reservation->customer_name }}
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                    <div class="text-[12px] text-[#8a7665] font-bold mb-2">電話番号</div>
                                    <div class="text-[15px] font-bold text-[#4b3f35]">
                                        {{ $reservation->customer_phone }}
                                    </div>
                                </div>

                                @if(!empty($reservation->customer_email))
                                    <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                        <div class="text-[12px] text-[#8a7665] font-bold mb-2">メールアドレス</div>
                                        <div class="text-[15px] font-bold text-[#4b3f35] break-all">
                                            {{ $reservation->customer_email }}
                                        </div>
                                    </div>
                                @endif

                                <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                    <div class="text-[12px] text-[#8a7665] font-bold mb-2">代表担当者</div>

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
                                            指名なし
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>

                        {{-- メニュー --}}
                        <section class="rounded-2xl border border-[#f0e2d4] bg-[#fffaf5] p-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-[14px] font-bold text-[#6b533f]">ご予約メニュー</div>
                                <div class="text-[12px] text-[#9a7d63]">
                                    {{ $menuRows->count() }}件
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach($menuRows as $menu)
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
                                    <span class="font-bold text-[#4b3f35]">お支払い目安</span>
                                    <span class="font-bold text-[#4b3f35]">{{ number_format($grandTotal) }}円</span>
                                </div>
                            </div>
                        </section>

                        {{-- 担当詳細 --}}
                        @if(!empty($details) && collect($details)->count() > 0)
                            <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                                <div class="text-[14px] font-bold mb-4 text-[#6b533f]">施術ごとの担当</div>

                                <div class="space-y-3">
                                    @foreach($details as $detail)
                                        <div class="rounded-xl border border-[#eadfd3] bg-[#fcf8f4] p-4">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                                <div>
                                                    <div class="font-bold text-[15px] text-[#4b3f35]">
                                                        {{ $detail->menu->name ?? 'メニュー' }}
                                                    </div>
                                                    <div class="mt-1 text-[13px] text-[#7b6654]">
                                                        {{ \Carbon\Carbon::parse($detail->start_at)->format('H:i') }}
                                                        〜
                                                        {{ \Carbon\Carbon::parse($detail->end_at)->format('H:i') }}
                                                        ／ {{ (int) ($detail->duration ?? 0) }}分
                                                    </div>
                                                </div>

                                                <div class="text-[14px] font-bold text-[#4b3f35]">
                                                    {{ $detail->staff->name ?? '担当未設定' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>

                    {{-- 右カラム --}}
                    <div class="space-y-6">
                        <div class="lg:sticky lg:top-6 space-y-6">

                            <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                                <div class="text-[14px] font-bold mb-4 text-[#6b533f]">ご予約サマリー</div>

                                <div class="space-y-3 text-[14px] text-[#6b5b4d]">
                                    <div class="flex items-center justify-between gap-3">
                                        <span>ご予約日時</span>
                                        <span class="font-bold text-[#4b3f35] text-right">{{ $dateLabel }} {{ $timeLabel }}</span>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span>メニュー数</span>
                                        <span class="font-bold text-[#4b3f35]">{{ $menuRows->count() }}件</span>
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

                            @if(!empty($reservation->customer_email))
                                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 sm:p-6">
                                    <div class="text-[13px] font-bold mb-2 text-emerald-900">メール送信について</div>
                                    <div class="text-[13px] leading-7 text-emerald-900">
                                        ご入力いただいたメールアドレス宛に、予約確認メールをお送りしています。<br>
                                        届かない場合は迷惑メールフォルダもあわせてご確認ください。
                                    </div>
                                </section>
                            @endif

                            <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                                <div class="text-[13px] font-bold mb-3 text-[#7a614d]">ご案内</div>
                                <div class="space-y-3 text-[13px] leading-7 text-[#6b5b4d]">
                                    <div class="rounded-xl bg-[#f8f2eb] px-4 py-3">
                                        ご予約時間の少し前にご来店いただくとご案内がスムーズです。
                                    </div>
                                    <div class="rounded-xl bg-[#f8f2eb] px-4 py-3">
                                        当日の状況により、担当者やご案内順が調整となる場合があります。
                                    </div>
                                    @if(!empty($reservation->customer_email))
                                        <div class="rounded-xl bg-[#f8f2eb] px-4 py-3">
                                            キャンセルや内容確認は、メール内のご案内からお手続きください。
                                        </div>
                                    @else
                                        <div class="rounded-xl bg-[#f8f2eb] px-4 py-3">
                                            メールアドレス未入力の場合、変更やキャンセルはお電話でご連絡ください。
                                        </div>
                                    @endif
                                </div>
                            </section>

                            <div class="hidden lg:flex flex-col gap-3">
                                <a
                                    href="{{ url('/r/' . $company->company_code) }}"
                                    class="inline-flex items-center justify-center rounded-full px-6 py-4 text-white font-bold text-sm shadow-sm"
                                    style="background:#b7875c;">
                                    トップへ戻る
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 sm:px-8 py-5 bg-[#f3ece4] text-center text-[12px] leading-7 text-[#9a8878]">
                © {{ date('Y') }} {{ $company->name }}
            </div>
        </div>
    </div>
</div>

{{-- 下部固定バー --}}
<div class="fixed bottom-0 inset-x-0 z-40 border-t border-[#e5d9cd] bg-white/95 backdrop-blur">
    <div class="max-w-5xl mx-auto px-4 py-3">
        <div class="flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="text-xs sm:text-sm text-[#8a7665] mb-1">
                    ご予約完了 ｜ {{ $dateLabel }} {{ $timeLabel }}
                </div>
                <div class="flex items-end gap-2">
                    <div class="text-xs sm:text-sm text-[#9a8878]">料金目安</div>
                    <div class="text-xl sm:text-2xl font-bold text-[#4b3f35]">
                        {{ number_format($grandTotal) }}円
                    </div>
                </div>
            </div>

            <a
                href="{{ url('/r/' . $company->company_code) }}"
                class="shrink-0 text-white px-5 sm:px-7 py-3.5 rounded-full text-sm sm:text-base font-bold shadow-lg text-center"
                style="background:#b7875c;">
                トップへ戻る
            </a>
        </div>
    </div>
</div>
@endsection