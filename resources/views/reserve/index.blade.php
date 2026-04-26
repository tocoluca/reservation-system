@extends('layouts.app')

@section('content')

@php
    $theme = $company->theme_color ?? '#b7875c';
@endphp

<div class="min-h-screen isolate bg-[#f7f3ee] pb-28">
    <div class="max-w-5xl mx-auto px-4 sm:px-5 py-5 sm:py-8">

        {{-- ヘッダー --}}
        <div class="bg-white rounded-[24px] overflow-hidden border border-[#eadfd3] shadow-sm mb-6 sm:mb-8">
            <div class="bg-gradient-to-br from-[#c9a27e] to-[#b7875c] px-6 sm:px-8 py-10 sm:py-12 text-white text-center">
                <div class="text-[12px] tracking-[0.12em] font-bold opacity-90">ONLINE RESERVATION</div>
                <h1 class="mt-3 text-3xl sm:text-4xl font-bold leading-tight">
                    ご予約
                </h1>
                <p class="mt-3 text-sm sm:text-base leading-7 opacity-95 max-w-2xl mx-auto">
                    メニューを選んで、ご希望の日付・時間を選ぶだけ。<br class="hidden sm:block">
                    担当者のご希望がある場合は、あとから選べます。
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700 text-sm">
                <div class="font-bold mb-2">入力内容をご確認ください</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($lineLoginEnabled ?? false)
            <div class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-5 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-[#4b3f35] mb-1">
                            LINEでかんたん予約
                        </h2>
                        <p class="text-sm text-[#7b6654] leading-6">
                            @if(!empty($lineProfile))
                                LINEログイン中です。お名前やメールアドレスの入力がかんたんになります。
                            @else
                                LINEでログインすると、次回以降の入力がかんたんになります。
                            @endif
                        </p>

                        @if(!empty($lineProfile))
                            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-100">
                                LINEログイン中
                                <span class="text-emerald-900">
                                    {{ $lineCustomer->name ?? ($lineProfile['name'] ?? 'LINEユーザー') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('reserve.line.redirect', ['company_code' => $company->company_code]) }}"
                           class="inline-flex items-center justify-center px-5 py-3 rounded-full text-white font-semibold shadow-sm"
                           style="background:#06C755;">
                            @if(!empty($lineProfile))
                                別のLINEでログイン
                            @else
                                LINEでログイン
                            @endif
                        </a>

                        @if(!empty($lineProfile))
                            <a href="{{ route('reserve.line.logout', ['company_code' => $company->company_code]) }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-full border border-[#d6c5b5] bg-white text-[#6b533f] font-semibold">
                                解除
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- お知らせ --}}
        <div class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base sm:text-lg font-bold text-[#4b3f35]">お知らせ</h2>
                <span class="text-xs text-[#9a7d63]">INFORMATION</span>
            </div>

            @forelse($notices as $notice)
                <a href="{{ route('reserve.notice.show', [$company->company_code, $notice->id]) }}"
                   class="flex items-start justify-between gap-3 py-3 border-t border-[#efe4d8] first:border-t-0 hover:bg-[#fcf8f4] rounded-xl transition px-1">
                    <div class="flex items-start gap-2 min-w-0">
                        <div class="pt-0.5">
                            @if($notice->is_important)
                                <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-600">
                                    重要
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                @if(method_exists($notice, 'isNew') && $notice->isNew())
                                    <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-600">
                                        NEW
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm sm:text-base font-medium text-[#4b3f35] truncate">
                                {{ $notice->title }}
                            </div>
                        </div>
                    </div>

                    <span class="text-xs text-[#9a7d63] shrink-0">
                        {{ $notice->created_at->format('m/d') }}
                    </span>
                </a>
            @empty
                <p class="text-sm text-[#9a7d63]">
                    現在お知らせはありません
                </p>
            @endforelse
        </div>

        <form method="POST" action="/r/{{ $company->company_code }}/confirm" id="reserveForm">
            @csrf

            @php
                $prefillMenuIds = collect($prefillMenuIds ?? [])->map(fn ($id) => (string) $id)->all();

                $oldMenuIds = collect(old('menu_ids', $prefillMenuIds))
                    ->map(fn ($id) => (string) $id)
                    ->all();

                $oldStaffId = old('staff_id', $prefillStaffId ?? '');

                $oldStartAt = old('start_at', $prefillStartAt ?? '');

                $oldDate = $oldStartAt ? \Carbon\Carbon::parse($oldStartAt)->format('Y-m-d') : '';
                $oldTime = $oldStartAt ? \Carbon\Carbon::parse($oldStartAt)->format('H:i') : '';

                $address = $company->address ?? $company->address ?? null;
                $phone = $company->phone ?? $company->phone ?? null;

                $salonMessage = $company->salon_message ?? $company->message ?? null;
                $businessHoursText = $company->business_hours_text ?? '営業時間は店舗情報をご確認ください。';
                $parkingText = $company->parking_info ?? $company->parking ?? '店舗へお問い合わせください。';
                $paymentText = $company->payment_methods ?? '現金';
                $accessText = $company->access_info ?? null;
                $salonNote = $company->salon_note ?? null;
                $stylePosts = collect($styles ?? $stylePosts ?? []);
            @endphp

            <input type="hidden" name="start_at" id="start_at" value="{{ old('start_at', $prefillStartAt ?? '') }}">

            <div class="grid lg:grid-cols-[1.45fr_0.85fr] gap-6">

                {{-- 左カラム --}}
                <div class="space-y-6">

                    <div class="relative z-10 bg-[#fcf8f4] rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-5">
                        <div class="grid grid-cols-4 gap-2 text-center text-xs sm:text-sm">
                            <div id="stepBox1" class="step-box rounded-2xl py-3 font-semibold">
                                1 メニュー
                            </div>
                            <div id="stepBox2" class="step-box rounded-2xl py-3 font-semibold">
                                2 日付
                            </div>
                            <div id="stepBox3" class="step-box rounded-2xl py-3 font-semibold">
                                3 時間
                            </div>
                            <div id="stepBox4" class="step-box rounded-2xl py-3 font-semibold">
                                4 担当者
                            </div>
                        </div>
                    </div>

                    {{-- メニュー --}}
                    <section class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepMenuTitle" class="text-lg sm:text-xl font-bold text-[#4b3f35] transition">
                                STEP1 メニューを選ぶ
                            </h2>
                            <span class="text-xs text-[#9a7d63]">MENU</span>
                        </div>

                        <div id="menuErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm"></div>

                        @foreach($menus as $categoryName => $categoryMenus)
                            @php
                                $categoryImageMap = [
                                    'カット' => asset('images/menu-icons/cut.jpg'),
                                    'カラー' => asset('images/menu-icons/color.jpg'),
                                    '白髪染め' => asset('images/menu-icons/graycolor.jpg'),
                                    'リタッチ' => asset('images/menu-icons/retouch.jpg'),
                                    'パーマ' => asset('images/menu-icons/perm.jpg'),
                                    '縮毛矯正' => asset('images/menu-icons/straight.jpg'),
                                    'コンディショナー' => asset('images/menu-icons/conditioner.jpg'),
                                    'トリートメント' => asset('images/menu-icons/treatment.jpg'),
                                    'ヘッドスパ' => asset('images/menu-icons/headspa.jpg'),
                                    'セット・ヘアアレンジ' => asset('images/menu-icons/hairset.jpg'),
                                    'メンズ' => asset('images/menu-icons/mens.jpg'),
                                    '前髪カット' => asset('images/menu-icons/bangcut.jpg'),
                                    '着付け' => asset('images/menu-icons/kitsuke.jpg'),
                                    'まつげ' => asset('images/menu-icons/eyelash_brow.jpg'),
                                    '眉' => asset('images/menu-icons/eyelash_brow.jpg'),
                                    'フェイシャル' => asset('images/menu-icons/facial.jpg'),
                                    'キッズ' => asset('images/menu-icons/kids.jpg'),
                                    'その他' => asset('images/menu-icons/other.jpg'),
                                ];

                                $menuImage = $categoryImageMap[$categoryName] ?? asset('images/menu-icons/other.jpg');
                            @endphp

                            <div class="{{ !$loop->first ? 'mt-8' : '' }}">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-1.5 h-6 rounded-full menu-accent"></div>
                                        <div class="font-bold text-[#4b3f35] text-base sm:text-lg">
                                            {{ $categoryName }}
                                        </div>
                                    </div>

                                    <div class="text-[11px] sm:text-xs text-[#9a7d63] tracking-wider">
                                        CATEGORY
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @foreach($categoryMenus as $menu)
                                        @php
                                            $isChecked = in_array((string) $menu->id, $oldMenuIds, true);
                                        @endphp

                                        <label class="block cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="menu_ids[]"
                                                value="{{ $menu->id }}"
                                                data-price="{{ $menu->price }}"
                                                data-duration="{{ $menu->duration }}"
                                                data-name="{{ $menu->name }}"
                                                class="sr-only menu-check"
                                                {{ $isChecked ? 'checked' : '' }}>

                                            <div class="menu-card relative z-10 rounded-[1.4rem] border border-[#eadfd3] bg-white p-4 sm:p-5 transition duration-200 hover:border-[#d6c5b5] hover:shadow-md">
                                                <div class="flex gap-4">
                                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-[1.2rem] overflow-hidden shrink-0 shadow-sm border border-[#efe4d8] bg-white soft-shine">
                                                        <img
                                                            src="{{ $menuImage }}"
                                                            alt="{{ $categoryName }}"
                                                            class="w-full h-full object-cover"
                                                            onerror="this.onerror=null;this.src='{{ asset('images/menu-icons/other.jpg') }}';">
                                                    </div>

                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <div class="font-bold text-[#4b3f35] text-base sm:text-lg leading-6">
                                                                        {{ $menu->name }}
                                                                    </div>

                                                                    @if($menu->is_popular)
                                                                        <span class="text-[10px] sm:text-xs px-2.5 py-1 rounded-full text-white font-bold shadow-sm"
                                                                              style="background: {{ $theme }}">
                                                                            人気
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                @if(!empty($menu->description))
                                                                    <div class="text-sm text-[#7b6654] mt-2 leading-6">
                                                                        {{ $menu->description }}
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="text-right shrink-0">
                                                                <div class="text-[11px] sm:text-xs text-[#9a7d63] mb-1">PRICE</div>
                                                                <div class="text-lg sm:text-xl font-bold text-[#4b3f35]">
                                                                    ¥{{ number_format($menu->price) }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($menu->tags->count())
                                                            <div class="flex flex-wrap gap-2 mt-3">
                                                                @foreach($menu->tags as $tag)
                                                                    <span class="text-[10px] sm:text-xs px-2.5 py-1 rounded-full bg-[#f8f2eb] text-[#7b6654] border border-[#eadfd3]">
                                                                        {{ $tag->name }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                                            <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 px-3 py-1.5 text-xs sm:text-sm font-semibold border border-amber-100">
                                                                <span>施術時間</span>
                                                                <span>{{ $menu->duration }}分</span>
                                                            </div>

                                                            <div class="text-xs sm:text-sm text-[#9a7d63]">
                                                                タップして選択
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </section>

                    {{-- 日付 --}}
                    <section class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepDateTitle" class="text-lg sm:text-xl font-bold text-[#4b3f35] transition">
                                STEP2 日付を選ぶ
                            </h2>
                            <span class="text-xs text-[#9a7d63]">DATE</span>
                        </div>

                        <div id="dateErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm"></div>

                        <div class="mb-4 rounded-2xl bg-[#fcf8f4] border border-[#eadfd3] px-4 py-3 text-sm text-[#6b5b4d] leading-6">
                            ご希望日を選ぶと、予約できる時間が表示されます。
                        </div>

                        <label class="block text-sm font-semibold text-[#7a614d] mb-2">
                            日付
                        </label>
                        <input
                            type="text"
                            id="date"
                            placeholder="日付を選択してください"
                            class="w-full border border-[#d9cabb] rounded-xl p-3.5 bg-white text-[#4b3f35] focus:outline-none focus:ring-2"
                            style="--tw-ring-color: {{ $theme }}"
                            value="{{ $oldDate }}">
                    </section>

                    {{-- 空き時間 --}}
                    <section class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepTimeTitle" class="text-lg sm:text-xl font-bold text-[#4b3f35] transition">
                                STEP3 時間を選ぶ
                            </h2>
                            <span class="text-xs text-[#9a7d63]">TIME</span>
                        </div>

                        <div id="datetimeErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm"></div>

                        <div id="slotGuide" class="text-sm text-[#9a7d63] mb-3">
                            メニューと日付を選ぶと、空いている時間が表示されます
                        </div>

                        <div class="mb-3 flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center gap-2 rounded-full bg-green-50 text-green-700 px-3 py-1 border border-green-100">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>予約可能
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 text-gray-500 px-3 py-1 border border-gray-200">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>満席
                            </span>
                        </div>

                        <div id="slots" class="space-y-5"></div>
                    </section>

                    {{-- 担当者 --}}
                    <section class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepStaffTitle" class="text-lg sm:text-xl font-bold text-[#4b3f35] transition">
                                STEP4 担当者を選ぶ（任意）
                            </h2>
                            <span class="text-xs text-[#9a7d63]">STAFF</span>
                        </div>

                        <div id="staffErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm"></div>

                        <div class="mb-4 rounded-2xl bg-[#fcf8f4] border border-[#eadfd3] px-4 py-3 text-sm text-[#6b5b4d] leading-6">
                            担当者のご希望がある場合はお選びください。<br>
                            指名しない場合は、空いている担当者をご案内します。
                        </div>

                        <div id="staffList" class="space-y-3">
                            <label class="block cursor-pointer">
                                <input type="radio"
                                       name="staff_id"
                                       value=""
                                       data-fee="0"
                                       data-name="指名しない"
                                       class="sr-only staff-radio"
                                       {{ ($oldStaffId === null || $oldStaffId === '') ? 'checked' : '' }}>

                                <div class="staff-card relative z-10 rounded-2xl border border-[#eadfd3] bg-white p-4 transition hover:border-[#d6c5b5] hover:shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-full bg-[#f3ece4] flex items-center justify-center text-[#9a8878] shrink-0">
                                            人
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-semibold text-[#4b3f35]">指名しない（おすすめ）</div>
                                            <div class="text-sm text-[#7b6654] mt-1">空いている担当者をご案内します</div>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            @foreach($staff as $s)
                                <label class="block cursor-pointer">
                                    <input
                                        type="radio"
                                        name="staff_id"
                                        value="{{ $s->id }}"
                                        data-fee="{{ $s->nomination_fee }}"
                                        data-name="{{ $s->name }}"
                                        class="sr-only staff-radio"
                                        {{ (string) $oldStaffId === (string) $s->id ? 'checked' : '' }}>

                                    <div class="staff-card relative z-10 rounded-2xl border border-[#eadfd3] bg-white p-4 transition hover:border-[#d6c5b5] hover:shadow-sm">
                                        <div class="flex gap-4">
                                            <img
                                                src="{{ $s->image_url ?? asset('images/noimage.png') }}"
                                                class="w-14 h-14 rounded-full object-cover shrink-0">

                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="font-semibold text-[#4b3f35]">
                                                        {{ $s->name }}
                                                    </div>

                                                    @if($s->nomination_fee)
                                                        <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700 font-semibold border border-amber-100">
                                                            +{{ number_format($s->nomination_fee) }}円
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($s->comment)
                                                    <div class="text-sm text-[#7b6654] mt-2 leading-6">
                                                        {{ $s->comment }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- 右カラム --}}
                <div class="space-y-6">
                    <div class="lg:sticky lg:top-6 space-y-6">

                        <div class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-[#4b3f35]">選択中の内容</h2>
                                <span class="text-xs text-[#9a7d63]">SUMMARY</span>
                            </div>

                            <div class="space-y-4 text-sm">
                                <div>
                                    <div class="text-[#9a7d63] mb-1">メニュー</div>
                                    <div id="selectedMenus" class="font-medium text-[#4b3f35] leading-6">未選択</div>
                                </div>

                                <div>
                                    <div class="text-[#9a7d63] mb-1">日付</div>
                                    <div id="selectedDateText" class="font-medium text-[#4b3f35]">未選択</div>
                                </div>

                                <div>
                                    <div class="text-[#9a7d63] mb-1">時間</div>
                                    <div id="selectedTimeText" class="font-medium text-[#4b3f35]">未選択</div>
                                </div>

                                <div>
                                    <div class="text-[#9a7d63] mb-1">担当</div>
                                    <div id="selectedStaff" class="font-medium text-[#4b3f35]">指名しない</div>
                                </div>

                                <div>
                                    <div class="text-[#9a7d63] mb-1">施術時間</div>
                                    <div id="selectedDuration" class="font-medium text-[#4b3f35]">0分</div>
                                </div>

                                <div class="pt-3 border-t border-[#e7d8ca]">
                                    <div class="text-[#9a7d63] mb-1">合計料金（目安）</div>
                                    <div class="text-2xl font-bold text-[#4b3f35]">
                                        <span id="price">0</span><span class="text-base ml-1">円</span>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-[#f8f2eb] border border-[#eadfd3] p-3 text-xs text-[#6b5b4d] leading-6">
                                    ※ 表示料金は目安です。施術内容や状態により前後する場合があります。<br>
                                    ※ 施術前に内容と料金の最終確認を行います。
                                </div>
                            </div>
                        </div>


                        <div class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-[#4b3f35]">サロン情報</h2>
                                <span class="text-xs text-[#9a7d63]">SALON INFO</span>
                            </div>

                            @if(!empty($salonMessage))
                                <div class="mb-4 rounded-2xl border border-[#eadfd3] px-4 py-3 salon-soft-box">
                                    <p class="text-sm text-[#5f5146] leading-7">{{ $salonMessage }}</p>
                                </div>
                            @endif

                            <div class="space-y-3 text-sm">
                                <div class="grid grid-cols-[88px_1fr] gap-3 border-b border-dashed border-[#eadfd3] pb-3 salon-info-row">
                                    <div class="text-[#9a7d63] font-semibold">営業時間</div>
                                    <div class="text-[#4b3f35] leading-7">{{ $businessHoursText }}</div>
                                </div>

                                <div class="grid grid-cols-[88px_1fr] gap-3 border-b border-dashed border-[#eadfd3] pb-3 salon-info-row">
                                    <div class="text-[#9a7d63] font-semibold">住所</div>
                                    <div class="text-[#4b3f35] leading-7">{{ $address }}</div>
                                </div>

                                <div class="grid grid-cols-[88px_1fr] gap-3 salon-info-row">
                                    <div class="text-[#9a7d63] font-semibold">電話番号</div>
                                    <div class="text-[#4b3f35] leading-7">{{ $phone }}</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-[#d9cabb] bg-white px-4 py-3 text-sm font-bold text-[#6b533f] hover:bg-[#fcf8f4] transition" data-accordion-target="#salonDetailAccordion">
                                    詳しいサロン情報を見る
                                </button>

                                <div id="salonDetailAccordion" class="hidden mt-4 pt-4 border-t border-[#eadfd3] space-y-4">
                                    @if(!empty($salonMessage))
                                        <div>
                                            <div class="text-sm font-bold text-[#9a7d63] mb-1.5">サロンからのメッセージ</div>
                                            <div class="text-sm text-[#6b5b4d] leading-7 whitespace-pre-line">{{ $salonMessage }}</div>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="text-sm font-bold text-[#9a7d63] mb-1.5">駐車場</div>
                                        <div class="text-sm text-[#6b5b4d] leading-7 whitespace-pre-line">{{ $parkingText }}</div>
                                    </div>

                                    <div>
                                        <div class="text-sm font-bold text-[#9a7d63] mb-1.5">お支払い方法</div>
                                        <div class="text-sm text-[#6b5b4d] leading-7 whitespace-pre-line">{{ $paymentText }}</div>
                                    </div>

                                    @if(!empty($accessText))
                                        <div>
                                            <div class="text-sm font-bold text-[#9a7d63] mb-1.5">アクセス</div>
                                            <div class="text-sm text-[#6b5b4d] leading-7 whitespace-pre-line">{{ $accessText }}</div>
                                        </div>
                                    @endif

                                    @if(!empty($salonNote))
                                        <div>
                                            <div class="text-sm font-bold text-[#9a7d63] mb-1.5">メモ</div>
                                            <div class="text-sm text-[#6b5b4d] leading-7 whitespace-pre-line">{{ $salonNote }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="relative z-10 bg-white rounded-2xl shadow-sm border border-[#eadfd3] p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-4 gap-3">
                                <h2 class="text-lg font-bold text-[#4b3f35]">最新スタイル</h2>

                                @if(Route::has('reserve.styles.index'))
                                    <a href="{{ route('reserve.styles.index', $company->company_code) }}" class="text-xs font-bold text-[#9a7d63] hover:text-[#6b533f] transition">
                                        もっと見る
                                    </a>
                                @endif
                            </div>

                            @if($stylePosts->count() > 0)
                                <div class="space-y-3">
                                    @foreach($stylePosts->take(3) as $style)
                                        @php
                                            $styleImage = $style->image_url ?? (!empty($style->image_path) ? asset('storage/' . $style->image_path) : asset('images/noimage.png'));
                                        @endphp


                                        <a href="{{ route('reserve.styles.index', $company->company_code) }}" class="style-preview-card block rounded-2xl border border-[#eadfd3] p-3 hover:bg-[#fcf8f4] transition group">
                                            <div class="flex gap-3">
                                                <img src="{{ $styleImage }}" alt="{{ $style->title ?? 'スタイル画像' }}" class="w-20 h-20 rounded-2xl object-cover shrink-0 border border-[#efe4d8] bg-white group-hover:scale-[1.02] transition">

                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-bold text-[#4b3f35] leading-6">
                                                        {{ $style->title ?? 'スタイル画像' }}
                                                    </div>

                                                    @if(!empty($style->comment))
                                                        <div class="mt-1 text-sm text-[#7b6654] leading-6 line-clamp-3">
                                                            {{ $style->comment }}
                                                        </div>
                                                    @endif

                                                    <div class="mt-2 text-[11px] font-bold text-[#9a7d63]">
                                                        画像一覧でコメントを見る
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-2xl bg-[#fcf8f4] border border-[#eadfd3] px-4 py-3 text-sm text-[#9a7d63]">
                                    まだスタイル投稿はありません。
                                </div>
                            @endif
                        </div>

                        <button
                            type="submit"
                            id="submitButtonDesktop"
                            class="hidden lg:block w-full text-white py-4 rounded-full text-base sm:text-lg font-bold shadow-lg hover:opacity-95 transition"
                            style="background: {{ $theme }};">
                            予約確認へ進む
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if(($reviewCount ?? 0) > 0)
            <section class="max-w-5xl mx-auto px-0 mt-10">
                <div class="bg-white rounded-2xl border border-[#eadfd3] shadow-sm p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-[#4b3f35]">お客様の口コミ</h2>
                            <p class="text-[#7b6654] text-sm mt-1">実際にご来店いただいたお客様のご感想です。</p>
                        </div>

                        <div class="text-right">
                            <div class="text-sm text-[#7b6654]">平均評価</div>
                            <div class="text-2xl font-bold text-amber-500">
                                ★{{ number_format((float) $averageRating, 1) }}
                            </div>
                            <div class="text-sm text-[#7b6654]">{{ $reviewCount }}件</div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($publicReviews as $review)
                            <div class="rounded-xl border border-[#eadfd3] p-4 bg-[#fcf8f4]">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="font-semibold text-[#4b3f35]">{{ $review->nickname ?: 'お客様' }}</div>
                                    <div class="text-amber-500 font-bold">★{{ $review->rating }}</div>
                                </div>

                                @if($review->comment)
                                    <div class="text-[#6b5b4d] leading-relaxed whitespace-pre-wrap">{{ $review->comment }}</div>
                                @endif

                                @if($review->owner_reply)
                                    <div class="mt-4 rounded-lg bg-white border border-[#eadfd3] p-3">
                                        <div class="text-sm font-semibold text-[#6b533f] mb-1">店舗からの返信</div>
                                        <div class="text-sm text-[#6b5b4d] whitespace-pre-wrap">{{ $review->owner_reply }}</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</div>

{{-- 下部固定バー --}}
<div class="fixed bottom-0 inset-x-0 z-40 pointer-events-none border-t border-[#e5d9cd] bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="max-w-5xl mx-auto px-4 py-3 pointer-events-auto">
        <div class="flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 text-xs sm:text-sm text-[#8a7665] mb-1">
                    <span>メニュー <span id="bottomMenuCount">0</span>件</span>
                    <span>・</span>
                    <span id="bottomDatetime">日時未選択</span>
                </div>

                <div class="flex items-end gap-2">
                    <div class="text-xs sm:text-sm text-[#9a8878]">合計</div>
                    <div class="text-xl sm:text-2xl font-bold text-[#4b3f35]">
                        <span id="bottomPrice">0</span>円
                    </div>
                </div>
            </div>

            <button
                type="button"
                id="bottomSubmitButton"
                onclick="submitReserveForm()"
                class="shrink-0 text-white px-5 sm:px-7 py-3.5 rounded-full text-sm sm:text-base font-bold shadow-lg hover:opacity-95 transition"
                style="background: {{ $theme }};">
                確認へ進む
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .menu-check:checked + .menu-card,
    .staff-radio:checked + .staff-card {
        border-color: {{ $theme }};
        box-shadow:
            0 0 0 3px rgba(183, 135, 92, 0.12),
            0 18px 40px rgba(75, 63, 53, 0.08);
        background: linear-gradient(to bottom right, #ffffff, #fcf8f4);
    }

    .menu-card,
    .staff-card {
        position: relative;
        overflow: hidden;
    }

    .menu-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.00), rgba(255,255,255,0.35));
        pointer-events: none;
    }

    .menu-card:hover,
    .staff-card:hover {
        transform: translateY(-1px);
    }

    .slot-active {
        background: {{ $theme }} !important;
        color: #fff !important;
        border-color: {{ $theme }} !important;
    }

    .menu-accent {
        background: linear-gradient(135deg, #c9a27e, #b7875c);
    }

    .soft-shine {
        position: relative;
    }

    .soft-shine::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(
            135deg,
            rgba(255,255,255,0.65) 0%,
            rgba(255,255,255,0.15) 40%,
            rgba(255,255,255,0) 100%
        );
        pointer-events: none;
    }

    .step-error {
        color: #dc2626 !important;
    }

    .step-ok {
        color: #4b3f35 !important;
    }

    .step-box {
        background: #fff;
        color: #7b6654;
        border: 1px solid #eadfd3;
        transition: all 0.2s ease;
    }

    .step-box-active {
        background: {{ $theme }};
        color: #fff;
        border-color: {{ $theme }};
        box-shadow: 0 8px 20px rgba(183, 135, 92, 0.18);
    }

    .step-box-done {
        background: #f8f2eb;
        color: #6b533f;
        border-color: #d6c5b5;
    }

    .step-box-error {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .salon-soft-box {
        background: linear-gradient(135deg, rgba(255,255,255,1), rgba(252,248,244,1));
    }

    .style-preview-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(75, 63, 53, 0.06);
    }

    @media (max-width: 640px) {
        .salon-info-row {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('reserveForm');
    const oldTime = @json($oldTime);

    bindMenuEvents();
    bindStaffEvents();

    flatpickr("#date", {
        locale: "ja",
        minDate: "today",
        dateFormat: "Y-m-d",
        defaultDate: @json($oldDate ?: null),
        onChange: async function () {
            document.getElementById('start_at').value = '';
            updateSummary();
            updateStepStates();
            clearErrors();
            await reloadAvailableStaff();
            loadSlots();
        }
    });

    form.addEventListener("submit", function (e) {
        clearErrors();
        updateStepStates(true);

        const menus = document.querySelectorAll('.menu-check:checked');
        const date = document.getElementById('date').value;
        const start = document.getElementById("start_at").value;
        const staff = document.querySelector('[name=staff_id]:checked');

        if (menus.length === 0) {
            e.preventDefault();
            showFieldError('menuErrorBox', 'メニューを選択してください。');
            scrollToStep('stepMenuTitle');
            updateStepStates(true);
            return;
        }

        if (!date) {
            e.preventDefault();
            showFieldError('dateErrorBox', '日付を選択してください。');
            scrollToStep('stepDateTitle');
            updateStepStates(true);
            return;
        }

        if (!start) {
            e.preventDefault();
            showFieldError('datetimeErrorBox', '時間を選択してください。');
            scrollToStep('stepTimeTitle');
            updateStepStates(true);
            return;
        }

        if (!staff) {
            e.preventDefault();
            showFieldError('staffErrorBox', '担当者を選択してください。');
            scrollToStep('stepStaffTitle');
            updateStepStates(true);
            return;
        }
    });

    (function showInitialErrors() {
        const hasMenuError = @json($errors->has('menu_ids'));
        const hasStartAtError = @json($errors->has('start_at'));
        const hasStaffError = @json($errors->has('staff_id'));

        if (hasMenuError) {
            showFieldError('menuErrorBox', 'メニューを選択してください。');
        }

        if (hasStartAtError && !document.getElementById('date').value) {
            showFieldError('dateErrorBox', '日付を選択してください。');
        } else if (hasStartAtError) {
            showFieldError('datetimeErrorBox', '時間を選択してください。');
        }

        if (hasStaffError) {
            showFieldError('staffErrorBox', '担当者を選択してください。');
        }
    })();

    (async function initializePage() {
        updatePrice();
        updateSummary();
        updateStepStates();

        const selectedMenus = document.querySelectorAll('.menu-check:checked');
        const date = document.getElementById('date').value;

        if (selectedMenus.length > 0 && date) {
            await reloadAvailableStaff();

            if (oldTime) {
                loadSlots(oldTime);
            } else {
                loadSlots();
            }
        }
    })();

    document.querySelectorAll('[data-accordion-target]').forEach((button) => {
        button.addEventListener('click', function () {
            const target = document.querySelector(this.dataset.accordionTarget);
            if (!target) return;

            target.classList.toggle('hidden');
            this.textContent = target.classList.contains('hidden')
                ? '詳しいサロン情報を見る'
                : '詳しいサロン情報を閉じる';
        });
    });
});

function bindMenuEvents() {
    document.querySelectorAll('.menu-check').forEach(el => {
        el.addEventListener('change', async function () {
            document.getElementById('start_at').value = '';
            updatePrice();
            updateSummary();
            updateStepStates();
            clearErrors();
            await reloadAvailableStaff();
            loadSlots();
        });
    });

    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const input = this.closest('label')?.querySelector('.menu-check');
            if (!input) return;

            input.checked = !input.checked;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

function bindStaffEvents() {
    document.querySelectorAll('[name=staff_id]').forEach(el => {
        el.addEventListener('change', function () {
            const date = document.getElementById('date').value;
            const selectedTime = document.getElementById('start_at').value;

            updatePrice();
            updateSummary();
            updateStepStates();
            clearErrors();

            if (date && selectedTime) {
                const time = selectedTime.split(' ')[1];
                loadSlots(time);
            }
        });
    });

    document.querySelectorAll('.staff-card').forEach(card => {
        card.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const input = this.closest('label')?.querySelector('.staff-radio');
            if (!input) return;

            input.checked = true;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

async function reloadAvailableStaff() {
    const date = document.getElementById('date').value;
    const menuEls = Array.from(document.querySelectorAll('.menu-check:checked'));
    const selectedTime = document.getElementById('start_at').value;
    const previouslySelected = document.querySelector('[name=staff_id]:checked')?.value ?? '';

    if (!date || menuEls.length === 0) {
        renderStaffList([], previouslySelected, false);
        return;
    }

    const params = new URLSearchParams();
    params.append('date', date);
    menuEls.forEach(m => params.append('menu_ids[]', m.value));

    if (selectedTime) {
        const time = selectedTime.split(' ')[1];
        if (time) {
            params.append('time', time);
        }
    }

    try {
        const response = await fetch(`/r/{{ $company->company_code }}/available-staff?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        const staff = Array.isArray(data.staff) ? data.staff : [];
        renderStaffList(staff, previouslySelected, true);
    } catch (e) {
        renderStaffList([], previouslySelected, true);
    }
}

function renderStaffList(staffItems, selectedValue = '', hasContext = false) {
    const staffList = document.getElementById('staffList');
    if (!staffList) return;

    const selectedTime = document.getElementById('start_at').value;
    const availabilityBadge = selectedTime
        ? '<span class="text-[10px] sm:text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-100">この時間で予約可能</span>'
        : '<span class="text-[10px] sm:text-xs px-2 py-1 rounded-full bg-sky-50 text-sky-700 font-semibold border border-sky-100">この日に勤務中</span>';

    let html = `
        <label class="block cursor-pointer">
            <input type="radio"
                   name="staff_id"
                   value=""
                   data-fee="0"
                   data-name="指名しない"
                   class="sr-only staff-radio"
                   ${selectedValue === '' ? 'checked' : ''}>

            <div class="staff-card relative z-10 rounded-2xl border border-[#eadfd3] bg-white p-4 transition hover:border-[#d6c5b5] hover:shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-[#f3ece4] flex items-center justify-center text-[#9a8878] shrink-0">
                        人
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-[#4b3f35]">指名しない（おすすめ）</div>
                        </div>
                        <div class="text-sm text-[#7b6654] mt-1">空いている担当者をご案内します</div>
                    </div>
                </div>
            </div>
        </label>
    `;

    if (hasContext && staffItems.length === 0) {
        html += `
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                この条件で候補の担当者が見つかりません。指名しない場合は時間を優先してご予約ください。
            </div>
        `;
    } else {
        html += staffItems.map(staff => `
            <label class="block cursor-pointer">
                <input
                    type="radio"
                    name="staff_id"
                    value="${staff.id}"
                    data-fee="${staff.nomination_fee ?? 0}"
                    data-name="${escapeHtml(staff.name ?? '')}"
                    class="sr-only staff-radio"
                    ${String(selectedValue) === String(staff.id) ? 'checked' : ''}>

                <div class="staff-card relative z-10 rounded-2xl border border-[#eadfd3] bg-white p-4 transition hover:border-[#d6c5b5] hover:shadow-sm">
                    <div class="flex gap-4">
                        <img
                            src="${staff.image_url ?? '{{ asset('images/noimage.png') }}'}"
                            class="w-14 h-14 rounded-full object-cover shrink-0">

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="font-semibold text-[#4b3f35]">
                                    ${escapeHtml(staff.name ?? '')}
                                </div>

                                ${(Number(staff.nomination_fee || 0) > 0) ? `
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700 font-semibold border border-amber-100">
                                        +${Number(staff.nomination_fee).toLocaleString()}円
                                    </span>
                                ` : ''}

                                ${availabilityBadge}
                            </div>

                            ${staff.comment ? `
                                <div class="text-sm text-[#7b6654] mt-2 leading-6">
                                    ${escapeHtml(staff.comment)}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </label>
        `).join('');
    }

    staffList.innerHTML = html;

    const stillExists = selectedValue === '' || staffItems.some(s => String(s.id) === String(selectedValue));
    if (!stillExists) {
        const noSelect = staffList.querySelector('input[name="staff_id"][value=""]');
        if (noSelect) {
            noSelect.checked = true;
        }
    }

    bindStaffEvents();
    updatePrice();
    updateSummary();
    updateStepStates();
}

function updatePrice() {
    const menus = document.querySelectorAll('.menu-check:checked');
    const staff = document.querySelector('[name=staff_id]:checked');

    let menuPrice = 0;
    menus.forEach(m => menuPrice += Number(m.dataset.price || 0));

    const staffFee = staff ? Number(staff.dataset.fee || 0) : 0;
    const total = menuPrice + staffFee;

    document.getElementById('price').innerText = total.toLocaleString();
    document.getElementById('bottomPrice').innerText = total.toLocaleString();
}

function updateSummary() {
    const menuEls = Array.from(document.querySelectorAll('.menu-check:checked'));
    const menuNames = menuEls.map(el => el.dataset.name);

    const staffEl = document.querySelector('[name=staff_id]:checked');
    const date = document.getElementById('date').value;
    const start = document.getElementById('start_at').value;

    let totalDuration = 0;
    menuEls.forEach(el => totalDuration += Number(el.dataset.duration || 0));

    document.getElementById('selectedMenus').innerText =
        menuNames.length ? menuNames.join(' / ') : '未選択';

    document.getElementById('selectedDateText').innerText =
        date ? date : '未選択';

    document.getElementById('selectedTimeText').innerText =
        start ? start.split(' ')[1] : '未選択';

    document.getElementById('selectedStaff').innerText =
        staffEl ? (staffEl.dataset.name || '指名しない') : '指名しない';

    document.getElementById('selectedDuration').innerText = totalDuration + '分';
    document.getElementById('bottomMenuCount').innerText = menuEls.length;

    if (start) {
        document.getElementById('bottomDatetime').innerText = start;
    } else if (date) {
        document.getElementById('bottomDatetime').innerText = date + '（時間未選択）';
    } else {
        document.getElementById('bottomDatetime').innerText = '日時未選択';
    }
}

function updateStepStates(forceHighlight = false) {
    const menus = document.querySelectorAll('.menu-check:checked');
    const date = document.getElementById('date').value;
    const start = document.getElementById('start_at').value;

    const menuDone = menus.length > 0;
    const dateDone = !!date;
    const timeDone = !!start;

    const menuTitle = document.getElementById('stepMenuTitle');
    const dateTitle = document.getElementById('stepDateTitle');
    const timeTitle = document.getElementById('stepTimeTitle');
    const staffTitle = document.getElementById('stepStaffTitle');

    setStepTitleState(menuTitle, menuDone, forceHighlight);
    setStepTitleState(dateTitle, dateDone, forceHighlight);
    setStepTitleState(timeTitle, timeDone, forceHighlight);
    setStepTitleState(staffTitle, true, forceHighlight);

    const box1 = document.getElementById('stepBox1');
    const box2 = document.getElementById('stepBox2');
    const box3 = document.getElementById('stepBox3');
    const box4 = document.getElementById('stepBox4');

    [box1, box2, box3, box4].forEach(resetStepBoxState);

    if (!menuDone) {
        setStepBoxActive(box1, forceHighlight);
        return;
    }

    setStepBoxDone(box1);

    if (!dateDone) {
        setStepBoxActive(box2, forceHighlight);
        return;
    }

    setStepBoxDone(box2);

    if (!timeDone) {
        setStepBoxActive(box3, forceHighlight);
        return;
    }

    setStepBoxDone(box3);
    setStepBoxActive(box4, false);
}

function setStepTitleState(el, isOk, forceHighlight) {
    el.classList.remove('step-error', 'step-ok');

    if (isOk) {
        el.classList.add('step-ok');
    } else {
        if (forceHighlight) {
            el.classList.add('step-error');
        } else {
            el.classList.add('step-ok');
        }
    }
}

function resetStepBoxState(el) {
    if (!el) return;
    el.classList.remove('step-box-active', 'step-box-done', 'step-box-error');
}

function setStepBoxDone(el) {
    if (!el) return;
    el.classList.add('step-box-done');
}

function setStepBoxActive(el, isError = false) {
    if (!el) return;

    if (isError) {
        el.classList.add('step-box-error');
    } else {
        el.classList.add('step-box-active');
    }
}

function loadSlots(preselectTime = '') {
    const date = document.getElementById('date').value;
    const guide = document.getElementById('slotGuide');
    const slotsBox = document.getElementById('slots');
    const staffEl = document.querySelector('[name=staff_id]:checked');

    const currentSelected = document.getElementById('start_at').value;
    const currentSelectedTime = currentSelected ? currentSelected.split(' ')[1] : '';

    if (!date) {
        document.getElementById('start_at').value = '';
        slotsBox.innerHTML = '';
        guide.innerText = '日付を選択すると空き時間が表示されます';
        updateSummary();
        updateStepStates();
        return;
    }

    const menuEls = document.querySelectorAll('.menu-check:checked');
    if (menuEls.length === 0) {
        document.getElementById('start_at').value = '';
        slotsBox.innerHTML = '';
        guide.innerText = 'メニューを選択すると空き時間が表示されます';
        updateSummary();
        updateStepStates();
        return;
    }

    const menuIds = [];
    menuEls.forEach(m => menuIds.push(m.value));

    const staff = staffEl ? staffEl.value : '';

    const params = new URLSearchParams();
    params.append('date', date);
    if (staff !== '') {
        params.append('staff_id', staff);
    }
    menuIds.forEach(id => params.append('menu_ids[]', id));

    guide.innerText = '空き時間を読み込み中です...';
    slotsBox.innerHTML = '';

    fetch(`/r/{{ $company->company_code }}/slots?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            const wantedTime = preselectTime || currentSelectedTime;

            if (!data.length) {
                document.getElementById('start_at').value = '';
                guide.innerText = '選択条件に合う空き時間がありません';
                slotsBox.innerHTML = '';
                updateSummary();
                updateStepStates();
                return;
            }

            guide.innerText = 'ご希望の時間を選択してください';

            const groups = {
                morning: [],
                afternoon: [],
                evening: [],
            };

            data.forEach(slot => {
                const hour = Number(String(slot.time).split(':')[0]);

                if (hour < 12) {
                    groups.morning.push(slot);
                } else if (hour < 17) {
                    groups.afternoon.push(slot);
                } else {
                    groups.evening.push(slot);
                }
            });

            let selectedStillExists = false;
            let html = '';

            const renderSection = (title, slots) => {
                if (!slots.length) return '';

                let sectionHtml = `
                    <div class="col-span-full mt-2 first:mt-0">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="h-px flex-1 bg-[#e7d8ca]"></div>
                            <div class="text-sm font-semibold text-[#6b533f] shrink-0">${title}</div>
                            <div class="h-px flex-1 bg-[#e7d8ca]"></div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                `;

                slots.forEach(slot => {
                    const remaining = Number(slot.remaining || 0);
                    const disabled = remaining <= 0;
                    const isSelected = wantedTime && slot.time === wantedTime;

                    if (isSelected) {
                        selectedStillExists = true;
                    }

                    if (!disabled) {
                        sectionHtml += `
                            <button type="button"
                                    data-time="${slot.time}"
                                    class="slot-btn border border-[#eadfd3] rounded-2xl px-3 py-3 text-center bg-white hover:bg-[#fcf8f4] transition ${isSelected ? 'slot-active' : ''}">
                                <div class="font-semibold ${isSelected ? 'text-white' : 'text-[#4b3f35]'}">${slot.time}</div>
                                <div class="text-xs mt-1 ${isSelected ? 'text-white' : 'text-green-600'} font-bold">○</div>
                                <div class="text-[11px] mt-1 ${isSelected ? 'text-white/90' : 'text-[#7b6654]'}">予約可能</div>
                            </button>
                        `;
                    } else {
                        sectionHtml += `
                            <div class="border border-[#efe4d8] rounded-2xl px-3 py-3 text-center bg-[#fcf8f4]">
                                <div class="font-semibold text-[#9a8878]">${slot.time}</div>
                                <div class="text-xs mt-1 text-[#9a8878] font-bold">×</div>
                                <div class="text-[11px] mt-1 text-[#9a8878]">満席</div>
                            </div>
                        `;
                    }
                });

                sectionHtml += `
                        </div>
                    </div>
                `;

                return sectionHtml;
            };

            html += renderSection('午前', groups.morning);
            html += renderSection('午後', groups.afternoon);
            html += renderSection('夕方', groups.evening);

            slotsBox.innerHTML = html;

            if (selectedStillExists && wantedTime) {
                document.getElementById('start_at').value = date + ' ' + wantedTime;
            } else {
                document.getElementById('start_at').value = '';
            }

            document.querySelectorAll('.slot-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const datetime = date + ' ' + this.dataset.time;
                    document.getElementById('start_at').value = datetime;

                    document.querySelectorAll('.slot-btn').forEach(el => {
                        el.classList.remove('slot-active');
                        el.querySelectorAll('div').forEach(d => {
                            d.classList.remove('text-white', 'text-white/90');
                        });
                    });

                    this.classList.add('slot-active');
                    this.querySelectorAll('div')[0]?.classList.add('text-white');
                    this.querySelectorAll('div')[1]?.classList.add('text-white');
                    this.querySelectorAll('div')[2]?.classList.add('text-white/90');

                    clearErrors();
                    await reloadAvailableStaff();
                    updateSummary();
                    updateStepStates();
                });
            });

            updateSummary();
            updateStepStates();
        })
        .catch(() => {
            document.getElementById('start_at').value = '';
            guide.innerText = '空き時間の取得に失敗しました';
            updateSummary();
            updateStepStates();
        });
}

function showFieldError(id, message) {
    const box = document.getElementById(id);
    if (!box) return;

    box.innerHTML = message;
    box.classList.remove('hidden');
}

function clearErrors() {
    ['menuErrorBox', 'dateErrorBox', 'datetimeErrorBox', 'staffErrorBox'].forEach(id => {
        const box = document.getElementById(id);
        if (!box) return;

        box.classList.add('hidden');
        box.innerHTML = '';
    });
}

function submitReserveForm() {
    const form = document.getElementById('reserveForm');
    if (form) {
        form.requestSubmit();
    }
}

function scrollToStep(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
@endpush