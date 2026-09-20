<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者ダッシュボード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, .12), transparent 32rem),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 52%, #f8fafc 100%);
        }
        .admin-panel {
            border: 2px solid #cbd5e1;
            border-radius: 1.5rem;
            background: rgba(255,255,255,.96);
            box-shadow: 0 12px 30px rgba(15,23,42,.08);
        }
        .admin-link-row { transition: .2s ease; }
        .admin-link-row:hover { transform: translateY(-1px); }
    </style>
</head>
<body class="text-slate-800">
@include('admin.partials.navigation')
<main class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 space-y-6">
    <section class="overflow-hidden rounded-3xl border border-slate-700 bg-slate-900 text-white shadow-2xl">
        <div class="border-b border-white/10 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 px-6 py-7 md:px-8 md:py-9">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold tracking-[0.18em] text-sky-300">
                        <i data-lucide="shield-check" class="h-4 w-4"></i>
                        ADMIN CONTROL CENTER
                    </div>
                    <h1 class="mt-3 text-2xl font-black md:text-4xl">管理者ダッシュボード</h1>
                    <p class="mt-3 text-sm font-medium leading-7 text-slate-300">
                        {{ now()->format('Y/m/d') }} 時点の状況です。対応が必要な項目を上から確認してください。
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('admin.company.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-sky-950/30 transition hover:bg-sky-400">
                        <i data-lucide="building-2" class="h-4 w-4"></i>
                        企業を登録
                    </a>
                    <a href="{{ route('admin.company-dashboard-notices.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/20">
                        <i data-lucide="megaphone" class="h-4 w-4"></i>
                        お知らせを作成
                    </a>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-px bg-white/10 lg:grid-cols-4">
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">登録企業</div><div class="mt-1 text-2xl font-black">{{ number_format($companyCount) }}<span class="ml-1 text-xs text-slate-400">社</span></div></div>
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">確認待ち申請</div><div class="mt-1 text-2xl font-black {{ $pendingCount > 0 ? 'text-violet-300' : 'text-emerald-300' }}">{{ number_format($pendingCount) }}<span class="ml-1 text-xs text-slate-400">件</span></div></div>
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">未回答</div><div class="mt-1 text-2xl font-black {{ $openInquiryCount > 0 ? 'text-rose-300' : 'text-emerald-300' }}">{{ number_format($openInquiryCount) }}<span class="ml-1 text-xs text-slate-400">件</span></div></div>
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">請求確認</div><div class="mt-1 text-2xl font-black {{ $billingAttentionCount > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ number_format($billingAttentionCount) }}<span class="ml-1 text-xs text-slate-400">件</span></div></div>
        </div>
    </section>
    @if(session('success'))
        <div role="status" class="flex items-center gap-3 rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4 font-bold text-emerald-900 shadow-sm"><i data-lucide="circle-check" class="h-5 w-5"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="flex items-center gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4 font-bold text-red-900 shadow-sm"><i data-lucide="circle-alert" class="h-5 w-5"></i>{{ $errors->first() }}</div>
    @endif

    <section aria-labelledby="admin-attention-title">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 id="admin-attention-title" class="flex items-center gap-2 text-xl font-black text-slate-950">
                    <i data-lucide="list-checks" class="h-5 w-5 text-sky-700"></i>
                    対応状況
                </h2>
                <p class="mt-1 text-sm font-medium text-slate-500">件数がある項目は、優先して確認してください。</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['利用申請・確認待ち', $pendingCount, route('admin.applications', ['status' => 'pending']), '申請を確認', 'file-check-2', 'violet'],
            ['お問い合わせ・未回答', $openInquiryCount, route('admin.inquiries.index', ['status' => 'open']), '回答する', 'message-circle-question', 'rose'],
            ['請求確認', $billingAttentionCount, route('admin.company.index', ['status' => 'billing_attention']), '対象企業を確認', 'credit-card', 'amber'],
            ['初期設定未完了', $uninitializedCount, route('admin.company.index', ['status' => 'uninitialized']), '設定状況を確認', 'settings', 'sky'],
        ] as [$label, $count, $url, $action, $icon, $tone])
            @php
                $toneClass = match($tone) {
                    'violet' => 'border-violet-300 bg-violet-50 text-violet-900',
                    'rose' => 'border-rose-300 bg-rose-50 text-rose-900',
                    'amber' => 'border-amber-300 bg-amber-50 text-amber-950',
                    default => 'border-sky-300 bg-sky-50 text-sky-950',
                };
                $iconClass = match($tone) {
                    'violet' => 'bg-violet-700',
                    'rose' => 'bg-rose-700',
                    'amber' => 'bg-amber-600',
                    default => 'bg-sky-700',
                };
            @endphp
            <a href="{{ $url }}" class="admin-link-row relative overflow-hidden rounded-3xl border-2 p-5 shadow-sm hover:shadow-lg {{ $count > 0 ? $toneClass : 'border-emerald-200 bg-emerald-50/70 text-emerald-950' }}">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl text-white shadow {{ $count > 0 ? $iconClass : 'bg-emerald-600' }}"><i data-lucide="{{ $count > 0 ? $icon : 'circle-check' }}" class="h-5 w-5"></i></span>
                    <span class="rounded-full border px-2.5 py-1 text-[11px] font-black {{ $count > 0 ? 'border-current bg-white/70' : 'border-emerald-300 bg-white text-emerald-700' }}">{{ $count > 0 ? '要対応' : '対応不要' }}</span>
                </div>
                <h3 class="mt-4 text-sm font-black">{{ $label }}</h3>
                <div class="mt-2 text-4xl font-black">{{ number_format($count) }}<span class="ml-1 text-sm">件</span></div>
                <p class="mt-4 flex items-center gap-1 text-sm font-black">{{ $action }}<i data-lucide="arrow-right" class="h-4 w-4"></i></p>
            </a>
        @endforeach
        </div>
    </section>

    <section class="admin-panel p-5 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h2 class="flex items-center gap-2 text-lg font-black text-slate-950"><i data-lucide="search" class="h-5 w-5 text-sky-700"></i>企業を探す</h2>
            <div class="flex gap-4 text-sm">
                <a class="font-bold text-sky-700 underline decoration-2 underline-offset-4" href="{{ route('admin.company.index') }}">登録 {{ number_format($companyCount) }}社</a>
                <a class="font-bold text-rose-700 underline decoration-2 underline-offset-4" href="{{ route('admin.company.index', ['status' => 'inactive']) }}">停止中 {{ number_format($inactiveCount) }}社</a>
            </div>
        </div>
        <form action="{{ route('admin.company.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row">
            <label for="dashboard-company-search" class="sr-only">企業検索</label>
            <input id="dashboard-company-search" type="search" name="keyword" placeholder="企業名・企業コード・業種・メールアドレス" class="min-w-0 flex-1 rounded-2xl border-2 border-slate-300 bg-slate-50 px-4 py-3.5 text-base focus:border-sky-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100">
            <button class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-7 py-3.5 text-sm font-black text-white shadow transition hover:bg-sky-800"><i data-lucide="search" class="h-4 w-4"></i>検索</button>
        </form>
    </section>
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="admin-panel overflow-hidden">
            <div class="border-b-2 border-violet-200 bg-violet-50 px-5 py-4 md:px-6">
            <div class="flex justify-between gap-3 items-center mb-4">
                <h2 class="flex items-center gap-2 text-lg font-black text-violet-950"><i data-lucide="file-check-2" class="h-5 w-5"></i>確認待ちの利用申請</h2>
                <a href="{{ route('admin.applications', ['status' => 'pending']) }}" class="whitespace-nowrap rounded-full border border-violet-300 bg-white px-3 py-1.5 text-xs font-black text-violet-800">すべて見る</a>
            </div>
            <p class="text-xs font-semibold text-violet-700">受付が古い順に最大5件を表示しています。</p>
            </div>
            <div class="space-y-3 p-4 md:p-5">
                @forelse($pendingApplications as $application)
                    <a href="{{ route('admin.applications', ['status' => 'pending', 'application_id' => $application->id]) }}" class="admin-link-row block rounded-2xl border-2 border-violet-200 bg-white p-4 hover:border-violet-400 hover:bg-violet-50">
                        <div class="flex flex-wrap justify-between gap-2"><span class="break-words font-black text-slate-950">{{ $application->company_name }}</span><span class="text-sm font-black text-violet-700">内容を確認 →</span></div>
                        <p class="mt-2 text-xs font-medium text-slate-500">受付 #{{ $application->id }} · {{ $application->industry_label }} · {{ $application->created_at->format('Y/m/d H:i') }}</p>
                    </a>
                @empty
                    <p class="rounded-2xl border border-emerald-200 bg-emerald-50 py-8 text-center text-sm font-bold text-emerald-700">確認待ちの申請はありません。</p>
                @endforelse
            </div>
        </section>
        <section class="admin-panel overflow-hidden">
            <div class="border-b-2 border-rose-200 bg-rose-50 px-5 py-4 md:px-6">
            <div class="flex justify-between gap-3 items-center mb-4">
                <h2 class="flex items-center gap-2 text-lg font-black text-rose-950"><i data-lucide="message-circle-question" class="h-5 w-5"></i>未回答のお問い合わせ</h2>
                <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}" class="whitespace-nowrap rounded-full border border-rose-300 bg-white px-3 py-1.5 text-xs font-black text-rose-800">すべて見る</a>
            </div>
            <p class="text-xs font-semibold text-rose-700">受付が古い順に最大5件を表示しています。</p>
            </div>
            <div class="space-y-3 p-4 md:p-5">
                @forelse($latestOpenInquiries as $inquiry)
                    <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="admin-link-row block rounded-2xl border-2 border-rose-200 bg-white p-4 hover:border-rose-400 hover:bg-rose-50">
                        <p class="break-words font-black text-slate-950">{{ $inquiry->subject }}</p>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $inquiry->company->name ?? '企業名不明' }} · {{ $inquiry->created_at->format('Y/m/d H:i') }}</p>
                        <p class="mt-2 text-sm font-black text-rose-700">内容を確認・回答 →</p>
                    </a>
                @empty
                    <p class="rounded-2xl border border-emerald-200 bg-emerald-50 py-8 text-center text-sm font-bold text-emerald-700">未回答のお問い合わせはありません。</p>
                @endforelse
            </div>
        </section>
    </div>
    <section class="admin-panel p-5 md:p-6">
        <h2 class="flex items-center gap-2 text-lg font-black text-slate-950"><i data-lucide="building-2" class="h-5 w-5 text-amber-600"></i>確認が必要な企業</h2>
        <p class="mb-4 mt-1 text-sm font-medium text-slate-500">停止中・初期設定未完了・請求確認の対象から、登録が新しい順に最大5社を表示。</p>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @forelse($attentionCompanies as $company)
                <a href="{{ route('admin.company.edit', ['id' => $company->id, 'return_to' => route('admin.dashboard')]) }}" class="admin-link-row rounded-2xl border-2 border-amber-200 bg-amber-50/60 p-4 hover:border-amber-400 hover:bg-amber-50">
                    <p class="break-words font-black text-slate-950">{{ $company->name }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-600">{{ $company->company_code }} · {{ $company->subscription_status_label }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        @if(!$company->is_active)<span class="rounded-full border border-red-300 bg-red-100 px-2.5 py-1 font-black text-red-800">停止中</span>@endif
                        @if(!$company->is_initialized)<span class="rounded-full border border-sky-300 bg-sky-100 px-2.5 py-1 font-black text-sky-800">初期設定未完了</span>@endif
                        @if($company->needs_billing_attention)<span class="rounded-full border border-amber-300 bg-amber-100 px-2.5 py-1 font-black text-amber-900">請求確認</span>@endif
                    </div>
                    <p class="mt-3 text-sm font-black text-amber-800">企業情報を確認 →</p>
                </a>
            @empty
                <p class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-6 text-center text-sm font-bold text-emerald-700">確認が必要な企業はありません。</p>
            @endforelse
        </div>
    </section>
    <section class="admin-panel p-5 md:p-6">
        <div class="flex justify-between items-center gap-3 mb-4">
            <h2 class="flex items-center gap-2 text-lg font-black text-slate-950"><i data-lucide="history" class="h-5 w-5 text-sky-700"></i>最近登録された企業</h2>
            <a class="rounded-full border border-sky-300 bg-sky-50 px-3 py-1.5 text-xs font-black text-sky-800" href="{{ route('admin.company.index') }}">企業一覧へ</a>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @forelse($latestCompanies as $company)
                <a href="{{ route('admin.company.edit', ['id' => $company->id, 'return_to' => route('admin.dashboard')]) }}" class="admin-link-row rounded-2xl border-2 border-slate-200 bg-white p-4 hover:border-sky-400 hover:bg-sky-50">
                    <div class="flex items-start justify-between gap-2">
                        <p class="break-words font-black text-slate-950">{{ $company->name }}</p>
                        <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-black {{ $company->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $company->is_active ? '利用中' : '停止中' }}</span>
                    </div>
                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $company->company_code }} · {{ $company->industry_label }}</p>
                    <p class="mt-3 text-xs font-bold text-slate-700">{{ $company->subscription_status_label }}</p>
                    <div class="mt-3 grid grid-cols-3 gap-1 rounded-xl bg-slate-50 p-2 text-center text-[11px] font-bold text-slate-600">
                        <span>スタッフ<br><b class="text-slate-950">{{ $company->staff_count }}</b></span>
                        <span class="border-x border-slate-200">予約<br><b class="text-slate-950">{{ $company->reservations_count }}</b></span>
                        <span>顧客<br><b class="text-slate-950">{{ $company->customers_count }}</b></span>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500">企業はまだ登録されていません。</p>
            @endforelse
        </div>
    </section>
    @if($billingStartCampaignEnabled)
        <details class="admin-panel p-4" @if($errors->has('company_id') || $errors->has('billing_starts_at')) open @endif>
            <summary class="cursor-pointer rounded-xl p-2 font-black text-slate-900 hover:bg-slate-50">キャンペーン請求開始日の設定 <span class="ml-2 text-sm font-medium text-slate-500">開いて設定・確認</span></summary>
            @include('admin.partials.billing_campaign')
        </details>
    @endif
</main>
@include('admin.partials.mobile_nav')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();
});
</script>
</body>
</html>
