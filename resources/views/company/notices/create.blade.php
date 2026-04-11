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
            <p class="text-sm opacity-90 mb-2">Create Notice</p>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">お知らせ作成</h1>
            <p class="text-sm sm:text-base opacity-90 mt-2">
                新しいお知らせを登録して、お客様に分かりやすく情報を届けます。
            </p>
        </div>
        <div class="px-6 sm:px-8 py-4 bg-amber-50 border-t border-amber-100 text-sm text-amber-900">
            「タイトル → 内容 → 掲載期間 → 重要設定」の順に入力すると、迷わず登録できます。
        </div>
    </div>

    <form method="POST"
          action="{{ route('company.notices.store') }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf

        {{-- 基本情報 --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">基本情報</h2>
                <p class="text-sm text-gray-500 mt-1">まずはお知らせの内容を入力します。</p>
            </div>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        タイトル
                    </label>
                    <input type="text"
                           name="title"
                           value="{{ old('title') }}"
                           placeholder="例：GW期間中の営業について"
                           class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('title')
                        <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                        内容
                    </label>
                    <textarea name="content"
                              rows="7"
                              placeholder="お客様に伝えたい内容を入力してください"
                              class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2"
                              style="--tw-ring-color: {{ $theme }};">{{ old('content') }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        長文でも大丈夫です。改行を入れると読みやすくなります。
                    </p>
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
                <p class="text-sm text-gray-500 mt-1">いつ表示するか、重要表示するかを設定します。</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">開始日</label>
                    <input type="date"
                           name="start_date"
                           value="{{ old('start_date') }}"
                           class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    <p class="text-xs text-gray-500 mt-2">未入力の場合はすぐに公開されます。</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">終了日</label>
                    <input type="date"
                           name="end_date"
                           value="{{ old('end_date') }}"
                           class="w-full rounded-2xl border border-gray-200 px-4 py-3 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    <p class="text-xs text-gray-500 mt-2">未入力の場合は無期限で表示されます。</p>
                </div>
            </div>

            <div class="mt-6">
                <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 cursor-pointer">
                    <input type="checkbox"
                           name="is_important"
                           value="1"
                           {{ old('is_important') ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300">
                    <div>
                        <p class="font-semibold text-gray-800">重要なお知らせとして表示する</p>
                        <p class="text-sm text-gray-500 mt-1">
                            休業案内や重要な変更点など、特に目立たせたい内容に使います。
                        </p>
                    </div>
                </label>
            </div>
        </div>

        {{-- 画像 --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">画像設定</h2>
                <p class="text-sm text-gray-500 mt-1">必要に応じて、お知らせ用の画像を登録します。</p>
            </div>

            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6">
                <label class="block text-sm font-semibold text-gray-800 mb-3">画像</label>
                <input type="file" name="image" class="block w-full text-sm text-gray-700">
                <p class="text-xs text-gray-500 mt-3">
                    キャンペーン画像や休業案内画像などを設定すると、一覧で目立ちやすくなります。
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
                登録する
            </button>
        </div>
    </form>
</div>
@endsection