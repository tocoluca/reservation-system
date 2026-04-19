<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>企業利用申請</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .industry-radio:checked + .industry-card {
            border-color: #0ea5e9;
            background: linear-gradient(135deg, #f0f9ff 0%, #eff6ff 100%);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.12);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-sky-50 min-h-screen text-slate-800">

<div class="min-h-screen px-4 py-8 lg:py-12">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-8 lg:mb-10">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-sky-100 text-sky-700 text-sm font-semibold mb-4">
                BUSINESS APPLICATION
            </div>
            <h1 class="text-3xl lg:text-5xl font-bold tracking-tight mb-4">
                システム利用申請
            </h1>
            <p class="text-slate-500 max-w-2xl mx-auto leading-7 text-sm sm:text-base">
                予約受付、顧客管理、シフト管理まで一元化できるシステムです。<br>
                必要事項を入力して送信すると、管理者側へ申請内容が通知されます。
            </p>
        </div>

        <div class="grid lg:grid-cols-[1.05fr_1.35fr] gap-6 lg:gap-8">

            {{-- 左側案内 --}}
            <div class="space-y-6">
                <div class="bg-slate-900 text-white rounded-3xl p-7 lg:p-8 shadow-2xl">
                    <h2 class="text-2xl font-bold mb-5">
                        申請の流れ
                    </h2>

                    <div class="space-y-5">
                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold shrink-0">1</div>
                            <div>
                                <div class="font-semibold mb-1">必要事項を入力</div>
                                <div class="text-white/70 text-sm leading-6">
                                    企業名、担当者名、連絡先などの基本情報を入力します。
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold shrink-0">2</div>
                            <div>
                                <div class="font-semibold mb-1">申請を送信</div>
                                <div class="text-white/70 text-sm leading-6">
                                    送信された内容は管理者画面に登録され、確認対象になります。
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold shrink-0">3</div>
                            <div>
                                <div class="font-semibold mb-1">審査後にご案内</div>
                                <div class="text-white/70 text-sm leading-6">
                                    承認後、ログイン情報や利用開始案内がメールで届きます。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">こんな企業におすすめ</h3>
                    <div class="space-y-3 text-sm text-slate-600 leading-6">
                        <div class="flex gap-3">
                            <span class="text-sky-600 font-bold">●</span>
                            <span>電話予約とWEB予約をまとめたい</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-sky-600 font-bold">●</span>
                            <span>スタッフごとの予約管理を見やすくしたい</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-sky-600 font-bold">●</span>
                            <span>顧客管理やお知らせ配信もまとめて使いたい</span>
                        </div>
                    </div>
                </div>

                <div class="bg-sky-50 rounded-3xl border border-sky-100 p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-sky-800 mb-3">ご入力いただく内容</h3>
                    <p class="text-sm text-sky-900/80 leading-6">
                        企業名、担当者名、メールアドレス、電話番号、補足情報のみです。<br>
                        入力しやすい内容だけに絞っているので、短時間で申請できます。
                    </p>
                </div>
            </div>

            {{-- 右側フォーム --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-8 lg:p-10">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold mb-2">利用申請フォーム</h2>
                    <p class="text-sm text-slate-500">
                        必須項目をご入力のうえ、送信してください。
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
                        <div class="font-semibold mb-2">入力内容を確認してください</div>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('company.application.store') }}" method="POST" class="space-y-6" id="applicationForm">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold mb-3">
                            業種 <span class="text-red-500">*</span>
                        </label>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="block cursor-pointer">
                                <input type="radio"
                                       name="industry_type"
                                       value="beauty"
                                       class="industry-radio sr-only"
                                       {{ old('industry_type') === 'beauty' ? 'checked' : '' }}>
                                <div class="industry-card rounded-2xl border-2 border-slate-200 p-5 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="font-bold text-lg">美容</div>
                                        <div class="text-sky-600">✦</div>
                                    </div>
                                    <div class="text-sm text-slate-500 leading-6">
                                        美容院、サロン、まつげ、ネイルなど
                                    </div>
                                </div>
                            </label>
{{-- いったん非表示 
                            <label class="block cursor-pointer">
                                <input type="radio"
                                       name="industry_type"
                                       value="dental"
                                       class="industry-radio sr-only"
                                       {{ old('industry_type') === 'dental' ? 'checked' : '' }}>
                                <div class="industry-card rounded-2xl border-2 border-slate-200 p-5 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="font-bold text-lg">歯科</div>
                                        <div class="text-sky-600">✦</div>
                                    </div>
                                    <div class="text-sm text-slate-500 leading-6">
                                        歯科医院、矯正歯科、クリニックなど
                                    </div>
                                </div>
                            </label>
いったん非表示　--}}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            企業名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="company_name"
                               value="{{ old('company_name') }}"
                               placeholder="例：株式会社サンプル美容"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            担当者名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="contact_person"
                               value="{{ old('contact_person') }}"
                               placeholder="例：山田 太郎"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">
                                メールアドレス <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@company.co.jp"
                                   class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
						    <p class="mt-2 text-xs sm:text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 leading-6">
						        @tocoluca.com ドメインからのメールを受信できるよう、あらかじめ設定をお願いいたします。
						    </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">
                                電話番号 <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="090-1234-5678"
                                   class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            補足・お問い合わせ
                        </label>
                        <textarea name="message"
                                  rows="5"
                                  placeholder="導入予定時期、相談したい内容、店舗数など"
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">{{ old('message') }}</textarea>
                        <p class="text-xs text-slate-400 mt-2">
                            任意入力です。ご相談内容があれば自由にご記入ください。
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        <label class="flex items-start gap-3 text-sm text-slate-600 leading-6">
                            <input type="checkbox" name="agree" value="1" class="mt-1 rounded border-slate-300">
                            <span>
                                申請内容を送信し、管理者からの連絡を受けることに同意します。
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                    </div>

                    <button type="submit"
                            id="submitButton"
                            class="w-full rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold py-4 shadow-lg shadow-sky-200 transition">
                        利用申請を送信する
                    </button>

                    <p class="text-xs text-center text-slate-400">
                        送信後、管理者に通知されます。
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('applicationForm').addEventListener('submit', function () {
    const button = document.getElementById('submitButton');
    button.disabled = true;
    button.textContent = '送信中...';
    button.classList.add('opacity-70', 'cursor-not-allowed');
});
</script>

</body>
</html>