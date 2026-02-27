@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">

    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">

        {{-- タイトル --}}
        <h1 class="text-2xl font-bold mb-8 text-center sm:text-left">
            ロゴ設定
        </h1>

        {{-- ================= 現在のロゴ ================= --}}
        <div class="mb-8 text-center">

            @if($company->logo_path)
                <img src="{{ asset('storage/'.$company->logo_path) }}"
                     class="h-28 sm:h-32 mx-auto mb-4 object-contain">
            @else
                <div class="h-24 w-24 sm:h-32 sm:w-32 mx-auto rounded-full bg-gray-200
                            flex items-center justify-center text-2xl sm:text-3xl font-bold">
                    {{ strtoupper(substr($company->name,0,1)) }}
                </div>
            @endif

        </div>

        {{-- ================= アップロード ================= --}}
        <form method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    ロゴ画像アップロード
                </label>

                <input type="file"
                       name="logo"
                       accept="image/*"
                       class="w-full border rounded-lg p-3 file:mr-4 file:py-2 file:px-4
                              file:rounded file:border-0 file:text-sm
                              file:font-semibold file:text-white
                              hover:file:opacity-90"
                       style="--tw-file-bg: {{ $theme }}">

                @error('logo')
                    <div class="text-red-500 mt-2 text-sm">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- ================= ボタン ================= --}}
            <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">

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
                               rounded-lg shadow-lg hover:opacity-90
                               transition duration-200"
                        style="background: {{ $theme }}">
                    保存する
                </button>

            </div>

        </form>

    </div>
</div>

@endsection