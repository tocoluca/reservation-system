@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    {{-- ヘッダー --}}
    <div class="rounded-3xl shadow-sm overflow-hidden mb-8 border border-gray-100 bg-white">
        <div class="p-6 sm:p-8 text-white" style="background: linear-gradient(135deg, {{ $theme }} 0%, {{ $theme }}dd 100%);">
            <p class="text-sm opacity-90 mb-2">Edit Notice</p>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">お知らせ編集</h1>
            <p class="text-sm sm:text-base opacity-90 mt-2">
                登録済みのお知らせ内容を更新できます。
            </p>
        </div>
        <div class="px-6 sm:px-8 py-4 bg-blue-50 border-t border-blue-100 text-sm text-blue-900">
            内容を変更したあとは、掲載期間と重要設定も合わせて確認すると安心です。
        </div>
    </div>

    <div class="mb-6">
        @include('company.notices._notice_nav', ['current' => 'edit'])
    </div>

    <form method="POST"
          action="{{ route('company.notices.update', $notice->id) }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PUT')

        {{-- 基本情報 --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">基本情報</h2>
                <p class="text-sm text-gray-500 mt-1">タイトルと本文を編集します。</p>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">タイトル</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $notice->title) }}"
                           class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('title')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">内容</label>
                    <textarea name="content"
                              rows="7"
                              class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2"
                              style="--tw-ring-color: {{ $theme }};">{{ old('content', $notice->content) }}</textarea>
                    @error('content')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- 掲載設定 --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">掲載設定</h2>
                <p class="text-sm text-gray-500 mt-1">表示期間と重要表示の設定です。</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">開始日</label>
                    <input type="date"
                           name="start_date"
                           value="{{ old('start_date', $notice->start_date) }}"
                           class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">終了日</label>
                    <input type="date"
                           name="end_date"
                           value="{{ old('end_date', $notice->end_date) }}"
                           class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                </div>
            </div>

            <div class="mt-6">
                <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 cursor-pointer">
                    <input type="checkbox"
                           name="is_important"
                           value="1"
                           {{ old('is_important', $notice->is_important) ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300">
                    <div>
                        <p class="font-semibold text-gray-800">重要なお知らせとして表示する</p>
                        <p class="text-sm text-gray-500 mt-1">
                            緊急連絡や特に目立たせたい案内に向いています。
                        </p>
                    </div>
                </label>
            </div>
        </div>

        {{-- 画像 --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">画像設定</h2>
                <p class="text-sm text-gray-500 mt-1">現在の画像確認と差し替えができます。</p>
            </div>

            @if(!empty($notice->image))
                <div class="mb-5">
                    <p class="text-sm font-semibold text-gray-800 mb-3">現在の画像</p>
                    <img src="{{ asset($notice->image) }}"
                         class="w-full max-w-sm rounded-2xl shadow border border-gray-100">
                </div>
            @endif

            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6">
                <label class="block text-sm font-semibold text-gray-800 mb-3">新しい画像に差し替える</label>
                <input type="file" name="image" class="block w-full text-sm text-gray-700">
                <p class="text-xs text-gray-500 mt-3">
                    画像を選ばなければ、現在の画像のまま保存されます。
                </p>
            </div>
        </div>

        {{-- ボタン --}}
        <div class="flex flex-col-reverse sm:flex-row justify-between gap-3">
            <a href="{{ route('company.notices.index') }}"
               class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                ← 一覧へ戻る
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-2xl text-white font-semibold shadow hover:opacity-90 transition"
                    style="background: {{ $theme }};">
                更新する
            </button>
        </div>
    </form>
</div>
@endsection
