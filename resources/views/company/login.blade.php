@extends('layouts.company')

@section('content')
@php
    $theme = '#7c3aed';
@endphp

<div class="min-h-screen bg-gradient-to-br from-white via-purple-50 to-fuchsia-50 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- ロゴ / タイトル --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-2">
                企業管理ログイン
            </h1>
            <p class="text-sm text-gray-500">
                企業コード・スタッフコード・パスワードを入力してください
            </p>
        </div>

        {{-- カード --}}
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-white/70 p-6 sm:p-8">

            {{-- 全体エラー --}}
            @if (session('error'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- バリデーションエラー一覧 --}}
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <div class="text-sm font-semibold text-red-700 mb-2">
                        入力内容をご確認ください
                    </div>
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.login.post') }}" class="space-y-5">
                @csrf

                {{-- 企業コード --}}
                <div>
                    <label for="company_code" class="block text-sm font-semibold text-gray-700 mb-2">
                        企業コード
                    </label>
                    <input
                        type="text"
                        id="company_code"
                        name="company_code"
                        value="{{ old('company_code') }}"
                        autocomplete="username"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        placeholder="例：TOCOLUCA"
                    >
                    @error('company_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- スタッフコード --}}
                <div>
                    <label for="staff_code" class="block text-sm font-semibold text-gray-700 mb-2">
                        スタッフコード
                    </label>
                    <input
                        type="text"
                        id="staff_code"
                        name="staff_code"
                        value="{{ old('staff_code') }}"
                        autocomplete="username"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        placeholder="例：MASTER01"
                    >
                    @error('staff_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- パスワード --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        パスワード
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        placeholder="パスワードを入力"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ログインボタン --}}
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-90"
                        style="background: {{ $theme }};"
                    >
                        ログイン
                    </button>
                </div>
            </form>
        </div>

        {{-- 補足 --}}
        <p class="text-center text-xs text-gray-400 mt-5">
            ログインできない場合は、企業コード・スタッフコード・パスワードをご確認ください。
        </p>
    </div>
</div>
@endsection