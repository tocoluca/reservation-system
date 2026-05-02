@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Theme Settings</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">テーマカラー設定</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        サイト全体の印象を決めるカラーを選択できます。<br class="hidden sm:block">
                        選んだ色は、予約ボタンや主要な導線に反映されます。
                    </p>
                </div>

                <div>
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._storefront_settings_nav', ['current' => 'theme'])
    </div>

    {{-- ガイド --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">選び方のポイント</h2>
                <p class="text-sm text-gray-500 mt-1">
                    店舗の雰囲気やブランドに合う色を選ぶと、予約画面全体の印象が統一されます。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">落ち着き</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">高級感</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">親しみやすさ</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('company.theme') }}">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- 左：カラー選択 --}}
            <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"
                     style="background: linear-gradient(180deg, {{ $theme }}15 0%, #ffffff 100%);">
                    <h2 class="text-lg font-bold text-gray-900">カラーを選ぶ</h2>
                    <p class="text-sm text-gray-500 mt-1">選択したカラーは、すぐ右のプレビューに反映されます。</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                        @foreach($colors as $color)
                            <label class="cursor-pointer group">
                                <input type="radio"
                                       name="theme_color"
                                       value="{{ $color }}"
                                       class="hidden theme-radio"
                                       {{ $company->theme_color == $color ? 'checked' : '' }}>

                                <div class="theme-box rounded-2xl border-4 h-16 shadow-sm transition group-hover:scale-[1.03]
                                            {{ $company->theme_color == $color ? 'border-black' : 'border-transparent' }}"
                                     style="background-color: {{ $color }}">
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 右：プレビュー --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"
                     style="background: linear-gradient(180deg, {{ $theme }}15 0%, #ffffff 100%);">
                    <h2 class="text-lg font-bold text-gray-900">プレビュー</h2>
                    <p class="text-sm text-gray-500 mt-1">実際の予約画面の雰囲気に近い見た目です。</p>
                </div>

                <div class="p-6">
                    <div class="max-w-sm mx-auto bg-white shadow-xl rounded-3xl border border-stone-200 overflow-hidden">
                        <div class="px-5 py-5 text-white"
                             style="background: linear-gradient(135deg, {{ $company->theme_color }} 0%, {{ $company->theme_color }}dd 100%);"
                             id="previewHeader">
                            <div class="text-lg font-bold">{{ $company->name }}</div>
                            <div class="text-sm text-white/80 mt-1">オンライン予約</div>
                        </div>

                        <div class="p-5">
                            <div class="space-y-3">
                                <div class="rounded-2xl border border-stone-200 p-3">
                                    <div class="text-xs text-stone-500">メニュー</div>
                                    <div class="font-semibold text-stone-800 mt-1">カット</div>
                                </div>

                                <div class="rounded-2xl border border-stone-200 p-3">
                                    <div class="text-xs text-stone-500">担当者</div>
                                    <div class="font-semibold text-stone-800 mt-1">指名なし</div>
                                </div>

                                <button id="previewButton"
                                        style="background-color: {{ $company->theme_color }}"
                                        class="w-full text-white py-4 rounded-2xl shadow-lg font-semibold">
                                    予約する
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button id="saveButton"
                                type="submit"
                                class="w-full text-white px-6 py-3 rounded-2xl shadow-lg hover:opacity-90 transition duration-200 font-semibold"
                                style="background-color: {{ $theme }}">
                            このカラーで保存する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.theme-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        let selectedColor = this.value;

        document.getElementById('previewButton').style.backgroundColor = selectedColor;
        document.getElementById('saveButton').style.backgroundColor = selectedColor;
        document.getElementById('previewHeader').style.background =
            `linear-gradient(135deg, ${selectedColor} 0%, ${selectedColor}dd 100%)`;

        document.querySelectorAll('.theme-box').forEach(box => {
            box.classList.remove('border-black');
            box.classList.add('border-transparent');
        });

        this.nextElementSibling.classList.remove('border-transparent');
        this.nextElementSibling.classList.add('border-black');
    });
});
</script>

@endsection
