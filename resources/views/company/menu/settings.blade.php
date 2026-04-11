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
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
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

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif

    {{-- カテゴリー管理 --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="font-bold text-lg text-gray-900">カテゴリー</h2>
            <p class="text-sm text-gray-500 mt-1">
                メニューを大きな種類ごとに整理するためのグループです。
            </p>
        </div>

        <div class="p-6 space-y-6">

            <div class="rounded-2xl border px-4 py-4"
                 style="border-color: {{ $theme }}22; background-color: #fffdf8;">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg shrink-0"
                         style="background: {{ $theme }}15; color: {{ $theme }};">
                        📁
                    </div>

                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800">カテゴリーとは</h3>

                        <p class="text-sm text-gray-600 leading-7 mt-1">
                            カテゴリーは、メニューを大きな種類ごとに整理するためのグループです。<br>
                            「カット」「カラー」「パーマ」などに分けることで、管理画面でも予約画面でも見やすくなります。
                        </p>

                        <p class="text-sm text-gray-600 leading-7 mt-3">
                            下記のカテゴリーを設定したメニューは、
                            <span class="font-semibold text-gray-800">予約画面でカテゴリーごとの固定イメージ画像</span>
                            を表示できます。
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
                            例：カットメニューは「カット」、白髪染めメニューは「白髪染め」、ヘッドスパメニューは「ヘッドスパ」のように設定しておくと分かりやすくなります。
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('company.menu.category.store') }}"
                  class="flex flex-col sm:flex-row gap-3">
                @csrf

                <input
                    type="text"
                    name="name"
                    placeholder="カテゴリー名を入力"
                    class="border border-stone-300 rounded-2xl px-4 py-3 flex-1">

                <button
                    class="text-white px-5 py-3 rounded-2xl shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    追加
                </button>
            </form>

            <div class="overflow-x-auto rounded-2xl border border-stone-200">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="border-b border-stone-200 px-4 py-3 text-left">カテゴリー名</th>
                            <th class="border-b border-stone-200 px-4 py-3 w-28 text-center">操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($categories as $category)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="border-b border-stone-100 px-4 py-3 font-medium text-stone-800">
                                    {{ $category->name }}
                                </td>

                                <td class="border-b border-stone-100 px-4 py-3 text-center">
                                    <form method="POST"
                                          action="{{ route('company.menu.category.delete',$category->id) }}"
                                          onsubmit="return confirm('削除しますか？')">
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
                                <td colspan="2"
                                    class="px-4 py-8 text-center text-gray-400">
                                    カテゴリーはまだ登録されていません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    {{-- タグ管理 --}}
    <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="font-bold text-lg text-gray-900">タグ</h2>
            <p class="text-sm text-gray-500 mt-1">
                メニューの特徴を伝えるための補助ラベルです。
            </p>
        </div>

        <div class="p-6 space-y-6">

            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
                <p class="text-sm text-gray-600 leading-6">
                    タグは「人気」「おすすめ」「新規向け」など、メニューの特徴をわかりやすく伝えるための目印です。
                    よく使う言葉を登録しておくことで、統一した表現で管理できます。
                </p>
            </div>

            <form method="POST"
                  action="{{ route('company.menu.tag.store') }}"
                  class="flex flex-col sm:flex-row gap-3">
                @csrf

                <input
                    type="text"
                    name="name"
                    placeholder="タグ名を入力"
                    class="border border-stone-300 rounded-2xl px-4 py-3 flex-1">

                <button
                    class="text-white px-5 py-3 rounded-2xl shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    追加
                </button>
            </form>

            <div class="overflow-x-auto rounded-2xl border border-stone-200">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="border-b border-stone-200 px-4 py-3 text-left">タグ名</th>
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
                                    <form method="POST"
                                          action="{{ route('company.menu.tag.delete',$tag->id) }}"
                                          onsubmit="return confirm('削除しますか？')">
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
                                <td colspan="2"
                                    class="px-4 py-8 text-center text-gray-400">
                                    タグはまだ登録されていません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </section>

</div>

@endsection