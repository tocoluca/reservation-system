@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $reservationStatusMeta = function ($status) {
        return match ($status) {
            'completed' => ['label' => '来店済', 'class' => 'bg-blue-100 text-blue-700'],
            'cancelled' => ['label' => 'キャンセル', 'class' => 'bg-stone-200 text-stone-700'],
            'no_show' => ['label' => '無断キャンセル', 'class' => 'bg-red-100 text-red-700'],
            'reserved' => ['label' => '予約中', 'class' => 'bg-emerald-100 text-emerald-700'],
            default => ['label' => $status, 'class' => 'bg-amber-100 text-amber-700'],
        };
    };
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-amber-50 via-white to-rose-50 shadow-sm">
        <div class="absolute inset-x-0 top-0 h-1.5" style="background: {{ $theme }};"></div>

        <div class="px-5 sm:px-8 py-6 sm:py-8">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold bg-white border border-gray-200 text-gray-600 shadow-sm">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }};"></span>
                        RESERVATION LIST
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-bold text-stone-800 mt-4 tracking-tight">
                        予約一覧
                    </h1>

                    <p class="text-stone-500 mt-2 text-sm sm:text-base leading-relaxed">
                        全顧客の予約確認とキャンセルができます。顧客名・電話番号・日付で絞り込み可能です。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.reserve', ['mode' => 'day']) }}"
                       class="group inline-flex items-center gap-3 rounded-2xl px-5 py-3 text-white shadow-sm hover:opacity-95 transition"
                       style="background: linear-gradient(135deg, {{ $theme }} 0%, {{ $theme }}dd 100%);">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-lg font-bold">
                            予
                        </span>

                        <span class="text-left leading-tight">
                            <span class="block text-sm font-bold">予約カレンダー</span>
                            <span class="block text-[11px] text-white/80">登録・確認はこちら</span>
                        </span>
                    </a>

                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border bg-white text-sm font-semibold hover:bg-stone-50 transition"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

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
        <div class="px-6 py-5 border-b border-stone-200 bg-gradient-to-r from-stone-50 to-white">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-stone-800">予約を絞り込む</h2>
                <p class="text-sm text-stone-500 mt-1">
                    顧客名・電話番号・日付・状態から条件を指定できます。
                </p>
            </div>

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
                                {{ $displayCustomerName }}
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
                                    $statusMeta = $reservationStatusMeta($reservation->status);
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusMeta['class'] }}">
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>

                            <td class="px-3 py-4">
                                @if(in_array($reservation->status, ['reserved', 'no_show'], true))
                                    <div class="flex flex-col gap-2">
                                        <form method="POST"
                                              action="{{ route('company.reservations.complete', $reservation->id) }}"
                                              onsubmit="return confirm('この予約を来店済みにしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}');">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center px-2 py-2 rounded-xl bg-blue-600 text-white text-[11px] font-semibold hover:opacity-90 transition shadow-sm whitespace-nowrap">
                                                来店済
                                            </button>
                                        </form>

                                    <form method="POST"
                                          action="{{ route('company.reservations.cancel', $reservation->id) }}"
                                          onsubmit="return confirm('この予約をキャンセルしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}\n電話番号：{{ $displayPhone }}\n主担当：{{ optional($reservation->staff)->name ?: '未指定' }}\n施術内訳：{{ $confirmDetailText }}');">
                                        @csrf
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center px-2 py-2 rounded-xl text-white text-[11px] font-semibold hover:opacity-90 transition shadow-sm whitespace-nowrap"
                                                style="background: {{ $theme }};">
                                            キャンセル
                                        </button>
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
                                $statusMeta = $reservationStatusMeta($reservation->status);
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['class'] }}">
                                {{ $statusMeta['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-stone-50 px-3 py-3">
                            <div class="text-xs text-stone-500">顧客名</div>
                            <div class="text-stone-800 mt-1 font-medium">{{ $displayCustomerName }}</div>
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
                        @if(in_array($reservation->status, ['reserved', 'no_show'], true))
                            <form method="POST"
                                  action="{{ route('company.reservations.complete', $reservation->id) }}"
                                  class="mb-2"
                                  onsubmit="return confirm('この予約を来店済みにしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}');">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-blue-600 text-white text-sm font-semibold hover:opacity-90 transition shadow-sm">
                                    来店済みにする
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('company.reservations.cancel', $reservation->id) }}"
                                  onsubmit="return confirm('この予約をキャンセルしますか？\n\n予約日時：{{ optional($reservation->start_at)->format('Y/m/d H:i') }}\n顧客名：{{ $displayCustomerName }}\n電話番号：{{ $displayPhone }}\n主担当：{{ optional($reservation->staff)->name ?: '未指定' }}\n施術内訳：{{ $confirmDetailText }}');">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                                        style="background: {{ $theme }};">
                                    この予約をキャンセル
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
