@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                 style="background-color: {{ $theme }}15; color: {{ $theme }};">
                Reservation List
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-stone-800 mt-3">予約一覧</h1>
            <p class="text-stone-500 mt-2 text-sm">
                全顧客の予約を確認できます。顧客名・電話番号・日付で絞り込み可能です。
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('company.reserve', ['mode' => 'day']) }}"
               class="group inline-flex items-center gap-3 rounded-2xl px-5 py-3 text-white shadow-lg hover:opacity-95 transition"
               style="background: linear-gradient(135deg, {{ $theme }} 0%, {{ $theme }}dd 100%);">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-lg font-bold">
                    予
                </span>

                <span class="text-left leading-tight">
                    <span class="block text-sm font-bold">予約カレンダー</span>
                    <span class="block text-[11px] text-white/80">
                        登録・確認はこちら
                    </span>
                </span>
            </a>

            <a href="{{ route('company.dashboard') }}"
               class="inline-flex items-center justify-center px-4 py-3 rounded-xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
               style="border-color: {{ $theme }}; color: {{ $theme }};">
                ← ダッシュボード
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('company.reservations.index', ['date_from' => $today, 'date_to' => $today, 'status' => 'reserved']) }}"
           class="block rounded-2xl border bg-white p-5 shadow-sm hover:bg-stone-50 transition"
           style="border-color: {{ $theme }}40;">
            <div class="text-sm text-stone-500">今日の予約</div>
            <div class="mt-2 text-3xl font-bold" style="color: {{ $theme }};">
                {{ $todayReservedCount }}件
            </div>
            <div class="mt-2 text-xs text-stone-400">
                {{ \Carbon\Carbon::parse($today)->format('m/d') }}
            </div>
        </a>

        <a href="{{ route('company.reservations.index', ['date_from' => $tomorrow, 'date_to' => $tomorrow, 'status' => 'reserved']) }}"
           class="block rounded-2xl border bg-white p-5 shadow-sm hover:bg-stone-50 transition"
           style="border-color: {{ $theme }}40;">
            <div class="text-sm text-stone-500">
                明日({{ \Carbon\Carbon::parse($tomorrow)->format('m/d') }})の予約
            </div>
            <div class="mt-2 text-3xl font-bold" style="color: {{ $theme }};">
                {{ $tomorrowReservedCount }}件
            </div>
            <div class="mt-2 text-xs text-stone-400">
                {{ \Carbon\Carbon::parse($tomorrow)->format('m/d') }}
            </div>
        </a>

        <a href="{{ route('company.reservations.index', ['date_from' => $dayAfterTomorrow, 'date_to' => $dayAfterTomorrow, 'status' => 'reserved']) }}"
           class="block rounded-2xl border bg-white p-5 shadow-sm hover:bg-stone-50 transition"
           style="border-color: {{ $theme }}40;">
            <div class="text-sm text-stone-500">
                明後日({{ \Carbon\Carbon::parse($dayAfterTomorrow)->format('m/d') }})の予約
            </div>
            <div class="mt-2 text-3xl font-bold" style="color: {{ $theme }};">
                {{ $dayAfterTomorrowReservedCount }}件
            </div>
            <div class="mt-2 text-xs text-stone-400">
                {{ \Carbon\Carbon::parse($dayAfterTomorrow)->format('m/d') }}
            </div>
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-sm rounded-2xl border border-stone-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-stone-200 bg-stone-50">
            <form method="GET" action="{{ route('company.reservations.index') }}"
                  class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">

                <div class="xl:col-span-2">
                    <label class="block text-xs text-stone-500 mb-1">顧客名または電話番号(数字のみ)</label>
                    <input type="text"
                           name="keyword"
                           value="{{ $keyword }}"
                           placeholder="例：山田 / 09012345678"
                           class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-stone-200">
                </div>

                <div>
                    <label class="block text-xs text-stone-500 mb-1">開始日</label>
                    <input type="date"
                           name="date_from"
                           value="{{ $dateFrom }}"
                           class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-stone-200">
                </div>

                <div>
                    <label class="block text-xs text-stone-500 mb-1">終了日</label>
                    <input type="date"
                           name="date_to"
                           value="{{ $dateTo }}"
                           class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-stone-200">
                </div>

                <div>
                    <label class="block text-xs text-stone-500 mb-1">状態</label>
                    <select name="status"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-stone-200">
                        <option value="">すべて</option>
                        <option value="reserved" {{ $status === 'reserved' ? 'selected' : '' }}>予約中</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>キャンセル済み</option>
                    </select>
                </div>

                <div class="md:col-span-2 xl:col-span-5 flex flex-wrap gap-2 pt-1">
                    <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                            style="background: {{ $theme }};">
                        検索する
                    </button>

                    <a href="{{ route('company.reservations.index') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-stone-300 bg-white text-sm font-medium text-stone-700 hover:bg-stone-50 transition">
                        全件表示
                    </a>

                    <a href="{{ route('company.reservations.index', ['date_from' => $today, 'date_to' => $today, 'status' => 'reserved']) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}; color: {{ $theme }};">
                        今日の予約
                    </a>

                    <a href="{{ route('company.reservations.index', ['date_from' => $tomorrow, 'date_to' => $tomorrow, 'status' => 'reserved']) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}; color: {{ $theme }};">
                        明日({{ \Carbon\Carbon::parse($tomorrow)->format('m/d') }})の予約
                    </a>

                    <a href="{{ route('company.reservations.index', ['date_from' => $dayAfterTomorrow, 'date_to' => $dayAfterTomorrow, 'status' => 'reserved']) }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl border bg-white text-sm font-medium hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}; color: {{ $theme }};">
                        明後日({{ \Carbon\Carbon::parse($dayAfterTomorrow)->format('m/d') }})の予約
                    </a>
                </div>
            </form>
        </div>

        {{-- PC表示 --}}
        <div class="hidden lg:block">
            <table class="w-full text-sm table-fixed">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr class="text-left text-stone-600">
                        <th class="px-4 py-3 font-semibold w-[160px]">予約日時</th>
                        <th class="px-4 py-3 font-semibold w-[140px]">顧客名</th>
                        <th class="px-4 py-3 font-semibold w-[140px]">電話番号</th>
                        <th class="px-4 py-3 font-semibold w-[120px]">担当者</th>
                        <th class="px-4 py-3 font-semibold">メニュー</th>
                        <th class="px-4 py-3 font-semibold w-[110px]">状態</th>
                        <th class="px-4 py-3 font-semibold w-[110px]">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        @php
                            $displayCustomerName = $reservation->customer_name ?: optional($reservation->customer)->name ?: '－';
                            $displayPhone = $reservation->customer_phone ?: optional($reservation->customer)->phone ?: '－';
                            $menuNames = $reservation->menus->pluck('name')->filter()->values();
                            $menuText = $menuNames->isNotEmpty() ? $menuNames->join('、') : '－';
                        @endphp

                        <tr class="border-b border-stone-100 hover:bg-stone-50/70 transition align-top">
                            <td class="px-4 py-4 text-stone-800 font-medium break-words">
                                {{ optional($reservation->start_at)->format('Y/m/d H:i') }}
                            </td>

                            <td class="px-4 py-4 text-stone-800 break-words">
                                {{ $displayCustomerName }}
                            </td>

                            <td class="px-4 py-4 text-stone-700 break-all">
                                {{ $displayPhone }}
                            </td>

                            <td class="px-4 py-4 text-stone-700 break-words">
                                {{ optional($reservation->staff)->name ?: '未指定' }}
                            </td>

                            <td class="px-4 py-4 text-stone-700">
                                <div class="whitespace-normal break-words leading-6">
                                    {{ $menuText }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if($reservation->status === 'reserved')
                                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-medium">
                                        予約中
                                    </span>
                                @elseif($reservation->status === 'cancelled')
                                    <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-medium">
                                        キャンセル済み
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-medium">
                                        {{ $reservation->status }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                @if($reservation->status === 'reserved')
                                    <form method="POST"
                                          action="{{ route('company.reservations.cancel', $reservation->id) }}"
                                          onsubmit="return confirm('この予約をキャンセルしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}\n電話番号：{{ $displayPhone }}\n担当者：{{ optional($reservation->staff)->name ?: '未指定' }}\nメニュー：{{ $menuText }}');">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition"
                                                style="background: {{ $theme }};">
                                            キャンセル
                                        </button>
                                    </form>
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
                @endphp

                <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs text-stone-500">予約日時</div>
                            <div class="font-semibold text-stone-800">
                                {{ optional($reservation->start_at)->format('Y/m/d H:i') }}
                            </div>
                        </div>

                        <div>
                            @if($reservation->status === 'reserved')
                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-medium">
                                    予約中
                                </span>
                            @elseif($reservation->status === 'cancelled')
                                <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-medium">
                                    キャンセル済み
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-medium">
                                    {{ $reservation->status }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-stone-500">顧客名</div>
                            <div class="text-stone-800">{{ $displayCustomerName }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-stone-500">電話番号</div>
                            <div class="text-stone-700 break-all">{{ $displayPhone }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-stone-500">担当者</div>
                            <div class="text-stone-700">{{ optional($reservation->staff)->name ?: '未指定' }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <div class="text-xs text-stone-500">メニュー</div>
                            <div class="text-stone-700 break-words leading-6">{{ $menuText }}</div>
                        </div>
                    </div>

                    <div class="pt-2">
                        @if($reservation->status === 'reserved')
                            <form method="POST"
                                  action="{{ route('company.reservations.cancel', $reservation->id) }}"
                                  onsubmit="return confirm('この予約をキャンセルしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}\n電話番号：{{ $displayPhone }}\n担当者：{{ optional($reservation->staff)->name ?: '未指定' }}\nメニュー：{{ $menuText }}');">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                                        style="background: {{ $theme }};">
                                    キャンセル
                                </button>
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
@endsection