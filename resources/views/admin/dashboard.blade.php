<!DOCTYPE html>
<html>
<head>
    <title>管理者ダッシュボード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebarBackdrop').classList.toggle('hidden');
        }

        function closeMenu() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebarBackdrop').classList.add('hidden');
        }
    </script>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">

    <button id="sidebarBackdrop"
            type="button"
            onclick="closeMenu()"
            class="fixed inset-0 bg-black/40 z-40 hidden md:hidden"
            aria-label="メニューを閉じる"></button>

    <!-- スマホ用オーバーレイ -->
    <div id="sidebar"
         class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white p-6 transform -translate-x-full md:translate-x-0 transition duration-200 ease-in-out z-50">

        <h1 class="text-xl font-bold mb-8">管理者</h1>

        <button type="button" onclick="closeMenu()" class="mb-4 md:hidden rounded-lg border border-gray-600 px-3 py-2 text-sm">
            閉じる
        </button>

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
                <a href="{{ route('admin.inquiries.index') }}" class="block hover:text-gray-300">
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
                    {{ $errors->first() }}
                </div>
            @endif

            <h2 class="text-2xl md:text-3xl font-bold mb-6 md:mb-8">
                ダッシュボード
            </h2>

            <!-- サマリーカード -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 mb-8 md:mb-10">

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">登録企業数</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ $companyCount ?? 0 }}
                    </div>
                </div>

                <a href="{{ route('admin.applications', ['status' => 'pending']) }}"
                   class="block p-6 rounded-xl shadow transition hover:-translate-y-0.5 hover:shadow-md {{ ($pendingCount ?? 0) > 0 ? 'border border-red-200' : 'bg-white' }}"
                   style="{{ ($pendingCount ?? 0) > 0 ? 'background:#fef2f2; box-shadow:0 10px 26px rgba(220,38,38,0.12);' : '' }}">
                    <div class="{{ ($pendingCount ?? 0) > 0 ? 'text-red-700' : 'text-gray-500' }} text-sm font-semibold">申請待ち</div>
                    <div class="text-3xl font-bold mt-2 {{ ($pendingCount ?? 0) > 0 ? 'text-red-700' : 'text-gray-900' }}">
                        {{ $pendingCount ?? 0 }}
                    </div>
                    <div class="text-xs font-semibold mt-3 {{ ($pendingCount ?? 0) > 0 ? 'text-red-600' : 'text-blue-600' }}">
                        申請管理へ
                    </div>
                </a>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">利用停止中</div>
                    <div class="text-3xl font-bold mt-2">
                        {{ $inactiveCount ?? 0 }}
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border border-amber-100">
                    <div class="text-gray-500 text-sm">FAQ・お問い合わせ</div>
                    <div class="text-3xl font-bold mt-2 text-amber-600">
                        {{ $openInquiryCount ?? 0 }}
                    </div>
                    <div class="text-sm font-semibold mt-3 text-amber-600">
                        回答待ち
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">お知らせ管理</div>
                    <div class="text-sm font-semibold mt-3 text-blue-600">
                        企業向け通知を登録
                    </div>
                </div>

            </div>

            <!-- キャンペーン請求開始日 -->
            @if($billingStartCampaignEnabled ?? true)
            <div class="bg-white p-6 rounded-xl shadow mb-8 border border-blue-100">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
                    <div class="xl:w-1/2">
                        <h3 class="text-lg md:text-xl font-bold">キャンペーン請求開始日</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-6">
                            キャンペーン対象企業の請求開始日を設定します。設定日までは契約管理の制限対象から外れます。
                        </p>

                        <form method="POST"
                              action="{{ route('admin.company.billing-start-campaign') }}"
                              class="mt-5 grid grid-cols-1 lg:grid-cols-[1fr_180px_auto] gap-3"
                              onsubmit="return confirm('選択した企業の請求開始日を設定しますか？');">
                            @csrf
                            <select name="company_id" class="border border-gray-300 rounded-lg p-3 bg-white">
                                <option value="">企業を選択</option>
                                @foreach($companyOptions as $company)
                                    <option value="{{ $company->id }}" @selected(old('company_id') == $company->id)>
                                        {{ $company->name }}（{{ $company->company_code }}）
                                    </option>
                                @endforeach
                            </select>

                            <input type="date"
                                   name="billing_starts_at"
                                   value="{{ old('billing_starts_at') }}"
                                   class="border border-gray-300 rounded-lg p-3">

                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold">
                                設定する
                            </button>
                        </form>
                    </div>

                    <div class="xl:w-1/2 rounded-xl bg-blue-50 border border-blue-100 p-5">
                        <div class="text-sm font-bold text-blue-800 mb-3">設定中の企業</div>
                        <div class="space-y-3">
                            @forelse($billingCampaignCompanies as $company)
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg bg-white border border-blue-100 px-4 py-3">
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $company->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $company->company_code }}</div>
                                    </div>
                                    <div class="text-sm font-bold text-blue-700">
                                        {{ optional($company->billing_starts_at)->format('Y/m/d') }} 開始
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-blue-700">現在、請求開始日が設定されている企業はありません。</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- 企業管理ミニダッシュボード -->
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
                <div class="xl:col-span-1 bg-white p-6 rounded-xl shadow border border-amber-100">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold">注意が必要な企業</h3>
                            <p class="text-sm text-gray-500 mt-1">停止中、初期設定未完了、請求確認が必要な企業です。</p>
                        </div>
                        <a href="{{ route('admin.company.index', ['status' => 'billing_attention']) }}"
                           class="text-sm font-semibold text-amber-700 hover:underline whitespace-nowrap">
                            一覧
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-1 gap-3 mb-5">
                        <a href="{{ route('admin.company.index', ['status' => 'inactive']) }}"
                           class="block rounded-xl border border-red-100 bg-red-50 p-4">
                            <div class="text-xs font-bold text-red-700">利用停止中</div>
                            <div class="mt-1 text-2xl font-black text-red-700">{{ $inactiveCount ?? 0 }}</div>
                        </a>
                        <a href="{{ route('admin.company.index', ['status' => 'uninitialized']) }}"
                           class="block rounded-xl border border-sky-100 bg-sky-50 p-4">
                            <div class="text-xs font-bold text-sky-700">初期設定未完了</div>
                            <div class="mt-1 text-2xl font-black text-sky-700">{{ $uninitializedCount ?? 0 }}</div>
                        </a>
                        <a href="{{ route('admin.company.index', ['status' => 'billing_attention']) }}"
                           class="block rounded-xl border border-amber-100 bg-amber-50 p-4">
                            <div class="text-xs font-bold text-amber-700">請求確認</div>
                            <div class="mt-1 text-2xl font-black text-amber-700">{{ $billingAttentionCount ?? 0 }}</div>
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($attentionCompanies as $company)
                            <div class="rounded-xl border border-gray-200 p-4">
                                <div class="font-bold text-gray-900">{{ $company->name }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $company->company_code }} / {{ $company->subscription_status_label }}</div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if(!$company->is_active)
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">停止中</span>
                                    @endif
                                    @if(!$company->is_initialized)
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700">初期設定未完了</span>
                                    @endif
                                    @if(!$company->is_billing_active)
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">請求確認</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-400 py-6 text-center">注意が必要な企業はありません</div>
                        @endforelse
                    </div>
                </div>

                <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold">企業一覧</h3>
                            <p class="text-sm text-gray-500 mt-1">直近登録された企業を確認し、利用停止/再開できます。</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('admin.company.index') }}"
                               class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-center font-semibold text-sm">
                                すべて見る
                            </a>
                            <a href="{{ route('admin.company.create') }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2.5 rounded-lg text-center font-semibold text-sm">
                                ＋ 企業登録
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border border-gray-200">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="p-3 text-left border">企業</th>
                                    <th class="p-3 text-center border">状態</th>
                                    <th class="p-3 text-center border">契約</th>
                                    <th class="p-3 text-center border">利用数</th>
                                    <th class="p-3 text-center border">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestCompanies as $company)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 border">
                                            <div class="font-bold text-gray-900">{{ $company->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $company->company_code }}</div>
                                        </td>
                                        <td class="p-3 border text-center">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $company->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $company->is_active ? '利用中' : '停止中' }}
                                            </span>
                                        </td>
                                        <td class="p-3 border text-center text-xs text-gray-600">
                                            {{ $company->subscription_status_label }}
                                        </td>
                                        <td class="p-3 border text-center text-xs text-gray-600">
                                            スタッフ {{ $company->staff_count }} / 予約 {{ $company->reservations_count }} / 顧客 {{ $company->customers_count }}
                                        </td>
                                        <td class="p-3 border">
                                            <div class="flex flex-col gap-2 min-w-[100px]">
                                                <a href="{{ route('admin.company.edit', $company->id) }}"
                                                   class="rounded-lg bg-gray-700 px-3 py-2 text-center text-xs font-bold text-white hover:bg-gray-800">
                                                    編集
                                                </a>
                                                <form method="POST"
                                                      action="{{ route('admin.company.toggle', $company->id) }}"
                                                      onsubmit="return confirm(@js($company->name . 'を' . ($company->is_active ? '利用停止' : '再開') . 'しますか？'));">
                                                    @csrf
                                                    <button class="w-full rounded-lg px-3 py-2 text-xs font-bold text-white {{ $company->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                                                        {{ $company->is_active ? '停止' : '再開' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-400">企業データがありません</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- クイックアクション -->
            <div class="bg-white p-6 rounded-xl shadow mb-8">

                <h3 class="text-lg md:text-xl font-bold mb-4">
                    クイック操作
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">

                    <a href="{{ route('admin.company.create') }}"
                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        ＋ 企業登録
                    </a>

                    <a href="{{ route('admin.company.index') }}"
                       class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        企業一覧を見る
                    </a>

                    <a href="{{ route('admin.applications') }}"
                       class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        申請確認
                    </a>

                    <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                       class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        FAQ確認
                    </a>

                    <a href="{{ route('admin.company-dashboard-notices.index') }}"
                       class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        お知らせ一覧
                    </a>

                    <a href="{{ route('admin.company-dashboard-notices.create') }}"
                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        お知らせ登録
                    </a>

                </div>

            </div>

            <!-- FAQ / お問い合わせ管理案内 -->
            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg md:text-xl font-bold mb-4">
                            FAQ・お問い合わせ管理
                        </h3>

                        <p class="text-gray-600 text-sm md:text-base mb-5 leading-7">
                            企業から届いたお問い合わせを確認し、回答できます。<br>
                            回答後は企業側のFAQ・お問い合わせ画面およびダッシュボードの回答欄で確認できるようになります。
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                               class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                                未回答のFAQを見る
                            </a>

                            <a href="{{ route('admin.inquiries.index') }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg text-center font-semibold">
                                FAQ一覧を開く
                            </a>
                        </div>
                    </div>

                    <div class="w-full lg:w-80 bg-amber-50 border border-amber-200 rounded-xl p-5">
                        <div class="text-sm text-amber-700 font-semibold">現在の状況</div>
                        <div class="mt-3 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">回答待ち</span>
                                <span class="font-bold text-amber-700">{{ $openInquiryCount ?? 0 }}件</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">回答済み</span>
                                <span class="font-bold text-emerald-700">{{ $answeredInquiryCount ?? 0 }}件</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 新着FAQ -->
            <div class="bg-white p-6 rounded-xl shadow mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold">
                            新着FAQ・お問い合わせ
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            直近で届いた未回答のお問い合わせです
                        </p>
                    </div>

                    <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                       class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg text-center font-semibold text-sm">
                        すべて確認する
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($latestOpenInquiries as $inquiry)
                        <div class="border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                            未回答
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ optional($inquiry->created_at)->format('Y/m/d H:i') }}
                                        </span>
                                    </div>

                                    <div class="font-bold text-gray-900">
                                        {{ $inquiry->subject }}
                                    </div>

                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ $inquiry->company->name ?? '企業名不明' }}
                                        @if(!empty($inquiry->category))
                                            ／ {{ $inquiry->category }}
                                        @endif
                                    </div>

                                    <div class="text-sm text-gray-600 mt-3 leading-6">
                                        {{ \Illuminate\Support\Str::limit($inquiry->body, 140) }}
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                                       class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-lg text-center font-semibold text-sm whitespace-nowrap">
                                        回答する
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 py-8 text-center">
                            現在、未回答のFAQ・お問い合わせはありません
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- お知らせ管理案内 -->
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg md:text-xl font-bold mb-4">
                    企業ダッシュボードお知らせ
                </h3>

                <p class="text-gray-600 text-sm md:text-base mb-5 leading-7">
                    企業のダッシュボードに表示するお知らせを管理できます。<br>
                    表示期間、NEW、重要、題名、詳細、画像、全企業向け / 特定企業向けの設定が可能です。
                </p>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('admin.company-dashboard-notices.create') }}"
                       class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        新しくお知らせを登録する
                    </a>

                    <a href="{{ route('admin.company-dashboard-notices.index') }}"
                       class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg text-center font-semibold">
                        お知らせ一覧を開く
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@include('admin.partials.mobile_nav')
</body>
</html>
