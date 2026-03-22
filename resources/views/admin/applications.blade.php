<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>企業申請管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">企業申請管理</h1>
            <p class="text-sm text-gray-500 mt-1">申請の確認・承認・却下・検索ができます</p>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gray-800 text-white hover:bg-black">
            ダッシュボードへ戻る
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-100 text-red-700 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-100 text-red-700 border border-red-200">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-sm text-gray-500">全件</div>
            <div class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['all'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-sm text-gray-500">審査待ち</div>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-sm text-gray-500">承認済</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border p-4">
            <div class="text-sm text-gray-500">却下</div>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border p-4 md:p-5 mb-6">
        <form method="GET" action="{{ route('admin.applications') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">

            <input type="text"
                   name="keyword"
                   value="{{ request('keyword') }}"
                   placeholder="企業名・担当者名・メール・電話で検索"
                   class="w-full rounded-xl border-gray-300 px-4 py-3 border">

            <select name="status" class="w-full rounded-xl border-gray-300 px-4 py-3 border">
                <option value="">状態すべて</option>
                <option value="pending"  @selected(request('status') === 'pending')>審査待ち</option>
                <option value="approved" @selected(request('status') === 'approved')>承認済</option>
                <option value="rejected" @selected(request('status') === 'rejected')>却下</option>
            </select>

            <select name="industry_type" class="w-full rounded-xl border-gray-300 px-4 py-3 border">
                <option value="">業種すべて</option>
                <option value="beauty" @selected(request('industry_type') === 'beauty')>美容</option>
                <option value="dental" @selected(request('industry_type') === 'dental')>歯科</option>
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 font-semibold">
                    検索
                </button>
                <a href="{{ route('admin.applications') }}"
                   class="rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 font-semibold text-center">
                    クリア
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left">申請日</th>
                        <th class="px-4 py-3 text-left">企業名</th>
                        <th class="px-4 py-3 text-left">業種</th>
                        <th class="px-4 py-3 text-left">担当者</th>
                        <th class="px-4 py-3 text-left">メール</th>
                        <th class="px-4 py-3 text-left">電話</th>
                        <th class="px-4 py-3 text-center">状態</th>
                        <th class="px-4 py-3 text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($applications as $app)
                    <tr class="border-t hover:bg-gray-50 align-top">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ optional($app->created_at)->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">
                            {{ $app->company_name }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $app->industry_label }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $app->contact_person }}
                        </td>
                        <td class="px-4 py-3 break-all">
                            {{ $app->email }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ $app->phone }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $app->status_color }}">
                                {{ $app->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2 justify-center">
                                <button type="button"
                                        onclick="openDetail({{ $app->id }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg">
                                    詳細
                                </button>

                                @if($app->status === 'pending')
                                    <form method="POST" action="{{ route('admin.applications.approve', $app->id) }}">
                                        @csrf
                                        <input type="hidden" name="send_mail" value="1">
                                        <button type="submit"
                                                onclick="return confirm('この申請を承認して企業アカウントを作成しますか？')"
                                                class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-2 rounded-lg">
                                            承認
                                        </button>
                                    </form>

                                    <button type="button"
                                            onclick="openReject({{ $app->id }}, @js($app->company_name))"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg">
                                        却下
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.applications.pending', $app->id) }}">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('審査待ちに戻しますか？')"
                                                class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg">
                                            審査待ちに戻す
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                            該当する申請はありません。
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($applications, 'links'))
            <div class="px-4 py-4 border-t">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</div>

<div id="detailModal" class="hidden fixed inset-0 bg-black/50 z-50 p-4">
    <div class="max-w-2xl mx-auto mt-10 bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-800">申請詳細</h2>
            <button onclick="closeDetail()" class="text-gray-500 hover:text-gray-700 text-xl">×</button>
        </div>
        <div id="detailBody" class="p-5 space-y-3 text-sm md:text-base"></div>
        <div class="px-5 py-4 border-t bg-gray-50">
            <button onclick="closeDetail()" class="px-5 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white">
                閉じる
            </button>
        </div>
    </div>
</div>

<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 z-50 p-4">
    <div class="max-w-xl mx-auto mt-16 bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-800">申請却下</h2>
            <button onclick="closeReject()" class="text-gray-500 hover:text-gray-700 text-xl">×</button>
        </div>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="p-5 space-y-4">
                <div class="text-sm text-gray-700">
                    <span id="rejectCompanyName" class="font-semibold"></span> の申請を却下します。
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">却下理由</label>
                    <textarea name="reject_reason"
                              rows="5"
                              class="w-full rounded-xl border border-gray-300 px-4 py-3"
                              placeholder="例：対象外業種のため、情報不足のため、など"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="send_mail" value="1" checked>
                    却下メールを送信する
                </label>
            </div>

            <div class="px-5 py-4 border-t bg-gray-50 flex gap-2 justify-end">
                <button type="button"
                        onclick="closeReject()"
                        class="px-5 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white">
                    キャンセル
                </button>
                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white">
                    却下する
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function openDetail(id) {
    const modal = document.getElementById('detailModal');
    const body = document.getElementById('detailBody');

    body.innerHTML = '<div class="text-gray-500">読み込み中...</div>';
    modal.classList.remove('hidden');

    try {
        const response = await fetch(`/admin/applications/${id}`);
        const app = await response.json();

        body.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><div class="text-gray-500 text-sm">企業名</div><div class="font-semibold">${escapeHtml(app.company_name ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">業種</div><div class="font-semibold">${escapeHtml(app.industry_label ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">担当者</div><div class="font-semibold">${escapeHtml(app.contact_person ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">状態</div><div class="font-semibold">${escapeHtml(app.status_label ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">メール</div><div class="font-semibold break-all">${escapeHtml(app.email ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">電話</div><div class="font-semibold">${escapeHtml(app.phone ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">申請日時</div><div class="font-semibold">${escapeHtml(app.created_at ?? '')}</div></div>
                <div><div class="text-gray-500 text-sm">処理日時</div><div class="font-semibold">${escapeHtml(app.reviewed_at ?? '-')}</div></div>
            </div>

            <div class="pt-2">
                <div class="text-gray-500 text-sm mb-1">申請メッセージ</div>
                <div class="bg-gray-50 border rounded-xl p-4 whitespace-pre-wrap">${escapeHtml(app.message ?? '') || '-'}</div>
            </div>

            ${(app.reject_reason)
                ? `<div>
                    <div class="text-gray-500 text-sm mb-1">却下理由</div>
                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 whitespace-pre-wrap">${escapeHtml(app.reject_reason)}</div>
                   </div>`
                : ''
            }

            ${(app.status === 'approved')
                ? `<div class="pt-2">
                    <div class="text-gray-500 text-sm mb-1">承認後情報</div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 space-y-2">
                        <div><b>企業ID:</b> ${escapeHtml(String(app.approved_company_id ?? '-'))}</div>
                        <div><b>担当者コード:</b> ${escapeHtml(app.initial_staff_code ?? '-')}</div>
                        <div><b>初期パスワード:</b> ${escapeHtml(app.initial_password_plain ?? '-')}</div>
                        <div><b>ログインURL:</b> ${escapeHtml(app.login_url ?? '-')}</div>
                    </div>
                   </div>`
                : ''
            }
        `;
    } catch (e) {
        body.innerHTML = '<div class="text-red-600">詳細の取得に失敗しました。</div>';
    }
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

function openReject(id, companyName) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const label = document.getElementById('rejectCompanyName');

    form.action = `/admin/applications/reject/${id}`;
    label.textContent = companyName;
    modal.classList.remove('hidden');
}

function closeReject() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target.id === 'detailModal') closeDetail();
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target.id === 'rejectModal') closeReject();
});
</script>

</body>
</html>