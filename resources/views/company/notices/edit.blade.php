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
            お知らせ編集
        </h1>

        <p class="text-sm opacity-90 mt-2">
            お知らせ内容を更新します
        </p>
    </div>

    {{-- ================= フォーム ================= --}}
    <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8">

        <form method="POST"
              action="{{ route('company.notices.update', $notice->id) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- タイトル --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    タイトル
                </label>

                <input type="text"
                       name="title"
                       value="{{ old('title', $notice->title) }}"
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
                          style="--tw-ring-color: {{ $theme }}">{{ old('content', $notice->content) }}</textarea>
            </div>

            {{-- 画像 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    画像
                </label>

                {{-- 現在の画像 --}}
                @if(!empty($notice->image))
                    <div class="mb-4">
                        <img src="{{ asset($notice->image) }}"
                             class="w-40 rounded shadow">
                    </div>
                @endif

                <input type="file" name="image">
            </div>

            {{-- 期間 --}}
            <div class="mb-8 flex gap-4">
                <div>
                    <label>開始日</label>
                    <input type="date"
                           name="start_date"
                           value="{{ old('start_date', $notice->start_date) }}"
                           class="border p-2 rounded">
                </div>

                <div>
                    <label>終了日</label>
                    <input type="date"
                           name="end_date"
                           value="{{ old('end_date', $notice->end_date) }}"
                           class="border p-2 rounded">
                </div>
            </div>

            {{-- 重要 --}}
            <div class="mb-8">
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           name="is_important"
                           value="1"
                           {{ old('is_important', $notice->is_important) ? 'checked' : '' }}>
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
                    更新する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection