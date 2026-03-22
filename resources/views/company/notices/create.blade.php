@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    {{-- ================= ヘッダー ================= --}}
    <div class="rounded-2xl shadow mb-8 p-6 sm:p-8 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl sm:text-3xl font-bold">
            お知らせ作成
        </h1>

        <p class="text-sm opacity-90 mt-2">
            新しいお知らせを登録します
        </p>
    </div>

    {{-- ================= フォーム ================= --}}
    <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8">

        <form method="POST"
              action="{{ route('company.notices.store') }}"
              enctype="multipart/form-data">
            @csrf

            {{-- タイトル --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    タイトル
                </label>

                <input type="text"
                       name="title"
                       value="{{ old('title') }}"
                       class="w-full border rounded-lg p-3
                              focus:ring-2 focus:outline-none"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- 内容 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    内容
                </label>

                <textarea name="content"
                          rows="5"
                          class="w-full border rounded-lg p-3
                                 focus:ring-2 focus:outline-none"
                          style="--tw-ring-color: {{ $theme }}">{{ old('content') }}</textarea>
            </div>

            {{-- 画像 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    画像
                </label>

                <input type="file" name="image">
            </div>

            {{-- 期間 --}}
            <div class="mb-8 flex gap-4">
                <div>
                    <label>開始日</label>
                    <input type="date" name="start_date" class="border p-2 rounded">
                </div>

                <div>
                    <label>終了日</label>
                    <input type="date" name="end_date" class="border p-2 rounded">
                </div>
            </div>

            {{-- 重要 --}}
            <div class="mb-8">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_important" value="1">
                    重要なお知らせとして表示
                </label>
            </div>

            {{-- ================= ボタン ================= --}}
            <div class="flex flex-col sm:flex-row justify-between gap-4">

                <a href="{{ route('company.notices.index') }}"
                   class="px-4 py-3 text-gray-600 hover:text-gray-800">
                    ← 一覧へ戻る
                </a>

                <button type="submit"
                        class="text-white px-6 py-3 rounded-lg shadow hover:opacity-90"
                        style="background: {{ $theme }}">
                    登録する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection