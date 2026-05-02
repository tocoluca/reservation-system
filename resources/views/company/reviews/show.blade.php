@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

    {{-- 上部導線 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('company.reviews.index') }}"
           class="inline-flex items-center text-sm font-semibold hover:opacity-80 transition"
           style="color: {{ $theme }};">
            ← 口コミ一覧へ戻る
        </a>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 text-sm rounded-2xl border bg-white hover:bg-gray-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ← ダッシュボードに戻る
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('company.reviews._review_nav', ['current' => 'detail'])

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Review Detail</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">口コミ詳細</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2">
                        投稿日：{{ optional($review->created_at)->format('Y年m月d日 H:i') }}
                    </p>
                </div>

                <div>
                    @if ($review->status === 'pending')
                        <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-4 py-2 text-sm font-semibold">
                            確認待ち
                        </span>
                    @elseif ($review->status === 'approved')
                        <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-4 py-2 text-sm font-semibold">
                            公開中
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-4 py-2 text-sm font-semibold">
                            非公開
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- 左メイン --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- 基本情報 --}}
            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"
                     style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                    <h2 class="text-lg font-bold text-stone-800">口コミ情報</h2>
                    <p class="text-sm text-stone-500 mt-1">評価と投稿者名を確認できます。</p>
                </div>

                <div class="p-6 grid md:grid-cols-2 gap-5">
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
            </section>

            {{-- 本文 --}}
            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"
                     style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                    <h2 class="text-lg font-bold text-stone-800">口コミ本文</h2>
                    <p class="text-sm text-stone-500 mt-1">投稿内容を確認して公開可否を判断します。</p>
                </div>

                <div class="p-6">
                    <div class="rounded-2xl border border-stone-200 bg-white p-5 leading-8 whitespace-pre-wrap text-stone-700">
                        {{ $review->comment ?: '（本文なし）' }}
                    </div>
                </div>
            </section>

            {{-- 返信 --}}
            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"
                     style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                    <h2 class="text-lg font-bold text-stone-800">店舗からの返信</h2>
                    <p class="text-sm text-stone-500 mt-1">丁寧な返信を登録して信頼感を高めます。</p>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('company.reviews.reply', $review) }}" class="space-y-4">
                        @csrf

                        <textarea
                            name="owner_reply"
                            rows="7"
                            maxlength="2000"
                            class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-4 text-stone-800 focus:outline-none focus:ring-2"
                            style="--tw-ring-color: {{ $theme }};"
                            placeholder="口コミへの返信を入力してください。">{{ old('owner_reply', $review->owner_reply) }}</textarea>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="rounded-2xl text-white px-6 py-3.5 font-semibold shadow hover:opacity-90 transition"
                                    style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                                返信を保存する
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        {{-- 右サイド --}}
        <div class="space-y-6">

            {{-- 公開設定 --}}
            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-stone-800">公開設定</h2>
                <p class="text-sm text-stone-500 mt-1">
                    内容を確認して公開・非公開を切り替えます。
                </p>

                <div class="mt-5 space-y-3">
                    <form method="POST" action="{{ route('company.reviews.approve', $review) }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-2xl text-white px-5 py-3.5 font-semibold shadow hover:opacity-90 transition"
                                style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                            公開する
                        </button>
                    </form>

                    <form method="POST" action="{{ route('company.reviews.reject', $review) }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-2xl bg-stone-700 text-white px-5 py-3.5 font-semibold hover:opacity-90 transition">
                            非公開にする
                        </button>
                    </form>
                </div>
            </section>

            {{-- 補助情報 --}}
            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-stone-800">確認ポイント</h2>

                <div class="mt-4 space-y-3 text-sm text-stone-600 leading-6">
                    <div class="rounded-2xl bg-stone-50 p-4">
                        <div class="font-semibold text-stone-800 mb-1">本文の内容確認</div>
                        誹謗中傷や不適切表現がないかを確認します。
                    </div>

                    <div class="rounded-2xl bg-stone-50 p-4">
                        <div class="font-semibold text-stone-800 mb-1">公開判断</div>
                        問題なければ公開し、必要なら非公開にします。
                    </div>

                    <div class="rounded-2xl bg-stone-50 p-4">
                        <div class="font-semibold text-stone-800 mb-1">店舗返信</div>
                        お礼や補足を丁寧に記載すると印象が良くなります。
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
