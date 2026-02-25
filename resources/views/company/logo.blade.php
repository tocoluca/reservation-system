@extends('layouts.company')

@section('content')

<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow">

    <h1 class="text-xl font-bold mb-6">
        ロゴ設定
    </h1>


    {{-- 現在のロゴ --}}
    <div class="mb-6 text-center">

        @if($company->logo_path)
            <img src="{{ asset('storage/'.$company->logo_path) }}"
                 class="h-32 mx-auto mb-4">
        @else
            <div class="h-32 w-32 mx-auto rounded-full bg-gray-200 flex items-center justify-center text-3xl font-bold">
                {{ strtoupper(substr($company->name,0,1)) }}
            </div>
        @endif

    </div>

    {{-- アップロード --}}
    <form method="POST" enctype="multipart/form-data">
        @csrf

        <input type="file"
               name="logo"
               accept="image/*"
               class="mb-4">

        @error('logo')
            <div class="text-red-500 mb-4">{{ $message }}</div>
        @enderror

		<div class="flex justify-between items-center mt-6">

		@php
		    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
		@endphp

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

		    <button type="submit"
		            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
		        保存する
		    </button>

		</div>

    </form>

</div>

@endsection