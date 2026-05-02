<!DOCTYPE html>
<html>
<head>
    <title>FAQ・お問い合わせ詳細</title>
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

            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 mb-6 rounded">
                    入力内容をご確認ください。
                </div>
            @endif

            <div class="mb-6">
                <a href="{{ route('admin.inquiries.index') }}"
                   class="inline-flex items-center justify-center bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-lg text-center font-semibold text-sm">
                    ← FAQ一覧へ戻る
                </a>
            </div>

            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold">
                            {{ $inquiry->subject }}
                        </h2>
                        <p class="text-gray-500 text-sm md:text-base mt-2 leading-7">
                            企業から届いたFAQ・お問い合わせの詳細です。
                        </p>
                    </div>

                    <div>
                        @if($inquiry->status === 'answered')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-700">
                                回答済み
                            </span>
                        @elseif($inquiry->status === 'closed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-200 text-gray-700">
                                完了
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-amber-100 text-amber-700">
                                受付中
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 text-sm text-gray-600">
                    <div>会社名：{{ $inquiry->company->name ?? '-' }}</div>
                    <div>カテゴリ：{{ $inquiry->category ?: '-' }}</div>
                    <div>受付日時：{{ optional($inquiry->created_at)->format('Y/m/d H:i') }}</div>
                    <div>回答日時：{{ optional($inquiry->replied_at)->format('Y/m/d H:i') ?: '-' }}</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <h3 class="text-lg md:text-xl font-bold mb-4">
                    お問い合わせ内容
                </h3>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <div class="text-sm text-gray-700 leading-7 whitespace-pre-line">{{ $inquiry->body }}</div>
                </div>
            </div>

            @if($inquiry->admin_reply)
                <div class="bg-white p-6 rounded-xl shadow mb-8">
                    <h3 class="text-lg md:text-xl font-bold mb-4">
                        現在の回答
                    </h3>

                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-5">
                        <div class="text-sm text-gray-700 leading-7 whitespace-pre-line">{{ $inquiry->admin_reply }}</div>
                    </div>
                </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg md:text-xl font-bold mb-4">
                    回答登録
                </h3>

                <form action="{{ route('admin.inquiries.reply', $inquiry) }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">回答内容</label>
                        <textarea name="admin_reply" rows="10"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">{{ old('admin_reply', $inquiry->admin_reply) }}</textarea>
                        @error('admin_reply')
                            <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">状態</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                            <option value="answered" @selected(old('status', $inquiry->status) === 'answered')>回答済み</option>
                            <option value="closed" @selected(old('status', $inquiry->status) === 'closed')>完了</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit"
                                class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                            回答を登録する
                        </button>

                        <a href="{{ route('admin.inquiries.index') }}"
                           class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg text-center font-semibold">
                            一覧へ戻る
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@include('admin.partials.mobile_nav')
</body>
</html>
