@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Support Detail</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">お問い合わせ詳細</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        送信内容と管理者からの回答を確認できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                    <a href="{{ route('company.support.index') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        FAQ・お問い合わせへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ステータス --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-stone-600">
            <div>カテゴリ：<span class="font-medium text-stone-800">{{ $inquiry->category ?: '-' }}</span></div>
            <div>
                状態：
                @if($inquiry->status === 'answered')
                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">
                        回答済み
                    </span>
                @elseif($inquiry->status === 'closed')
                    <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-semibold">
                        完了
                    </span>
                @else
                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">
                        受付中
                    </span>
                @endif
            </div>
            <div>受付日時：<span class="font-medium text-stone-800">{{ optional($inquiry->created_at)->format('Y/m/d H:i') }}</span></div>
            <div>回答日時：<span class="font-medium text-stone-800">{{ optional($inquiry->replied_at)->format('Y/m/d H:i') ?: '-' }}</span></div>
        </div>
    </div>

    {{-- 問い合わせ内容 --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="text-lg sm:text-xl font-bold text-stone-800">{{ $inquiry->subject }}</h2>
            <p class="text-sm text-stone-500 mt-1">お問い合わせ内容</p>
        </div>

        <div class="p-5 sm:p-6">
            <div class="rounded-2xl bg-stone-50 border border-stone-200 p-5">
                <div class="text-sm text-stone-700 leading-7 whitespace-pre-line">{{ $inquiry->body }}</div>
            </div>
        </div>
    </div>

    {{-- 回答内容 --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="text-lg sm:text-xl font-bold text-stone-800">回答内容</h2>
            <p class="text-sm text-stone-500 mt-1">管理者からの回答です</p>
        </div>

        <div class="p-5 sm:p-6">
            @if($inquiry->admin_reply)
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">
                    <div class="text-sm font-bold text-emerald-700 mb-2">回答</div>
                    <div class="text-sm text-stone-700 leading-7 whitespace-pre-line">{{ $inquiry->admin_reply }}</div>
                </div>
            @else
                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-sm text-amber-700 leading-7">
                    まだ回答は登録されていません。確認までしばらくお待ちください。
                </div>
            @endif
        </div>
    </div>

    {{-- 下部ボタン --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('company.support.index') }}"
           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-stone-300 text-sm text-stone-700 bg-white hover:bg-stone-50 transition">
            FAQ・お問い合わせ一覧へ戻る
        </a>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-sm font-semibold text-white shadow hover:opacity-90 transition"
           style="background: var(--company-theme-gradient);">
            ダッシュボードに戻る
        </a>
    </div>
</div>
@endsection
