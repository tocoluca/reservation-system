@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Dashboard Notice</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">お知らせ詳細</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        ダッシュボードに表示されるお知らせの詳細内容を確認できます。
                    </p>
                </div>

                <div>
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 md:px-8 py-6 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">

            <div class="flex flex-wrap items-center gap-2 mb-3">
                @if($notice->is_important)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">重要</span>
                @endif

                @if($notice->is_new)
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">NEW</span>
                @endif

                <span class="text-xs text-gray-500">
                    {{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }}
                    〜
                    {{ optional($notice->end_date)->format('Y/m/d') ?: '指定なし' }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 leading-tight">
                {{ $notice->title }}
            </h1>
        </div>

        <div class="p-6 md:p-8">
            @if($notice->image)
                <div class="mb-8">
                    <img src="{{ asset($notice->image) }}"
                         class="w-full max-h-[460px] object-contain rounded-3xl border border-stone-200 bg-stone-50">
                </div>
            @endif

            <div class="rounded-2xl border border-stone-200 bg-white p-5 sm:p-6 whitespace-pre-wrap text-gray-700 leading-8">
                {{ $notice->body }}
            </div>
        </div>
    </div>
</div>
@endsection