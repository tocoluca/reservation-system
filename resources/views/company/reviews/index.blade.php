@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto space-y-8">

    {{-- タイトル --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-800">口コミ管理</h1>
            <p class="text-sm text-stone-500 mt-1">
                投稿された口コミの確認・公開設定ができます。
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2 text-sm rounded-lg border bg-white hover:bg-gray-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ← ダッシュボード
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border px-4 py-3 text-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 口コミ一覧カード --}}
    <div class="bg-white rounded-xl shadow overflow-hidden border border-stone-200">

        {{-- ヘッダー --}}
        <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-lg text-stone-800">口コミ一覧</h2>
                <p class="text-sm text-stone-500 mt-1">
                    状態ごとに絞り込みながら確認できます。
                </p>
            </div>
        </div>

        {{-- フィルター --}}
        <div class="p-6 border-b bg-stone-50/60">
            <form method="GET" class="flex flex-wrap gap-3 items-center">
                <select name="status"
                        class="border border-stone-300 rounded-lg px-3 py-2 bg-white text-sm">
                    <option value="">すべて</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>確認待ち</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>公開中</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>非公開</option>
                </select>

                <button type="submit"
                        class="text-white px-4 py-2 rounded-lg text-sm transition"
                        style="background: {{ $theme }};">
                    絞り込む
                </button>

                @if(request()->filled('status'))
                    <a href="{{ route('company.reviews.index') }}"
                       class="px-4 py-2 rounded-lg border border-stone-300 text-sm text-stone-700 hover:bg-white transition">
                        条件をクリア
                    </a>
                @endif
            </form>
        </div>

        {{-- テーブル --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-50">
                    <tr class="text-left text-stone-600">
                        <th class="border-b border-stone-200 px-4 py-3 whitespace-nowrap">投稿日</th>
                        <th class="border-b border-stone-200 px-4 py-3 whitespace-nowrap">評価</th>
                        <th class="border-b border-stone-200 px-4 py-3 whitespace-nowrap">ニックネーム</th>
                        <th class="border-b border-stone-200 px-4 py-3">本文</th>
                        <th class="border-b border-stone-200 px-4 py-3 whitespace-nowrap">状態</th>
                        <th class="border-b border-stone-200 px-4 py-3 text-center whitespace-nowrap">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="border-b border-stone-100 px-4 py-4 whitespace-nowrap text-stone-700">
                                {{ optional($review->created_at)->format('Y/m/d H:i') }}
                            </td>

                            <td class="border-b border-stone-100 px-4 py-4 whitespace-nowrap font-medium text-amber-500">
                                ★{{ $review->rating }}
                            </td>

                            <td class="border-b border-stone-100 px-4 py-4 whitespace-nowrap text-stone-800">
                                {{ $review->nickname ?: 'お客様' }}
                            </td>

                            <td class="border-b border-stone-100 px-4 py-4 text-stone-700">
                                <div class="max-w-md line-clamp-2 leading-relaxed">
                                    {{ $review->comment }}
                                </div>
                            </td>

                            <td class="border-b border-stone-100 px-4 py-4 whitespace-nowrap">
                                @if ($review->status === 'pending')
                                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-medium">
                                        確認待ち
                                    </span>
                                @elseif ($review->status === 'approved')
                                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-medium">
                                        公開中
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-medium">
                                        非公開
                                    </span>
                                @endif
                            </td>

                            <td class="border-b border-stone-100 px-4 py-4 text-center">
                                <a href="{{ route('company.reviews.show', $review) }}"
                                   class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50 transition"
                                   style="border-color: {{ $theme }}; color: {{ $theme }};">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-stone-400">
                                口コミはまだありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ページネーション --}}
        <div class="p-4 border-t border-stone-100 bg-white">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection