<!DOCTYPE html>
<html lang="ja">
<head>
    <title>企業ダッシュボードお知らせ編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100">
@include('admin.partials.navigation')

<div class="flex min-h-screen">



    <div class="flex-1 w-full min-w-0">



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
                    <select name="target_type" id="target_type" class="w-full border rounded-xl px-4 py-3" onchange="toggleCompanySelect(true)">
                        <option value="all" {{ old('target_type', $notice->target_type) === 'all' ? 'selected' : '' }}>全企業向け</option>
                        <option value="company" {{ old('target_type', $notice->target_type) === 'company' ? 'selected' : '' }}>特定企業向け</option>
                    </select>
                </div>

                <div id="companySelectWrap" style="{{ old('target_type', $notice->target_type) === 'company' ? '' : 'display:none;' }}">
                    <div class="rounded-2xl border-2 border-blue-200 bg-blue-50 p-4">
                        <label for="company_code" class="block text-sm font-bold text-blue-950 mb-2">
                            対象企業コード <span class="text-red-600">必須</span>
                        </label>
                        <input type="text"
                               name="company_code"
                               id="company_code"
                               list="companyCodeOptions"
                               value="{{ old('company_code', optional($notice->company)->company_code) }}"
                               maxlength="8"
                               autocomplete="off"
                               class="w-full rounded-xl border-2 border-blue-300 bg-white px-4 py-3 font-mono uppercase focus:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100"
                               placeholder="例：ABCD1234">
                        <datalist id="companyCodeOptions">
                            @foreach($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->name }}</option>
                            @endforeach
                        </datalist>
                        <p class="mt-2 text-xs font-medium text-blue-800">入力した企業コードの企業にだけ表示されます。候補には企業名も表示されます。</p>
                    </div>
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
<script>
function toggleCompanySelect(focusInput = false) {
    const isCompany = document.getElementById('target_type')?.value === 'company';
    const wrap = document.getElementById('companySelectWrap');
    const input = document.getElementById('company_code');

    if (wrap) wrap.style.display = isCompany ? '' : 'none';
    if (input) {
        input.required = isCompany;
        input.disabled = !isCompany;
        if (isCompany && focusInput) input.focus();
    }
}

document.addEventListener('DOMContentLoaded', toggleCompanySelect);
</script>
</body>
</html>
