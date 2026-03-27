@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

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

<div class="max-w-5xl mx-auto space-y-8">

{{-- タイトル --}}
<div class="flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold">カテゴリー・タグ管理</h1>
        <p class="text-gray-500 text-sm mt-1">
            カテゴリー・タグの設定・変更
        </p>
    </div>

    <a href="{{ route('company.dashboard') }}"
       class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-50 transition"
       style="border-color: {{ $theme }}; color: {{ $theme }}">
        ← ダッシュボード
    </a>
</div>


{{-- メッセージ --}}
@if(session('error'))
<div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg">
    {{ session('error') }}
</div>
@endif

@if(session('success'))
<div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg">
    {{ session('success') }}
</div>
@endif


{{-- カテゴリー管理 --}}
<div class="bg-white rounded-xl shadow">

    <div class="p-6 border-b">
        <h2 class="font-bold text-lg">カテゴリー</h2>
    </div>

    <div class="p-6">

        {{-- 説明 --}}
        <div class="mb-5 rounded-xl border px-4 py-4"
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
                        たとえば「カット」「カラー」「パーマ」のように分けておくと、
                        メニュー管理画面でも予約画面でも見やすくなり、お客様が内容を選びやすくなります。
                    </p>

                    <p class="text-sm text-gray-600 leading-7 mt-3">
                        また、下記のカテゴリーを設定したメニューは、
                        <span class="font-semibold text-gray-800">予約画面でカテゴリーごとの固定イメージ画像</span>
                        を表示できます。見た目で内容が伝わりやすくなるため、該当するメニューにはカテゴリー設定をおすすめします。
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
                        例：カットメニューは「カット」、白髪染めメニューは「白髪染め」、
                        ヘッドスパメニューは「ヘッドスパ」のように設定しておくと、
                        予約画面で内容に合った画像を自動表示しやすくなります。
                    </p>
                </div>
            </div>
        </div>

        {{-- 追加フォーム --}}
        <form method="POST"
              action="{{ route('company.menu.category.store') }}"
              class="flex gap-3 mb-6">

            @csrf

            <input
                type="text"
                name="name"
                placeholder="カテゴリー名"
                class="border rounded-lg px-3 py-2 flex-1">

            <button
                class="text-white px-4 py-2 rounded-lg"
                style="background: {{ $theme }}">
                追加
            </button>

        </form>


        {{-- 一覧 --}}
        <table class="w-full border text-sm">

            <thead class="bg-gray-50">

                <tr>
                    <th class="border px-4 py-2 text-left">カテゴリー名</th>
                    <th class="border px-4 py-2 w-24 text-center">操作</th>
                </tr>

            </thead>

            <tbody>

                @forelse($categories as $category)

                <tr class="hover:bg-gray-50">

                    <td class="border px-4 py-2">
                        {{ $category->name }}
                    </td>

                    <td class="border px-4 py-2 text-center">

                        <form method="POST"
                              action="{{ route('company.menu.category.delete',$category->id) }}"
                              onsubmit="return confirm('削除しますか？')">

                            @csrf
                            @method('DELETE')

                            <button class="text-red-500 hover:underline text-sm">
                                削除
                            </button>

                        </form>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="2"
                        class="border px-4 py-6 text-center text-gray-400">
                        カテゴリーはまだ登録されていません
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- タグ管理 --}}
<div class="bg-white rounded-xl shadow">

    <div class="p-6 border-b">
        <h2 class="font-bold text-lg">タグ</h2>
    </div>

    <div class="p-6">

        {{-- 説明 --}}
        <div class="mb-5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-sm text-gray-600 leading-6">
                タグは、メニューの特徴をわかりやすく伝えるための目印です。
                「人気」「おすすめ」「新規向け」など、よく使う言葉を登録しておくことで、
                統一した表現で管理できます。
            </p>
        </div>

        {{-- 追加フォーム --}}
        <form method="POST"
              action="{{ route('company.menu.tag.store') }}"
              class="flex gap-3 mb-6">

            @csrf

            <input
                type="text"
                name="name"
                placeholder="タグ名"
                class="border rounded-lg px-3 py-2 flex-1">

            <button
                class="bg-green-500 text-white px-4 py-2 rounded-lg">
                追加
            </button>

        </form>


        {{-- 一覧 --}}
        <table class="w-full border text-sm">

            <thead class="bg-gray-50">

                <tr>
                    <th class="border px-4 py-2 text-left">タグ名</th>
                    <th class="border px-4 py-2 w-24 text-center">操作</th>
                </tr>

            </thead>

            <tbody>

                @forelse($tags as $tag)

                <tr class="hover:bg-gray-50">

                    <td class="border px-4 py-2">
                        {{ $tag->name }}
                    </td>

                    <td class="border px-4 py-2 text-center">

                        <form method="POST"
                              action="{{ route('company.menu.tag.delete',$tag->id) }}"
                              onsubmit="return confirm('削除しますか？')">

                            @csrf
                            @method('DELETE')

                            <button class="text-red-500 hover:underline text-sm">
                                削除
                            </button>

                        </form>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="2"
                        class="border px-4 py-6 text-center text-gray-400">
                        タグはまだ登録されていません
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


</div>

@endsection
