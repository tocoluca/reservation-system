@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-amber-50 via-white to-sky-50 shadow-sm mb-6">
        <div class="absolute inset-x-0 top-0 h-1.5" style="background: {{ $theme }};"></div>

        <div class="px-5 sm:px-8 py-6 sm:py-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/90 border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        COMPANY LOGO
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        企業ロゴ
                    </h1>

                    <p class="text-gray-500 text-sm sm:text-base mt-2 leading-7">
                        企業ロゴの設定・変更ができます。登録したロゴは画面や案内で利用されます。
                    </p>
                </div>

                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white text-sm font-semibold shadow-sm border hover:bg-gray-50 transition"
                   style="border-color: {{ $theme }}22; color: {{ $theme }};">
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white p-5 sm:p-8 rounded-[2rem] shadow-sm border border-gray-100">

        <div class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr] gap-8 items-start">

            {{-- 現在のロゴ --}}
            <div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900">現在のロゴ</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        現在登録されている企業ロゴです。
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-6 text-center shadow-sm">
                    @if($company->logo_path)
                        <div class="flex items-center justify-center min-h-[220px]">
                            <img src="{{ asset($company->logo_path) }}"
                                 alt="企業ロゴ"
                                 class="max-h-44 w-auto object-contain bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        </div>
                    @else
                        <div class="flex items-center justify-center min-h-[220px]">
                            <div class="h-24 w-24 sm:h-32 sm:w-32 rounded-full bg-gray-200 flex items-center justify-center text-2xl sm:text-3xl font-bold text-gray-700 shadow-inner">
                                {{ strtoupper(substr($company->name,0,1)) }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- アップロード --}}
            <div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900">ロゴをアップロードする</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        画像を選択して保存すると、企業ロゴを更新できます。
                    </p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="rounded-[1.75rem] border border-gray-200 bg-gray-50 p-5 sm:p-6">
                    @csrf

                    <div class="rounded-2xl bg-white border border-gray-200 p-4">
                        <label class="block font-semibold text-gray-900 mb-2">
                            ロゴ画像アップロード
                        </label>

                        <p class="text-sm text-gray-500 mb-4">
                            PNG・JPG などの画像ファイルを選択してください。
                        </p>

                        <input type="file"
                               name="logo"
                               accept="image/*"
                               class="w-full border rounded-xl p-3 bg-white">

                        @error('logo')
                            <div class="text-red-500 mt-2 text-sm">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mt-5 flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                                class="w-full sm:w-auto text-white px-6 py-3 rounded-xl shadow-sm hover:opacity-90 transition duration-200 font-semibold"
                                style="background: {{ $theme }}">
                            保存する
                        </button>

                        <a href="{{ route('company.dashboard') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-xl border font-semibold bg-white hover:bg-gray-50 transition"
                           style="border-color: {{ $theme }}22; color: {{ $theme }}">
                            キャンセル
                        </a>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

@endsection