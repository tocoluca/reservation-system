@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">My Profile</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">マイプロフィール</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        プロフィール画像・コメント・パスワードをこの画面から更新できます。
                    </p>
                </div>

                <div>
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('company.my-profile.update') }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf

        {{-- プロフィール設定 --}}
        <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900">プロフィール設定</h2>
                <p class="text-sm text-gray-500 mt-1">画像と自己紹介コメントを設定します。</p>
            </div>

            <div class="p-6 space-y-8">
                <div>
                    <label class="block font-semibold mb-4">プロフィール画像</label>

                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        @if($staff->image_path)
                            <img src="{{ asset($staff->image_path) }}"
                                 class="h-24 w-24 rounded-full object-cover border border-stone-200 shadow-sm">
                        @else
                            <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 border border-stone-200">
                                No Image
                            </div>
                        @endif

                        <div class="w-full">
                            <input type="file"
                                   name="image"
                                   class="border border-stone-300 p-3 rounded-2xl w-full bg-white">
                            <p class="text-xs text-gray-500 mt-2">
                                一覧やプロフィール画面に表示される画像です。
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-3">コメント</label>
                    <textarea name="comment"
                              rows="5"
                              class="border border-stone-300 p-4 w-full rounded-2xl focus:ring-2"
                              style="--tw-ring-color: {{ $theme }}">{{ old('comment',$staff->comment) }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        自己紹介や得意分野など、プロフィールに表示したい内容を入力できます。
                    </p>
                </div>
            </div>
        </section>

        {{-- パスワード変更 --}}
        <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900">パスワード変更</h2>
                <p class="text-sm text-gray-500 mt-1">変更が必要な場合だけ入力してください。</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-600 font-medium">新しいパスワード</label>
                        <input type="password"
                               name="password"
                               class="border border-stone-300 p-3 w-full rounded-2xl focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-600 font-medium">確認用パスワード</label>
                        <input type="password"
                               name="password_confirmation"
                               class="border border-stone-300 p-3 w-full rounded-2xl focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}">
                    </div>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit"
                    class="w-full sm:w-auto text-white px-8 py-3 rounded-2xl shadow-lg hover:opacity-90 transition duration-200"
                    style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                更新する
            </button>
        </div>
    </form>
</div>

@endsection