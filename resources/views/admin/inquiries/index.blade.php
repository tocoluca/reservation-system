<!DOCTYPE html>
<html>
<head>
    <title>FAQ・お問い合わせ管理</title>
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
                <a href="{{ route('admin.inquiries.index') }}" class="block text-amber-300 font-semibold">
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

            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold">
                            FAQ・お問い合わせ管理
                        </h2>
                        <p class="text-gray-500 text-sm md:text-base mt-2 leading-7">
                            企業から届いたお問い合わせを確認し、回答できます。
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                           class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-3 rounded-lg text-center font-semibold text-sm">
                            未回答のみ表示
                        </a>

                        <a href="{{ route('admin.inquiries.index') }}"
                           class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-lg text-center font-semibold text-sm">
                            すべて表示
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <h3 class="text-lg md:text-xl font-bold mb-4">
                    絞り込み
                </h3>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">状態</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                            <option value="">すべて</option>
                            <option value="open" @selected(request('status') === 'open')>受付中</option>
                            <option value="answered" @selected(request('status') === 'answered')>回答済み</option>
                            <option value="closed" @selected(request('status') === 'closed')>完了</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">カテゴリ</label>
                        <select name="category" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                            <option value="">すべて</option>
                            <option value="staff" @selected(request('category') === 'staff')>スタッフ表示</option>
                            <option value="business_day" @selected(request('category') === 'business_day')>営業日設定</option>
                            <option value="reservation" @selected(request('category') === 'reservation')>予約設定</option>
                            <option value="mail" @selected(request('category') === 'mail')>メール</option>
                            <option value="other" @selected(request('category') === 'other')>その他</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex items-end gap-3">
                        <button type="submit"
                                class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-3 rounded-lg text-center font-semibold text-sm">
                            絞り込む
                        </button>

                        <a href="{{ route('admin.inquiries.index') }}"
                           class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-lg text-center font-semibold text-sm">
                            リセット
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <h3 class="text-lg md:text-xl font-bold">
                        一覧
                    </h3>

                    <div class="text-sm text-gray-500">
                        {{ $inquiries->total() }}件
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50 text-gray-600">
                                <th class="p-3 text-left whitespace-nowrap">受付日時</th>
                                <th class="p-3 text-left whitespace-nowrap">会社名</th>
                                <th class="p-3 text-left whitespace-nowrap">カテゴリ</th>
                                <th class="p-3 text-left whitespace-nowrap">件名</th>
                                <th class="p-3 text-left whitespace-nowrap">状態</th>
                                <th class="p-3 text-left whitespace-nowrap">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inquiry)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3 whitespace-nowrap">
                                        {{ optional($inquiry->created_at)->format('Y/m/d H:i') }}
                                    </td>

                                    <td class="p-3 whitespace-nowrap">
                                        {{ $inquiry->company->name ?? '-' }}
                                    </td>

                                    <td class="p-3 whitespace-nowrap">
                                        {{ $inquiry->category ?: '-' }}
                                    </td>

                                    <td class="p-3 min-w-[220px]">
                                        <div class="font-semibold text-gray-900">
                                            {{ $inquiry->subject }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ \Illuminate\Support\Str::limit($inquiry->body, 70) }}
                                        </div>
                                    </td>

                                    <td class="p-3 whitespace-nowrap">
                                        @if($inquiry->status === 'answered')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                                回答済み
                                            </span>
                                        @elseif($inquiry->status === 'closed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700">
                                                完了
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                                受付中
                                            </span>
                                        @endif
                                    </td>

                                    <td class="p-3 whitespace-nowrap">
                                        <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                                           class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-center font-semibold text-sm">
                                            {{ $inquiry->status === 'open' ? '回答する' : '詳細を見る' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-gray-400 py-10">
                                        お問い合わせはありません
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $inquiries->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>