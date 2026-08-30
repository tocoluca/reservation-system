@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '15';

    $reviewCollection = $reviews->getCollection();
    $pendingCount = $reviewCollection->where('status', 'pending')->count();
    $approvedCount = $reviewCollection->where('status', 'approved')->count();
    $rejectedCount = $reviewCollection->where('status', 'rejected')->count();
    $lowRatingCount = $reviewCollection->filter(fn ($review) => (int) $review->rating <= 2)->count();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

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
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Review Management</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">口コミ管理</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        投稿された口コミの確認・公開設定・返信管理ができます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    @include('company.reviews._review_nav', ['current' => 'index'])

    {{-- サマリー --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-stone-500">確認待ち</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-amber-500">{{ $pendingCount }}</div>
                <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">
                    pending
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-stone-500">公開中</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-emerald-500">{{ $approvedCount }}</div>
                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">
                    approved
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-stone-500">非公開</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-stone-500">{{ $rejectedCount }}</div>
                <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-semibold">
                    rejected
                </span>
            </div>
        </div>

        <div class="bg-red-50 rounded-3xl shadow-sm border border-red-100 p-5">
            <div class="text-sm text-red-700">低評価</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-red-600">{{ $lowRatingCount }}</div>
                <span class="inline-flex rounded-full bg-white text-red-700 px-3 py-1 text-xs font-semibold">
                    ★2以下
                </span>
            </div>
        </div>
    </div>

    {{-- 一覧本体 --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- 見出し --}}
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-stone-800">口コミ一覧</h2>
                    <p class="text-sm text-stone-500 mt-1">
                        状態ごとに絞り込みながら、内容確認と公開判断ができます。
                    </p>
                </div>
            </div>
        </div>

        {{-- フィルター --}}
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100 bg-stone-50/70">
            <form method="GET" class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:items-center">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-semibold text-stone-500 mb-1">公開状態</label>
                    <select name="status"
                            class="w-full sm:w-52 border border-stone-300 rounded-2xl px-4 py-3 bg-white text-sm focus:outline-none focus:ring-2"
                            style="--tw-ring-color: {{ $theme }};">
                        <option value="">すべて</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>確認待ち</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>公開中</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>非公開</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-0 sm:pt-5">
                    <button type="submit"
                            class="text-white px-5 py-3 rounded-2xl text-sm font-semibold shadow hover:opacity-90 transition"
                            style="background: var(--company-theme-gradient);">
                        絞り込む
                    </button>

                    @if(request()->filled('status'))
                        <a href="{{ route('company.reviews.index') }}"
                           class="px-5 py-3 rounded-2xl border border-stone-300 text-sm text-stone-700 bg-white hover:bg-stone-50 transition">
                            条件をクリア
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- PCテーブル --}}
        <div class="hidden lg:block max-h-[72vh] overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50">
                    <tr class="text-left text-stone-600">
                        <th class="sticky top-0 z-20 bg-stone-50 px-5 py-4 border-b border-stone-300 shadow-sm whitespace-nowrap">
                            投稿日
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-5 py-4 border-b border-stone-300 shadow-sm whitespace-nowrap">
                            評価
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-5 py-4 border-b border-stone-300 shadow-sm whitespace-nowrap">
                            ニックネーム
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-5 py-4 border-b border-stone-300 shadow-sm">
                            本文
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-5 py-4 border-b border-stone-300 shadow-sm whitespace-nowrap">
                            状態
                        </th>
                        <th class="sticky top-0 z-20 bg-stone-50 px-5 py-4 border-b border-stone-300 shadow-sm text-center whitespace-nowrap">
                            操作
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($reviews as $review)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="px-5 py-5 border-b border-stone-100 whitespace-nowrap text-stone-700">
                                {{ optional($review->created_at)->format('Y/m/d H:i') }}
                            </td>

                            <td class="px-5 py-5 border-b border-stone-100 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-600 px-3 py-1 font-semibold">
                                    ★{{ $review->rating }}
                                </span>
                            </td>

                            <td class="px-5 py-5 border-b border-stone-100 whitespace-nowrap text-stone-800 font-medium">
                                {{ $review->nickname ?: 'お客様' }}
                            </td>

                            <td class="px-5 py-5 border-b border-stone-100 text-stone-700">
                                <div class="max-w-xl line-clamp-2 leading-relaxed">
                                    {{ $review->comment }}
                                </div>
                            </td>

                            <td class="px-5 py-5 border-b border-stone-100 whitespace-nowrap">
                                @if ($review->status === 'pending')
                                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">
                                        確認待ち
                                    </span>
                                @elseif ($review->status === 'approved')
                                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">
                                        公開中
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-semibold">
                                        非公開
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-5 border-b border-stone-100 text-center">
                                <a href="{{ route('company.reviews.show', ['review' => $review, 'from_status' => request('status')]) }}"
                                   class="inline-flex items-center rounded-2xl border px-4 py-2.5 text-sm font-medium bg-white hover:bg-stone-50 transition"
                                   style="border-color: {{ $theme }}; color: {{ $theme }};">
                                    詳細を見る
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-stone-400">
                                口コミはまだありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- SPカード --}}
        <div class="lg:hidden divide-y divide-stone-100">
            @forelse ($reviews as $review)
                <div class="p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs text-stone-500">
                                {{ optional($review->created_at)->format('Y/m/d H:i') }}
                            </div>
                            <div class="mt-1 font-semibold text-stone-800">
                                {{ $review->nickname ?: 'お客様' }}
                            </div>
                        </div>

                        @if ($review->status === 'pending')
                            <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold whitespace-nowrap">
                                確認待ち
                            </span>
                        @elseif ($review->status === 'approved')
                            <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold whitespace-nowrap">
                                公開中
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-semibold whitespace-nowrap">
                                非公開
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-600 px-3 py-1 text-sm font-semibold">
                            ★{{ $review->rating }}
                        </span>
                    </div>

                    <div class="text-sm text-stone-700 leading-relaxed line-clamp-3">
                        {{ $review->comment }}
                    </div>

                    <div>
                        <a href="{{ route('company.reviews.show', ['review' => $review, 'from_status' => request('status')]) }}"
                           class="inline-flex items-center justify-center w-full rounded-2xl border px-4 py-3 text-sm font-medium bg-white hover:bg-stone-50 transition"
                           style="border-color: {{ $theme }}; color: {{ $theme }};">
                            詳細を見る
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center text-stone-400">
                    口コミはまだありません。
                </div>
            @endforelse
        </div>

        {{-- ページネーション --}}
        <div class="px-5 py-4 border-t border-stone-100 bg-white">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
