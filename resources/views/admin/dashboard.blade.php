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
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-8 md:mb-10">

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

            </div>

            <!-- クイックアクション -->
            <div class="bg-white p-6 rounded-xl shadow">

                <h3 class="text-lg md:text-xl font-bold mb-4">
                    クイック操作
                </h3>

                <div class="flex flex-col sm:flex-row gap-4">

                    <a href="{{ route('admin.company.create') }}"
                       class="bg-blue-500 text-white px-6 py-3 rounded-lg text-center">
                        ＋ 企業登録
                    </a>

                    <a href="{{ route('admin.company.index') }}"
                       class="bg-indigo-500 text-white px-6 py-3 rounded-lg text-center">
                        企業一覧を見る
                    </a>

                    <a href="{{ route('admin.applications') }}"
                       class="bg-green-500 text-white px-6 py-3 rounded-lg text-center">
                        申請確認
                    </a>

                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>