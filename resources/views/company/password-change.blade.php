@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-md mx-auto px-4 sm:px-6 py-10">

    <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8">

        <h1 class="text-2xl sm:text-3xl font-bold mb-8 text-center">
            パスワード変更
        </h1>

        <form method="POST">
            @csrf

            {{-- 新しいパスワード --}}
            <div class="mb-6">
                <input type="password"
                       name="password"
                       placeholder="新しいパスワード"
                       class="border p-4 w-full rounded-lg focus:ring-2 focus:outline-none"
                       style="--tw-ring-color: {{ $theme }}">
                
                @error('password')
                    <div class="text-red-500 text-sm mt-2">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- 確認用パスワード --}}
            <div class="mb-8">
                <input type="password"
                       name="password_confirmation"
                       placeholder="確認用パスワード"
                       class="border p-4 w-full rounded-lg focus:ring-2 focus:outline-none"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- 送信ボタン --}}
            <button type="submit"
                    class="w-full text-white py-4 rounded-lg shadow-lg
                           hover:opacity-90 transition duration-200"
                    style="background: {{ $theme }}">
                変更する
            </button>

        </form>

    </div>

</div>

@endsection