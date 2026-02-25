@extends('layouts.company')

@section('content')


@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-2xl font-bold mb-8">
        マイプロフィール
    </h1>


    <form method="POST"
          action="{{ route('company.my-profile.update') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- ============================= --}}
        {{-- 画像 --}}
        {{-- ============================= --}}
        <div class="mb-8">
            <label class="block font-semibold mb-3">
                プロフィール画像
            </label>

            <div class="flex items-center space-x-6">

                @if($staff->image_path)
                    <img src="{{ asset('storage/'.$staff->image_path) }}"
                         class="h-24 w-24 rounded-full object-cover shadow">
                @else
                    <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                        No Image
                    </div>
                @endif

                <input type="file"
                       name="image"
                       class="border p-2 rounded w-full">
            </div>
        </div>

        {{-- ============================= --}}
        {{-- コメント --}}
        {{-- ============================= --}}
        <div class="mb-8">
            <label class="block font-semibold mb-2">
                コメント
            </label>

            <textarea name="comment"
                      rows="3"
                      class="border p-3 w-full rounded-lg focus:ring focus:ring-blue-200">{{ old('comment',$staff->comment) }}</textarea>
        </div>

        <hr class="my-8">

        {{-- ============================= --}}
        {{-- パスワード変更 --}}
        {{-- ============================= --}}
        <h2 class="text-lg font-bold mb-4">
            パスワード変更
        </h2>

        <div class="grid grid-cols-2 gap-6 mb-8">

            <div>
                <label class="block mb-2 text-sm text-gray-600">
                    新しいパスワード
                </label>
                <input type="password"
                       name="password"
                       class="border p-3 w-full rounded-lg">
            </div>

            <div>
                <label class="block mb-2 text-sm text-gray-600">
                    確認用パスワード
                </label>
                <input type="password"
                       name="password_confirmation"
                       class="border p-3 w-full rounded-lg">
            </div>

        </div>

        {{-- ============================= --}}
        {{-- ボタン --}}
        {{-- ============================= --}}
        <div class="flex justify-between items-center">

		<div class="flex justify-between items-center mt-6">

		<a href="{{ route('company.dashboard') }}"
		   class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200"
		   style="color: {{ $theme }}">

		    <svg xmlns="http://www.w3.org/2000/svg"
		         class="w-4 h-4 transition-transform duration-200"
		         fill="none" viewBox="0 0 24 24" stroke="currentColor">
		        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
		              d="M15 19l-7-7 7-7" />
		    </svg>

		    ダッシュボードへ戻る
		</a>

		</div>

            <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                更新する
            </button>

        </div>

    </form>

</div>

@endsection