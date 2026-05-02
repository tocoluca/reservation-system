@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-amber-50 via-white to-rose-50 shadow-sm mb-6">
        <div class="absolute inset-x-0 top-0 h-1.5" style="background: {{ $theme }};"></div>

        <div class="px-5 sm:px-8 py-6 sm:py-8">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        YEAR CALENDAR
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        {{ $year }}年 年間カレンダー
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-gray-600 leading-relaxed">
                        月を選ぶと、その月の営業日カレンダーへ移動します。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white text-sm font-semibold shadow-sm border hover:bg-gray-50 transition"
                       style="border-color: {{ $theme }}22; color: {{ $theme }};">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 年移動 --}}
    <div class="bg-white rounded-[1.75rem] shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="text-base font-bold text-gray-900">年を切り替える</div>
                <p class="text-sm text-gray-500 mt-1">
                    前年・翌年へ移動して、年間の営業日設定を確認できます。
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
                <a href="{{ route('company.calendar.year', ['year' => $year - 1]) }}"
                   class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                   style="border-color: {{ $theme }}22; color: {{ $theme }};">
                    ◀ {{ $year - 1 }}年
                </a>

                <a href="{{ route('company.calendar.year', ['year' => now()->year]) }}"
                   class="inline-flex items-center justify-center px-4 py-3 rounded-2xl font-semibold text-white shadow-sm transition hover:opacity-90"
                   style="background: {{ $theme }};">
                    今年へ戻る
                </a>

                <a href="{{ route('company.calendar.year', ['year' => $year + 1]) }}"
                   class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                   style="border-color: {{ $theme }}22; color: {{ $theme }};">
                    {{ $year + 1 }}年 ▶
                </a>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Calendar Navigation</p>
                <h2 class="mt-1 text-lg font-black text-gray-900">営業日管理の表示切替</h2>
                <p class="mt-1 text-sm text-gray-500">年間で見てから、編集したい月へ移動できます。</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:min-w-[520px]">
                <a href="{{ route('company.calendar.index', ['year' => now()->year, 'month' => now()->month]) }}"
                   class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-700 transition hover:bg-gray-100">
                    <div class="text-sm font-black">今月</div>
                    <div class="mt-1 text-xs text-gray-500">今日の月を編集</div>
                </a>
                <a href="{{ route('company.calendar.index', ['year' => $year, 'month' => now()->year == $year ? now()->month : 1]) }}"
                   class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-700 transition hover:bg-gray-100">
                    <div class="text-sm font-black">月間カレンダー</div>
                    <div class="mt-1 text-xs text-gray-500">{{ $year }}年の月を編集</div>
                </a>
                <div class="rounded-2xl border px-4 py-3 text-white shadow-sm"
                     style="background: {{ $theme }}; border-color: {{ $theme }};">
                    <div class="text-sm font-black">年間カレンダー</div>
                    <div class="mt-1 text-xs text-white/80">{{ $year }}年を表示中</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 月一覧 --}}
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-white to-gray-50">
            <h2 class="text-lg font-bold text-gray-900">月を選択</h2>
            <p class="text-sm text-gray-500 mt-1">
                管理したい月を選ぶと、月間カレンダー画面に移動します。
            </p>
        </div>

        <div class="p-5 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $isCurrentMonth = now()->year == $year && now()->month == $m;
                    @endphp

                    <a href="{{ route('company.calendar.index', ['year' => $year, 'month' => $m]) }}"
                       class="group rounded-[1.5rem] border p-5 bg-gradient-to-br from-white to-amber-50/40 shadow-sm hover:shadow-md transition hover:-translate-y-0.5"
                       style="border-color: {{ $isCurrentMonth ? $theme : '#f1f5f9' }};">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-semibold text-gray-500 tracking-wide">
                                    {{ $year }}
                                </div>
                                <div class="mt-2 text-2xl font-bold text-gray-900">
                                    {{ $m }}月
                                </div>
                                <div class="mt-2 text-sm text-gray-500">
                                    営業日を確認・編集
                                </div>
                            </div>

                            @if($isCurrentMonth)
                                <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold text-white"
                                      style="background: {{ $theme }};">
                                    今月
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 inline-flex items-center text-sm font-semibold group-hover:translate-x-1 transition"
                             style="color: {{ $theme }};">
                            開く →
                        </div>
                    </a>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
