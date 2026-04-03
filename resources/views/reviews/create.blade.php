<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>口コミ投稿</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 text-stone-800">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 md:p-8">
            <h1 class="text-2xl font-bold mb-2">ご来店ありがとうございました</h1>
            <p class="text-sm text-stone-600 mb-6">
                よろしければご感想をお聞かせください。
            </p>

            <div class="mb-6 p-4 rounded-xl bg-stone-50 border border-stone-200">
                <div class="text-sm text-stone-500">店舗</div>
                <div class="font-semibold">{{ $reservation->company->name ?? '店舗名' }}</div>

                <div class="text-sm text-stone-500 mt-3">ご来店日時</div>
                <div class="font-medium">
                    {{ optional($reservation->start_at)->format('Y年m月d日 H:i') }}
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-red-700 text-sm">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('reviews.store', $reservation->review_token) }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold mb-3">評価</label>
                    <div class="flex gap-2 flex-wrap">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" {{ old('rating') == $i ? 'checked' : '' }}>
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full border border-stone-300 bg-white peer-checked:bg-amber-400 peer-checked:text-white peer-checked:border-amber-400 font-bold">
                                    {{ $i }}
                                </span>
                            </label>
                        @endfor
                    </div>
                    <p class="text-xs text-stone-500 mt-2">1が低評価、5が高評価です。</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">ニックネーム</label>
                    <input
                        type="text"
                        name="nickname"
                        value="{{ old('nickname') }}"
                        maxlength="100"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-300"
                        placeholder="例：さくら"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">口コミ本文</label>
                    <textarea
                        name="comment"
                        rows="6"
                        maxlength="2000"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-300"
                        placeholder="施術や接客のご感想をご記入ください。">{{ old('comment') }}</textarea>
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    ご投稿内容は確認後に公開されます。
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-stone-900 text-white py-3 font-semibold hover:opacity-90 transition"
                >
                    口コミを送信する
                </button>
            </form>
        </div>
    </div>
</body>
</html>