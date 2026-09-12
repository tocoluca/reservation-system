<!DOCTYPE html>
<html lang="ja">
<head>
    <title>企業ダッシュボードお知らせ登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100">
@include('admin.partials.navigation')

<div class="flex min-h-screen">



    <div class="flex-1 w-full min-w-0">



        <div class="max-w-4xl mx-auto px-4 py-6 md:py-10">

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">企業ダッシュボードお知らせ登録</h1>
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

            <form action="{{ route('admin.company-dashboard-notices.store') }}" method="POST" enctype="multipart/form-data"
                  class="bg-white rounded-2xl shadow-sm border p-6 space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold mb-2">題名</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded-xl px-4 py-3" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">詳細</label>
                    <textarea name="body" rows="8" class="w-full border rounded-xl px-4 py-3">{{ old('body') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">画像</label>
                    <input type="file" name="image" class="w-full border rounded-xl px-4 py-3">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">表示開始日</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full border rounded-xl px-4 py-3">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">表示終了日</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border rounded-xl px-4 py-3">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_new" value="1" {{ old('is_new') ? 'checked' : '' }}>
                        <span>NEWを付ける</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_important" value="1" {{ old('is_important') ? 'checked' : '' }}>
                        <span>重要を付ける</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <span>表示する</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">公開対象</label>
                    <select name="target_type" id="target_type" class="w-full border rounded-xl px-4 py-3" onchange="toggleCompanySelect()">
                        <option value="all" {{ old('target_type', 'all') === 'all' ? 'selected' : '' }}>全企業向け</option>
                        <option value="company" {{ old('target_type') === 'company' ? 'selected' : '' }}>特定企業向け</option>
                    </select>
                </div>

                <div id="companySelectWrap" style="{{ old('target_type', 'all') === 'company' ? '' : 'display:none;' }}">
                    <label class="block text-sm font-semibold mb-2">対象企業</label>
                    <select name="company_id" class="w-full border rounded-xl px-4 py-3">
                        <option value="">企業を選択してください</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4">
                    <button class="px-6 py-3 rounded-xl text-white font-semibold bg-blue-600">
                        登録する
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@include('admin.partials.mobile_nav')
</body>
</html>
