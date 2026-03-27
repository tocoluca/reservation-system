@extends('layouts.app')

@section('content')

@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="min-h-screen isolate bg-gradient-to-b from-rose-50/40 via-white to-slate-50 pb-28">
    <div class="max-w-5xl mx-auto px-4 sm:px-5 py-5 sm:py-8">

        {{-- ヘッダー --}}
        <div class="relative overflow-hidden rounded-[2rem] mb-6 sm:mb-8">
            <div class="absolute inset-0 pointer-events-none bg-gradient-to-br from-white via-pink-50 to-sky-50"></div>

            <div class="absolute -top-10 -left-10 w-40 h-40 rounded-full blur-3xl opacity-30 pointer-events-none"
                 style="background: {{ $theme }}"></div>

            <div class="absolute top-10 right-0 w-40 h-40 rounded-full blur-3xl opacity-20 bg-pink-300 pointer-events-none"></div>

            <div class="absolute bottom-0 left-1/3 w-32 h-32 rounded-full blur-3xl opacity-20 bg-amber-200 pointer-events-none"></div>

            <div class="relative z-10 text-center px-5 sm:px-8 py-10 sm:py-14 border border-white/50 bg-white/60 backdrop-blur-sm">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold text-white mb-3 shadow-sm"
                     style="background: {{ $theme }}">
                    ONLINE RESERVATION
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">
                    {{ $company->name }} 予約
                </h1>

                <p class="text-sm sm:text-base text-gray-600 mt-3 leading-7 max-w-2xl mx-auto">
                    メニュー・担当・日時を選ぶだけで、かんたんに予約できます。<br class="hidden sm:block">
                    スマホからでも見やすく、スムーズにご予約いただけます。
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

        @if($lineLoginEnabled ?? false)
            <div class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-1">
                            LINEでかんたん予約
                        </h2>
                        <p class="text-sm text-gray-500 leading-6">
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
                           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-semibold"
                           style="background:#06C755;">
                            @if(!empty($lineProfile))
                                別のLINEでログイン
                            @else
                                LINEでログイン
                            @endif
                        </a>

                        @if(!empty($lineProfile))
                            <a href="{{ route('reserve.line.logout', ['company_code' => $company->company_code]) }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-gray-200 bg-white text-gray-700 font-semibold">
                                解除
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- お知らせ --}}
        <div class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base sm:text-lg font-bold text-gray-900">お知らせ</h2>
                <span class="text-xs text-gray-400">INFORMATION</span>
            </div>

            @forelse($notices as $notice)
                <a href="{{ route('reserve.notice.show', [$company->company_code, $notice->id]) }}"
                   class="flex items-start justify-between gap-3 py-3 border-t first:border-t-0 hover:bg-gray-50 rounded-xl transition px-1">
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

                            <div class="text-sm sm:text-base font-medium text-gray-800 truncate">
                                {{ $notice->title }}
                            </div>
                        </div>
                    </div>

                    <span class="text-xs text-gray-400 shrink-0">
                        {{ $notice->created_at->format('m/d') }}
                    </span>
                </a>
            @empty
                <p class="text-sm text-gray-400">
                    現在お知らせはありません
                </p>
            @endforelse
        </div>

        <form method="POST" action="/r/{{ $company->company_code }}/confirm" id="reserveForm">
            @csrf

            <input type="hidden" name="start_at" id="start_at">

            <div class="grid lg:grid-cols-[1.4fr_0.8fr] gap-6">

                {{-- 左カラム --}}
                <div class="space-y-6">

                    {{-- STEPガイド --}}
                    <div class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-5">
                        <div class="grid grid-cols-4 gap-2 text-center text-xs sm:text-sm">
                            <div class="rounded-2xl bg-gray-50 py-3 font-semibold text-gray-700">1 メニュー</div>
                            <div class="rounded-2xl bg-gray-50 py-3 font-semibold text-gray-700">2 スタッフ</div>
                            <div class="rounded-2xl bg-gray-50 py-3 font-semibold text-gray-700">3 日付</div>
                            <div class="rounded-2xl bg-gray-50 py-3 font-semibold text-gray-700">4 時間</div>
                        </div>
                    </div>

                    {{-- メニュー --}}
                    <section class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepMenuTitle" class="text-lg sm:text-xl font-bold text-gray-900 transition">
                                STEP1 メニューを選ぶ
                            </h2>
                            <span class="text-xs text-gray-400">MENU</span>
                        </div>

                        <div id="menuErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
                        </div>

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
                                        <div class="font-bold text-gray-800 text-base sm:text-lg">
                                            {{ $categoryName }}
                                        </div>
                                    </div>

                                    <div class="text-[11px] sm:text-xs text-gray-400 tracking-wider">
                                        CATEGORY
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    @foreach($categoryMenus as $menu)
                                        <label class="block cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="menu_ids[]"
                                                value="{{ $menu->id }}"
                                                data-price="{{ $menu->price }}"
                                                data-duration="{{ $menu->duration }}"
                                                data-name="{{ $menu->name }}"
                                                class="sr-only menu-check">

                                            <div class="menu-card relative z-10 rounded-[1.4rem] border border-gray-200 bg-white p-4 sm:p-5 transition duration-200 hover:border-gray-300 hover:shadow-md">
                                                <div class="flex gap-4">
                                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-[1.2rem] overflow-hidden shrink-0 shadow-sm border border-gray-100 bg-white soft-shine">
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
                                                                    <div class="font-bold text-gray-900 text-base sm:text-lg leading-6">
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
                                                                    <div class="text-sm text-gray-500 mt-2 leading-6">
                                                                        {{ $menu->description }}
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="text-right shrink-0">
                                                                <div class="text-[11px] sm:text-xs text-gray-400 mb-1">
                                                                    PRICE
                                                                </div>
                                                                <div class="text-lg sm:text-xl font-bold text-gray-900">
                                                                    ¥{{ number_format($menu->price) }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($menu->tags->count())
                                                            <div class="flex flex-wrap gap-2 mt-3">
                                                                @foreach($menu->tags as $tag)
                                                                    <span class="text-[10px] sm:text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
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

                                                            <div class="text-xs sm:text-sm text-gray-400">
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

                    {{-- スタッフ --}}
                    <section class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepStaffTitle" class="text-lg sm:text-xl font-bold text-gray-900 transition">
                                STEP2 スタッフを選ぶ
                            </h2>
                            <span class="text-xs text-gray-400">STAFF</span>
                        </div>

                        <div id="staffErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
                        </div>

                        <div class="space-y-3">
                            <label class="block cursor-pointer">
                                <input type="radio"
                                       name="staff_id"
                                       value=""
                                       data-fee="0"
                                       data-name="指名なし"
                                       class="sr-only staff-radio">

                                <div class="staff-card relative z-10 rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-gray-300 hover:shadow-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 shrink-0">
                                            人
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">指名なし</div>
                                            <div class="text-sm text-gray-500 mt-1">空いている担当者をご案内します</div>
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
                                        class="sr-only staff-radio">

                                    <div class="staff-card relative z-10 rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-gray-300 hover:shadow-sm">
                                        <div class="flex gap-4">
                                            <img
                                                src="{{ $s->image_url ?? asset('images/noimage.png') }}"
                                                class="w-14 h-14 rounded-full object-cover shrink-0">

                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="font-semibold text-gray-900">
                                                        {{ $s->name }}
                                                    </div>

                                                    @if($s->nomination_fee)
                                                        <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-600 font-semibold">
                                                            +{{ number_format($s->nomination_fee) }}円
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($s->comment)
                                                    <div class="text-sm text-gray-500 mt-2 leading-6">
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

                    {{-- 日付と時間 --}}
                    <section class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 id="stepDatetimeTitle" class="text-lg sm:text-xl font-bold text-gray-900 transition">
                                STEP3・4 日時を選ぶ
                            </h2>
                            <span class="text-xs text-gray-400">DATE & TIME</span>
                        </div>

                        <div id="datetimeErrorBox"
                             class="hidden mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                日付
                            </label>
                            <input
                                type="text"
                                id="date"
                                placeholder="日付を選択してください"
                                class="w-full border border-gray-300 rounded-2xl p-3.5 bg-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                空き時間
                            </label>

                            <div id="slotGuide" class="text-sm text-gray-400 mb-3">
                                メニュー・担当者・日付を選ぶと、空いている時間が表示されます
                            </div>

                            <div id="slots" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
                        </div>
                    </section>
                </div>

                {{-- 右カラム --}}
                <div class="space-y-6">
                    <div class="lg:sticky lg:top-6 space-y-6">

                        <div class="relative z-10 bg-white rounded-3xl shadow-sm border border-gray-100 p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-900">
                                    選択中の内容
                                </h2>
                                <span class="text-xs text-gray-400">SUMMARY</span>
                            </div>

                            <div class="space-y-4 text-sm">
                                <div>
                                    <div class="text-gray-400 mb-1">メニュー</div>
                                    <div id="selectedMenus" class="font-medium text-gray-800 leading-6">
                                        未選択
                                    </div>
                                </div>

                                <div>
                                    <div class="text-gray-400 mb-1">担当</div>
                                    <div id="selectedStaff" class="font-medium text-gray-800">
                                        未選択
                                    </div>
                                </div>

                                <div>
                                    <div class="text-gray-400 mb-1">日時</div>
                                    <div id="selectedDatetimeText" class="font-medium text-gray-800">
                                        未選択
                                    </div>
                                </div>

                                <div>
                                    <div class="text-gray-400 mb-1">施術時間</div>
                                    <div id="selectedDuration" class="font-medium text-gray-800">
                                        0分
                                    </div>
                                </div>

                                <div class="pt-3 border-t">
                                    <div class="text-gray-400 mb-1">合計料金（目安）</div>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <span id="price">0</span><span class="text-base ml-1">円</span>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-3 text-xs text-gray-500 leading-6">
                                    ※ 表示料金は目安です。施術内容や状態により前後する場合があります。<br>
                                    ※ 施術前に内容と料金の最終確認を行います。
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            id="submitButtonDesktop"
                            class="hidden lg:block w-full text-white py-4 rounded-2xl text-base sm:text-lg font-bold shadow-lg"
                            style="background: {{ $theme }}">
                            予約確認へ進む
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 下部固定バー --}}
<div class="fixed bottom-0 inset-x-0 z-40 pointer-events-none border-t border-gray-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="max-w-5xl mx-auto px-4 py-3 pointer-events-auto">
        <div class="flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 text-xs sm:text-sm text-gray-500 mb-1">
                    <span>メニュー <span id="bottomMenuCount">0</span>件</span>
                    <span>・</span>
                    <span id="bottomDatetime">日時未選択</span>
                </div>

                <div class="flex items-end gap-2">
                    <div class="text-xs sm:text-sm text-gray-400">合計</div>
                    <div class="text-xl sm:text-2xl font-bold text-gray-900">
                        <span id="bottomPrice">0</span>円
                    </div>
                </div>
            </div>

            <button
                type="button"
                id="bottomSubmitButton"
                onclick="submitReserveForm()"
                class="shrink-0 text-white px-5 sm:px-7 py-3.5 rounded-2xl text-sm sm:text-base font-bold shadow-lg"
                style="background: {{ $theme }}">
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
            0 0 0 3px rgba(59,130,246,0.10),
            0 18px 40px rgba(15,23,42,0.08);
        background: linear-gradient(to bottom right, #ffffff, #f8fbff);
    }

    .menu-card {
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

    .menu-card:hover {
        transform: translateY(-1px);
    }

    .slot-active {
        background: {{ $theme }} !important;
        color: #fff !important;
        border-color: {{ $theme }} !important;
    }

    .menu-accent {
        background: linear-gradient(135deg, {{ $theme }}, #111827);
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
        color: #111827 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('reserveForm');

    document.querySelectorAll('.menu-check').forEach(el => {
        el.addEventListener('change', function () {
            updatePrice();
            updateSummary();
            updateStepStates();
            loadSlots();
        });
    });

    document.querySelectorAll('[name=staff_id]').forEach(el => {
        el.addEventListener('change', function () {
            updatePrice();
            updateSummary();
            updateStepStates();
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

    flatpickr("#date", {
        locale: "ja",
        minDate: "today",
        dateFormat: "Y-m-d",
        onChange: function () {
            updateSummary();
            updateStepStates();
            loadSlots();
        }
    });

    form.addEventListener("submit", function (e) {
        clearErrors();
        updateStepStates(true);

        const menus = document.querySelectorAll('.menu-check:checked');
        const staff = document.querySelector('[name=staff_id]:checked');
        const start = document.getElementById("start_at").value;

        if (menus.length === 0) {
            e.preventDefault();
            showFieldError('menuErrorBox', 'メニューを選択してください。');
            scrollToStep('stepMenuTitle');
            return;
        }

        if (!staff) {
            e.preventDefault();
            showFieldError('staffErrorBox', '担当者を選択してください。');
            scrollToStep('stepStaffTitle');
            return;
        }

        if (!start) {
            e.preventDefault();
            showFieldError('datetimeErrorBox', '日時を選択してください。');
            scrollToStep('stepDatetimeTitle');
            return;
        }
    });

    updatePrice();
    updateSummary();
    updateStepStates();
});

function updatePrice() {
    const menus = document.querySelectorAll('.menu-check:checked');
    const staff = document.querySelector('[name=staff_id]:checked');

    let menuPrice = 0;
    menus.forEach(m => menuPrice += Number(m.dataset.price || 0));

    const staffFee = staff ? Number(staff.dataset.fee || 0) : 0;
    const total = menuPrice + staffFee;

    document.getElementById('price').innerText = total;
    document.getElementById('bottomPrice').innerText = total;
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

    document.getElementById('selectedStaff').innerText =
        staffEl ? (staffEl.dataset.name || '指名なし') : '未選択';

    document.getElementById('selectedDuration').innerText = totalDuration + '分';
    document.getElementById('bottomMenuCount').innerText = menuEls.length;

    if (start) {
        document.getElementById('selectedDatetimeText').innerText = start;
        document.getElementById('bottomDatetime').innerText = start;
    } else if (date) {
        document.getElementById('selectedDatetimeText').innerText = date + '（時間未選択）';
        document.getElementById('bottomDatetime').innerText = date + '（時間未選択）';
    } else {
        document.getElementById('selectedDatetimeText').innerText = '未選択';
        document.getElementById('bottomDatetime').innerText = '日時未選択';
    }
}

function updateStepStates(forceHighlight = false) {
    const menus = document.querySelectorAll('.menu-check:checked');
    const staff = document.querySelector('[name=staff_id]:checked');
    const start = document.getElementById("start_at").value;

    const menuTitle = document.getElementById('stepMenuTitle');
    const staffTitle = document.getElementById('stepStaffTitle');
    const datetimeTitle = document.getElementById('stepDatetimeTitle');

    setStepState(menuTitle, menus.length > 0, forceHighlight);
    setStepState(staffTitle, !!staff, forceHighlight);
    setStepState(datetimeTitle, !!start, forceHighlight);
}

function setStepState(el, isOk, forceHighlight) {
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

function loadSlots() {
    const date = document.getElementById('date').value;
    const guide = document.getElementById('slotGuide');
    const slotsBox = document.getElementById('slots');
    const staffEl = document.querySelector('[name=staff_id]:checked');

    document.getElementById('start_at').value = '';
    updateSummary();
    updateStepStates();

    if (!date) {
        slotsBox.innerHTML = '';
        guide.innerText = '日付を選択すると空き時間が表示されます';
        return;
    }

    const menuEls = document.querySelectorAll('.menu-check:checked');
    if (menuEls.length === 0) {
        slotsBox.innerHTML = '';
        guide.innerText = 'メニューを選択すると空き時間が表示されます';
        return;
    }

    if (!staffEl) {
        slotsBox.innerHTML = '';
        guide.innerText = '担当者を選択すると空き時間が表示されます';
        return;
    }

    const menuIds = [];
    menuEls.forEach(m => menuIds.push(m.value));

    const staff = staffEl.value;

    const params = new URLSearchParams();
    params.append('date', date);
    params.append('staff_id', staff);
    menuIds.forEach(id => params.append('menu_ids[]', id));

    guide.innerText = '空き時間を読み込み中です...';
    slotsBox.innerHTML = '';

    fetch(`/r/{{ $company->company_code }}/slots?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            let html = '';

            if (!data.length) {
                guide.innerText = '選択条件に合う空き時間がありません';
                slotsBox.innerHTML = '';
                return;
            }

            guide.innerText = 'ご希望の時間を選択してください';

            data.forEach(slot => {
                let statusText = '';
                let disabled = false;
                let statusClass = '';

                if (slot.remaining >= 3) {
                    statusText = '◎';
                    statusClass = 'text-emerald-600';
                } else if (slot.remaining == 2) {
                    statusText = '○';
                    statusClass = 'text-green-600';
                } else if (slot.remaining == 1) {
                    statusText = '△';
                    statusClass = 'text-amber-500';
                } else {
                    statusText = '×';
                    statusClass = 'text-gray-400';
                    disabled = true;
                }

                if (!disabled) {
                    html += `
                        <button type="button"
                                data-time="${slot.time}"
                                class="slot-btn border border-gray-200 rounded-2xl px-3 py-3 text-center bg-white hover:bg-gray-50 transition">
                            <div class="font-semibold text-gray-800">${slot.time}</div>
                            <div class="text-xs mt-1 ${statusClass} font-bold">${statusText}</div>
                        </button>
                    `;
                } else {
                    html += `
                        <div class="border border-gray-100 rounded-2xl px-3 py-3 text-center bg-gray-50">
                            <div class="font-semibold text-gray-400">${slot.time}</div>
                            <div class="text-xs mt-1 ${statusClass} font-bold">${statusText}</div>
                        </div>
                    `;
                }
            });

            slotsBox.innerHTML = html;

            document.querySelectorAll('.slot-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const datetime = date + ' ' + this.dataset.time;
                    document.getElementById('start_at').value = datetime;

                    document.querySelectorAll('.slot-btn').forEach(el => {
                        el.classList.remove('slot-active');
                    });

                    this.classList.add('slot-active');
                    clearErrors();
                    updateSummary();
                    updateStepStates();
                });
            });
        })
        .catch(() => {
            guide.innerText = '空き時間の取得に失敗しました';
        });
}

function showFieldError(id, message) {
    const box = document.getElementById(id);
    if (!box) return;

    box.innerHTML = message;
    box.classList.remove('hidden');
}

function clearErrors() {
    ['menuErrorBox', 'staffErrorBox', 'datetimeErrorBox'].forEach(id => {
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
</script>
@endpush