@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto">

    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg">

    {{-- タイトル --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold">企業ロゴ</h1>
            <p class="text-gray-500 text-sm mt-1">企業ロゴの設定・変更</p>
        </div>
<a href="{{ route('company.dashboard') }}"
   class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
   style="border-color: {{ $theme }}; color: {{ $theme }}">
    ← ダッシュボード
</a>

        </div>

        {{-- ================= 現在のロゴ ================= --}}
        <div class="mb-8 text-center">

            @if($company->logo_path)
		<img src="{{ asset($company->logo_path) }}"
     class="w-32 h-32 object-contain bg-white rounded">
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

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

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