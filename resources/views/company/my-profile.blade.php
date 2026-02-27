@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8">

        <h1 class="text-2xl sm:text-3xl font-bold mb-8">
            マイプロフィール
        </h1>

        <form method="POST"
              action="{{ route('company.my-profile.update') }}"
              enctype="multipart/form-data">
            @csrf

            {{-- ================= 画像 ================= --}}
            <div class="mb-10">
                <label class="block font-semibold mb-4">
                    プロフィール画像
                </label>

                <div class="flex flex-col sm:flex-row items-center gap-6">

                    @if($staff->image_path)
                        <img src="{{ asset('storage/'.$staff->image_path) }}"
                             class="h-24 w-24 rounded-full object-cover shadow">
                    @else
                        <div class="h-24 w-24 rounded-full bg-gray-200
                                    flex items-center justify-center text-gray-500">
                            No Image
                        </div>
                    @endif

                    <input type="file"
                           name="image"
                           class="border p-3 rounded-lg w-full">
                </div>
            </div>

            {{-- ================= コメント ================= --}}
            <div class="mb-10">
                <label class="block font-semibold mb-3">
                    コメント
                </label>

                <textarea name="comment"
                          rows="4"
                          class="border p-3 w-full rounded-lg focus:ring-2"
                          style="--tw-ring-color: {{ $theme }}">{{ old('comment',$staff->comment) }}</textarea>
            </div>

            <hr class="my-10">

            {{-- ================= パスワード変更 ================= --}}
            <h2 class="text-lg font-bold mb-6">
                パスワード変更
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

                <div>
                    <label class="block mb-2 text-sm text-gray-600">
                        新しいパスワード
                    </label>
                    <input type="password"
                           name="password"
                           class="border p-3 w-full rounded-lg focus:ring-2"
                           style="--tw-ring-color: {{ $theme }}">
                </div>

                <div>
                    <label class="block mb-2 text-sm text-gray-600">
                        確認用パスワード
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           class="border p-3 w-full rounded-lg focus:ring-2"
                           style="--tw-ring-color: {{ $theme }}">
                </div>

            </div>

            {{-- ================= ボタン ================= --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">

                <a href="{{ route('company.dashboard') }}"
                   class="w-full sm:w-auto text-center sm:text-left
                          inline-flex items-center justify-center gap-2
                          text-sm font-semibold px-4 py-3 rounded-lg
                          transition-all duration-200"
                   style="color: {{ $theme }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 19l-7-7 7-7" />
                    </svg>

                    ダッシュボードへ戻る
                </a>

                <button type="submit"
                        class="w-full sm:w-auto text-white px-6 py-3
                               rounded-lg shadow hover:opacity-90
                               transition duration-200"
                        style="background: {{ $theme }}">
                    更新する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection