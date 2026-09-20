<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>企業ダッシュボードお知らせ編集</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background: radial-gradient(circle at top left, rgba(245,158,11,.1), transparent 32rem), linear-gradient(180deg, #f8fafc 0%, #eef2f7 55%, #f8fafc 100%); }
        .form-panel { overflow: hidden; border: 2px solid #cbd5e1; border-radius: 1.5rem; background: #fff; box-shadow: 0 12px 30px rgba(15,23,42,.07); }
        .form-control { width: 100%; border: 2px solid #cbd5e1; border-radius: 1rem; background: #f8fafc; padding: .8rem 1rem; color: #0f172a; transition: .2s; }
        .form-control:focus { border-color: #0284c7; background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(14,165,233,.12); }
        .target-card:has(input:checked) { border-color: #0284c7; background: #f0f9ff; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
        .target-card:has(input:checked) .target-icon { background: #0369a1; color: #fff; }
    </style>
</head>
<body class="text-slate-800">
@include('admin.partials.navigation')

@php
    $selectedTarget = old('target_type', $notice->target_type);
    $currentCompanyCode = old('company_code', optional($notice->company)->company_code);
@endphp

<main class="mx-auto max-w-5xl px-4 py-6 md:px-6 md:py-10">
    <header class="mb-6 overflow-hidden rounded-3xl border border-slate-700 bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 px-6 py-7 text-white shadow-xl md:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-amber-300"><i data-lucide="file-pen-line" class="h-4 w-4"></i>EDIT COMPANY NOTICE</div>
                <h1 class="mt-3 text-2xl font-black md:text-3xl">企業向けお知らせを編集</h1>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-300">現在の内容を確認し、変更した項目を保存してください。</p>
            </div>
            <a href="{{ route('admin.company-dashboard-notices.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/20"><i data-lucide="arrow-left" class="h-4 w-4"></i>お知らせ一覧へ戻る</a>
        </div>
    </header>

    @if($errors->any())
        <div role="alert" class="mb-6 rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-4 text-red-900 shadow-sm">
            <div class="flex items-center gap-2 font-black"><i data-lucide="circle-alert" class="h-5 w-5"></i>入力内容を確認してください</div>
            <ul class="mt-2 list-disc space-y-1 pl-6 text-sm font-medium">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.company-dashboard-notices.update', $notice) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="form-panel">
            <div class="border-b-2 border-sky-200 bg-sky-50 px-5 py-4 md:px-6">
                <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-700 text-white"><i data-lucide="file-text" class="h-5 w-5"></i></span><div><h2 class="font-black text-sky-950">1. お知らせ内容</h2><p class="mt-0.5 text-xs font-medium text-sky-700">企業ダッシュボードに表示する題名と詳細です。</p></div></div>
            </div>
            <div class="space-y-6 p-5 md:p-6">
                <div>
                    <label for="title" class="mb-2 block text-sm font-black text-slate-800">題名 <span class="text-red-600">必須</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title', $notice->title) }}" class="form-control" maxlength="255" required placeholder="例：システムメンテナンスのお知らせ">
                    <p class="mt-2 text-xs font-medium text-slate-500">企業ダッシュボードでは題名がコンパクトに表示されます。</p>
                </div>
                <div><label for="body" class="mb-2 block text-sm font-black text-slate-800">詳細</label><textarea id="body" name="body" rows="8" class="form-control" placeholder="お知らせの詳しい内容を入力してください。">{{ old('body', $notice->body) }}</textarea></div>
                <div>
                    <label for="image" class="mb-2 block text-sm font-black text-slate-800">画像</label>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[220px_1fr]">
                        <div class="flex min-h-40 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50">
                            @if($notice->image)
                                <img id="imagePreview" src="{{ asset($notice->image) }}" alt="現在のお知らせ画像" class="h-48 w-full object-contain">
                                <div id="imagePlaceholder" class="hidden px-4 text-center text-sm font-bold text-slate-400"><i data-lucide="image" class="mx-auto mb-2 h-8 w-8"></i>画像プレビュー</div>
                            @else
                                <img id="imagePreview" src="" alt="選択したお知らせ画像" class="hidden h-48 w-full object-contain">
                                <div id="imagePlaceholder" class="px-4 text-center text-sm font-bold text-slate-400"><i data-lucide="image" class="mx-auto mb-2 h-8 w-8"></i>画像は未登録です</div>
                            @endif
                        </div>
                        <div class="self-center"><input id="image" type="file" name="image" accept="image/*" class="form-control bg-white"><p class="mt-2 text-xs font-medium leading-5 text-slate-500">新しい画像を選択すると現在の画像を差し替えます。変更しない場合は選択不要です。画像は2MB以内です。</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="border-b-2 border-violet-200 bg-violet-50 px-5 py-4 md:px-6">
                <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-700 text-white"><i data-lucide="calendar-clock" class="h-5 w-5"></i></span><div><h2 class="font-black text-violet-950">2. 表示期間・状態</h2><p class="mt-0.5 text-xs font-medium text-violet-700">掲載期間とダッシュボードでの表示方法を設定します。</p></div></div>
            </div>
            <div class="space-y-6 p-5 md:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div><label for="start_date" class="mb-2 block text-sm font-black">表示開始日</label><input id="start_date" type="date" name="start_date" value="{{ old('start_date', optional($notice->start_date)->format('Y-m-d')) }}" class="form-control"></div>
                    <div><label for="end_date" class="mb-2 block text-sm font-black">表示終了日</label><input id="end_date" type="date" name="end_date" value="{{ old('end_date', optional($notice->end_date)->format('Y-m-d')) }}" class="form-control"></div>
                </div>
                <p class="-mt-3 flex items-center gap-1.5 text-xs font-medium text-slate-500"><i data-lucide="info" class="h-4 w-4"></i>開始日・終了日は当日を含みます。未指定の場合は期間を限定しません。</p>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    @foreach([
                        ['is_new', 'NEWを付ける', '新着として強調', 'sparkles', old('is_new', $notice->is_new)],
                        ['is_important', '重要を付ける', '重要情報として強調', 'circle-alert', old('is_important', $notice->is_important)],
                        ['is_active', '表示する', '公開対象として有効にする', 'eye', old('is_active', $notice->is_active)],
                    ] as [$name, $label, $description, $icon, $checked])
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 bg-slate-50 p-4 transition hover:border-sky-300 hover:bg-white">
                            <input type="checkbox" name="{{ $name }}" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-700" {{ $checked ? 'checked' : '' }}>
                            <span><span class="flex items-center gap-1.5 text-sm font-black text-slate-900"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i>{{ $label }}</span><span class="mt-1 block text-xs font-medium leading-5 text-slate-500">{{ $description }}</span></span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="border-b-2 border-amber-200 bg-amber-50 px-5 py-4 md:px-6">
                <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-600 text-white"><i data-lucide="send" class="h-5 w-5"></i></span><div><h2 class="font-black text-amber-950">3. 公開対象</h2><p class="mt-0.5 text-xs font-medium text-amber-800">変更による誤配信を防ぐため、保存前に対象を確認してください。</p></div></div>
            </div>
            <div class="space-y-5 p-5 md:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label class="target-card cursor-pointer rounded-2xl border-2 border-slate-200 bg-white p-5 transition hover:border-sky-300"><span class="flex items-start gap-3"><input type="radio" name="target_type" value="all" class="mt-1 h-5 w-5 text-sky-700" onchange="toggleCompanySelect()" {{ $selectedTarget === 'all' ? 'checked' : '' }}><span class="target-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i data-lucide="building-2" class="h-5 w-5"></i></span><span><span class="block font-black text-slate-950">全企業向け</span><span class="mt-1 block text-xs font-medium leading-5 text-slate-500">すべての企業ダッシュボードに表示します。</span></span></span></label>
                    <label class="target-card cursor-pointer rounded-2xl border-2 border-slate-200 bg-white p-5 transition hover:border-sky-300"><span class="flex items-start gap-3"><input type="radio" name="target_type" value="company" class="mt-1 h-5 w-5 text-sky-700" onchange="toggleCompanySelect(true)" {{ $selectedTarget === 'company' ? 'checked' : '' }}><span class="target-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i data-lucide="building" class="h-5 w-5"></i></span><span><span class="block font-black text-slate-950">特定企業向け</span><span class="mt-1 block text-xs font-medium leading-5 text-slate-500">入力した企業コードの企業だけに表示します。</span></span></span></label>
                </div>
                <div id="companySelectWrap" class="rounded-2xl border-2 border-blue-300 bg-blue-50 p-5" style="{{ $selectedTarget === 'company' ? '' : 'display:none;' }}">
                    <label for="company_code" class="mb-2 block text-sm font-black text-blue-950">対象企業コード <span class="text-red-600">必須</span></label>
                    <input type="text" name="company_code" id="company_code" list="companyCodeOptions" value="{{ $currentCompanyCode }}" maxlength="8" autocomplete="off" class="form-control border-blue-300 bg-white font-mono uppercase" placeholder="例：ABCD1234">
                    <datalist id="companyCodeOptions">@foreach($companies as $company)<option value="{{ $company->company_code }}">{{ $company->name }}</option>@endforeach</datalist>
                    <p class="mt-2 text-xs font-bold text-blue-800">候補には企業名も表示されます。企業コードを保存前に確認してください。</p>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 rounded-2xl border-2 border-slate-300 bg-white p-4 shadow-lg sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('admin.company-dashboard-notices.index') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-slate-300 px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">キャンセル</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-600 px-7 py-3.5 text-base font-black text-white shadow-lg transition hover:bg-amber-700 focus:outline-none focus:ring-4 focus:ring-amber-200"><i data-lucide="save" class="h-5 w-5"></i>変更内容を保存する</button>
        </div>
    </form>
</main>

@include('admin.partials.mobile_nav')
<script>
function toggleCompanySelect(focusInput = false) {
    const selectedTarget = document.querySelector('input[name="target_type"]:checked')?.value;
    const isCompany = selectedTarget === 'company';
    const wrap = document.getElementById('companySelectWrap');
    const input = document.getElementById('company_code');
    if (wrap) wrap.style.display = isCompany ? '' : 'none';
    if (input) { input.required = isCompany; input.disabled = !isCompany; if (isCompany && focusInput) input.focus(); }
}

document.addEventListener('DOMContentLoaded', () => {
    toggleCompanySelect();
    if (window.lucide) window.lucide.createIcons();
    document.getElementById('image')?.addEventListener('change', event => {
        const file = event.target.files?.[0];
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        if (!file || !preview) return;
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        placeholder?.classList.add('hidden');
    });
});
</script>
</body>
</html>
