<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>システム利用のお申し込み</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .industry-radio:checked + .industry-card {
            border-color: #0ea5e9;
            background: linear-gradient(135deg, #f0f9ff 0%, #eff6ff 100%);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.12);
        }
        .industry-radio:focus-visible + .industry-card { outline: 3px solid #0284c7; outline-offset: 3px; }
        .industry-indicator { width: 18px; height: 18px; border: 2px solid #94a3b8; border-radius: 50%; background: white; }
        .industry-radio:checked + .industry-card .industry-indicator { border: 5px solid #0284c7; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-sky-50 min-h-screen text-slate-800">

<div class="min-h-screen px-4 py-8 lg:py-12">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-8 lg:mb-10">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-sky-100 text-sky-700 text-sm font-semibold mb-4">
                ご利用をお考えの方へ
            </div>
            <h1 class="text-3xl lg:text-5xl font-bold tracking-tight mb-4">
                システム利用のお申し込み
            </h1>
            <p class="text-slate-500 max-w-2xl mx-auto leading-7 text-sm sm:text-base">
                毎日の予約受付や顧客管理、シフト管理をもっとスムーズに。<br>
                ご利用をご希望の方は、こちらからお申し込みください。
            </p>
        </div>

        <div class="grid lg:grid-cols-[0.8fr_1.6fr] gap-6 lg:gap-8">

            {{-- 左側案内 --}}
            <div class="space-y-6 order-2 lg:order-1">
                <div class="bg-slate-900 text-white rounded-3xl p-7 lg:p-8 shadow-2xl">
                    <h2 class="text-2xl font-bold mb-5">
                        お申し込みの流れ
                    </h2>

                    <div class="space-y-5">
                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold shrink-0">1</div>
                            <div>
                                <div class="font-semibold mb-1">基本情報を入力</div>
                                <div class="text-white/70 text-sm leading-6">
                                    業種や企業名、ご担当者の連絡先をご入力ください。
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold shrink-0">2</div>
                            <div>
                                <div class="font-semibold mb-1">お申し込み内容を送信</div>
                                <div class="text-white/70 text-sm leading-6">
                                    送信後、ご入力のメールアドレスに受付メールをお送りします。
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold shrink-0">3</div>
                            <div>
                                <div class="font-semibold mb-1">メールでご案内</div>
                                <div class="text-white/70 text-sm leading-6">
                                    お申し込み内容を確認し、利用開始についてメールでご連絡します。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">こんなお悩みに</h3>
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
                    <h3 class="text-lg font-bold text-sky-800 mb-3">ご相談もあわせてどうぞ</h3>
                    <p class="text-sm text-sky-900/80 leading-6">
                        導入にあたって気になることがあれば、補足欄にお書きください。<br>
                        補足欄は空欄のままでもお申し込みいただけます。
                    </p>
                </div>
            </div>

            {{-- 右側フォーム --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-5 sm:p-8 lg:p-10 order-1 lg:order-2 min-w-0">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold mb-2">お申し込みフォーム</h2>
                    <p class="text-sm text-slate-500">
                        「必須」「*」の項目をご入力ください。補足・ご相談は任意です。
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
                        <div class="font-semibold mb-2">以下の項目をご確認ください。</div>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('company.application.store') }}" method="POST" class="space-y-6" id="applicationForm">
                    @csrf

                    <fieldset aria-describedby="industry-help{{ $errors->has('industry_type') ? ' industry-error' : '' }}">
                        <legend class="text-base font-bold mb-2">業種 <span class="text-xs text-red-600 ml-2">必須</span></legend>
                        <p id="industry-help" class="text-sm text-slate-500 mb-4">主な業種を1つお選びください。</p>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach(config('industries.options') as $value => $label)
                                <label class="block cursor-pointer min-w-0">
                                    <input type="radio" name="industry_type" value="{{ $value }}"
                                           class="industry-radio sr-only" required
                                           @checked(old('industry_type') === $value)
                                           aria-invalid="{{ $errors->has('industry_type') ? 'true' : 'false' }}">
                                    <span class="industry-card flex items-center gap-2 rounded-xl border-2 border-slate-200 px-3 py-4 h-full text-sm sm:text-base font-semibold transition hover:border-sky-300">
                                        <span class="industry-indicator shrink-0" aria-hidden="true"></span>
                                        <span>{{ $label }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500 mt-3">選択に迷う場合や、当てはまる業種がない場合は「その他」をお選びください。</p>
                        @error('industry_type')
                            <p id="industry-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <div>
                        <label for="company_name" class="block text-sm font-semibold mb-2">
                            企業名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="company_name" name="company_name" required maxlength="255" autocomplete="organization"
                               value="{{ old('company_name') }}"
                               placeholder="例：株式会社サンプル美容"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-semibold mb-2">
                            担当者名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="contact_person" name="contact_person" required maxlength="255" autocomplete="name"
                               value="{{ old('contact_person') }}"
                               placeholder="例：山田 太郎"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                    </div>

                    <div class="grid gap-5">
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-2">
                                メールアドレス <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   id="email" name="email" required maxlength="255" autocomplete="email"
                                   value="{{ old('email') }}"
                                   placeholder="example@company.co.jp"
                                   class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                            <p class="mt-2 text-xs sm:text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 leading-6">
                                ご案内は @tocoluca.com からお送りします。受信制限を設定されている方は、このドメインの受信を許可してください。
                            </p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold mb-2">
                                電話番号 <span class="text-red-500">*</span>
                            </label>
                            <input type="tel"
                                   id="phone" name="phone" required maxlength="255" autocomplete="tel"
                                   value="{{ old('phone') }}"
                                   placeholder="090-1234-5678"
                                   class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold mb-2">
                            補足・ご相談（任意）
                        </label>
                        <textarea id="message" name="message" maxlength="3000"
                                  rows="5"
                                  placeholder="例：導入時期について相談したい／複数店舗で利用したい"
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400">{{ old('message') }}</textarea>
                        <p class="text-xs text-slate-400 mt-2">
                            気になることがあればお気軽にどうぞ。「その他」を選んだ方は、業種もこちらにご記入いただけます。
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 sm:p-5">
                        <div class="text-sm font-semibold text-slate-800 mb-3">
                            規約への同意 <span class="text-red-500">*</span>
                        </div>

                        <label class="flex items-start gap-3 text-sm text-slate-600 leading-7">
                            <input
                                type="checkbox"
                                name="agree_terms"
                                value="1"
                                class="mt-1 rounded border-slate-300 text-sky-600 focus:ring-sky-400"
                                {{ old('agree_terms') ? 'checked' : '' }}
                                required
                            >
                            <span>
                                <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer" class="text-sky-600 font-semibold underline underline-offset-2 hover:text-sky-700">
                                    利用規約
                                </a>
                                ・
                                <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer" class="text-sky-600 font-semibold underline underline-offset-2 hover:text-sky-700">
                                    プライバシーポリシー
                                </a>
                                ・
                                <a href="{{ route('tokusho') }}" target="_blank" rel="noopener noreferrer" class="text-sky-600 font-semibold underline underline-offset-2 hover:text-sky-700">
                                    特定商取引法に基づく表記
                                </a>
                                を確認し、同意のうえ申し込みます。
                            </span>
                        </label>

                        @error('agree_terms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            id="submitButton"
                            class="w-full rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold py-4 shadow-lg shadow-sky-200 transition">
                        利用を申し込む
                    </button>

                    <p class="text-xs text-center text-slate-400">
                        受付後、お申し込み内容を確認し、利用開始についてメールでご案内します。
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