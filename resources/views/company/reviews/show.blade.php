@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    {{-- 上部ナビ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <a href="{{ route('company.reviews.index') }}"
           class="inline-flex items-center text-sm font-medium hover:opacity-80 transition"
           style="color: {{ $theme }};">
            ← 口コミ一覧へ戻る
        </a>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2 text-sm rounded-lg border bg-white hover:bg-gray-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ← ダッシュボードに戻る
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

    <div class="bg-white rounded-2xl shadow border border-stone-200 overflow-hidden">

        {{-- ヘッダー --}}
        <div class="px-6 md:px-8 py-6 border-b bg-gradient-to-r from-stone-50 to-white">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-stone-800">口コミ詳細</h1>
                    <p class="text-sm text-stone-500 mt-1">
                        投稿日：{{ optional($review->created_at)->format('Y年m月d日 H:i') }}
                    </p>
                </div>

                <div>
                    @if ($review->status === 'pending')
                        <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-sm font-medium">
                            確認待ち
                        </span>
                    @elseif ($review->status === 'approved')
                        <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-sm font-medium">
                            公開中
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-sm font-medium">
                            非公開
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 md:p-8">

            {{-- 基本情報 --}}
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-5">
                    <div class="text-sm text-stone-500">評価</div>
                    <div class="mt-2 text-3xl font-bold text-amber-500">
                        ★{{ $review->rating }}
                    </div>
                </div>

                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-5">
                    <div class="text-sm text-stone-500">ニックネーム</div>
                    <div class="mt-2 text-lg font-semibold text-stone-800">
                        {{ $review->nickname ?: 'お客様' }}
                    </div>
                </div>
            </div>

            {{-- 本文 --}}
            <div class="mb-8">
                <h2 class="text-lg font-bold text-stone-800 mb-3">口コミ本文</h2>
                <div class="rounded-2xl border border-stone-200 bg-white p-5 leading-relaxed whitespace-pre-wrap text-stone-700">
                    {{ $review->comment ?: '（本文なし）' }}
                </div>
            </div>

            {{-- 公開操作 --}}
            <div class="mb-8">
                <h2 class="text-lg font-bold text-stone-800 mb-3">公開設定</h2>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('company.reviews.approve', $review) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl text-white px-5 py-3 font-semibold hover:opacity-90 transition"
                                style="background: {{ $theme }};">
                            公開する
                        </button>
                    </form>

                    <form method="POST" action="{{ route('company.reviews.reject', $review) }}">
                        @csrf
                        <button type="submit"
                                class="rounded-xl bg-stone-700 text-white px-5 py-3 font-semibold hover:opacity-90 transition">
                            非公開にする
                        </button>
                    </form>
                </div>
            </div>

            {{-- 店舗返信 --}}
            <div>
                <h2 class="text-lg font-bold text-stone-800 mb-3">店舗からの返信</h2>

                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 md:p-5">
                    <form method="POST" action="{{ route('company.reviews.reply', $review) }}" class="space-y-4">
                        @csrf

                        <textarea
                            name="owner_reply"
                            rows="6"
                            maxlength="2000"
                            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-stone-800 focus:outline-none focus:ring-2"
                            style="focus-ring: {{ $theme }};"
                            placeholder="口コミへの返信を入力してください。">{{ old('owner_reply', $review->owner_reply) }}</textarea>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="rounded-xl text-white px-5 py-3 font-semibold hover:opacity-90 transition"
                                    style="background: {{ $theme }};">
                                返信を保存する
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection