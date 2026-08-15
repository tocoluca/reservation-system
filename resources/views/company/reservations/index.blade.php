@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $reservationStatusMeta = function ($reservation) {
        $status = is_object($reservation) ? $reservation->status : $reservation;
        $cancelledType = is_object($reservation) ? $reservation->cancelled_type : null;

        if ($status === 'cancelled') {
            return match ($cancelledType) {
                'customer' => ['label' => 'キャンセル（連絡あり）', 'class' => 'bg-sky-100 text-sky-700'],
                'shop' => ['label' => 'キャンセル（店舗都合）', 'class' => 'bg-stone-200 text-stone-700'],
                default => ['label' => 'キャンセル', 'class' => 'bg-stone-200 text-stone-700'],
            };
        }

        return match ($status) {
            'completed' => ['label' => '来店済', 'class' => 'bg-blue-100 text-blue-700'],
            'cancelled' => ['label' => 'キャンセル', 'class' => 'bg-stone-200 text-stone-700'],
            'no_show' => ['label' => '無断キャンセル', 'class' => 'bg-red-100 text-red-700'],
            'reserved' => ['label' => '予約中', 'class' => 'bg-emerald-100 text-emerald-700'],
            default => ['label' => $status, 'class' => 'bg-amber-100 text-amber-700'],
        };
    };

    $currentReservationFilters = collect(request()->only(['keyword', 'date_from', 'date_to', 'status', 'customer_id']))
        ->filter(fn ($value) => filled($value))
        ->all();
    $hasActiveReservationFilters = count($currentReservationFilters) > 0;
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold bg-white/15 text-white/90">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }};"></span>
                        RESERVATION LIST
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-bold text-white mt-4 tracking-tight">
                        予約一覧
                    </h1>

                    <p class="text-white/85 mt-2 text-sm sm:text-base leading-relaxed">
                        全顧客の予約確認とキャンセルができます。顧客名・電話番号・日付で絞り込み可能です。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.reserve', ['mode' => 'day']) }}"
                       class="group inline-flex items-center gap-3 rounded-2xl px-5 py-3 text-white bg-white/15 hover:bg-white/20 backdrop-blur-sm transition">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-lg font-bold">
                            予
                        </span>

                        <span class="text-left leading-tight">
                            <span class="block text-sm font-bold">予約カレンダー</span>
                            <span class="block text-[11px] text-white/80">登録・確認はこちら</span>
                        </span>
                    </a>

                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-semibold text-white">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 直近予約サマリー --}}
    @if(!empty($customerFilter))
        <div class="rounded-[1.75rem] border border-sky-100 bg-sky-50 p-5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="text-sm font-bold text-sky-800">顧客で絞り込み中</div>
                    <div class="mt-1 text-lg font-black text-gray-900">{{ $customerFilter->name }}</div>
                    <div class="mt-1 text-sm text-gray-600">{{ $customerFilter->phone ?: '電話番号未登録' }}</div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('company.customers.show', $customerFilter->id) }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-sky-700 border border-sky-200 hover:bg-sky-50 transition">
                        顧客詳細へ戻る
                    </a>
                    <a href="{{ route('company.reservations.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-gray-700 border border-gray-200 hover:bg-gray-50 transition">
                        絞り込み解除
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- 直近予約サマリー --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('company.reservations.index', ['date_from' => $today, 'date_to' => $today, 'status' => 'reserved']) }}"
           class="block rounded-[1.75rem] border bg-white p-5 shadow-sm hover:shadow-md transition"
           style="border-color: {{ $theme }}22;">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-stone-500">今日の予約</div>
                    <div class="mt-2 text-3xl font-bold" style="color: {{ $theme }};">
                        {{ $todayReservedCount }}件
                    </div>
                    <div class="mt-2 text-xs text-stone-400">
                        {{ \Carbon\Carbon::parse($today)->format('m/d') }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-lg">
                    📅
                </div>
            </div>
        </a>

        <a href="{{ route('company.reservations.index', ['date_from' => $tomorrow, 'date_to' => $tomorrow, 'status' => 'reserved']) }}"
           class="block rounded-[1.75rem] border bg-white p-5 shadow-sm hover:shadow-md transition"
           style="border-color: {{ $theme }}22;">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-stone-500">
                        明日の予約
                    </div>
                    <div class="mt-2 text-3xl font-bold" style="color: {{ $theme }};">
                        {{ $tomorrowReservedCount }}件
                    </div>
                    <div class="mt-2 text-xs text-stone-400">
                        {{ \Carbon\Carbon::parse($tomorrow)->format('m/d') }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-lg">
                    ⏰
                </div>
            </div>
        </a>

        <a href="{{ route('company.reservations.index', ['date_from' => $dayAfterTomorrow, 'date_to' => $dayAfterTomorrow, 'status' => 'reserved']) }}"
           class="block rounded-[1.75rem] border bg-white p-5 shadow-sm hover:shadow-md transition"
           style="border-color: {{ $theme }}22;">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm text-stone-500">
                        明後日の予約
                    </div>
                    <div class="mt-2 text-3xl font-bold" style="color: {{ $theme }};">
                        {{ $dayAfterTomorrowReservedCount }}件
                    </div>
                    <div class="mt-2 text-xs text-stone-400">
                        {{ \Carbon\Carbon::parse($dayAfterTomorrow)->format('m/d') }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center text-lg">
                    🗓️
                </div>
            </div>
        </a>
    </div>

    <div class="sticky top-24 z-30 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-bold text-stone-900">よく使う絞り込み</div>
                <div class="text-xs text-stone-500 mt-1">日常確認で使う条件をすぐ切り替えられます。</div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('company.reservations.index', ['date_from' => $today, 'date_to' => $today, 'status' => 'reserved']) }}"
                   class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold {{ $dateFrom === $today && $dateTo === $today && $status === 'reserved' ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
                   style="{{ $dateFrom === $today && $dateTo === $today && $status === 'reserved' ? 'background: '.$theme : '' }}">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    今日
                </a>

                <a href="{{ route('company.reservations.index', ['date_from' => $tomorrow, 'date_to' => $tomorrow, 'status' => 'reserved']) }}"
                   class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold {{ $dateFrom === $tomorrow && $dateTo === $tomorrow && $status === 'reserved' ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
                   style="{{ $dateFrom === $tomorrow && $dateTo === $tomorrow && $status === 'reserved' ? 'background: '.$theme : '' }}">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    明日
                </a>

                <a href="{{ route('company.reservations.index', ['status' => 'reserved']) }}"
                   class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold {{ empty($dateFrom) && empty($dateTo) && $status === 'reserved' ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
                   style="{{ empty($dateFrom) && empty($dateTo) && $status === 'reserved' ? 'background: '.$theme : '' }}">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                    予約中
                </a>

                <a href="{{ route('company.reservations.index', ['status' => 'cancelled']) }}"
                   class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold {{ $status === 'cancelled' ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
                   style="{{ $status === 'cancelled' ? 'background: '.$theme : '' }}">
                    <i data-lucide="ban" class="w-4 h-4"></i>
                    キャンセル
                </a>

                <a href="{{ route('company.reservations.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold border border-stone-200 bg-white text-stone-700 hover:bg-stone-50">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    リセット
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border px-4 py-3 text-sm shadow-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm shadow-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 検索 --}}
    <div class="bg-white shadow-sm rounded-[2rem] border border-stone-200 overflow-hidden">
        <details class="group" {{ $hasActiveReservationFilters ? 'open' : '' }}>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 bg-gradient-to-r from-stone-50 to-white">
                <span class="min-w-0">
                    <span class="block text-lg font-bold text-stone-800">予約を絞り込む</span>
                    <span class="mt-1 block text-sm text-stone-500">
                        顧客名・電話番号・日付・状態から条件を指定できます。
                    </span>
                </span>
                <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-bold text-stone-600">
                    @if($hasActiveReservationFilters)
                        条件あり
                    @else
                        詳細条件
                    @endif
                    <span class="text-base leading-none transition group-open:rotate-180">⌄</span>
                </span>
            </summary>

            <div class="border-t border-stone-200 px-6 py-5">
                <form method="GET" action="{{ route('company.reservations.index') }}"
                      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">

                <div class="xl:col-span-2">
                    <label class="block text-xs text-stone-500 mb-1">顧客名または電話番号(数字のみ)</label>
                    <input type="text"
                           name="keyword"
                           value="{{ $keyword }}"
                           placeholder="例：山田 / 09012345678"
                           class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-stone-200 bg-white">
                </div>

                <div>
                    <label class="block text-xs text-stone-500 mb-1">開始日</label>
                    <input type="date"
                           name="date_from"
                           value="{{ $dateFrom }}"
                           class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-stone-200 bg-white">
                </div>

                <div>
                    <label class="block text-xs text-stone-500 mb-1">終了日</label>
                    <input type="date"
                           name="date_to"
                           value="{{ $dateTo }}"
                           class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-stone-200 bg-white">
                </div>

                <div>
                    <label class="block text-xs text-stone-500 mb-1">状態</label>
                    <select name="status"
                            class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-stone-200">
                        <option value="">すべて</option>
                        <option value="reserved" {{ $status === 'reserved' ? 'selected' : '' }}>予約中</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>来店済</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>キャンセル</option>
                        <option value="no_show" {{ $status === 'no_show' ? 'selected' : '' }}>無断キャンセル</option>
                    </select>
                </div>

                <div class="md:col-span-2 xl:col-span-5 flex flex-wrap gap-2 pt-1">
                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                            style="background: {{ $theme }};">
                        検索する
                    </button>

                    <a href="{{ route('company.reservations.index') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-stone-300 bg-white text-sm font-medium text-stone-700 hover:bg-stone-50 transition">
                        全件表示
                    </a>

                    <a href="{{ route('company.reservations.index', ['date_from' => $today, 'date_to' => $today, 'status' => 'reserved']) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        今日の予約
                    </a>

                    <a href="{{ route('company.reservations.index', ['date_from' => $tomorrow, 'date_to' => $tomorrow, 'status' => 'reserved']) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        明日({{ \Carbon\Carbon::parse($tomorrow)->format('m/d') }})の予約
                    </a>

                    <a href="{{ route('company.reservations.index', ['date_from' => $dayAfterTomorrow, 'date_to' => $dayAfterTomorrow, 'status' => 'reserved']) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        明後日({{ \Carbon\Carbon::parse($dayAfterTomorrow)->format('m/d') }})の予約
                    </a>
                </div>
                </form>
            </div>
        </details>

        @if($hasActiveReservationFilters)
            <div class="flex flex-wrap items-center gap-2 border-t border-stone-200 bg-stone-50/80 px-6 py-3">
                <span class="mr-1 text-xs font-bold text-stone-500">適用中の条件</span>
                @if(filled(request('keyword')))
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-700 shadow-sm">キーワード：{{ request('keyword') }}</span>
                @endif
                @if(filled(request('date_from')))
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-700 shadow-sm">開始日：{{ request('date_from') }}</span>
                @endif
                @if(filled(request('date_to')))
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-700 shadow-sm">終了日：{{ request('date_to') }}</span>
                @endif
                @if(filled(request('status')))
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-stone-700 shadow-sm">状態：{{ $reservationStatusMeta(request('status'))['label'] }}</span>
                @endif
                @if(!empty($customerFilter))
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 shadow-sm">顧客：{{ $customerFilter->name }}</span>
                @endif
                <a href="{{ route('company.reservations.index') }}"
                   class="ml-auto text-xs font-bold underline"
                   style="color: {{ $theme }};">
                    条件をクリア
                </a>
            </div>
        @endif

        {{-- PC表示 --}}
        <div class="hidden lg:block max-h-[72vh] overflow-y-auto overflow-x-hidden">
            <table class="w-full text-sm table-fixed">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr class="text-left text-stone-600">
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold w-[120px] shadow-sm border-b border-stone-300">
                            予約日時
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold w-[120px] shadow-sm border-b border-stone-300">
                            顧客名
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold w-[120px] shadow-sm border-b border-stone-300">
                            電話番号
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold w-[100px] shadow-sm border-b border-stone-300">
                            主担当
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold shadow-sm border-b border-stone-300">
                            施術・担当内訳
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold w-[100px] shadow-sm border-b border-stone-300">
                            状態
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-3 py-3 font-semibold w-[112px] shadow-sm border-b border-stone-300">
                            操作
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reservations as $reservation)
                        @php
                            $displayCustomerName = $reservation->customer_name ?: optional($reservation->customer)->name ?: '－';
                            $displayPhone = $reservation->customer_phone ?: optional($reservation->customer)->phone ?: '－';
                            $menuNames = $reservation->menus->pluck('name')->filter()->values();
                            $menuText = $menuNames->isNotEmpty() ? $menuNames->join('、') : '－';

                            $detailRows = $reservation->details
                                ->filter(fn($detail) => $detail->menu || $detail->staff)
                                ->map(function ($detail) {
                                    $menuName = optional($detail->menu)->name ?: 'メニュー未設定';
                                    $staffName = optional($detail->staff)->name ?: '担当未設定';
                                    $timeText = '';

                                    if ($detail->start_at && $detail->end_at) {
                                        $timeText = $detail->start_at->format('H:i') . '〜' . $detail->end_at->format('H:i');
                                    }

                                    return [
                                        'menu_name' => $menuName,
                                        'staff_name' => $staffName,
                                        'time_text' => $timeText,
                                    ];
                                })
                                ->values();

                            $confirmDetailText = $detailRows->isNotEmpty()
                                ? $detailRows->map(function ($row) {
                                    return $row['menu_name'] . '：' . $row['staff_name'];
                                })->join(' / ')
                                : $menuText;
                        @endphp

                        <tr class="border-b border-stone-100 hover:bg-amber-50/40 transition align-top">
                            <td class="px-3 py-4 text-stone-800 font-semibold break-words">
                                <div>{{ optional($reservation->start_at)->format('Y/m/d') }}</div>
                                <div class="text-sm text-stone-500 mt-1">{{ optional($reservation->start_at)->format('H:i') }}</div>
                            </td>

                            <td class="px-3 py-4 text-stone-800 break-words">
                                @if($reservation->customer_id)
                                    <a href="{{ route('company.customers.show', $reservation->customer_id) }}"
                                       class="font-bold hover:underline"
                                       style="color: {{ $theme }};">
                                        {{ $displayCustomerName }}
                                    </a>
                                @else
                                    {{ $displayCustomerName }}
                                @endif
                            </td>

                            <td class="px-3 py-4 text-stone-700 break-all">
                                {{ $displayPhone }}
                            </td>

                            <td class="px-3 py-4 text-stone-700 break-words">
                                {{ optional($reservation->staff)->name ?: '未指定' }}
                            </td>

                            <td class="px-3 py-4 text-stone-700">
                                @if($detailRows->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach($detailRows as $row)
                                            <div class="rounded-xl border border-stone-200 bg-stone-50 px-3 py-2">
                                                <div class="font-medium text-stone-800 break-words">
                                                    {{ $row['menu_name'] }}
                                                </div>
                                                <div class="text-xs text-stone-500 mt-1">
                                                    担当：{{ $row['staff_name'] }}
                                                    @if($row['time_text'] !== '')
                                                        <span class="ml-2">({{ $row['time_text'] }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="whitespace-normal break-words leading-6">
                                        {{ $menuText }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-4">
                                @php
                                    $statusMeta = $reservationStatusMeta($reservation);
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>

                            <td class="px-3 py-4">
                                @if($reservation->status === 'reserved')
                                    <div class="flex flex-col gap-2">
                                        <form method="POST"
                                              action="{{ route('company.reservations.complete', $reservation->id) }}"
                                              data-busy-form="true"
                                              onsubmit="return confirm('この予約を来店済みにしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}');">
                                            @csrf
                                            @foreach($currentReservationFilters as $filterKey => $filterValue)
                                                <input type="hidden" name="filters[{{ $filterKey }}]" value="{{ $filterValue }}">
                                            @endforeach
                                            <button type="submit"
                                                    data-busy-button
                                                    class="w-full inline-flex items-center justify-center px-2 py-2 rounded-xl bg-blue-600 text-white text-[11px] font-semibold hover:opacity-90 transition shadow-sm whitespace-nowrap">
                                                来店済
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('company.reservations.cancel', $reservation->id) }}"
                                              class="js-cancel-form"
                                              data-busy-form="true">
                                            @csrf
                                            @foreach($currentReservationFilters as $filterKey => $filterValue)
                                                <input type="hidden" name="filters[{{ $filterKey }}]" value="{{ $filterValue }}">
                                            @endforeach
                                            <input type="hidden" name="cancel_kind" value="">
                                            <details class="relative">
                                                <summary class="flex cursor-pointer list-none items-center justify-center rounded-xl border border-stone-200 bg-white px-2 py-2 text-[11px] font-semibold text-stone-600 hover:bg-stone-50">
                                                    その他の操作
                                                </summary>
                                                <div class="absolute right-0 top-full z-20 mt-2 w-36 rounded-xl border border-stone-200 bg-white p-1 shadow-lg">
                                                    <button type="button"
                                                            data-cancel-open
                                                            data-busy-button
                                                            data-reservation-date="{{ optional($reservation->start_at)->format('Y/m/d H:i') }}"
                                                            data-customer-name="{{ $displayCustomerName }}"
                                                            data-customer-phone="{{ $displayPhone }}"
                                                            class="w-full rounded-lg px-3 py-2 text-left text-[11px] font-semibold text-rose-700 hover:bg-rose-50">
                                                        キャンセル
                                                    </button>
                                                </div>
                                            </details>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-stone-400 text-xs">操作不可</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-stone-400">
                                該当する予約はありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- スマホ・タブレット表示 --}}
        <div class="grid grid-cols-1 gap-4 p-4 lg:hidden">
            @forelse($reservations as $reservation)
                @php
                    $displayCustomerName = $reservation->customer_name ?: optional($reservation->customer)->name ?: '－';
                    $displayPhone = $reservation->customer_phone ?: optional($reservation->customer)->phone ?: '－';
                    $menuNames = $reservation->menus->pluck('name')->filter()->values();
                    $menuText = $menuNames->isNotEmpty() ? $menuNames->join('、') : '－';

                    $detailRows = $reservation->details
                        ->filter(fn($detail) => $detail->menu || $detail->staff)
                        ->map(function ($detail) {
                            $menuName = optional($detail->menu)->name ?: 'メニュー未設定';
                            $staffName = optional($detail->staff)->name ?: '担当未設定';
                            $timeText = '';

                            if ($detail->start_at && $detail->end_at) {
                                $timeText = $detail->start_at->format('H:i') . '〜' . $detail->end_at->format('H:i');
                            }

                            return [
                                'menu_name' => $menuName,
                                'staff_name' => $staffName,
                                'time_text' => $timeText,
                            ];
                        })
                        ->values();

                    $confirmDetailText = $detailRows->isNotEmpty()
                        ? $detailRows->map(function ($row) {
                            return $row['menu_name'] . '：' . $row['staff_name'];
                        })->join(' / ')
                        : $menuText;
                @endphp

                <div class="rounded-[1.75rem] border border-stone-200 bg-white p-4 shadow-sm space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs text-stone-500">予約日時</div>
                            <div class="font-bold text-stone-800 mt-1">
                                {{ optional($reservation->start_at)->format('Y/m/d H:i') }}
                            </div>
                        </div>

                        <div>
                            @php
                                $statusMeta = $reservationStatusMeta($reservation);
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['class'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-stone-50 px-3 py-3">
                            <div class="text-xs text-stone-500">顧客名</div>
                            <div class="text-stone-800 mt-1 font-medium">
                                @if($reservation->customer_id)
                                    <a href="{{ route('company.customers.show', $reservation->customer_id) }}"
                                       class="font-bold hover:underline"
                                       style="color: {{ $theme }};">
                                        {{ $displayCustomerName }}
                                    </a>
                                @else
                                    {{ $displayCustomerName }}
                                @endif
                            </div>
                        </div>

                        <div class="rounded-xl bg-stone-50 px-3 py-3">
                            <div class="text-xs text-stone-500">電話番号</div>
                            <div class="text-stone-700 mt-1 break-all">{{ $displayPhone }}</div>
                        </div>

                        <div class="rounded-xl bg-stone-50 px-3 py-3 sm:col-span-2">
                            <div class="text-xs text-stone-500">主担当</div>
                            <div class="text-stone-700 mt-1">{{ optional($reservation->staff)->name ?: '未指定' }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <div class="text-xs text-stone-500 mb-2">施術・担当内訳</div>

                            @if($detailRows->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($detailRows as $row)
                                        <div class="rounded-xl border border-stone-200 bg-stone-50 px-3 py-3">
                                            <div class="font-medium text-stone-800 break-words">
                                                {{ $row['menu_name'] }}
                                            </div>
                                            <div class="text-xs text-stone-500 mt-1">
                                                担当：{{ $row['staff_name'] }}
                                                @if($row['time_text'] !== '')
                                                    <span class="ml-2">({{ $row['time_text'] }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-stone-700 break-words leading-6 rounded-xl border border-stone-200 bg-stone-50 px-3 py-3">
                                    {{ $menuText }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-1">
                        @if($reservation->status === 'reserved')
                            <form method="POST"
                                  action="{{ route('company.reservations.complete', $reservation->id) }}"
                                  data-busy-form="true"
                                  class="mb-2"
                                  onsubmit="return confirm('この予約を来店済みにしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}');">
                                @csrf
                                @foreach($currentReservationFilters as $filterKey => $filterValue)
                                    <input type="hidden" name="filters[{{ $filterKey }}]" value="{{ $filterValue }}">
                                @endforeach
                                <button type="submit"
                                        data-busy-button
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-blue-600 text-white text-sm font-semibold hover:opacity-90 transition shadow-sm">
                                    来店済みにする
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('company.reservations.cancel', $reservation->id) }}"
                                  class="js-cancel-form"
                                  data-busy-form="true">
                                @csrf
                                @foreach($currentReservationFilters as $filterKey => $filterValue)
                                    <input type="hidden" name="filters[{{ $filterKey }}]" value="{{ $filterValue }}">
                                @endforeach
                                <input type="hidden" name="cancel_kind" value="">
                                <details class="relative">
                                    <summary class="flex cursor-pointer list-none items-center justify-center rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                                        その他の操作
                                    </summary>
                                    <div class="absolute inset-x-0 top-full z-20 mt-2 rounded-2xl border border-stone-200 bg-white p-2 shadow-lg">
                                        <button type="button"
                                                data-cancel-open
                                                data-busy-button
                                                data-reservation-date="{{ optional($reservation->start_at)->format('Y/m/d H:i') }}"
                                                data-customer-name="{{ $displayCustomerName }}"
                                                data-customer-phone="{{ $displayPhone }}"
                                                class="w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50">
                                            この予約をキャンセル
                                        </button>
                                    </div>
                                </details>
                            </form>
                        @else
                            <div class="text-stone-400 text-xs">操作不可</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-stone-400">
                    該当する予約はありません。
                </div>
            @endforelse
        </div>

        <div class="px-6 py-4 bg-white border-t border-stone-100">
            {{ $reservations->links() }}
        </div>
    </div>
</div>

<div id="cancelKindModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-stone-900">キャンセル種別を選択</h2>
                <p class="mt-1 text-sm text-stone-500">この予約をどの扱いで記録するか選んでください。</p>
            </div>
            <button type="button"
                    class="rounded-full bg-stone-100 px-3 py-1.5 text-sm font-bold text-stone-600 hover:bg-stone-200"
                    data-cancel-close>
                閉じる
            </button>
        </div>

        <div class="mt-4 rounded-2xl bg-stone-50 px-4 py-3 text-sm text-stone-700">
            <div class="font-semibold" id="cancelModalCustomer">-</div>
            <div class="mt-1 text-xs text-stone-500" id="cancelModalDate">-</div>
            <div class="mt-1 text-xs text-stone-500" id="cancelModalPhone">-</div>
        </div>

        <div class="mt-5 grid gap-3">
            <button type="button"
                    data-cancel-kind="customer"
                    class="w-full rounded-2xl bg-sky-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90">
                連絡あり
            </button>
            <button type="button"
                    data-cancel-kind="no_show"
                    class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90">
                無断キャンセル
            </button>
            <button type="button"
                    data-cancel-kind="shop"
                    class="w-full rounded-2xl px-4 py-3 text-sm font-bold text-white shadow-sm hover:opacity-90"
                    style="background: {{ $theme }};">
                店舗都合
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('cancelKindModal');
    const customerText = document.getElementById('cancelModalCustomer');
    const dateText = document.getElementById('cancelModalDate');
    const phoneText = document.getElementById('cancelModalPhone');
    let activeForm = null;

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeForm = null;
    };

    document.querySelectorAll('[data-cancel-open]').forEach((button) => {
        button.addEventListener('click', function () {
            activeForm = this.closest('form');
            customerText.textContent = this.dataset.customerName || '-';
            dateText.textContent = this.dataset.reservationDate ? `予約日時：${this.dataset.reservationDate}` : '予約日時：-';
            phoneText.textContent = this.dataset.customerPhone ? `電話番号：${this.dataset.customerPhone}` : '電話番号：-';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    document.querySelectorAll('[data-cancel-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.querySelectorAll('[data-cancel-kind]').forEach((button) => {
        button.addEventListener('click', function () {
            if (!activeForm) return;

            const input = activeForm.querySelector('input[name="cancel_kind"]');
            if (input) {
                input.value = this.dataset.cancelKind;
            }

            activeForm.requestSubmit();
        });
    });
});
</script>
@endsection
