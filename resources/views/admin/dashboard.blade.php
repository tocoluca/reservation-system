<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者ダッシュボード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
@include('admin.partials.navigation')
<main class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500 mb-1">{{ now()->format('Y/m/d') }} 時点の状況</p>
            <h1 class="text-2xl md:text-3xl font-bold">ダッシュボード</h1>
            <p class="text-sm text-slate-500 mt-2">対応が必要な項目から、各管理画面へ進めます。</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.company.create') }}" class="rounded-xl bg-sky-700 hover:bg-sky-800 text-white px-4 py-3 text-sm font-bold">＋ 企業登録</a>
            <a href="{{ route('admin.company-dashboard-notices.create') }}" class="rounded-xl border bg-white hover:bg-slate-100 px-4 py-3 text-sm font-bold">お知らせを作成</a>
        </div>
    </div>
    @if(session('success'))
        <div role="status" class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 p-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl bg-red-50 border border-red-200 text-red-800 p-4">{{ $errors->first() }}</div>
    @endif
    <section aria-label="対応状況" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        @foreach([
            ['利用申請・確認待ち', $pendingCount, route('admin.applications', ['status' => 'pending']), '申請を確認'],
            ['お問い合わせ・未回答', $openInquiryCount, route('admin.inquiries.index', ['status' => 'open']), '回答する'],
            ['請求確認', $billingAttentionCount, route('admin.company.index', ['status' => 'billing_attention']), '対象企業を確認'],
            ['初期設定未完了', $uninitializedCount, route('admin.company.index', ['status' => 'uninitialized']), '設定状況を確認'],
        ] as [$label, $count, $url, $action])
            <a href="{{ $url }}" class="rounded-2xl border p-4 md:p-5 transition hover:shadow-md {{ $count > 0 ? 'bg-white border-sky-200' : 'bg-white border-slate-200' }}">
                <h2 class="text-xs sm:text-sm font-semibold text-slate-600">{{ $label }}</h2>
                <div class="mt-3 text-3xl font-bold {{ $count > 0 ? 'text-sky-800' : 'text-slate-500' }}">{{ number_format($count) }}<span class="ml-1 text-sm font-normal">件</span></div>
                <p class="mt-3 text-xs sm:text-sm text-sky-700">{{ $action }} →</p>
            </a>
        @endforeach
    </section>
    <section class="bg-white rounded-2xl border p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h2 class="font-bold">企業を探す</h2>
            <div class="flex gap-4 text-sm">
                <a class="text-sky-700 underline" href="{{ route('admin.company.index') }}">登録 {{ number_format($companyCount) }}社</a>
                <a class="text-slate-600 underline" href="{{ route('admin.company.index', ['status' => 'inactive']) }}">停止中 {{ number_format($inactiveCount) }}社</a>
            </div>
        </div>
        <form action="{{ route('admin.company.index') }}" method="GET" class="flex gap-2">
            <label for="dashboard-company-search" class="sr-only">企業検索</label>
            <input id="dashboard-company-search" type="search" name="keyword" placeholder="企業名・企業コード・業種・メールアドレス" class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-3">
            <button class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 font-semibold text-sm">検索</button>
        </form>
    </section>
    <div class="grid lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-2xl border p-4 md:p-6">
            <div class="flex justify-between gap-3 items-center mb-4">
                <h2 class="font-bold text-lg">確認待ちの利用申請</h2>
                <a href="{{ route('admin.applications', ['status' => 'pending']) }}" class="text-sm text-sky-700 underline whitespace-nowrap">すべて見る</a>
            </div>
            <p class="text-xs text-slate-500 mb-4">受付が古い順に最大5件を表示しています。</p>
            <div class="divide-y">
                @forelse($pendingApplications as $application)
                    <a href="{{ route('admin.applications', ['status' => 'pending', 'application_id' => $application->id]) }}" class="block py-4 hover:bg-slate-50 rounded-lg">
                        <div class="flex flex-wrap justify-between gap-2"><span class="font-semibold break-words">{{ $application->company_name }}</span><span class="text-sm text-sky-700">内容を確認 →</span></div>
                        <p class="text-xs text-slate-500 mt-2">受付 #{{ $application->id }} · {{ $application->industry_label }} · {{ $application->created_at->format('Y/m/d H:i') }}</p>
                    </a>
                @empty
                    <p class="rounded-xl bg-slate-50 text-slate-500 text-sm text-center py-8">確認待ちの申請はありません。</p>
                @endforelse
            </div>
        </section>
        <section class="bg-white rounded-2xl border p-4 md:p-6">
            <div class="flex justify-between gap-3 items-center mb-4">
                <h2 class="font-bold text-lg">未回答のお問い合わせ</h2>
                <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}" class="text-sm text-sky-700 underline whitespace-nowrap">すべて見る</a>
            </div>
            <p class="text-xs text-slate-500 mb-4">受付が古い順に最大5件を表示しています。</p>
            <div class="divide-y">
                @forelse($latestOpenInquiries as $inquiry)
                    <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="block py-4 hover:bg-slate-50 rounded-lg">
                        <p class="font-semibold break-words">{{ $inquiry->subject }}</p>
                        <p class="text-xs text-slate-500 mt-2">{{ $inquiry->company->name ?? '企業名不明' }} · {{ $inquiry->created_at->format('Y/m/d H:i') }}</p>
                        <p class="text-sm text-sky-700 mt-2">内容を確認・回答 →</p>
                    </a>
                @empty
                    <p class="rounded-xl bg-slate-50 text-slate-500 text-sm text-center py-8">未回答のお問い合わせはありません。</p>
                @endforelse
            </div>
        </section>
    </div>
    <section class="bg-white rounded-2xl border p-4 md:p-6">
        <h2 class="text-lg font-bold mb-1">確認が必要な企業</h2>
        <p class="text-sm text-slate-500 mb-4">停止中・初期設定未完了・請求確認の対象から、登録が新しい順に最大5社を表示。</p>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @forelse($attentionCompanies as $company)
                <a href="{{ route('admin.company.edit', ['id' => $company->id, 'return_to' => route('admin.dashboard')]) }}" class="rounded-xl border p-4 hover:border-sky-400 hover:bg-sky-50">
                    <p class="font-bold break-words">{{ $company->name }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $company->company_code }} · {{ $company->subscription_status_label }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        @if(!$company->is_active)<span class="rounded-md bg-red-50 text-red-700 px-2 py-1">停止中</span>@endif
                        @if(!$company->is_initialized)<span class="rounded-md bg-sky-50 text-sky-700 px-2 py-1">初期設定未完了</span>@endif
                        @if($company->needs_billing_attention)<span class="rounded-md bg-amber-50 text-amber-800 px-2 py-1">請求確認</span>@endif
                    </div>
                    <p class="mt-3 text-sm text-sky-700">企業情報を確認 →</p>
                </a>
            @empty
                <p class="text-sm text-slate-500 py-4">確認が必要な企業はありません。</p>
            @endforelse
        </div>
    </section>
    <section class="bg-white rounded-2xl border p-4 md:p-6">
        <div class="flex justify-between items-center gap-3 mb-4">
            <h2 class="text-lg font-bold">最近登録された企業</h2>
            <a class="text-sky-700 text-sm underline" href="{{ route('admin.company.index') }}">企業一覧へ</a>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @forelse($latestCompanies as $company)
                <a href="{{ route('admin.company.edit', ['id' => $company->id, 'return_to' => route('admin.dashboard')]) }}" class="rounded-xl border p-4 hover:bg-slate-50">
                    <p class="font-semibold break-words">{{ $company->name }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $company->company_code }} · {{ $company->industry_label }}</p>
                    <p class="mt-3 text-xs">{{ $company->is_active ? '利用中' : '停止中' }} · {{ $company->subscription_status_label }}</p>
                    <p class="mt-2 text-xs text-slate-500">スタッフ {{ $company->staff_count }} / 予約 {{ $company->reservations_count }} / 顧客 {{ $company->customers_count }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">企業はまだ登録されていません。</p>
            @endforelse
        </div>
    </section>
    @if($billingStartCampaignEnabled)
        <details class="rounded-2xl border bg-white p-4" @if($errors->has('company_id') || $errors->has('billing_starts_at')) open @endif>
            <summary class="cursor-pointer font-bold p-2">キャンペーン請求開始日の設定 <span class="text-sm font-normal text-slate-500">開いて設定・確認</span></summary>
            @include('admin.partials.billing_campaign')
        </details>
    @endif
</main>
@include('admin.partials.mobile_nav')
</body>
</html>
