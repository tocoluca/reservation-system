@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';

    $imageCategories = [
        'カット',
        'メンズ',
        'キッズ',
        '前髪カット',
        'カラー',
        '白髪染め',
        'リタッチ',
        'パーマ',
        '縮毛矯正',
        'コンディショナー',
        'トリートメント',
        'ヘッドスパ',
        'ヘアアレンジ',
        '着付け',
        'まつげ',
        '眉',
        'フェイシャル',
        '整体',
    ];
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
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Category & Tag Settings</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">カテゴリー・タグ管理</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        メニューを見やすく整理するためのカテゴリーとタグを管理できます。
                    </p>
                </div>

                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                    ← ダッシュボード
                </a>
            </div>
        </div>
    </div>

    @include('company.menu._setup_nav', [
        'currentStep' => 1,
        'links' => [
            ['label' => 'メニュー管理へ', 'route' => 'company.menu.index', 'icon' => 'arrow-right'],
        ],
    ])

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <p class="font-bold">入力内容を確認してください</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="sticky z-30 rounded-[1.5rem] border border-white/80 bg-white/95 p-2 shadow-lg backdrop-blur"
         style="top: calc(var(--company-topbar-height, 6rem) + .75rem);">
        <div class="grid grid-cols-2 gap-2">
            <button type="button" data-menu-settings-tab="category"
                    class="menu-settings-tab rounded-2xl px-4 py-3 text-sm font-black transition">
                カテゴリー {{ $categories->count() }}件
            </button>
            <button type="button" data-menu-settings-tab="tag"
                    class="menu-settings-tab rounded-2xl px-4 py-3 text-sm font-black transition">
                タグ {{ $tags->count() }}件
            </button>
        </div>
    </div>

    {{-- カテゴリー管理 --}}
    <section data-menu-settings-panel="category" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="font-bold text-lg text-gray-900">カテゴリー</h2>
            <p class="text-sm text-gray-500 mt-1">
                メニューを大きな種類ごとに整理するためのグループです。
            </p>
        </div>

        <div class="p-6 space-y-6">

            <details class="group rounded-2xl border bg-stone-50/70"
                     style="border-color: {{ $theme }}22;">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 sm:px-5">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-lg"
                              style="background: {{ $theme }}15; color: {{ $theme }};">
                            📁
                        </span>
                        <span>
                            <span class="block font-bold text-gray-800">カテゴリーとは</span>
                            <span class="mt-0.5 block text-xs text-gray-500">使い方や標準画像について確認できます</span>
                        </span>
                    </span>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-stone-600 shadow-sm ring-1 ring-stone-200">
                        <span class="group-open:hidden">説明を開く</span>
                        <span class="hidden group-open:inline">閉じる</span>
                        <i data-lucide="chevron-down" class="h-4 w-4 transition group-open:rotate-180"></i>
                    </span>
                </summary>

                <div class="border-t border-stone-200 px-5 py-5 sm:pl-[4.5rem]">
                        <p class="text-sm text-gray-600 leading-7">
                            カテゴリーは、メニューを大きな種類ごとに整理するためのグループです。<br>
                            「カット」「カラー」「パーマ」などに分けることで、管理画面でも予約画面でも見やすくなります。
                        </p>

                        <p class="text-sm text-gray-600 leading-7 mt-3">
                            カテゴリーごとにお店独自の画像を設定できます。画像を設定しない場合は、
                            <span class="font-semibold text-gray-800">下記のカテゴリー名に合った標準画像</span>
                            が予約画面に表示されます。
                        </p>

                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($imageCategories as $imageCategory)
                                <span class="px-3 py-1.5 text-xs rounded-full border bg-white text-gray-700"
                                      style="border-color: {{ $theme }}33;">
                                    {{ $imageCategory }}
                                </span>
                            @endforeach
                        </div>

                        <p class="text-xs text-gray-500 mt-4 leading-6">
                            アップロードした画像は自動で中央を基準に切り抜かれ、640×640pxの正方形になります。
                        </p>
                </div>
            </details>

            <div class="rounded-2xl border border-stone-200 bg-white p-4 sm:p-5 shadow-sm">
                <div class="mb-3">
                    <h3 class="font-black text-stone-900">新しいカテゴリーを追加</h3>
                    <p class="mt-1 text-xs text-stone-500">お客様が内容を想像しやすい短い名前がおすすめです。</p>
                </div>
                <form method="POST"
                      action="{{ route('company.menu.category.store') }}"
                      class="flex flex-col sm:flex-row sm:items-end gap-3">
                    @csrf

                    <div class="min-w-0 flex-1">
                        <label for="category-name" class="mb-1.5 block text-sm font-bold text-stone-700">カテゴリー名</label>
                        <input id="category-name"
                               type="text"
                               name="category_name"
                               value="{{ old('category_name') }}"
                               maxlength="50"
                               required
                               autocomplete="off"
                               placeholder="例：カット、カラー、ヘッドスパ"
                               class="w-full rounded-2xl border border-stone-300 px-4 py-3 focus:outline-none focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}55;">
                    </div>

                    <button type="submit"
                            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl px-5 py-3 font-bold text-white shadow hover:opacity-90 transition"
                            style="background: {{ $theme }}">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        カテゴリーを追加
                    </button>
                </form>
            </div>

            <div>
                <div class="mb-3 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="font-black text-stone-900">登録済みカテゴリー</h3>
                        <p class="mt-1 text-xs text-stone-500">画像の変更や、未使用カテゴリーの削除ができます。</p>
                    </div>
                    <span class="text-xs font-bold text-stone-500">{{ $categories->count() }}件</span>
                </div>
                <div class="space-y-4">
                @forelse($categories as $category)
                    <article class="rounded-3xl border border-stone-200 bg-stone-50/60 p-4 sm:p-5">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                            <div class="flex min-w-0 items-center gap-4 lg:w-72">
                                <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                                    <img src="{{ $category->display_image_url }}"
                                         alt="{{ $category->name }}のカテゴリー画像"
                                         class="h-full w-full object-cover"
                                         data-category-image-preview="{{ $category->id }}"
                                         onerror="this.onerror=null;this.src='{{ asset('images/menu-icons/other.jpg') }}';">
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-black text-stone-900">{{ $category->name }}</h3>
                                    @if($category->image_path)
                                        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                            <i data-lucide="image" class="h-3.5 w-3.5"></i>
                                            お店の画像
                                        </span>
                                    @else
                                        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-stone-200 px-2.5 py-1 text-xs font-bold text-stone-600">
                                            <i data-lucide="sparkles" class="h-3.5 w-3.5"></i>
                                            標準画像
                                        </span>
                                    @endif
                                    <p class="mt-2 text-xs leading-5 text-stone-500">予約画面で表示される画像です</p>
                                    <span class="mt-2 inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-bold text-stone-600 ring-1 ring-stone-200">
                                        使用中 {{ $category->menus_count }}メニュー
                                    </span>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <form method="POST"
                                      action="{{ route('company.menu.category.image.update', $category->id) }}"
                                      enctype="multipart/form-data"
                                      class="flex flex-col gap-3 sm:flex-row sm:items-center"
                                      data-category-image-form>
                                    @csrf
                                    @method('PUT')

                                    <input id="category-image-{{ $category->id }}"
                                           type="file"
                                           name="image"
                                           accept="image/jpeg,image/png,image/webp"
                                           class="sr-only"
                                           data-category-image-input="{{ $category->id }}">

                                    <label for="category-image-{{ $category->id }}"
                                           class="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-stone-700 shadow-sm transition hover:border-stone-400 hover:bg-stone-50 focus-within:ring-2"
                                           style="--tw-ring-color: {{ $theme }}55;">
                                        <i data-lucide="image-plus" class="h-4 w-4"></i>
                                        <span data-category-file-label>画像を選ぶ</span>
                                    </label>

                                    <button type="submit"
                                            disabled
                                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                                            style="background: {{ $theme }}"
                                            data-category-image-submit>
                                        <i data-lucide="upload" class="h-4 w-4"></i>
                                        この画像を設定
                                    </button>
                                </form>
                                <p class="mt-2 text-xs text-stone-500">JPG・PNG・WebP／10MBまで。640×640pxに自動調整します。</p>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:w-44 lg:flex-col">
                                @if($category->image_path)
                                    <form method="POST"
                                          action="{{ route('company.menu.category.image.delete', $category->id) }}"
                                          onsubmit="return confirm('お店の画像を削除して標準画像に戻しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-bold text-stone-600 ring-1 ring-stone-200 transition hover:bg-stone-100">
                                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                                            標準画像に戻す
                                        </button>
                                    </form>
                                @endif

                                @if($category->menus_count > 0)
                                    <div class="rounded-xl bg-stone-100 px-3 py-2 text-center text-xs font-bold leading-5 text-stone-400"
                                         title="使用中のメニューからカテゴリーを外すと削除できます">
                                        使用中のため削除不可
                                    </div>
                                @else
                                    <form method="POST"
                                          action="{{ route('company.menu.category.delete', $category->id) }}"
                                          onsubmit="return confirm('カテゴリー「{{ addslashes($category->name) }}」を削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-100">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            カテゴリーを削除
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-stone-300 px-4 py-10 text-center text-sm text-gray-400">
                        カテゴリーはまだ登録されていません
                    </div>
                @endforelse
                </div>
            </div>

        </div>
    </section>

    {{-- タグ管理 --}}
    <section data-menu-settings-panel="tag" class="hidden bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="font-bold text-lg text-gray-900">タグ</h2>
            <p class="text-sm text-gray-500 mt-1">
                メニューの特徴を伝えるための補助ラベルです。
            </p>
        </div>

        <div class="p-6 space-y-6">

            <details class="group rounded-2xl border border-gray-200 bg-gray-50">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 sm:px-5">
                    <span>
                        <span class="block font-bold text-gray-800">タグとは</span>
                        <span class="mt-0.5 block text-xs text-gray-500">カテゴリーとの使い分けを確認できます</span>
                    </span>
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-stone-600 shadow-sm ring-1 ring-stone-200">
                        <span class="group-open:hidden">説明を開く</span>
                        <span class="hidden group-open:inline">閉じる</span>
                        <i data-lucide="chevron-down" class="h-4 w-4 transition group-open:rotate-180"></i>
                    </span>
                </summary>
                <div class="border-t border-gray-200 px-5 py-4">
                    <p class="text-sm text-gray-600 leading-6">
                        タグは「人気」「おすすめ」「新規向け」など、メニューの特徴をわかりやすく伝えるための目印です。
                        よく使う言葉を登録しておくことで、統一した表現で管理できます。
                    </p>
                </div>
            </details>

            <div class="rounded-2xl border border-stone-200 bg-white p-4 sm:p-5 shadow-sm">
                <div class="mb-3">
                    <h3 class="font-black text-stone-900">新しいタグを追加</h3>
                    <p class="mt-1 text-xs text-stone-500">メニューの特徴を短い言葉で登録してください。</p>
                </div>
                <form method="POST"
                      action="{{ route('company.menu.tag.store') }}"
                      class="flex flex-col sm:flex-row sm:items-end gap-3">
                    @csrf

                    <div class="min-w-0 flex-1">
                        <label for="tag-name" class="mb-1.5 block text-sm font-bold text-stone-700">タグ名</label>
                        <input id="tag-name"
                               type="text"
                               name="tag_name"
                               value="{{ old('tag_name') }}"
                               maxlength="50"
                               required
                               autocomplete="off"
                               placeholder="例：人気、おすすめ、新規向け"
                               class="w-full rounded-2xl border border-stone-300 px-4 py-3 focus:outline-none focus:ring-2"
                               style="--tw-ring-color: {{ $theme }}55;">
                    </div>

                    <button type="submit"
                            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl px-5 py-3 font-bold text-white shadow hover:opacity-90 transition"
                            style="background: {{ $theme }}">
                        <i data-lucide="plus" class="h-4 w-4"></i>
                        タグを追加
                    </button>
                </form>
            </div>

            <div>
                <div class="mb-3 flex items-end justify-between gap-3">
                    <div>
                        <h3 class="font-black text-stone-900">登録済みタグ</h3>
                        <p class="mt-1 text-xs text-stone-500">メニューで使用している件数も確認できます。</p>
                    </div>
                    <span class="shrink-0 text-xs font-bold text-stone-500">{{ $tags->count() }}件</span>
                </div>
            <div class="overflow-x-auto rounded-2xl border border-stone-200">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="border-b border-stone-200 px-4 py-3 text-left">タグ名</th>
                            <th class="border-b border-stone-200 px-4 py-3 w-28 text-center">使用状況</th>
                            <th class="border-b border-stone-200 px-4 py-3 w-28 text-center">操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tags as $tag)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="border-b border-stone-100 px-4 py-3 font-medium text-stone-800">
                                    {{ $tag->name }}
                                </td>

                                <td class="border-b border-stone-100 px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $tag->menus_count > 0 ? 'bg-sky-50 text-sky-700' : 'bg-stone-100 text-stone-500' }}">
                                        {{ $tag->menus_count }}件
                                    </span>
                                </td>

                                <td class="border-b border-stone-100 px-4 py-3 text-center">
                                    <form method="POST"
                                          action="{{ route('company.menu.tag.delete',$tag->id) }}"
                                          onsubmit="return confirm('タグ「{{ addslashes($tag->name) }}」を削除しますか？{{ $tag->menus_count > 0 ? '\n使用中のメニューからもタグが外れます。' : '' }}')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                                            削除
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"
                                    class="px-4 py-8 text-center text-gray-400">
                                    タグはまだ登録されていません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>

        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = Array.from(document.querySelectorAll('[data-menu-settings-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-menu-settings-panel]'));
    const storageKey = 'company-menu-settings-tab';
    const previewUrls = new Map();

    const activate = (name, updateHash = false) => {
        const selected = name === 'tag' ? 'tag' : 'category';
        panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.menuSettingsPanel !== selected));
        tabs.forEach(tab => {
            const active = tab.dataset.menuSettingsTab === selected;
            tab.classList.toggle('bg-slate-900', active);
            tab.classList.toggle('text-white', active);
            tab.classList.toggle('bg-slate-100', !active);
            tab.classList.toggle('text-slate-600', !active);
        });
        sessionStorage.setItem(storageKey, selected);
        if (updateHash) history.replaceState(null, '', `#${selected}`);
    };

    tabs.forEach(tab => tab.addEventListener('click', () => activate(tab.dataset.menuSettingsTab, true)));
    panels.forEach(panel => {
        panel.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => sessionStorage.setItem(storageKey, panel.dataset.menuSettingsPanel));
        });
    });

    document.querySelectorAll('[data-category-image-input]').forEach(input => {
        input.addEventListener('change', () => {
            const form = input.closest('[data-category-image-form]');
            const submit = form?.querySelector('[data-category-image-submit]');
            const label = form?.querySelector('[data-category-file-label]');
            const preview = document.querySelector(`[data-category-image-preview="${input.dataset.categoryImageInput}"]`);
            const file = input.files?.[0];

            if (previewUrls.has(input)) {
                URL.revokeObjectURL(previewUrls.get(input));
                previewUrls.delete(input);
            }

            if (!file) {
                if (submit) submit.disabled = true;
                if (label) label.textContent = '画像を選ぶ';
                return;
            }

            if (label) label.textContent = file.name;
            if (submit) submit.disabled = false;

            if (preview) {
                const previewUrl = URL.createObjectURL(file);
                previewUrls.set(input, previewUrl);
                preview.src = previewUrl;
            }
        });
    });

    activate(location.hash.slice(1) || sessionStorage.getItem(storageKey) || 'category');
});
</script>

@endsection
