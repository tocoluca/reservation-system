@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- ヘッダー --}}
    <div class="rounded-3xl shadow-sm overflow-hidden mb-8 border border-gray-100 bg-white">
        <div class="p-6 sm:p-8 text-white" style="background: linear-gradient(135deg, {{ $theme }} 0%, {{ $theme }}dd 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="text-sm opacity-90 mb-2">Notice Management</p>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">お知らせ管理</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2">
                        掲載中のお知らせや重要告知を、分かりやすく管理できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-xl bg-white/15 hover:bg-white/25 transition text-white border border-white/20">
                        ← ダッシュボード
                    </a>
                    <a href="{{ route('company.notices.create') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-sm font-semibold shadow hover:opacity-90 transition"
                       style="color: {{ $theme }};">
                        ＋ 新規作成
                    </a>
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-4 bg-amber-50 border-t border-amber-100 text-sm text-amber-900">
            重要なお知らせやキャンペーン情報は、タイトルを短く分かりやすくすると見やすくなります。
        </div>
    </div>

    {{-- 件数 --}}
    <div class="flex items-center justify-between mb-5">
        <div class="text-sm text-gray-600">
            全 <span class="font-bold text-gray-900">{{ $notices->count() }}</span> 件
        </div>
    </div>

    {{-- 一覧 --}}
    @forelse($notices as $notice)
        @php
            $today = now()->toDateString();
            $isStarted = empty($notice->start_date) || $notice->start_date <= $today;
            $isNotEnded = empty($notice->end_date) || $notice->end_date >= $today;
            $isPublished = $isStarted && $isNotEnded;
        @endphp

        <div class="bg-white border border-gray-100 rounded-3xl shadow-sm hover:shadow-md transition mb-5 overflow-hidden">
            <div class="p-5 sm:p-6">
                <div class="flex flex-col lg:flex-row gap-5">
                    {{-- 画像 --}}
                    <div class="lg:w-64 shrink-0">
                        @if($notice->image)
                            <img src="{{ asset($notice->image) }}"
                                 class="w-full h-44 object-cover rounded-2xl border border-gray-100">
                        @else
                            <div class="w-full h-44 rounded-2xl border border-dashed border-gray-200 bg-gray-50 flex items-center justify-center text-sm text-gray-400">
                                画像未登録
                            </div>
                        @endif
                    </div>

                    {{-- 本文 --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            @if($notice->is_important)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                    重要
                                </span>
                            @endif

                            @if($isPublished)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    掲載中
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                    非掲載
                                </span>
                            @endif
                        </div>

                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 leading-snug">
                            {{ $notice->title }}
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <p class="text-xs text-gray-500 mb-1">掲載開始</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $notice->start_date ?: '即時公開' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <p class="text-xs text-gray-500 mb-1">掲載終了</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $notice->end_date ?: '無期限' }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white border border-gray-100 px-4 py-4">
                            <p class="text-sm leading-7 text-gray-700 whitespace-pre-line">
                                {{ $notice->content }}
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mt-5">
                            <a href="{{ route('company.notices.edit', $notice) }}"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-xl text-white font-medium shadow hover:opacity-90 transition"
                               style="background: {{ $theme }};">
                                編集する
                            </a>

                            <form method="POST" action="{{ route('company.notices.destroy', $notice) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('このお知らせを削除しますか？')"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 rounded-xl bg-red-500 text-white font-medium shadow hover:bg-red-600 transition">
                                    削除する
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-3xl border border-dashed border-gray-200 p-10 text-center shadow-sm">
            <div class="text-5xl mb-3">📢</div>
            <h2 class="text-lg font-bold text-gray-800 mb-2">お知らせがまだありません</h2>
            <p class="text-sm text-gray-500 mb-6">
                新規作成から、お客様に伝えたい情報を登録できます。
            </p>
            <a href="{{ route('company.notices.create') }}"
               class="inline-flex items-center justify-center px-5 py-3 rounded-xl text-white font-medium shadow hover:opacity-90 transition"
               style="background: {{ $theme }};">
                ＋ はじめてのお知らせを作成
            </a>
        </div>
    @endforelse

</div>
@endsection