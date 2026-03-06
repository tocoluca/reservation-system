@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

    {{-- ================= ヘッダー ================= --}}

<div class="max-w-6xl mx-auto">

    {{-- タイトル --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold">テーマカラー設定</h1>
            <p class="text-gray-500 text-sm mt-1">サイト全体のカラー設定</p>
        </div>
<a href="{{ route('company.dashboard') }}"
   class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
   style="border-color: {{ $theme }}; color: {{ $theme }}">
    ← ダッシュボード
</a>

    </div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    {{-- ================= テーマ選択 ================= --}}
    <form method="POST" action="{{ route('company.theme') }}">
        @csrf

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4 mb-10">

            @foreach($colors as $color)
                <label class="cursor-pointer">
                    <input type="radio"
                           name="theme_color"
                           value="{{ $color }}"
                           class="hidden theme-radio"
                           {{ $company->theme_color == $color ? 'checked' : '' }}>

                    <div class="h-14 rounded-xl border-4 transition
                                {{ $company->theme_color == $color ? 'border-black' : 'border-transparent' }}
                                theme-box"
                         style="background-color: {{ $color }}">
                    </div>
                </label>
            @endforeach

        </div>

        {{-- ボタン --}}
        <div class="flex flex-col sm:flex-row justify-between gap-4 mb-10">
@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp
            <button id="saveButton"
                    type="submit"
                    class="w-full sm:w-auto text-white px-6 py-3 rounded-lg shadow-lg
                           hover:opacity-90 transition duration-200"
                    style="background-color: {{ $theme }}">
                保存する
            </button>
        </div>

    </form>

    {{-- ================= プレビュー ================= --}}
    <hr class="my-10">

    <h2 class="text-xl font-bold mb-6">
        プレビュー
    </h2>

    <div class="max-w-md mx-auto bg-white shadow-2xl rounded-2xl p-6">

        <div class="text-center mb-6">
            <div class="text-lg font-bold">
                {{ $company->name }}
            </div>
            <div class="text-sm text-gray-500">
                オンライン予約
            </div>
        </div>

        <button id="previewButton"
                style="background-color: {{ $company->theme_color }}"
                class="w-full text-white py-4 rounded-full shadow-lg">
            予約する
        </button>

    </div>

</div>

{{-- ================= JS ================= --}}
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