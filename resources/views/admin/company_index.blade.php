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

    {{-- 検索 --}}
    <form method="GET" action="{{ route('admin.company.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
        <input type="text"
               name="keyword"
               value="{{ request('keyword') }}"
               placeholder="企業名・企業コード・業種・メールアドレスで検索"
               class="border border-gray-300 p-3 rounded-lg w-full sm:w-96 focus:outline-none focus:ring-2 focus:ring-blue-400">

        <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition">
            検索
        </button>
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
                                    <span class="text-green-600 font-semibold">
                                        利用中
                                    </span>
                                @else
                                    <span class="text-red-600 font-semibold">
                                        停止
                                    </span>
                                @endif
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

                                    <form method="POST" action="{{ route('admin.company.toggle', $company->id) }}">
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
                            <td colspan="9" class="border p-6 text-center text-gray-500">
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