<!DOCTYPE html>
<html>
<head>
    <title>管理者ダッシュボード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }
    </script>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- スマホ用オーバーレイ -->
    <div id="sidebar"
         class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white p-6 transform -translate-x-full md:translate-x-0 transition duration-200 ease-in-out z-50">

        <h1 class="text-xl font-bold mb-8">管理者</h1>

        <ul class="space-y-4">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="block hover:text-gray-300">
                    ダッシュボード
                </a>
            </li>
            <li>
                <a href="{{ route('admin.company.index') }}" class="block hover:text-gray-300">
                    企業一覧
                </a>
            </li>
            <li>
                <a href="{{ route('admin.company.create') }}" class="block hover:text-gray-300">
                    企業登録
                </a>
            </li>
            <li>
                <a href="{{ route('admin.applications') }}" class="block hover:text-gray-300">
                    申請管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.inquiries.index') }}" class="block hover:text-gray-300">
                    FAQ・お問い合わせ管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.company-dashboard-notices.index') }}" class="block hover:text-gray-300">
                    企業向けお知らせ管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.company-dashboard-notices.create') }}" class="block hover:text-gray-300">
                    企業向けお知らせ登録
                </a>
            </li>
            <li class="pt-6 border-t border-gray-600">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="hover:text-gray-300">
                        ログアウト
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- メイン -->
    <div class="flex-1 w-full md:ml-64">

        <!-- スマホ用ヘッダー -->
        <div class="md:hidden bg-white p-4 shadow flex justify-between items-center">
            <button onclick="toggleMenu()" class="text-gray-700 text-2xl">
                ☰
            </button>
            <h1 class="font-bold">管理画面</h1>
        </div>

        <div class="p-4 md:p-10">

            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 mb-6 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <h2 class="text-2xl md:text-3xl font-bold mb-6 md:mb-8">
                ダッシュボード
            </h2>

            <!-- サマリーカード -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 mb-8 md:mb-10">

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">登録企業数</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ $companyCount ?? 0 }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">申請待ち</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ $pendingCount ?? 0 }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">利用停止中</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ $inactiveCount ?? 0 }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border border-amber-100">
                    <div class="text-gray-500 text-sm">FAQ・お問い合わせ</div>
                    <div class="text-3xl font-bold mt-2 text-amber-600">
                        {{ $openInquiryCount ?? 0 }}
                    </div>
                    <div class="text-sm font-semibold mt-3 text-amber-600">
                        回答待ち
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">お知らせ管理</div>
                    <div class="text-sm font-semibold mt-3 text-blue-600">
                        企業向け通知を登録
                    </div>
                </div>

            </div>

            <!-- クイックアクション -->
            <div class="bg-white p-6 rounded-xl shadow mb-8">

                <h3 class="text-lg md:text-xl font-bold mb-4">
                    クイック操作
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">

                    <a href="{{ route('admin.company.create') }}"
                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        ＋ 企業登録
                    </a>

                    <a href="{{ route('admin.company.index') }}"
                       class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        企業一覧を見る
                    </a>

                    <a href="{{ route('admin.applications') }}"
                       class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        申請確認
                    </a>

                    <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                       class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        FAQ確認
                    </a>

                    <a href="{{ route('admin.company-dashboard-notices.index') }}"
                       class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        お知らせ一覧
                    </a>

                    <a href="{{ route('admin.company-dashboard-notices.create') }}"
                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        お知らせ登録
                    </a>

                </div>

            </div>

            <!-- FAQ / お問い合わせ管理案内 -->
            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg md:text-xl font-bold mb-4">
                            FAQ・お問い合わせ管理
                        </h3>

                        <p class="text-gray-600 text-sm md:text-base mb-5 leading-7">
                            企業から届いたお問い合わせを確認し、回答できます。<br>
                            回答後は企業側のFAQ・お問い合わせ画面およびダッシュボードの回答欄で確認できるようになります。
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                               class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                                未回答のFAQを見る
                            </a>

                            <a href="{{ route('admin.inquiries.index') }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg text-center font-semibold">
                                FAQ一覧を開く
                            </a>
                        </div>
                    </div>

                    <div class="w-full lg:w-80 bg-amber-50 border border-amber-200 rounded-xl p-5">
                        <div class="text-sm text-amber-700 font-semibold">現在の状況</div>
                        <div class="mt-3 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">回答待ち</span>
                                <span class="font-bold text-amber-700">{{ $openInquiryCount ?? 0 }}件</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">回答済み</span>
                                <span class="font-bold text-emerald-700">{{ $answeredInquiryCount ?? 0 }}件</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 新着FAQ -->
            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold">
                            新着FAQ・お問い合わせ
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            直近で届いた未回答のお問い合わせです
                        </p>
                    </div>

                    <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                       class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg text-center font-semibold text-sm">
                        すべて確認する
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($latestOpenInquiries as $inquiry)
                        <div class="border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                            未回答
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ optional($inquiry->created_at)->format('Y/m/d H:i') }}
                                        </span>
                                    </div>

                                    <div class="font-bold text-gray-900">
                                        {{ $inquiry->subject }}
                                    </div>

                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ $inquiry->company->name ?? '企業名不明' }}
                                        @if(!empty($inquiry->category))
                                            ／ {{ $inquiry->category }}
                                        @endif
                                    </div>

                                    <div class="text-sm text-gray-600 mt-3 leading-6">
                                        {{ \Illuminate\Support\Str::limit($inquiry->body, 140) }}
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                                       class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-lg text-center font-semibold text-sm whitespace-nowrap">
                                        回答する
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 py-8 text-center">
                            現在、未回答のFAQ・お問い合わせはありません
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- お知らせ管理案内 -->
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg md:text-xl font-bold mb-4">
                    企業ダッシュボードお知らせ
                </h3>

                <p class="text-gray-600 text-sm md:text-base mb-5 leading-7">
                    企業のダッシュボードに表示するお知らせを管理できます。<br>
                    表示期間、NEW、重要、題名、詳細、画像、全企業向け / 特定企業向けの設定が可能です。
                </p>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('admin.company-dashboard-notices.create') }}"
                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        新しくお知らせを登録する
                    </a>

                    <a href="{{ route('admin.company-dashboard-notices.index') }}"
                       class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        お知らせ一覧を開く
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>