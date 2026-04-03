@extends('layouts.company')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-stone-800">口コミ管理</h1>
            <p class="text-sm text-stone-500">投稿された口コミの確認・公開設定ができます。</p>
        </div>

        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-lg border border-stone-300 px-3 py-2">
                <option value="">すべて</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>確認待ち</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>公開中</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>非公開</option>
            </select>
            <button class="rounded-lg bg-stone-900 text-white px-4 py-2">絞り込む</button>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50">
                    <tr class="text-left text-stone-600">
                        <th class="px-4 py-3">投稿日</th>
                        <th class="px-4 py-3">評価</th>
                        <th class="px-4 py-3">ニックネーム</th>
                        <th class="px-4 py-3">本文</th>
                        <th class="px-4 py-3">状態</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr class="border-t border-stone-100">
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ optional($review->created_at)->format('Y/m/d H:i') }}
                            </td>
                            <td class="px-4 py-3">★{{ $review->rating }}</td>
                            <td class="px-4 py-3">{{ $review->nickname ?: 'お客様' }}</td>
                            <td class="px-4 py-3">
                                <div class="max-w-md line-clamp-2 text-stone-700">
                                    {{ $review->comment }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($review->status === 'pending')
                                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs">確認待ち</span>
                                @elseif ($review->status === 'approved')
                                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs">公開中</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs">非公開</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('company.reviews.show', $review) }}"
                                   class="inline-flex rounded-lg border border-stone-300 px-3 py-2 hover:bg-stone-50">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-stone-500">
                                口コミはまだありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection