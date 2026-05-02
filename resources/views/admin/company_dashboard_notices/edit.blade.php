<!DOCTYPE html>
<html>
<head>
    <title>企業ダッシュボードお知らせ編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
        }
        function toggleCompanySelect() {
            const target = document.getElementById('target_type').value;
            const wrap = document.getElementById('companySelectWrap');
            wrap.style.display = (target === 'company') ? 'block' : 'none';
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

        <div class="max-w-4xl mx-auto px-4 py-6 md:py-10">

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">企業ダッシュボードお知らせ編集</h1>
                <a href="{{ route('admin.company-dashboard-notices.index') }}"
                   class="px-4 py-2 rounded-xl border font-semibold">
                    戻る
                </a>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.company-dashboard-notices.update', $notice) }}" method="POST" enctype="multipart/form-data"
                  class="bg-white rounded-2xl shadow-sm border p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold mb-2">題名</label>
                    <input type="text" name="title" value="{{ old('title', $notice->title) }}" class="w-full border rounded-xl px-4 py-3" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">詳細</label>
                    <textarea name="body" rows="8" class="w-full border rounded-xl px-4 py-3">{{ old('body', $notice->body) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">画像</label>
                    @if($notice->image)
                        <img src="{{ asset('storage/' . $notice->image) }}" class="w-40 rounded-xl border mb-3">
                    @endif
                    <input type="file" name="image" class="w-full border rounded-xl px-4 py-3">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">表示開始日</label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($notice->start_date)->format('Y-m-d')) }}" class="w-full border rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">表示終了日</label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($notice->end_date)->format('Y-m-d')) }}" class="w-full border rounded-xl px-4 py-3">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_new" value="1" {{ old('is_new', $notice->is_new) ? 'checked' : '' }}>
                        <span>NEWを付ける</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_important" value="1" {{ old('is_important', $notice->is_important) ? 'checked' : '' }}>
                        <span>重要を付ける</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $notice->is_active) ? 'checked' : '' }}>
                        <span>表示する</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">公開対象</label>
                    <select name="target_type" id="target_type" class="w-full border rounded-xl px-4 py-3" onchange="toggleCompanySelect()">
                        <option value="all" {{ old('target_type', $notice->target_type) === 'all' ? 'selected' : '' }}>全企業向け</option>
                        <option value="company" {{ old('target_type', $notice->target_type) === 'company' ? 'selected' : '' }}>特定企業向け</option>
                    </select>
                </div>

                <div id="companySelectWrap" style="{{ old('target_type', $notice->target_type) === 'company' ? '' : 'display:none;' }}">
                    <label class="block text-sm font-semibold mb-2">対象企業</label>
                    <select name="company_id" class="w-full border rounded-xl px-4 py-3">
                        <option value="">企業を選択してください</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" @selected(old('company_id', $notice->company_id) == $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4">
                    <button class="px-6 py-3 rounded-xl text-white font-semibold bg-blue-600">
                        更新する
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@include('admin.partials.mobile_nav')
</body>
</html>
