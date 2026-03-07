@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto space-y-8">

{{-- タイトル --}}
<div class="flex justify-between items-center">

    <div>
        <h1 class="text-2xl font-bold">メニュー管理</h1>
        <p class="text-gray-500 text-sm mt-1">
            メニューの設定・変更・削除
        </p>
    </div>

    <a href="{{ route('company.dashboard') }}"
       class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-50 transition"
       style="border-color: {{ $theme }}; color: {{ $theme }}">
        ← ダッシュボード
    </a>

</div>


{{-- メニュー一覧カード --}}
<div class="bg-white rounded-xl shadow">


    {{-- ヘッダー --}}
    <div class="p-6 border-b flex justify-between items-center">

        <h2 class="font-bold text-lg">
            メニュー一覧
        </h2>

        <a href="{{ route('company.menu.create') }}"
           class="text-white px-4 py-2 rounded-lg"
           style="background: {{ $theme }}">
            新規メニュー
        </a>

    </div>


    {{-- フィルター --}}
    <div class="p-6 border-b">

        <form method="GET" class="flex flex-wrap gap-3 items-center">

            <select name="category_id"
                    class="border rounded-lg px-3 py-2">

                <option value="">カテゴリ</option>

                @foreach($categories as $category)

                <option value="{{ $category->id }}"
                    @if(request('category_id')==$category->id) selected @endif>

                    {{ $category->name }}

                </option>

                @endforeach

            </select>


            <select name="tag_id"
                    class="border rounded-lg px-3 py-2">

                <option value="">タグ</option>

                @foreach($tags as $tag)

                <option value="{{ $tag->id }}"
                    @if(request('tag_id')==$tag->id) selected @endif>

                    {{ $tag->name }}

                </option>

                @endforeach

            </select>


            <select name="sort"
                    class="border rounded-lg px-3 py-2">

                <option value="">並び替え</option>

                <option value="name"
                    @if(request('sort')=='name') selected @endif>
                    名前順
                </option>

                <option value="price"
                    @if(request('sort')=='price') selected @endif>
                    料金順
                </option>

            </select>


            <button
                class="bg-gray-700 text-white px-4 py-2 rounded-lg">
                検索
            </button>

        </form>

    </div>


    {{-- テーブル --}}
    <div class="overflow-x-auto">

        <table class="w-full text-sm border">

            <thead class="bg-gray-50">

                <tr>

                    <th class="border px-4 py-3 text-left">
                        カテゴリ
                    </th>

                    <th class="border px-4 py-3 text-left">
                        名前
                    </th>

                    <th class="border px-4 py-3 text-left">
                        タグ
                    </th>

                    <th class="border px-4 py-3 text-center w-28">
                        時間
                    </th>

                    <th class="border px-4 py-3 text-center w-32">
                        料金
                    </th>

                    <th class="border px-4 py-3 text-center w-32">
                        操作
                    </th>

                </tr>

            </thead>


            <tbody>

            @forelse($menus as $menu)

                <tr class="hover:bg-gray-50">

                    <td class="border px-4 py-3">
                        {{ $menu->category->name ?? '-' }}
                    </td>

                    <td class="border px-4 py-3 font-medium">
                        {{ $menu->name }}
                    </td>


                    {{-- タグ --}}
                    <td class="border px-4 py-3">

                        <div class="flex flex-wrap gap-1">

                        @foreach($menu->tags as $tag)

                            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">
                                {{ $tag->name }}
                            </span>

                        @endforeach

                        </div>

                    </td>


                    <td class="border px-4 py-3 text-center">
                        {{ $menu->duration }}分
                    </td>

                    <td class="border px-4 py-3 text-center">
                        ¥{{ number_format($menu->price) }}
                    </td>


                    {{-- 操作 --}}
                    <td class="border px-4 py-3">

                        <div class="flex justify-center gap-3">

                            <a href="{{ route('company.menu.edit',$menu->id) }}"
                               class="text-blue-500 hover:underline text-sm">
                                編集
                            </a>

                            <form method="POST"
                                  action="{{ route('company.menu.destroy',$menu->id) }}"
                                  onsubmit="return confirm('削除しますか？')">

                                @csrf
                                @method('DELETE')

                                <button class="text-red-500 hover:underline text-sm">
                                    削除
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6"
                        class="border px-4 py-10 text-center text-gray-400">

                        メニューがまだ登録されていません

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

</div>

@endsection