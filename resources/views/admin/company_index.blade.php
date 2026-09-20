<!DOCTYPE html>
<html lang="ja">
<head>
    <title>企業一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background: radial-gradient(circle at top left, rgba(14,165,233,.1), transparent 30rem), linear-gradient(180deg, #f8fafc, #eef2f7 55%, #f8fafc); }
        .admin-panel { border: 2px solid #cbd5e1; border-radius: 1.5rem; background: #fff; box-shadow: 0 12px 28px rgba(15,23,42,.07); }
        .company-table-row { border-left: 5px solid #10b981; }
        .company-table-row-inactive { border-left-color: #e11d48; background: #fff7f8; }
        .company-mobile-card { border-left: 6px solid #10b981; }
        .company-mobile-card-inactive { border-left-color: #e11d48; }
    </style>
</head>

<body class="text-slate-800">
@include('admin.partials.navigation')

<div class="mx-3 mt-3 max-w-7xl sm:mx-auto md:mt-8">

    <header class="mb-6 overflow-hidden rounded-3xl border border-slate-700 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 px-6 py-7 text-white shadow-xl md:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div><div class="flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-sky-300"><i data-lucide="building-2" class="h-4 w-4"></i>COMPANY MANAGEMENT</div><h1 class="mt-3 text-2xl font-black md:text-3xl">企業一覧</h1><p class="mt-2 text-sm font-medium text-slate-300">企業情報、利用状態、請求状況を確認・管理します。</p></div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.company.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500 px-5 py-3 text-sm font-black text-white shadow hover:bg-sky-400"><i data-lucide="building-2" class="h-4 w-4"></i>新規登録</a>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-black text-white hover:bg-white/20"><i data-lucide="arrow-left" class="h-4 w-4"></i>ダッシュボード</a>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- サマリー --}}
    <section aria-label="企業状態サマリー" class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('admin.company.index') }}" class="rounded-2xl border-2 border-slate-300 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ request('status') ? '' : 'ring-4 ring-sky-100' }}">
            <div class="flex items-center justify-between text-xs font-black text-slate-600"><span>全企業</span><i data-lucide="buildings" class="h-4 w-4"></i></div>
            <div class="mt-1 text-3xl font-black text-slate-950">{{ number_format($summary['total'] ?? 0) }}<span class="ml-1 text-xs">社</span></div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'active']) }}" class="rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'active' ? 'ring-4 ring-emerald-100' : '' }}">
            <div class="flex items-center justify-between text-xs font-black text-emerald-700"><span>利用中</span><i data-lucide="circle-check" class="h-4 w-4"></i></div>
            <div class="mt-1 text-3xl font-black text-emerald-800">{{ number_format($summary['active'] ?? 0) }}<span class="ml-1 text-xs">社</span></div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'inactive']) }}" class="rounded-2xl border-2 border-rose-300 bg-rose-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'inactive' ? 'ring-4 ring-rose-100' : '' }}">
            <div class="flex items-center justify-between text-xs font-black text-rose-700"><span>停止中</span><i data-lucide="circle-pause" class="h-4 w-4"></i></div>
            <div class="mt-1 text-3xl font-black text-rose-800">{{ number_format($summary['inactive'] ?? 0) }}<span class="ml-1 text-xs">社</span></div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'uninitialized']) }}" class="rounded-2xl border-2 border-sky-300 bg-sky-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'uninitialized' ? 'ring-4 ring-sky-100' : '' }}">
            <div class="flex items-center justify-between text-xs font-black text-sky-700"><span>初期設定未完了</span><i data-lucide="settings" class="h-4 w-4"></i></div>
            <div class="mt-1 text-3xl font-black text-sky-800">{{ number_format($summary['uninitialized'] ?? 0) }}<span class="ml-1 text-xs">社</span></div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'billing_attention']) }}" class="rounded-2xl border-2 border-amber-300 bg-amber-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'billing_attention' ? 'ring-4 ring-amber-100' : '' }}">
            <div class="flex items-center justify-between text-xs font-black text-amber-800"><span>請求確認</span><i data-lucide="credit-card" class="h-4 w-4"></i></div>
            <div class="mt-1 text-3xl font-black text-amber-900">{{ number_format($summary['billing_attention'] ?? 0) }}<span class="ml-1 text-xs">社</span></div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'billing_campaign']) }}" class="rounded-2xl border-2 border-blue-300 bg-blue-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ request('status') === 'billing_campaign' ? 'ring-4 ring-blue-100' : '' }}">
            <div class="flex items-center justify-between text-xs font-black text-blue-700"><span>請求開始前</span><i data-lucide="calendar-clock" class="h-4 w-4"></i></div>
            <div class="mt-1 text-3xl font-black text-blue-800">{{ number_format($summary['billing_campaign'] ?? 0) }}<span class="ml-1 text-xs">社</span></div>
        </a>
    </section>

    {{-- 検索・状態フィルタ --}}
    <section class="admin-panel mb-6 p-5">
    <div class="mb-4"><h2 class="flex items-center gap-2 font-black text-slate-950"><i data-lucide="search" class="h-4 w-4 text-sky-700"></i>企業を検索・絞り込み</h2><p class="mt-1 text-xs font-medium text-slate-500">企業情報、状態、業種を組み合わせて検索できます。</p></div>
    <form method="GET" action="{{ route('admin.company.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto_auto]">
        <input type="text"
               aria-label="企業検索" name="keyword"
               value="{{ request('keyword') }}"
               placeholder="企業名・企業コード・業種・メールアドレスで検索"
               class="w-full rounded-xl border-2 border-slate-300 bg-slate-50 p-3 focus:border-sky-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">

        <select aria-label="企業の状態" name="status" class="rounded-xl border-2 border-slate-300 bg-slate-50 p-3 focus:border-sky-600 focus:bg-white focus:outline-none">
            <option value="">すべての状態</option>
            <option value="active" @selected(request('status') === 'active')>利用中</option>
            <option value="inactive" @selected(request('status') === 'inactive')>停止中</option>
            <option value="uninitialized" @selected(request('status') === 'uninitialized')>初期設定未完了</option>
            <option value="billing_attention" @selected(request('status') === 'billing_attention')>請求確認</option>
            <option value="billing_campaign" @selected(request('status') === 'billing_campaign')>請求開始前</option>
            <option value="line_enabled" @selected(request('status') === 'line_enabled')>LINE有効</option>
        </select>

        <select name="industry_type" aria-label="業種" class="rounded-xl border-2 border-slate-300 bg-slate-50 p-3 focus:border-sky-600 focus:bg-white focus:outline-none">
            <option value="">すべての業種</option>
            @foreach(config('industries.options') + config('industries.legacy') as $value => $label)
                <option value="{{ $value }}" @selected(request('industry_type') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-700 px-6 py-3 font-black text-white shadow transition hover:bg-sky-800">
            <i data-lucide="search" class="h-4 w-4"></i>検索
        </button>

        <a href="{{ route('admin.company.index') }}"
           class="inline-flex items-center justify-center border border-gray-300 bg-white text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition">
            リセット
        </a>
    </form>
    </section>

    <p class="text-sm text-gray-600 mb-4" role="status">検索結果 {{ number_format($companies->total()) }}件 @if($companies->total())（{{ $companies->firstItem() }}〜{{ $companies->lastItem() }}件を表示）@endif</p>
    @if(($billingAttentionCompanies ?? collect())->isNotEmpty())
        <details class="mb-5 rounded-2xl border border-amber-200 bg-amber-50/95 p-4 shadow-lg backdrop-blur">
            <summary class="cursor-pointer text-sm font-bold text-amber-800">請求確認が必要な企業（{{ $summary['billing_attention'] ?? 0 }}社）を表示</summary>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-3">
                <div>
                    <div class="text-sm font-black text-amber-800">請求確認が必要な企業</div>
                    <div class="text-xs text-amber-700 mt-1">停止前・未払い・請求開始済み未確認の企業を表示しています。</div>
                </div>
                <a href="{{ route('admin.company.index', ['status' => 'billing_attention']) }}"
                   class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700">
                    すべて見る
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-3">
                @foreach($billingAttentionCompanies as $attentionCompany)
                    <div class="rounded-xl border border-amber-200 bg-white p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500 font-mono">{{ $attentionCompany->company_code }}</div>
                                <div class="mt-1 font-bold text-gray-900 truncate">{{ $attentionCompany->name }}</div>
                            </div>
                            @if(!$attentionCompany->is_active)
                                <span class="shrink-0 rounded-full bg-red-100 px-2 py-1 text-[11px] font-bold text-red-700">停止</span>
                            @elseif(!$attentionCompany->is_billing_active)
                                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-700">確認</span>
                            @endif
                        </div>

                        <div class="mt-2 text-xs text-gray-600">
                            {{ $attentionCompany->subscription_status_label }}
                        </div>
                        @if($attentionCompany->billing_starts_at)
                            <div class="mt-1 text-xs text-gray-500">
                                請求開始 {{ $attentionCompany->billing_starts_at->format('Y/m/d') }}
                            </div>
                        @endif
                        <div class="mt-1 text-xs text-gray-500">
                            利用開始 {{ optional($attentionCompany->usage_started_at)->format('Y/m/d') ?? '-' }}
                            <span class="text-gray-400">（{{ $attentionCompany->usage_started_source_label }}）</span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.company.edit', ['id' => $attentionCompany->id, 'return_to' => request()->fullUrl()]) }}"
                               class="rounded-lg bg-gray-800 px-3 py-2 text-center text-xs font-bold text-white hover:bg-gray-900">
                                編集
                            </a>
                            <a href="{{ route('admin.company.index', ['keyword' => $attentionCompany->company_code]) }}"
                               class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-center text-xs font-bold text-gray-700 hover:bg-gray-50">
                                一覧
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
    @endif

    {{-- 上部操作 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <h2 class="flex items-center gap-2 text-lg font-black text-slate-950"><i data-lucide="list" class="h-5 w-5 text-sky-700"></i>企業一覧</h2>
        <div class="text-sm font-medium text-slate-500">
            複数選択して一括編集できます
        </div>
    </div>

    {{-- 一括編集フォーム --}}
    <form id="company-bulk-edit-form" method="POST" action="{{ route('admin.company.bulk-edit') }}">
        @csrf
    </form>

        <div class="mb-4 flex flex-wrap items-center justify-end gap-3 rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
            <span id="selection-count" role="status" class="text-sm font-bold text-slate-700">0社を選択中</span>
            <button type="submit" id="bulk-edit-button"
                    form="company-bulk-edit-form"
                    class="disabled:opacity-40 disabled:cursor-not-allowed w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 sm:py-2 rounded-lg transition">
                選択した企業を一括編集
            </button>
        </div>

        {{-- テーブル --}}
        <div class="md:hidden space-y-3">
            @forelse($companies as $company)
                <div class="company-mobile-card {{ $company->is_active ? '' : 'company-mobile-card-inactive' }} rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <input type="checkbox"
                               form="company-bulk-edit-form"
                               name="company_ids[]" aria-label="{{ $company->name }}（{{ $company->company_code }}）を選択"
                               value="{{ $company->id }}"
                               class="mt-1 h-5 w-5">

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500 font-mono">ID {{ $company->id }} / {{ $company->company_code }}</div>
                                    <div class="mt-1 font-bold text-gray-900 break-words">{{ $company->name }}</div>
                                    @if(!empty($company->email))
                                        <div class="mt-1 text-xs text-gray-500 break-all">{{ $company->email }}</div>
                                    @endif
                                </div>

                                @if($company->is_active)
                                    <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">利用中</span>
                                @else
                                    <span class="shrink-0 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">停止中</span>
                                @endif
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-600">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="font-bold text-gray-500">業種</div>
                                    <div class="mt-1">{{ $company->industry_label ?: '-' }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="font-bold text-gray-500">契約</div>
                                    <div class="mt-1">{{ $company->subscription_status_label }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="font-bold text-gray-500">利用開始</div>
                                    <div class="mt-1">{{ optional($company->usage_started_at)->format('Y/m/d') ?? '-' }}</div>
                                    <div class="mt-1 text-[11px] text-gray-400">{{ $company->usage_started_source_label }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="font-bold text-gray-500">利用数</div>
                                    <div class="mt-1">スタッフ {{ number_format($company->staff_count ?? 0) }}</div>
                                    <div>予約 {{ number_format($company->reservations_count ?? 0) }}</div>
                                    <div>顧客 {{ number_format($company->customers_count ?? 0) }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="font-bold text-gray-500">LINE</div>
                                    <div class="mt-1">{{ $company->line_login_enabled ? 'ON' : 'OFF' }}</div>
                                </div>
                            </div>

                            @if($company->billing_starts_at && $company->billing_starts_at->isFuture())
                                <div class="mt-3 inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">
                                    請求開始 {{ $company->billing_starts_at->format('Y/m/d') }}
                                </div>
                            @endif
                            @if($company->needs_billing_attention)
                                <div class="mt-3 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                                    請求確認
                                </div>
                            @endif

                            <div class="mt-4">
                                <input
                                    id="mobile_url_{{ $company->id }}"
                                    type="text"
                                    value="{{ url('/r/'.$company->company_code) }}"
                                    class="w-full rounded-lg border p-2 text-xs"
                                    readonly>
                                <div class="mt-2 grid grid-cols-3 gap-2">
                                    <button type="button"
                                            onclick="copyUrl('mobile_url_{{ $company->id }}')"
                                            class="rounded-lg bg-blue-500 px-3 py-2 text-xs font-semibold text-white">
                                        コピー
                                    </button>
                                    <a href="{{ url('/r/'.$company->company_code) }}"
                                       target="_blank"
                                       class="rounded-lg bg-gray-700 px-3 py-2 text-center text-xs font-semibold text-white">
                                        開く
                                    </a>
                                    <button type="button"
                                            onclick="showQR('{{ url('/r/'.$company->company_code) }}')"
                                            class="rounded-lg bg-green-500 px-3 py-2 text-xs font-semibold text-white">
                                        QR
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="{{ route('admin.company.edit', ['id' => $company->id, 'return_to' => request()->fullUrl()]) }}"
                                   class="rounded-lg bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white">
                                    編集
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.company.toggle', $company->id) }}"
                                      onsubmit="return confirm(@js($company->name . 'を' . ($company->is_active ? '利用停止' : '再開') . 'しますか？'));">
                                    @csrf
                                    <button class="w-full rounded-lg px-4 py-3 text-sm font-semibold text-white {{ $company->is_active ? 'bg-red-500' : 'bg-green-500' }}">
                                        {{ $company->is_active ? '停止' : '再開' }}
                                    </button>
                                </form>
                            </div>
                            <a href="{{ route('admin.company.impersonate', $company->id) }}"
                               class="mt-2 block w-full rounded-lg bg-gray-900 px-4 py-3 text-center text-sm font-semibold text-white"
                               onclick="return confirm(@js($company->name . ' の企業管理画面へマスター権限で代理ログインしますか？'));">
                                企業画面へ
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 p-6 text-center text-gray-500">
                    企業データがありません。
                </div>
            @endforelse
        </div>

        <div class="admin-panel hidden overflow-x-auto md:block">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="w-12 border-r border-slate-600 p-3 text-center">
                            <input type="checkbox" id="select-all-companies" aria-label="このページの企業をすべて選択" onclick="toggleAll(this)">
                        </th>
                        <th class="whitespace-nowrap p-3 text-left text-xs font-black">ID</th>
                        <th class="whitespace-nowrap p-3 text-left text-xs font-black">企業コード</th>
                        <th class="whitespace-nowrap p-3 text-left text-xs font-black">企業名</th>
                        <th class="whitespace-nowrap p-3 text-left text-xs font-black">業種</th>
                        <th class="whitespace-nowrap p-3 text-left text-xs font-black">予約URL</th>
                        <th class="whitespace-nowrap p-3 text-center text-xs font-black">状態</th>
                        <th class="whitespace-nowrap p-3 text-center text-xs font-black">契約</th>
                        <th class="whitespace-nowrap p-3 text-center text-xs font-black">利用開始</th>
                        <th class="whitespace-nowrap p-3 text-center text-xs font-black">利用数</th>
                        <th class="whitespace-nowrap p-3 text-center text-xs font-black">LINE</th>
                        <th class="whitespace-nowrap p-3 text-center text-xs font-black">操作</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($companies as $company)
                        <tr class="company-table-row {{ $company->is_active ? '' : 'company-table-row-inactive' }} border-b-2 border-slate-200 align-top transition hover:bg-sky-50/70">
                            <td class="border-r border-slate-200 p-3 text-center">
                                <input type="checkbox" form="company-bulk-edit-form" name="company_ids[]" aria-label="{{ $company->name }}（{{ $company->company_code }}）を選択" value="{{ $company->id }}">
                            </td>

                            <td class="p-3 font-semibold text-slate-600">{{ $company->id }}</td>

                            <td class="p-3 font-mono font-bold text-slate-700">
                                {{ $company->company_code }}
                            </td>

                            <td class="p-3">
                                <div class="font-black text-slate-950">{{ $company->name }}</div>

                                @if(!empty($company->email))
                                    <div class="text-xs text-gray-500 mt-1">{{ $company->email }}</div>
                                @endif
                            </td>

                            <td class="p-3">
                                {{ $company->industry_label }}
                            </td>

                            <td class="p-3">
                                <div class="space-y-2 min-w-[240px]">
                                    <input
                                        id="url_{{ $company->id }}"
                                        type="text"
                                        value="{{ url('/r/'.$company->company_code) }}"
                                        class="border p-2 rounded w-full text-xs"
                                        readonly>

                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            onclick="copyUrl('url_{{ $company->id }}')"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                            コピー
                                        </button>

                                        <a
                                            href="{{ url('/r/'.$company->company_code) }}"
                                            target="_blank"
                                            class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded text-xs">
                                            開く
                                        </a>

                                        <button
                                            type="button"
                                            onclick="showQR('{{ url('/r/'.$company->company_code) }}')"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
                                            QR
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <td class="p-3 text-center">
                                @if($company->is_active)
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                        利用中
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                        停止
                                    </span>
                                @endif
                                @if(!$company->is_initialized)
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700">
                                            初期未完了
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="p-3 text-center">
                                <div class="text-sm font-semibold text-gray-700">{{ $company->subscription_status_label }}</div>
                                @if($company->billing_starts_at && $company->billing_starts_at->isFuture())
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">
                                            {{ $company->billing_starts_at->format('Y/m/d') }} 開始
                                        </span>
                                    </div>
                                @endif
                                @if($company->needs_billing_attention)
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                                            請求確認
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="p-3 text-center text-sm">
                                <div class="font-semibold text-gray-800">
                                    {{ optional($company->usage_started_at)->format('Y/m/d') ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $company->usage_started_source_label }}
                                </div>
                            </td>

                            <td class="p-3 text-center text-xs text-gray-600">
                                <div>スタッフ {{ number_format($company->staff_count ?? 0) }}</div>
                                <div class="mt-1">予約 {{ number_format($company->reservations_count ?? 0) }}</div>
                                <div class="mt-1">顧客 {{ number_format($company->customers_count ?? 0) }}</div>
                            </td>

                            <td class="p-3 text-center">
                                @if($company->line_login_enabled)
                                    <span class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-semibold">
                                        ON
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-1 rounded bg-gray-100 text-gray-600 text-xs font-semibold">
                                        OFF
                                    </span>
                                @endif
                            </td>

                            <td class="p-3 text-center">
                                <div class="flex flex-col gap-2 min-w-[110px]">
                                    <a href="{{ route('admin.company.edit', ['id' => $company->id, 'return_to' => request()->fullUrl()]) }}"
                                       class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition text-sm">
                                        個別編集
                                    </a>

                                    <a href="{{ route('admin.company.impersonate', $company->id) }}"
                                       class="w-full px-4 py-2 rounded-lg bg-gray-900 hover:bg-black text-white transition text-sm"
                                       onclick="return confirm(@js($company->name . ' の企業管理画面へマスター権限で代理ログインしますか？'));">
                                        企業画面へ
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.company.toggle', $company->id) }}"
                                          onsubmit="return confirm(@js($company->name . 'を' . ($company->is_active ? '利用停止' : '再開') . 'しますか？'));">
                                        @csrf
                                        <button class="w-full px-4 py-2 rounded-lg text-white transition text-sm
                                            {{ $company->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                                            {{ $company->is_active ? '停止' : '再開' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="border p-6 text-center text-gray-500">
                                企業データがありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    <div class="mt-6">
        {{ $companies->links() }}
    </div>
</div>

<script>
function copyUrl(id) {
    const input = document.getElementById(id);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("予約URLをコピーしました");
}

const companyCheckboxes = [...document.querySelectorAll('input[name="company_ids[]"]')];
function updateCompanySelection() {
    const selected = new Set(companyCheckboxes.filter(el => el.checked).map(el => el.value));
    const total = new Set(companyCheckboxes.map(el => el.value)).size;
    document.getElementById('selection-count').textContent = `${selected.size}社を選択中`;
    document.getElementById('bulk-edit-button').disabled = selected.size === 0;
    const master = document.getElementById('select-all-companies');
    master.checked = total > 0 && selected.size === total;
    master.indeterminate = selected.size > 0 && selected.size < total;
}
companyCheckboxes.forEach(el => el.addEventListener('change', () => {
    companyCheckboxes.filter(other => other.value === el.value).forEach(other => other.checked = el.checked);
    updateCompanySelection();
}));
function toggleAll(master) {
    companyCheckboxes.forEach(el => el.checked = master.checked);
    updateCompanySelection();
}
document.getElementById('company-bulk-edit-form').addEventListener('formdata', event => {
    const selected = [...new Set(event.formData.getAll('company_ids[]'))];
    event.formData.delete('company_ids[]');
    selected.forEach(id => event.formData.append('company_ids[]', id));
});
window.addEventListener('pageshow', updateCompanySelection);
updateCompanySelection();

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();
});

/* QR表示 */
function showQR(url) {
    const modal = document.getElementById("qrModal");
    const img = document.getElementById("qrImage");

    img.src = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" + encodeURIComponent(url);
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function closeQR() {
    const modal = document.getElementById("qrModal");
    modal.classList.remove("flex");
    modal.classList.add("hidden");
}

function downloadQR() {
    const img = document.getElementById("qrImage").src;
    const link = document.createElement("a");

    link.href = img;
    link.download = "reservation_qr.png";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="font-bold mb-4">予約QRコード</h3>

        <img id="qrImage" class="mx-auto mb-4">

        <div class="flex justify-center gap-3">
            <button
                type="button"
                onclick="downloadQR()"
                class="bg-blue-500 text-white px-4 py-2 rounded">
                ダウンロード
            </button>

            <button
                type="button"
                onclick="closeQR()"
                class="bg-gray-700 text-white px-4 py-2 rounded">
                閉じる
            </button>
        </div>
    </div>
</div>

@include('admin.partials.mobile_nav')
</body>
</html>
