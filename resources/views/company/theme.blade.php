@extends('layouts.company')

@section('content')

<div class="max-w-4xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">
        テーマカラー設定
    </h1>

    {{-- ============================= --}}
    {{-- テーマ選択フォーム --}}
    {{-- ============================= --}}
    <form method="POST" action="{{ route('company.theme') }}">
        @csrf

        <div class="grid grid-cols-5 gap-4 mb-8">

            @foreach($colors as $color)
                <label class="cursor-pointer">
                    <input type="radio"
                           name="theme_color"
                           value="{{ $color }}"
                           class="hidden theme-radio"
                           {{ $company->theme_color == $color ? 'checked' : '' }}>

                    <div class="h-12 rounded-lg border-4
                                {{ $company->theme_color == $color ? 'border-black' : 'border-transparent' }}
                                theme-box"
                         style="background-color: {{ $color }}">
                    </div>
                </label>
            @endforeach

        </div>
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

    {{-- ============================= --}}
    {{-- プレビューエリア --}}
    {{-- ============================= --}}
    <hr class="my-10">

    <h2 class="text-xl font-bold mb-4">
        プレビュー
    </h2>

    <div class="max-w-md mx-auto bg-white shadow-xl rounded-2xl p-6">

        <div class="text-center mb-4">
            <div class="text-lg font-bold">
                {{ $company->name }}
            </div>
            <div class="text-sm text-gray-500">
                オンライン予約
            </div>
        </div>

        <button id="previewButton"
                style="background-color: {{ $company->theme_color }}"
                class="w-full text-white py-3 rounded-full">
            予約する
        </button>

    </div>

</div>

{{-- ============================= --}}
{{-- JS（リアルタイムプレビュー） --}}
{{-- ============================= --}}
<script>

document.querySelectorAll('.theme-radio').forEach(radio => {

    radio.addEventListener('change', function() {

        let selectedColor = this.value;

        // プレビュー更新
        document.getElementById('previewButton')
                .style.backgroundColor = selectedColor;

        document.getElementById('saveButton')
                .style.backgroundColor = selectedColor;

        // 枠線リセット
        document.querySelectorAll('.theme-box').forEach(box => {
            box.classList.remove('border-black');
            box.classList.add('border-transparent');
        });

        // 選択中を強調
        this.nextElementSibling.classList.remove('border-transparent');
        this.nextElementSibling.classList.add('border-black');
    });
});

</script>

@endsection