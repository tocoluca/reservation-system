@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Create Menu</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">メニュー登録</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        新しいメニューを登録します。カテゴリ・タグ・所要時間・料金を設定できます。
                    </p>
                </div>

                <a href="{{ route('company.menu.index') }}"
                   class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                    ← メニュー一覧
                </a>
            </div>
        </div>
    </div>

    <div class="sticky top-24 z-30 rounded-[1.75rem] border border-white/80 bg-white/90 p-4 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-bold text-gray-900">予約画面プレビュー</div>
                <div class="text-xs text-gray-500 mt-1">入力中のメニュー名・時間・料金がこのように表示されます。</div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span id="menuPreviewName" class="rounded-2xl bg-stone-100 px-4 py-2 text-sm font-bold text-stone-800">メニュー名</span>
                <span id="menuPreviewDuration" class="rounded-2xl bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700">0分</span>
                <span id="menuPreviewPrice" class="rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">¥0</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="POST" action="{{ route('company.menu.store') }}">
            @csrf

            <div class="px-6 py-4 border-b border-gray-100"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="font-bold text-lg text-gray-900">メニュー情報</h2>
                <p class="text-sm text-gray-500 mt-1">お客様に見せるメニュー内容を入力してください。</p>
            </div>

            <div class="p-6 space-y-6">

                <div>
                    <label class="block text-sm font-medium mb-2">カテゴリー</label>
                    <select name="menu_category_id"
                            required
                            class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">メニュー名</label>
                    <input
                        name="name"
                        class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">内容</label>
                    <textarea
                        name="description"
                        rows="5"
                        class="border border-stone-300 rounded-2xl px-4 py-3 w-full"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-3">タグ</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label class="flex items-center gap-2 bg-stone-100 px-4 py-2 rounded-2xl cursor-pointer hover:bg-stone-200 transition">
                                <input type="checkbox"
                                       name="tags[]"
                                       value="{{ $tag->id }}">
                                <span class="text-sm text-stone-700">
                                    {{ $tag->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">時間（分）</label>
                        <input
                            name="duration"
                            type="number"
                            class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">料金</label>
                        <input
                            name="price"
                            type="number"
                            class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                    </div>
                </div>

            </div>

            <div class="border-t border-stone-100 p-6 flex justify-end">
                <button
                    class="text-white px-8 py-3 rounded-2xl shadow-lg font-semibold hover:opacity-90 transition"
                    style="background: var(--company-theme-gradient);">
                    保存する
                </button>
            </div>

        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.querySelector('[name="name"]');
    const durationInput = document.querySelector('[name="duration"]');
    const priceInput = document.querySelector('[name="price"]');
    const namePreview = document.getElementById('menuPreviewName');
    const durationPreview = document.getElementById('menuPreviewDuration');
    const pricePreview = document.getElementById('menuPreviewPrice');

    function refreshMenuPreview() {
        if (namePreview) namePreview.textContent = nameInput?.value || 'メニュー名';
        if (durationPreview) durationPreview.textContent = `${durationInput?.value || 0}分`;
        if (pricePreview) pricePreview.textContent = `¥${Number(priceInput?.value || 0).toLocaleString()}`;
    }

    [nameInput, durationInput, priceInput].forEach(input => input?.addEventListener('input', refreshMenuPreview));
    refreshMenuPreview();
});
</script>

</div>

@endsection
