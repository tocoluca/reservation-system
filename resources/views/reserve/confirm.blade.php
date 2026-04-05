@extends('layouts.app')

@section('content')
@php
    $theme = $company->theme_color ?? '#b7875c';
    $selectedStaff = $staff ?? null;
    $lineName = $lineCustomer->name ?? ($lineProfile['name'] ?? null);
    $lineEmail = $lineCustomer->email ?? ($lineProfile['email'] ?? null);
@endphp

<div class="min-h-screen bg-[#f7f3ee] py-8 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-[24px] overflow-hidden border border-[#eadfd3] shadow-sm">
            <div class="bg-gradient-to-br from-[#c9a27e] to-[#b7875c] px-6 sm:px-8 py-8 text-white text-center">
                <div class="text-[12px] tracking-[0.12em] font-bold opacity-90">RESERVATION CONFIRM</div>
                <h1 class="mt-3 text-2xl sm:text-3xl font-bold leading-tight">ご予約内容の確認</h1>
                <p class="mt-3 text-sm sm:text-base leading-7 opacity-95">
                    内容をご確認のうえ、ご予約を確定してください。
                </p>
            </div>

            <div class="px-5 sm:px-8 py-8 space-y-6 text-[#4b3f35]">
                @if(session('error'))
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>・{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="rounded-2xl border border-[#eadfd3] bg-[#fcf8f4] p-5 sm:p-6">
                    <div class="text-[12px] text-[#9a7d63] font-bold tracking-[0.08em] mb-3">ご予約内容</div>

                    <div class="space-y-3">
                        <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                            <div class="text-[13px] text-[#8a7665]">日時</div>
                            <div class="text-[15px] font-bold text-[#4b3f35] mt-1 sm:mt-0">
                                {{ \Carbon\Carbon::parse($start_at)->format('Y年m月d日 H:i') }}
                            </div>
                        </div>

                        @if($selectedStaff)
                            <div class="sm:grid sm:grid-cols-[110px_1fr] sm:gap-4">
                                <div class="text-[13px] text-[#8a7665]">ご希望担当</div>
                                <div class="text-[14px] mt-1 sm:mt-0">
                                    {{ $selectedStaff->name }}
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-2xl border border-[#f0e2d4] bg-[#fffaf5] p-5 sm:p-6">
                    <div class="text-[14px] font-bold mb-4 text-[#6b533f]">選択メニュー</div>

                    <div class="space-y-3">
                        @php
                            $totalPrice = 0;
                            $totalDuration = 0;
                        @endphp

                        @foreach($menus as $menu)
                            @php
                                $totalPrice += (int) ($menu->price ?? 0);
                                $totalDuration += (int) ($menu->duration ?? 0);
                            @endphp

                            <div class="rounded-xl border border-[#eadfd3] bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
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

                    <div class="mt-4 rounded-xl bg-[#f8f2eb] p-4 text-[14px] text-[#6b5b4d]">
                        <div class="flex items-center justify-between">
                            <span>メニュー合計</span>
                            <span class="font-bold text-[#4b3f35]">{{ number_format($totalPrice) }}円</span>
                        </div>

                        @if($selectedStaff && !empty($selectedStaff->nomination_fee))
                            <div class="mt-2 flex items-center justify-between">
                                <span>指名料</span>
                                <span class="font-bold text-[#4b3f35]">{{ number_format((int) $selectedStaff->nomination_fee) }}円</span>
                            </div>
                        @endif

                        <div class="mt-2 flex items-center justify-between">
                            <span>目安時間</span>
                            <span class="font-bold text-[#4b3f35]">{{ $totalDuration }}分</span>
                        </div>

                        <div class="mt-3 pt-3 border-t border-[#e7d8ca] flex items-center justify-between text-[15px]">
                            <span class="font-bold text-[#4b3f35]">料金目安</span>
                            <span class="font-bold text-[#4b3f35]">
                                {{ number_format($totalPrice + (int) ($selectedStaff->nomination_fee ?? 0)) }}円
                            </span>
                        </div>
                    </div>
                </section>

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

                <form method="POST" action="{{ url('/r/' . $company->company_code . '/store') }}" class="space-y-6">
                    @csrf

                    <input type="hidden" name="start_at" value="{{ $start_at }}">
                    @if($selectedStaff)
                        <input type="hidden" name="staff_id" value="{{ $selectedStaff->id }}">
                    @endif

                    @foreach($menus as $menu)
                        <input type="hidden" name="menu_ids[]" value="{{ $menu->id }}">
                    @endforeach

                    <section class="rounded-2xl border border-[#eadfd3] bg-white p-5 sm:p-6">
                        <div class="text-[14px] font-bold mb-4 text-[#6b533f]">お客様情報</div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-[13px] font-bold text-[#7a614d] mb-2">お名前</label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    value="{{ old('customer_name', $lineCustomer->name ?? '') }}"
                                    class="w-full rounded-xl border border-[#d9cabb] px-4 py-3 text-[14px] focus:outline-none focus:ring-2"
                                    style="--tw-ring-color: {{ $theme }};"
                                >
                            </div>

                            <div>
                                <label class="block text-[13px] font-bold text-[#7a614d] mb-2">電話番号</label>
                                <input
                                    type="text"
                                    name="customer_phone"
                                    value="{{ old('customer_phone', $lineCustomer->phone ?? '') }}"
                                    class="w-full rounded-xl border border-[#d9cabb] px-4 py-3 text-[14px] focus:outline-none focus:ring-2"
                                    style="--tw-ring-color: {{ $theme }};"
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

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-full px-6 py-3 text-white font-bold text-sm shadow-sm"
                            style="background:#b7875c;"
                        >
                            この内容で予約する
                        </button>

                        <a
                            href="{{ url('/r/' . $company->company_code) }}"
                            class="inline-flex items-center justify-center rounded-full px-6 py-3 border border-[#d6c5b5] text-[#6b533f] font-bold text-sm bg-white"
                        >
                            戻って修正する
                        </a>
                    </div>
                </form>
            </div>

            <div class="px-6 sm:px-8 py-5 bg-[#f3ece4] text-center text-[12px] leading-7 text-[#9a8878]">
                © {{ date('Y') }} {{ $company->name }}
            </div>
        </div>
    </div>
</div>
@endsection