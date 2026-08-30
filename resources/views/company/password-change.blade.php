@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Password Settings</p>
            <h1 class="text-2xl sm:text-3xl font-bold mt-1">パスワード変更</h1>
            <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                ログイン用パスワードを安全に更新できます。
            </p>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-3xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $theme }}15 0%, #ffffff 100%);">
            <h2 class="text-lg font-bold text-gray-900">新しいパスワードを入力</h2>
            <p class="text-sm text-gray-500 mt-1">
                変更する場合のみ入力してください。確認用にも同じ内容を入力します。
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <form method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        新しいパスワード
                    </label>

                    <input type="password"
                           name="password"
                           placeholder="新しいパスワード"
                           class="border border-stone-300 p-4 w-full rounded-2xl focus:ring-2 focus:outline-none"
                           style="--tw-ring-color: {{ $theme }}">

                    @error('password')
                        <div class="text-red-500 text-sm mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-8">
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        確認用パスワード
                    </label>

                    <input type="password"
                           name="password_confirmation"
                           placeholder="確認用パスワード"
                           class="border border-stone-300 p-4 w-full rounded-2xl focus:ring-2 focus:outline-none"
                           style="--tw-ring-color: {{ $theme }}">
                </div>

                <button type="submit"
                        class="w-full text-white py-4 rounded-2xl shadow-lg
                               hover:opacity-90 transition duration-200 font-semibold"
                        style="background: var(--company-theme-gradient);">
                    変更する
                </button>
            </form>
        </div>
    </div>

</div>

@endsection
