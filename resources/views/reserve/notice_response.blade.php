<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ご予約変更のご確認</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="max-w-xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-4">ご予約変更のご確認</h1>

            <p class="text-sm text-gray-600 mb-6">
                店舗からのご案内内容をご確認のうえ、お手続きをお願いいたします。
            </p>

            <div class="space-y-3 text-sm mb-6">
                <div>
                    <div class="text-gray-500">お名前</div>
                    <div class="font-semibold">{{ $item->customer_name }}</div>
                </div>

                <div>
                    <div class="text-gray-500">ご予約日時</div>
                    <div class="font-semibold">
                        {{ optional($item->reservation->start_at)->format('Y/m/d H:i') }}
                    </div>
                </div>

                <div>
                    <div class="text-gray-500">ご案内理由</div>
                    <div class="font-semibold whitespace-pre-line">
                        {{ $item->notice->reason_text ?? '店舗都合によるご予約変更のお願い' }}
                    </div>
                </div>
            </div>

            @if($item->confirmed_at)
                <div class="rounded-xl bg-green-50 border border-green-200 p-4 text-green-700 font-semibold">
                    すでに確認手続きは完了しています。
                </div>
            @else
                <form method="POST" action="{{ route('reservation.notice.response.confirm', ['token' => $item->response_token]) }}">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-bold shadow-md hover:opacity-90 transition"
                            style="background:#2563eb;">
                        内容を確認しました
                    </button>
                </form>
            @endif

            <p class="text-xs text-gray-500 mt-4">
                ご不明点がございましたら店舗までご連絡ください。
            </p>
        </div>
    </div>
</body>
</html>