@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto">

    {{-- タイトル --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold">マイプロフィール</h1>
            <p class="text-gray-500 text-sm mt-1">プロフィール設定・パスワード変更</p>
        </div>
<a href="{{ route('company.dashboard') }}"
   class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
   style="border-color: {{ $theme }}; color: {{ $theme }}">
    ← ダッシュボード
</a>

        </div>
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
			    <img src="{{ asset($staff->image_path) }}"
			         class="h-10 w-10 rounded-full object-cover">
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

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp
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