@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">

    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-3 py-1 text-xs font-medium text-gray-500 shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full" style="background: {{ $theme }}"></span>
                シフト設定
            </div>

            <h1 class="mt-3 text-2xl sm:text-3xl font-bold text-gray-900">
                シフトパターン
            </h1>

            <p class="text-sm text-gray-500 mt-2">
                月シフトで使う勤務時間パターンを登録します
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl border text-sm font-semibold bg-white shadow-sm hover:bg-gray-50 transition"
           style="border-color:{{ $theme }}; color:{{ $theme }};">
            ダッシュボード
        </a>
    </div>

    <div class="mb-6 rounded-3xl border border-blue-100 bg-blue-50 px-5 py-4">
        <h2 class="text-sm font-bold text-blue-900">使い方</h2>
        <p class="mt-2 text-sm text-blue-800 leading-6">
            月シフト画面では、ここで登録した <span class="font-semibold">名前・色・表示順</span> がそのまま使われます。
            よく使う順に並べると、月シフト画面がかなり操作しやすくなります。
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">入力内容をご確認ください。</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('company.shift-patterns.store') }}" class="mb-6">
        @csrf

        <div class="bg-white shadow-sm rounded-3xl p-5 sm:p-6 border border-gray-100">
            <div class="flex items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">新しいシフトパターンを追加</h2>
                    <p class="text-sm text-gray-500 mt-1">月シフトで使いやすいように、色と表示順も一緒に設定できます。</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 items-start">
                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        パターン名 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="例：通常 / 早番 / 遅番 / 通し"
                        class="w-full border rounded-2xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        開始時間 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="start_time"
                        value="{{ old('start_time') }}"
                        class="w-full border rounded-2xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 @error('start_time') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        終了時間 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="end_time"
                        value="{{ old('end_time') }}"
                        class="w-full border rounded-2xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 @error('end_time') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        色
                    </label>
                    <div class="flex items-center gap-3">
                        <input
                            type="color"
                            id="colorPicker"
                            name="color"
                            value="{{ old('color', '#64748b') }}"
                            class="h-11 w-16 rounded-xl border border-gray-300 bg-white p-1 cursor-pointer">

                        <div id="colorPreviewLabel"
                             class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold text-white shadow-sm"
                             style="background: {{ old('color', '#64748b') }}">
                            プレビュー
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        表示順
                    </label>
                    <input
                        type="number"
                        name="sort_order"
                        min="1"
                        value="{{ old('sort_order', $patterns->count() + 1) }}"
                        class="w-full border rounded-2xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 @error('sort_order') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>
            </div>

            <div class="mt-5">
                <div class="text-sm font-medium text-gray-700 mb-2">おすすめカラー</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($presetColors as $presetColor)
                        <button type="button"
                                class="preset-color w-8 h-8 rounded-full border-2 border-white shadow"
                                style="background: {{ $presetColor }}"
                                data-color="{{ $presetColor }}"
                                aria-label="色を選択">
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    type="submit"
                    class="px-5 py-2.5 text-white rounded-2xl text-sm font-semibold shadow hover:opacity-90 transition"
                    style="background:{{ $theme }};">
                    追加する
                </button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('company.shift-patterns.order') }}">
        @csrf

        <div class="bg-white shadow-sm rounded-3xl overflow-hidden border border-gray-100">
            <div class="px-5 py-4 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">登録済みパターン</h2>
                    <p class="text-sm text-gray-500 mt-1">表示順を変えると、月シフト画面のボタン順も同じ順番になります。</p>
                </div>

                <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-2xl text-white text-sm font-semibold shadow hover:opacity-90 transition"
                        style="background: {{ $theme }}">
                    表示順を保存
                </button>
            </div>

            @if($patterns->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:{{ $theme }}; color:white;">
                                <th class="p-3 text-left font-semibold w-28">表示順</th>
                                <th class="p-3 text-left font-semibold">表示</th>
                                <th class="p-3 text-left font-semibold">名前</th>
                                <th class="p-3 text-left font-semibold">勤務時間</th>
                                <th class="p-3 text-center font-semibold w-24">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patterns as $p)
                                <tr class="border-t">
                                    <td class="p-3">
                                        <input type="number"
                                               name="orders[{{ $p->id }}]"
                                               min="1"
                                               value="{{ $p->sort_order }}"
                                               class="w-20 border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold text-white shadow-sm"
                                              style="background: {{ $p->color ?: '#64748b' }}">
                                            {{ $p->name }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-medium text-gray-900">
                                        {{ $p->name }}
                                    </td>
                                    <td class="p-3 text-gray-700">
                                        {{ substr($p->start_time, 0, 5) }} ～ {{ substr($p->end_time, 0, 5) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('company.shift-patterns.delete', $p->id) }}"
                                           class="text-red-500 hover:text-red-700 font-medium"
                                           onclick="return confirm('このシフトパターンを削除しますか？')">
                                            削除
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    まだシフトパターンは登録されていません。
                </div>
            @endif
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const colorPicker = document.getElementById('colorPicker');
    const colorPreviewLabel = document.getElementById('colorPreviewLabel');
    const presetButtons = document.querySelectorAll('.preset-color');

    function updatePreview(color) {
        if (!colorPicker || !colorPreviewLabel) return;
        colorPicker.value = color;
        colorPreviewLabel.style.background = color;
    }

    if (colorPicker) {
        colorPicker.addEventListener('input', function () {
            updatePreview(this.value);
        });
    }

    presetButtons.forEach(button => {
        button.addEventListener('click', function () {
            const color = this.dataset.color;
            updatePreview(color);
        });
    });
});
</script>

@endsection