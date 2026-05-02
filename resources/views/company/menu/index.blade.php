@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
    $menuCollection = collect($menus ?? []);
    $menuCount = $menuCollection->count();
    $averagePrice = $menuCount > 0 ? (int) round($menuCollection->avg('price')) : 0;
    $averageDuration = $menuCount > 0 ? (int) round($menuCollection->avg('duration')) : 0;
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

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
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">
                        Menu Management
                    </p>

                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">
                        メニュー管理
                    </h1>

                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        メニューの登録・検索・編集・削除を行えます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>

                    <a href="{{ route('company.menu.create') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white text-sm font-semibold shadow text-stone-800 hover:bg-stone-100 transition">
                        新規メニュー
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('company.menu._setup_nav', [
        'currentStep' => 2,
        'links' => [
            ['label' => 'カテゴリー・タグ管理へ', 'route' => 'company.menu.settings', 'icon' => 'arrow-left'],
            ['label' => 'メニュー対応スタッフ設定へ', 'route' => 'company.menu-staff.index', 'icon' => 'arrow-right'],
        ],
    ])

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-3xl bg-white border border-gray-100 p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-500">表示中メニュー</div>
            <div class="mt-2 text-3xl font-black text-gray-900">{{ number_format($menuCount) }}</div>
        </div>
        <div class="rounded-3xl bg-white border border-gray-100 p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-500">平均時間</div>
            <div class="mt-2 text-3xl font-black text-gray-900">{{ number_format($averageDuration) }}分</div>
        </div>
        <div class="rounded-3xl bg-white border border-gray-100 p-5 shadow-sm">
            <div class="text-xs font-bold text-gray-500">平均料金</div>
            <div class="mt-2 text-3xl font-black text-gray-900">¥{{ number_format($averagePrice) }}</div>
        </div>
    </div>

    <div class="rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-sm">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('company.menu.index') }}" class="rounded-2xl px-4 py-2.5 text-sm font-bold {{ request('category_id') ? 'bg-stone-100 text-stone-700' : 'text-white' }}" style="{{ request('category_id') ? '' : 'background: '.$theme }}">
                すべて
            </a>
            @foreach($categories as $category)
                <a href="{{ route('company.menu.index', array_filter(['category_id' => $category->id, 'tag_id' => request('tag_id'), 'sort' => request('sort')])) }}"
                   class="rounded-2xl px-4 py-2.5 text-sm font-bold {{ (string) request('category_id') === (string) $category->id ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
                   style="{{ (string) request('category_id') === (string) $category->id ? 'background: '.$theme : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- フィルター --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="font-bold text-lg text-gray-900">
                検索・絞り込み
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                カテゴリ・タグ・並び順で一覧を整理できます。
            </p>
        </div>

        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">
                        カテゴリ
                    </label>

                    <select name="category_id"
                            class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                        <option value="">すべて</option>

                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @if(request('category_id') == $category->id) selected @endif>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">
                        タグ
                    </label>

                    <select name="tag_id"
                            class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                        <option value="">すべて</option>

                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}"
                                @if(request('tag_id') == $tag->id) selected @endif>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">
                        並び替え
                    </label>

                    <select name="sort"
                            class="border border-stone-300 rounded-2xl px-4 py-3 w-full">
                        <option value="">標準</option>
                        <option value="name" @if(request('sort') == 'name') selected @endif>
                            名前順
                        </option>
                        <option value="price" @if(request('sort') == 'price') selected @endif>
                            料金順
                        </option>
                    </select>
                </div>

                <div>
                    <button class="w-full bg-stone-800 text-white px-4 py-3 rounded-2xl font-semibold hover:bg-stone-700 transition">
                        検索する
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- メニュー一覧 --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-lg text-gray-900">
                メニュー一覧
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                登録済みのメニューを確認できます。
            </p>
        </div>

        <div class="max-h-[72vh] overflow-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="sticky top-0 z-20 bg-stone-50 border-b border-stone-300 px-4 py-3 text-left shadow-sm whitespace-nowrap">
                            カテゴリ
                        </th>

                        <th class="sticky top-0 z-20 bg-stone-50 border-b border-stone-300 px-4 py-3 text-left shadow-sm whitespace-nowrap">
                            名前
                        </th>

                        <th class="sticky top-0 z-20 bg-stone-50 border-b border-stone-300 px-4 py-3 text-left shadow-sm">
                            タグ
                        </th>

                        <th class="sticky top-0 z-20 bg-stone-50 border-b border-stone-300 px-4 py-3 text-center w-28 shadow-sm whitespace-nowrap">
                            時間
                        </th>

                        <th class="sticky top-0 z-20 bg-stone-50 border-b border-stone-300 px-4 py-3 text-center w-32 shadow-sm whitespace-nowrap">
                            料金
                        </th>

                        <th class="sticky top-0 z-20 bg-stone-50 border-b border-stone-300 px-4 py-3 text-center w-40 shadow-sm whitespace-nowrap">
                            操作
                        </th>
                    </tr>
                </thead>

                <tbody>
                @forelse($menus as $menu)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="border-b border-stone-100 px-4 py-3 text-stone-700">
                            {{ $menu->category->name ?? '-' }}
                        </td>

                        <td class="border-b border-stone-100 px-4 py-3 font-semibold text-stone-800">
                            {{ $menu->name }}
                        </td>

                        <td class="border-b border-stone-100 px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($menu->tags as $tag)
                                    <span class="text-xs px-2.5 py-1 rounded-full"
                                          style="background-color: {{ $theme }}15; color: {{ $theme }};">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <td class="border-b border-stone-100 px-4 py-3 text-center text-stone-700 whitespace-nowrap">
                            {{ $menu->duration }}分
                        </td>

                        <td class="border-b border-stone-100 px-4 py-3 text-center font-medium text-stone-800 whitespace-nowrap">
                            ¥{{ number_format($menu->price) }}
                        </td>

                        <td class="border-b border-stone-100 px-4 py-3">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('company.menu.edit', $menu->id) }}"
                                   class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                                    編集
                                </a>

                                <form method="POST"
                                      action="{{ route('company.menu.destroy', $menu->id) }}"
                                      onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                                        削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="px-4 py-10 text-center text-gray-400">
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
