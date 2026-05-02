@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '14';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    @include('company.style_posts._style_nav', ['current' => 'edit'])

    <div class="overflow-hidden rounded-[28px] border border-stone-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
        <div class="px-6 sm:px-8 py-6 border-b border-stone-200"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center rounded-full border border-stone-200 bg-white px-3 py-1 text-xs font-semibold tracking-[0.16em] uppercase text-stone-500">
                        Edit Style Post
                    </div>
                    <h1 class="mt-3 text-2xl font-bold text-stone-900">最新スタイルを編集</h1>
                    <p class="mt-2 text-sm leading-6 text-stone-500">
                        内容を修正して、予約画面の表示を更新できます。
                    </p>
                </div>

                <a href="{{ route('company.style-posts.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                    一覧へ戻る
                </a>
            </div>
        </div>

        <form method="POST"
              action="{{ route('company.style-posts.update', $style->id) }}"
              enctype="multipart/form-data"
              class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            @include('company.style_posts._form')

            <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 border-t border-stone-100 pt-6">
                <a href="{{ route('company.style-posts.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                    キャンセル
                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-95"
                        style="background: {{ $theme }};">
                    更新する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
