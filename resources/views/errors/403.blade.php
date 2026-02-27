@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->check()
        ? auth()->guard('company')->user()->company->theme_color
        : '#ef4444';
@endphp

<div class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6 py-12">

    <div class="w-full max-w-xl bg-white shadow-2xl rounded-2xl
                p-8 sm:p-12 text-center">

        {{-- エラーコード --}}
        <div class="text-5xl sm:text-6xl font-bold mb-6"
             style="color: {{ $theme }}">
            403
        </div>

        {{-- タイトル --}}
        <h1 class="text-xl sm:text-2xl font-semibold mb-4">
            権限がありません
        </h1>

        {{-- 説明 --}}
        <p class="text-gray-500 mb-10 text-sm sm:text-base">
            上位権限が必要なため、この操作は実行できません。
        </p>

        {{-- ボタンエリア --}}
        <div class="flex flex-col sm:flex-row justify-center gap-4">

            {{-- 戻る --}}
            <button onclick="history.back()"
                    class="w-full sm:w-auto px-6 py-3 rounded-lg text-white
                           shadow-lg hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                ← 戻る
            </button>

            {{-- ダッシュボード --}}
            <a href="{{ route('company.dashboard') }}"
               class="w-full sm:w-auto px-6 py-3 rounded-lg border
                      text-center hover:bg-gray-50 transition"
               style="border-color: {{ $theme }};
                      color: {{ $theme }}">
                ダッシュボードへ
            </a>

        </div>

    </div>

</div>

@endsection