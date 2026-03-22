<!DOCTYPE html>
<html>
<head>
    <title>企業ダッシュボードお知らせ管理</title>
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

    <div id="sidebar"
         class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white p-6 transform -translate-x-full md:translate-x-0 transition duration-200 ease-in-out z-50">

        <h1 class="text-xl font-bold mb-8">管理者</h1>

        <ul class="space-y-4">
            <li><a href="{{ route('admin.dashboard') }}" class="block hover:text-gray-300">ダッシュボード</a></li>
            <li><a href="{{ route('admin.company.index') }}" class="block hover:text-gray-300">企業一覧</a></li>
            <li><a href="{{ route('admin.company.create') }}" class="block hover:text-gray-300">企業登録</a></li>
            <li><a href="{{ route('admin.applications') }}" class="block hover:text-gray-300">申請管理</a></li>
            <li><a href="{{ route('admin.company-dashboard-notices.index') }}" class="block hover:text-gray-300">企業向けお知らせ管理</a></li>
            <li><a href="{{ route('admin.company-dashboard-notices.create') }}" class="block hover:text-gray-300">企業向けお知らせ登録</a></li>
            <li class="pt-6 border-t border-gray-600">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="hover:text-gray-300">ログアウト</button>
                </form>
            </li>
        </ul>
    </div>

    <div class="flex-1 w-full md:ml-64">

        <div class="md:hidden bg-white p-4 shadow flex justify-between items-center">
            <button onclick="toggleMenu()" class="text-gray-700 text-2xl">☰</button>
            <h1 class="font-bold">管理画面</h1>
        </div>

        <div class="p-4 md:p-10">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">企業ダッシュボードお知らせ管理</h1>
                    <p class="text-sm text-gray-500 mt-1">企業向けのお知らせを管理します</p>
                </div>

                <a href="{{ route('admin.company-dashboard-notices.create') }}"
                   class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold">
                    ＋ 新規登録
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">題名</th>
                                <th class="px-4 py-3 text-left">対象</th>
                                <th class="px-4 py-3 text-left">表示期間</th>
                                <th class="px-4 py-3 text-center">状態</th>
                                <th class="px-4 py-3 text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notices as $notice)
                                <tr class="border-t">
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($notice->is_important)
                                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">重要</span>
                                            @endif
                                            @if($notice->is_new)
                                                <span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">NEW</span>
                                            @endif
                                            <span class="font-semibold">{{ $notice->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ $notice->target_label }}</td>
                                    <td class="px-4 py-3">
                                        {{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }}
                                        〜
                                        {{ optional($notice->end_date)->format('Y/m/d') ?: '指定なし' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($notice->is_active)
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">表示中</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">非表示</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('admin.company-dashboard-notices.edit', $notice) }}"
                                               class="px-3 py-2 rounded-lg bg-amber-500 text-white">
                                                編集
                                            </a>
                                            <form action="{{ route('admin.company-dashboard-notices.destroy', $notice) }}" method="POST" onsubmit="return confirm('削除しますか？')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-2 rounded-lg bg-red-500 text-white">
                                                    削除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        お知らせはまだありません。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-4 border-t">
                    {{ $notices->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>