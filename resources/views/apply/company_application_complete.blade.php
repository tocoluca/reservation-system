<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>申請完了</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-sky-50 min-h-screen text-slate-800">

<div class="min-h-screen px-4 py-8 lg:py-12 flex items-center justify-center">
    <div class="w-full max-w-4xl grid lg:grid-cols-[0.95fr_1.05fr] bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200">

        <div class="bg-gradient-to-br from-emerald-600 to-sky-700 text-white p-8 lg:p-10">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-white/15 text-sm font-semibold mb-5">
                APPLICATION COMPLETE
            </div>

            <h1 class="text-3xl lg:text-4xl font-bold leading-tight mb-4">
                申請を受け付けました
            </h1>

            <p class="text-white/90 leading-7 text-sm lg:text-base mb-8">
                ご入力いただいた内容は正常に送信されました。<br>
                管理者側で確認後、登録メールアドレス宛にご案内をお送りします。
            </p>

            <div class="space-y-5 text-sm">
                <div class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center font-bold shrink-0">1</div>
                    <div>
                        <div class="font-semibold mb-1">管理者へ通知</div>
                        <div class="text-white/75 leading-6">
                            申請内容は管理者画面に登録され、確認対象になります。
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center font-bold shrink-0">2</div>
                    <div>
                        <div class="font-semibold mb-1">内容確認・審査</div>
                        <div class="text-white/75 leading-6">
                            内容確認後に承認または確認のご連絡を行います。
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center font-bold shrink-0">3</div>
                    <div>
                        <div class="font-semibold mb-1">利用開始案内</div>
                        <div class="text-white/75 leading-6">
                            承認後、ログイン情報や開始手順がメールで届きます。
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 lg:p-10 flex items-center">
            <div class="w-full">
                <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl mb-6 shadow-sm">
                    ✓
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold mb-3">
                        ありがとうございました
                    </h2>
                    <p class="text-slate-500 leading-7 text-sm sm:text-base">
                        通常は管理者確認後にご案内となります。<br>
                        少しお待ちください。
                    </p>
                </div>

                @if(!empty($applicationId))
                    <div class="rounded-2xl bg-sky-50 border border-sky-200 p-5 mb-6 text-center">
                        <div class="text-sm text-sky-700 mb-2 font-semibold">
                            受付番号
                        </div>
                        <div class="text-3xl font-bold text-sky-900 tracking-wider">
                            {{ $applicationId }}
                        </div>
                        <div class="text-xs text-sky-700 mt-2">
                            お問い合わせ時に必要になる場合があります
                        </div>
                    </div>
                @endif

                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 mb-8">
                    <h3 class="font-bold mb-3 text-slate-800">ご確認ください</h3>
                    <ul class="space-y-2 text-sm text-slate-600 leading-6">
                        <li>・入力したメールアドレスに案内が届きます</li>
                        <li>・迷惑メールフォルダも念のためご確認ください</li>
                        <li>・申請内容に不備がある場合は別途ご連絡することがあります</li>
                    </ul>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.application.create') }}"
                       class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold px-6 py-3.5">
                        申請画面に戻る
                    </a>

                    <a href="{{ url('/') }}"
                       class="flex-1 inline-flex items-center justify-center rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-semibold px-6 py-3.5 shadow-lg shadow-sky-200">
                        トップへ戻る
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>