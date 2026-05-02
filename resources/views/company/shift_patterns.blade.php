@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

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
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Shift Settings</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">シフトパターン</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        勤務管理で使う勤務時間パターンを、色付きでわかりやすく登録できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._shift_setup_nav', [
            'currentStep' => 1,
            'links' => [
                ['label' => '基本シフトへ', 'route' => 'company.staff-default-shifts', 'icon' => 'arrow-right'],
            ],
        ])
    </div>

    {{-- ガイド --}}
    <div class="mb-6 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">使い方</h2>
                <p class="text-sm text-gray-500 mt-1">
                    ここで登録した名前・色・表示順が、そのまま勤務管理画面に反映されます。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">1. パターンを追加</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">2. 色を決める</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">3. 表示順を整える</span>
            </div>
        </div>
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

    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-3xl bg-white border border-gray-100 p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-500">登録済み</div>
            <div class="mt-2 text-3xl font-black text-gray-900">{{ number_format($patterns->count()) }}</div>
        </div>
        <div class="rounded-3xl bg-white border border-gray-100 p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-500">次の表示順</div>
            <div class="mt-2 text-3xl font-black text-gray-900">{{ number_format($patterns->count() + 1) }}</div>
        </div>
        <div class="rounded-3xl bg-amber-50 border border-amber-100 p-5 shadow-sm">
            <div class="text-xs font-bold text-amber-700">勤務管理への反映</div>
            <div class="mt-2 text-sm font-bold text-amber-900">名前・色・順番がそのまま表示されます</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- 左：新規追加 --}}
        <div class="xl:col-span-1">
            <form method="POST" action="{{ route('company.shift-patterns.store') }}" class="h-full">
                @csrf

                <div class="bg-white shadow-sm rounded-3xl p-5 sm:p-6 border border-gray-100 h-full">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-gray-900">新しいシフトパターンを追加</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            名前・時間・色・表示順をまとめて設定できます。
                        </p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                パターン名 <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="例：通常 / 早番 / 遅番 / 通し"
                                class="w-full border rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 focus:ring-red-200 @else border-gray-300 @enderror"
                                style="--tw-ring-color: {{ $theme }};">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    開始時間 <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="time"
                                    name="start_time"
                                    value="{{ old('start_time') }}"
                                    class="w-full border rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 @error('start_time') border-red-400 focus:ring-red-200 @else border-gray-300 @enderror"
                                    style="--tw-ring-color: {{ $theme }};">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    終了時間 <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="time"
                                    name="end_time"
                                    value="{{ old('end_time') }}"
                                    class="w-full border rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 @error('end_time') border-red-400 focus:ring-red-200 @else border-gray-300 @enderror"
                                    style="--tw-ring-color: {{ $theme }};">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">色</label>

                            <div class="flex items-center gap-3">
                                <input
                                    type="color"
                                    id="colorPicker"
                                    name="color"
                                    value="{{ old('color', '#64748b') }}"
                                    class="h-12 w-20 rounded-2xl border border-gray-300 bg-white p-1 cursor-pointer">

                                <div class="flex-1 rounded-2xl border border-gray-200 bg-stone-50 px-4 py-3">
                                    <div class="text-xs text-gray-500 mb-2">プレビュー</div>
                                    <div id="colorPreviewLabel"
                                         class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold text-white shadow-sm"
                                         style="background: {{ old('color', '#64748b') }}">
                                        シフトラベル
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">おすすめカラー</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($presetColors as $presetColor)
                                    <button type="button"
                                            class="preset-color w-9 h-9 rounded-full border-2 border-white shadow hover:scale-105 transition"
                                            style="background: {{ $presetColor }}"
                                            data-color="{{ $presetColor }}"
                                            aria-label="色を選択">
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">表示順</label>
                            <input
                                type="number"
                                name="sort_order"
                                min="1"
                                value="{{ old('sort_order', $patterns->count() + 1) }}"
                                class="w-full border rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 @error('sort_order') border-red-400 focus:ring-red-200 @else border-gray-300 @enderror"
                                style="--tw-ring-color: {{ $theme }};">
                            <p class="text-xs text-gray-500 mt-2">
                                小さい番号ほど先に表示されます。
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            class="w-full px-5 py-3 text-white rounded-2xl text-sm font-semibold shadow hover:opacity-90 transition"
                            style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                            追加する
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- 右：一覧 --}}
        <div class="xl:col-span-2">
            <form method="POST" action="{{ route('company.shift-patterns.order') }}">
                @csrf

                <div class="bg-white shadow-sm rounded-3xl overflow-hidden border border-gray-100">
                    <div class="px-5 sm:px-6 py-5 border-b"
                         style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">登録済みパターン</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    表示順を変えると、月シフト画面のボタン順も同じ順番になります。
                                </p>
                            </div>

                            @if($patterns->count())
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl text-white text-sm font-semibold shadow hover:opacity-90 transition"
                                        style="background: {{ $theme }}">
                                    表示順を保存
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($patterns->count())
                        {{-- PC表示 --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-stone-50">
                                    <tr class="text-stone-700">
                                        <th class="p-4 text-left font-semibold w-28">表示順</th>
                                        <th class="p-4 text-left font-semibold">表示</th>
                                        <th class="p-4 text-left font-semibold">名前</th>
                                        <th class="p-4 text-left font-semibold">勤務時間</th>
                                        <th class="p-4 text-center font-semibold w-24">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patterns as $p)
                                        <tr class="border-t border-stone-100 hover:bg-stone-50 transition">
                                            <td class="p-4">
                                                <input type="number"
                                                       name="orders[{{ $p->id }}]"
                                                       min="1"
                                                       value="{{ $p->sort_order }}"
                                                       class="w-20 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                                       style="--tw-ring-color: {{ $theme }};">
                                            </td>
                                            <td class="p-4">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold text-white shadow-sm"
                                                      style="background: {{ $p->color ?: '#64748b' }}">
                                                    {{ $p->name }}
                                                </span>
                                            </td>
                                            <td class="p-4 font-medium text-gray-900">
                                                {{ $p->name }}
                                            </td>
                                            <td class="p-4 text-gray-700">
                                                {{ substr($p->start_time, 0, 5) }} ～ {{ substr($p->end_time, 0, 5) }}
                                            </td>
                                            <td class="p-4 text-center">
                                                <a href="{{ route('company.shift-patterns.delete', $p->id) }}"
                                                   class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 transition"
                                                   onclick="return confirm('このシフトパターンを削除しますか？')">
                                                    削除
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- SP表示 --}}
                        <div class="md:hidden divide-y divide-stone-100">
                            @foreach($patterns as $p)
                                <div class="p-4 space-y-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs text-gray-500">パターン名</div>
                                            <div class="mt-1 font-semibold text-gray-900">{{ $p->name }}</div>
                                        </div>

                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold text-white shadow-sm"
                                              style="background: {{ $p->color ?: '#64748b' }}">
                                            {{ $p->name }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl bg-stone-50 border border-stone-200 p-3">
                                            <div class="text-xs text-gray-500">勤務時間</div>
                                            <div class="mt-1 text-sm font-semibold text-gray-800">
                                                {{ substr($p->start_time, 0, 5) }} ～ {{ substr($p->end_time, 0, 5) }}
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-stone-50 border border-stone-200 p-3">
                                            <div class="text-xs text-gray-500">表示順</div>
                                            <input type="number"
                                                   name="orders[{{ $p->id }}]"
                                                   min="1"
                                                   value="{{ $p->sort_order }}"
                                                   class="mt-2 w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                                   style="--tw-ring-color: {{ $theme }};">
                                        </div>
                                    </div>

                                    <a href="{{ route('company.shift-patterns.delete', $p->id) }}"
                                       class="inline-flex items-center justify-center w-full rounded-xl px-3 py-3 text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 transition"
                                       onclick="return confirm('このシフトパターンを削除しますか？')">
                                        このシフトパターンを削除
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-5 py-4 border-t bg-white">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-2xl text-white text-sm font-semibold shadow hover:opacity-90 transition"
                                    style="background: {{ $theme }}">
                                表示順を保存
                            </button>
                        </div>
                    @else
                        <div class="px-5 py-12 text-center">
                            <div class="text-sm text-gray-500">まだシフトパターンは登録されていません。</div>
                            <div class="text-xs text-gray-400 mt-2">左のフォームから最初のパターンを追加してください。</div>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
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
