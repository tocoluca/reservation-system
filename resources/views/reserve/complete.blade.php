@extends('layouts.app')

@section('content')
@php
    $theme = $company->theme_color ?? '#b7875c';
    $details = $details ?? ($reservation->details ?? collect());
@endphp

<div class="min-h-screen bg-[#f7f3ee] py-8 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-[24px] overflow-hidden border border-[#eadfd3] shadow-sm">
            <div class="bg-gradient-to-br from-[#c9a27e] to-[#b7875c] px-6 sm:px-8 py-8 text-white text-center">
                <div class="text-[12px] tracking-[0.12em] font-bold opacity-90">RESERVATION COMPLETE</div>
                <h1 class="mt-3 text-2xl sm:text-3xl font-bold leading-tight">ご予約ありがとうございます</h1>
                <p class="mt-3 text-sm sm:text-base leading-7 opacity-95">
                    ご予約を承りました。ご来店を心よりお待ちしております。
                </p>
            </div>

            <div class="px-5 sm:px-8 py-8 space-y-6 text-[#4b3f35]">
                <div>
                    <p class="text-[15px] leading-8">
                        {{ $reservation->customer_name }} 様
                    </p>
                    <p class="mt-3 text-[14px] leading-8 text-[#6b5b4d]">
                        このたびは <strong>{{ $company->name }}</strong> へご予約いただき、誠にありがとうございます。<br>
                        以下の内容でご予約を受け付けいたしました。
                    </p>
                </div>

                <section class="rounded-2xl border border-[#eadfd3] bg-[#fcf8f4] p-5 sm:p-6">
                    <div class="text-[12px] text-[#9a7d63] font-bold tracking-[0.08em] mb-3">ご予約内容</div>

                    <div class="space-y-3">
                        <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                            <div class="text-[13px] text-[#8a7665]">日時</div>
                            <div class="text-[15px] font-bold text-[#4b3f35] mt-1 sm:mt-0">
                                {{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}
                                〜
                                {{ \Carbon\Carbon::parse($reservation->end_at)->format('H:i') }}
                            </div>
                        </div>

                        @if(!empty($reservation->staff?->name))
                            <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                                <div class="text-[13px] text-[#8a7665]">代表担当</div>
                                <div class="text-[14px] mt-1 sm:mt-0">
                                    {{ $reservation->staff->name }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($reservation->customer_phone))
                            <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                                <div class="text-[13px] text-[#8a7665]">電話番号</div>
                                <div class="text-[14px] mt-1 sm:mt-0">
                                    {{ $reservation->customer_phone }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($reservation->customer_email))
                            <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                                <div class="text-[13px] text-[#8a7665]">メール</div>
                                <div class="text-[14px] mt-1 sm:mt-0 break-all">
                                    {{ $reservation->customer_email }}
                                </div>
                            </div>
                        @endif

                        @if(!empty($reservation->total_price))
                            <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                                <div class="text-[13px] text-[#8a7665]">料金目安</div>
                                <div class="text-[14px] mt-1 sm:mt-0">
                                    {{ number_format((int) $reservation->total_price) }}円
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-2xl border border-[#f0e2d4] bg-[#fffaf5] p-5 sm:p-6">
                    <div class="text-[14px] font-bold mb-4 text-[#6b533f]">施術内容</div>

                    @if($details->isNotEmpty())
                        <div class="hidden sm:block overflow-hidden rounded-xl border border-[#eadfd3] bg-white">
                            <div class="grid grid-cols-12 bg-[#f8f2eb] text-[12px] font-bold text-[#8a7665]">
                                <div class="col-span-4 px-4 py-3">メニュー</div>
                                <div class="col-span-3 px-4 py-3">担当</div>
                                <div class="col-span-3 px-4 py-3">時間</div>
                                <div class="col-span-2 px-4 py-3 text-right">料金</div>
                            </div>

                            @foreach($details as $detail)
                                <div class="grid grid-cols-12 border-t border-[#f3e8dc] text-[14px] text-[#4b3f35]">
                                    <div class="col-span-4 px-4 py-4">
                                        {{ $detail->menu->name ?? 'メニュー' }}
                                    </div>
                                    <div class="col-span-3 px-4 py-4">
                                        {{ $detail->staff->name ?? '未設定' }}
                                    </div>
                                    <div class="col-span-3 px-4 py-4">
                                        {{ \Carbon\Carbon::parse($detail->start_at)->format('H:i') }}
                                        〜
                                        {{ \Carbon\Carbon::parse($detail->end_at)->format('H:i') }}
                                    </div>
                                    <div class="col-span-2 px-4 py-4 text-right">
                                        {{ number_format((int) ($detail->price ?? 0)) }}円
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="sm:hidden space-y-3">
                            @foreach($details as $detail)
                                <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="font-bold text-[15px] text-[#4b3f35]">
                                            {{ $detail->menu->name ?? 'メニュー' }}
                                        </div>
                                        <div class="text-[14px] font-bold text-[#4b3f35] whitespace-nowrap">
                                            {{ number_format((int) ($detail->price ?? 0)) }}円
                                        </div>
                                    </div>

                                    <div class="mt-2 text-[13px] leading-7 text-[#6b5b4d]">
                                        担当：{{ $detail->staff->name ?? '未設定' }}<br>
                                        時間：
                                        {{ \Carbon\Carbon::parse($detail->start_at)->format('H:i') }}
                                        〜
                                        {{ \Carbon\Carbon::parse($detail->end_at)->format('H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-[#eadfd3] bg-white p-4 text-[14px] leading-8 text-[#6b5b4d]">
                            @if(!empty($staff?->name))
                                担当：{{ $staff->name }}<br>
                            @endif

                            @foreach($menus as $menuRow)
                                ・{{ $menuRow->menu->name ?? 'メニュー' }}
                                @if(!empty($menuRow->price))
                                    （{{ number_format((int) $menuRow->price) }}円）
                                @endif
                                <br>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl bg-[#f8f2eb] p-5 sm:p-6">
                    <div class="text-[13px] font-bold mb-3 text-[#7a614d]">店舗情報</div>
                    <div class="text-[13px] leading-8 text-[#6b5b4d]">
                        <strong>{{ $company->name }}</strong><br>
                        @if(!empty($company->phone))
                            TEL：{{ $company->phone }}<br>
                        @endif
                        @if(!empty($company->homepage))
                            WEB：{{ $company->homepage }}
                        @endif
                    </div>
                </section>

                <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                    <div class="text-[14px] font-bold mb-3 text-[#6b533f]">ご案内</div>
                    <p class="text-[14px] leading-8 text-[#6b5b4d]">
                        ご予約内容は送信メールでもご確認いただけます。<br>
                        当日の状況により、ご案内順や担当の最終調整が入る場合があります。
                    </p>
                </section>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <a href="{{ url('/r/' . $company->company_code) }}"
                       class="inline-flex items-center justify-center rounded-full px-6 py-3 text-white font-bold text-sm shadow-sm"
                       style="background:#b7875c;">
                        予約トップへ戻る
                    </a>

                    @if(!empty($company->phone))
                        <a href="tel:{{ preg_replace('/[^0-9]/', '', $company->phone) }}"
                           class="inline-flex items-center justify-center rounded-full px-6 py-3 border border-[#d6c5b5] text-[#6b533f] font-bold text-sm bg-white">
                            お店に電話する
                        </a>
                    @endif
                </div>
            </div>

            <div class="px-6 sm:px-8 py-5 bg-[#f3ece4] text-center text-[12px] leading-7 text-[#9a8878]">
                © {{ date('Y') }} {{ $company->name }}
            </div>
        </div>
    </div>
</div>
@endsection