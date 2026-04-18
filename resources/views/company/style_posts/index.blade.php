@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '14';
    $themeSoftStrong = $theme . '22';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <div class="relative overflow-hidden rounded-[28px] border border-white/60 shadow-[0_20px_60px_rgba(15,23,42,0.08)] mb-6 bg-white">
        <div class="absolute inset-0 pointer-events-none"
             style="background:
                radial-gradient(circle at top right, {{ $themeSoftStrong }} 0%, transparent 30%),
                linear-gradient(135deg, {{ $themeSoft }} 0%, #ffffff 55%, #fafaf9 100%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold tracking-[0.18em] uppercase text-stone-600 bg-white/80 border border-stone-200">
                        Style Posts
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
                        最新スタイル投稿
                    </h1>

                    <p class="mt-3 text-sm sm:text-base leading-7 text-stone-600">
                        予約画面に表示する写真とコメントを管理できます。<br class="hidden sm:block">
                        サロンの雰囲気や得意なスタイルが伝わる投稿を、見やすく整理して掲載できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                        ダッシュボードへ戻る
                    </a>

                    <a href="{{ route('company.style-posts.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-95"
                       style="background: {{ $theme }};">
                        ＋ 新規投稿
                    </a>
                </div>
            </div>

            @if($styles->count())
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-stone-200 bg-white/80 px-4 py-3">
                        <div class="text-xs text-stone-500">投稿数</div>
                        <div class="mt-1 text-xl font-bold text-stone-900">{{ $styles->total() }}</div>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white/80 px-4 py-3">
                        <div class="text-xs text-stone-500">公開中</div>
                        <div class="mt-1 text-xl font-bold text-stone-900">{{ $styles->where('is_public', true)->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-white/80 px-4 py-3">
                        <div class="text-xs text-stone-500">非公開</div>
                        <div class="mt-1 text-xl font-bold text-stone-900">{{ $styles->where('is_public', false)->count() }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($styles->count())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($styles as $style)
                <div class="group overflow-hidden rounded-[26px] border border-stone-200 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(15,23,42,0.08)]">
                    <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
                        @if($style->image_path)
                            <img src="{{ asset($style->image_path) }}"
                                 alt="{{ $style->title }}"
                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-sm text-stone-400">
                                画像なし
                            </div>
                        @endif

                        <div class="absolute top-4 left-4">
                            @if($style->is_public)
                                <span class="inline-flex items-center rounded-full bg-emerald-500/90 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    公開中
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-stone-700/80 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    非公開
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <h2 class="text-lg font-bold leading-7 text-stone-900">
                                {{ $style->title }}
                            </h2>

                            <div class="shrink-0 rounded-full border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-semibold text-stone-600">
                                並び順 {{ $style->sort_order }}
                            </div>
                        </div>

                        <p class="mt-3 min-h-[84px] whitespace-pre-line text-sm leading-7 text-stone-600">
                            {{ \Illuminate\Support\Str::limit($style->comment, 120) }}
                        </p>

                        <div class="mt-5 flex items-center gap-3">
                            <a href="{{ route('company.style-posts.edit', $style->id) }}"
                               class="inline-flex items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                                編集
                            </a>

                            <form method="POST"
                                  action="{{ route('company.style-posts.destroy', $style->id) }}"
                                  onsubmit="return confirm('この投稿を削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                                    削除
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $styles->links() }}
        </div>
    @else
        <div class="rounded-[28px] border border-dashed border-stone-300 bg-white px-6 py-14 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-stone-100 text-2xl">
                ✂
            </div>
            <h2 class="mt-5 text-xl font-bold text-stone-900">まだ投稿がありません</h2>
            <p class="mt-2 text-sm leading-7 text-stone-500">
                最初のスタイル投稿を作成すると、予約画面にサロンの魅力を表示できます。
            </p>

            <a href="{{ route('company.style-posts.create') }}"
               class="mt-6 inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-95"
               style="background: {{ $theme }};">
                ＋ 新規投稿
            </a>
        </div>
    @endif
</div>
@endsection