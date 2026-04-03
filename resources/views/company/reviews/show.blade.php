@extends('layouts.company')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('company.reviews.index') }}" class="text-sm text-stone-500 hover:text-stone-700">
            ← 口コミ一覧へ戻る
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-stone-800">口コミ詳細</h1>
                <p class="text-sm text-stone-500 mt-1">
                    投稿日：{{ optional($review->created_at)->format('Y年m月d日 H:i') }}
                </p>
            </div>

            <div>
                @if ($review->status === 'pending')
                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-sm">確認待ち</span>
                @elseif ($review->status === 'approved')
                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-sm">公開中</span>
                @else
                    <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-sm">非公開</span>
                @endif
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="rounded-xl bg-stone-50 border border-stone-200 p-4">
                <div class="text-sm text-stone-500">評価</div>
                <div class="text-2xl font-bold mt-1">★{{ $review->rating }}</div>
            </div>

            <div class="rounded-xl bg-stone-50 border border-stone-200 p-4">
                <div class="text-sm text-stone-500">ニックネーム</div>
                <div class="text-lg font-semibold mt-1">{{ $review->nickname ?: 'お客様' }}</div>
            </div>
        </div>

        <div class="mb-8">
            <div class="text-sm text-stone-500 mb-2">口コミ本文</div>
            <div class="rounded-xl border border-stone-200 bg-white p-4 leading-relaxed whitespace-pre-wrap">{{ $review->comment ?: '（本文なし）' }}</div>
        </div>

        <div class="flex flex-wrap gap-3 mb-8">
            <form method="POST" action="{{ route('company.reviews.approve', $review) }}">
                @csrf
                <button type="submit" class="rounded-xl bg-emerald-600 text-white px-5 py-3 font-semibold hover:opacity-90">
                    公開する
                </button>
            </form>

            <form method="POST" action="{{ route('company.reviews.reject', $review) }}">
                @csrf
                <button type="submit" class="rounded-xl bg-stone-700 text-white px-5 py-3 font-semibold hover:opacity-90">
                    非公開にする
                </button>
            </form>
        </div>

        <div>
            <h2 class="text-lg font-bold mb-3">店舗からの返信</h2>
            <form method="POST" action="{{ route('company.reviews.reply', $review) }}" class="space-y-4">
                @csrf
                <textarea
                    name="owner_reply"
                    rows="5"
                    maxlength="2000"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-300"
                    placeholder="口コミへの返信を入力してください。">{{ old('owner_reply', $review->owner_reply) }}</textarea>

                <button type="submit" class="rounded-xl bg-stone-900 text-white px-5 py-3 font-semibold hover:opacity-90">
                    返信を保存する
                </button>
            </form>
        </div>
    </div>
</div>
@endsection