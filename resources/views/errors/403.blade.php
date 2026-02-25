@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->check()
        ? auth()->guard('company')->user()->company->theme_color
        : '#ef4444';
@endphp

<div class="max-w-xl mx-auto mt-20 bg-white shadow-xl rounded-xl p-10 text-center">

    <div class="text-6xl font-bold mb-4" style="color: {{ $theme }}">
        403
    </div>

    <h1 class="text-xl font-semibold mb-4">
        権限がありません
    </h1>

    <p class="text-gray-500 mb-8">
        上位権限のため操作できません。
    </p>

    <div class="flex justify-center gap-4">

        {{-- 戻る --}}
        <button onclick="history.back()"
                class="px-6 py-2 rounded-lg text-white shadow"
                style="background: {{ $theme }}">
            ← 戻る
        </button>

        {{-- ダッシュボード --}}
        <a href="{{ route('company.dashboard') }}"
           class="px-6 py-2 rounded-lg border"
           style="border-color: {{ $theme }}; color: {{ $theme }}">
            ダッシュボードへ
        </a>

    </div>

</div>

@endsection