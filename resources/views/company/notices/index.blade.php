@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    {{-- ================= ヘッダー ================= --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8">

        <h1 class="text-2xl sm:text-3xl font-bold">
            お知らせ管理
        </h1>

        <a href="{{ route('company.dashboard') }}"
           class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }}">
            ← ダッシュボード
        </a>

    </div>

    {{-- 新規作成 --}}
    <div class="mb-6 text-right">
        <a href="{{ route('company.notices.create') }}"
           class="text-white px-4 py-2 rounded-lg shadow hover:opacity-90"
           style="background: {{ $theme }}">
            ＋ 新規作成
        </a>
    </div>

    {{-- ================= カード一覧 ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @forelse($notices as $notice)
        <div class="bg-white rounded-2xl shadow p-4">

            {{-- 画像 --}}
            @if($notice->image)
                <img src="{{ asset($notice->image) }}"
                     class="w-full h-40 object-cover rounded-xl mb-3">
            @endif

            {{-- タイトル --}}
            <div class="flex items-center gap-2 mb-2">
                @if($notice->is_important)
                    <span class="text-red-500 text-xs font-bold">重要</span>
                @endif

                <h2 class="font-bold text-lg">
                    {{ $notice->title }}
                </h2>
            </div>

            {{-- 期間 --}}
            <p class="text-xs text-gray-500 mb-2">
                {{ $notice->start_date ?? '即時' }} 〜 {{ $notice->end_date ?? '無期限' }}
            </p>

            {{-- 内容 --}}
            <p class="text-sm text-gray-700 line-clamp-3">
                {{ $notice->content }}
            </p>

            {{-- 操作 --}}
            <div class="flex justify-end gap-3 mt-4">

                <a href="{{ route('company.notices.edit',$notice) }}"
                   class="px-3 py-1 text-sm rounded-lg text-white shadow"
                   style="background: {{ $theme }}">
                    編集
                </a>

                <form method="POST"
                      action="{{ route('company.notices.destroy',$notice) }}">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('削除しますか？')"
                            class="px-3 py-1 text-sm rounded-lg bg-red-500 text-white shadow">
                        削除
                    </button>
                </form>

            </div>

        </div>

        @empty
            <p class="text-gray-400">お知らせがありません</p>
        @endforelse

    </div>

</div>

@endsection