<!DOCTYPE html>
<html>
<head>
    <title>企業一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 md:mt-10 bg-white p-4 md:p-8 rounded-xl shadow">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-2
                  border border-gray-300
                  text-gray-700
                  px-4 py-2 rounded-lg
                  hover:bg-gray-100
                  transition text-sm md:text-base">
            <span class="text-lg">←</span> ダッシュボードへ戻る
        </a>

        <h2 class="text-xl md:text-2xl font-bold">
            企業一覧
        </h2>
    </div>

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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 mb-5">
        <a href="{{ route('admin.company.index') }}" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="text-xs font-bold text-gray-500">全企業</div>
            <div class="mt-1 text-2xl font-black text-gray-900">{{ number_format($summary['total'] ?? 0) }}</div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'active']) }}" class="rounded-xl border border-green-100 bg-green-50 p-4">
            <div class="text-xs font-bold text-green-700">利用中</div>
            <div class="mt-1 text-2xl font-black text-green-700">{{ number_format($summary['active'] ?? 0) }}</div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'inactive']) }}" class="rounded-xl border border-red-100 bg-red-50 p-4">
            <div class="text-xs font-bold text-red-700">停止中</div>
            <div class="mt-1 text-2xl font-black text-red-700">{{ number_format($summary['inactive'] ?? 0) }}</div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'uninitialized']) }}" class="rounded-xl border border-sky-100 bg-sky-50 p-4">
            <div class="text-xs font-bold text-sky-700">初期設定未完了</div>
            <div class="mt-1 text-2xl font-black text-sky-700">{{ number_format($summary['uninitialized'] ?? 0) }}</div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'billing_attention']) }}" class="rounded-xl border border-amber-100 bg-amber-50 p-4">
            <div class="text-xs font-bold text-amber-700">請求確認</div>
            <div class="mt-1 text-2xl font-black text-amber-700">{{ number_format($summary['billing_attention'] ?? 0) }}</div>
        </a>
        <a href="{{ route('admin.company.index', ['status' => 'billing_campaign']) }}" class="rounded-xl border border-blue-100 bg-blue-50 p-4">
            <div class="text-xs font-bold text-blue-700">請求開始前</div>
            <div class="mt-1 text-2xl font-black text-blue-700">{{ number_format($summary['billing_campaign'] ?? 0) }}</div>
        </a>
    </div>

    {{-- 検索・状態フィルタ --}}
    <form method="GET" action="{{ route('admin.company.index') }}" class="mb-6 grid grid-cols-1 lg:grid-cols-[1fr_220px_auto_auto] gap-3">
        <input type="text"
               name="keyword"
               value="{{ request('keyword') }}"
               placeholder="企業名・企業コード・業種・メールアドレスで検索"
               class="border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-400">

        <select name="status" class="border border-gray-300 p-3 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">すべての状態</option>
            <option value="active" @selected(request('status') === 'active')>利用中</option>
            <option value="inactive" @selected(request('status') === 'inactive')>停止中</option>
            <option value="uninitialized" @selected(request('status') === 'uninitialized')>初期設定未完了</option>
            <option value="billing_attention" @selected(request('status') === 'billing_attention')>請求確認</option>
            <option value="billing_campaign" @selected(request('status') === 'billing_campaign')>請求開始前</option>
            <option value="line_enabled" @selected(request('status') === 'line_enabled')>LINE有効</option>
        </select>

        <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition">
            検索
        </button>

        <a href="{{ route('admin.company.index') }}"
           class="inline-flex items-center justify-center border border-gray-300 bg-white text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition">
            リセット
        </a>
    </form>

    {{-- 上部操作 --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <a href="{{ route('admin.company.create') }}"
           class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg transition text-center">
            ＋ 新規登録
        </a>

        <div class="text-sm text-gray-500">
            複数選択して一括編集できます
        </div>
    </div>

    {{-- 一括編集フォーム --}}
    <form method="POST" action="{{ route('admin.company.bulk-edit') }}">
        @csrf

        <div class="mb-4 flex justify-end">
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg transition">
                選択した企業を一括編集
            </button>
        </div>

        {{-- テーブル --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 text-sm md:text-base">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border text-center w-12">
                            <input type="checkbox" onclick="toggleAll(this)">
                        </th>
                        <th class="p-3 border text-left">ID</th>
                        <th class="p-3 border text-left">企業コード</th>
                        <th class="p-3 border text-left">企業名</th>
                        <th class="p-3 border text-left">業種</th>
                        <th class="p-3 border text-left">予約URL</th>
                        <th class="p-3 border text-center">状態</th>
                        <th class="p-3 border text-center">契約</th>
                        <th class="p-3 border text-center">利用数</th>
                        <th class="p-3 border text-center">LINE</th>
                        <th class="p-3 border text-center">操作</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($companies as $company)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="border p-3 text-center">
                                <input type="checkbox" name="company_ids[]" value="{{ $company->id }}">
                            </td>

                            <td class="border p-3">{{ $company->id }}</td>

                            <td class="border p-3 font-mono">
                                {{ $company->company_code }}
                            </td>

                            <td class="border p-3">
                                <div class="font-semibold">{{ $company->name }}</div>

                                @if(!empty($company->email))
                                    <div class="text-xs text-gray-500 mt-1">{{ $company->email }}</div>
                                @endif
                            </td>

                            <td class="border p-3">
                                {{ $company->industry_type }}
                            </td>

                            <td class="border p-3">
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

                            <td class="border p-3 text-center">
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

                            <td class="border p-3 text-center">
                                <div class="text-sm font-semibold text-gray-700">{{ $company->subscription_status_label }}</div>
                                @if($company->billing_starts_at && $company->billing_starts_at->isFuture())
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">
                                            {{ $company->billing_starts_at->format('Y/m/d') }} 開始
                                        </span>
                                    </div>
                                @endif
                                @if(!$company->is_billing_active)
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                                            請求確認
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="border p-3 text-center text-xs text-gray-600">
                                <div>スタッフ {{ number_format($company->staff_count ?? 0) }}</div>
                                <div class="mt-1">予約 {{ number_format($company->reservations_count ?? 0) }}</div>
                                <div class="mt-1">顧客 {{ number_format($company->customers_count ?? 0) }}</div>
                            </td>

                            <td class="border p-3 text-center">
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

                            <td class="border p-3 text-center">
                                <div class="flex flex-col gap-2 min-w-[110px]">
                                    <a href="{{ route('admin.company.edit', $company->id) }}"
                                       class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition text-sm">
                                        個別編集
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
                            <td colspan="11" class="border p-6 text-center text-gray-500">
                                企業データがありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

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

function toggleAll(master) {
    document.querySelectorAll('input[name="company_ids[]"]').forEach(el => {
        el.checked = master.checked;
    });
}

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

</body>
</html>
