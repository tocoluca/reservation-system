<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>企業ダッシュボードお知らせ管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background: radial-gradient(circle at top left, rgba(14,165,233,.1), transparent 30rem), linear-gradient(180deg, #f8fafc, #eef2f7 55%, #f8fafc); }
        .admin-panel { border: 2px solid #cbd5e1; border-radius: 1.5rem; background: #fff; box-shadow: 0 12px 28px rgba(15,23,42,.07); }
        .notice-row { border-left: 5px solid #0ea5e9; }
        .notice-row-inactive { border-left-color: #94a3b8; background: #f8fafc; }
    </style>
</head>
<body class="text-slate-800">
@include('admin.partials.navigation')

<main class="mx-auto max-w-7xl px-4 py-6 md:px-6 md:py-10">
    <header class="mb-6 overflow-hidden rounded-3xl border border-slate-700 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 px-6 py-7 text-white shadow-xl md:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-sky-300"><i data-lucide="megaphone" class="h-4 w-4"></i>COMPANY NOTICE MANAGEMENT</div>
                <h1 class="mt-3 text-2xl font-black md:text-3xl">企業ダッシュボードお知らせ管理</h1>
                <p class="mt-2 text-sm font-medium text-slate-300">企業へ表示するお知らせの公開対象・期間・状態を管理します。</p>
            </div>
            <a href="{{ route('admin.company-dashboard-notices.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500 px-5 py-3 text-sm font-black text-white shadow hover:bg-sky-400"><i data-lucide="plus" class="h-4 w-4"></i>新規登録</a>
        </div>
    </header>

    @if(session('success'))
        <div role="status" class="mb-6 flex items-center gap-3 rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4 font-bold text-emerald-900"><i data-lucide="circle-check" class="h-5 w-5"></i>{{ session('success') }}</div>
    @endif

    <section aria-label="お知らせサマリー" class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border-2 border-slate-300 bg-white p-4 shadow-sm"><div class="flex items-center justify-between text-xs font-black text-slate-600"><span>登録件数</span><i data-lucide="files" class="h-4 w-4"></i></div><div class="mt-1 text-3xl font-black text-slate-950">{{ number_format($summary['total'] ?? 0) }}<span class="ml-1 text-xs">件</span></div></div>
        <div class="rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4 shadow-sm"><div class="flex items-center justify-between text-xs font-black text-emerald-700"><span>表示中</span><i data-lucide="eye" class="h-4 w-4"></i></div><div class="mt-1 text-3xl font-black text-emerald-800">{{ number_format($summary['active'] ?? 0) }}<span class="ml-1 text-xs">件</span></div></div>
        <div class="rounded-2xl border-2 border-blue-300 bg-blue-50 p-4 shadow-sm"><div class="flex items-center justify-between text-xs font-black text-blue-700"><span>全企業向け</span><i data-lucide="buildings" class="h-4 w-4"></i></div><div class="mt-1 text-3xl font-black text-blue-800">{{ number_format($summary['all'] ?? 0) }}<span class="ml-1 text-xs">件</span></div></div>
        <div class="rounded-2xl border-2 border-violet-300 bg-violet-50 p-4 shadow-sm"><div class="flex items-center justify-between text-xs font-black text-violet-700"><span>特定企業向け</span><i data-lucide="building" class="h-4 w-4"></i></div><div class="mt-1 text-3xl font-black text-violet-800">{{ number_format($summary['company'] ?? 0) }}<span class="ml-1 text-xs">件</span></div></div>
    </section>

    <div class="mb-4 flex items-center justify-between gap-3">
        <div><h2 class="flex items-center gap-2 text-lg font-black text-slate-950"><i data-lucide="list" class="h-5 w-5 text-sky-700"></i>お知らせ一覧</h2><p class="mt-1 text-xs font-medium text-slate-500">重要なお知らせ、公開対象、表示期間を確認できます。</p></div>
        <span class="rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-black text-slate-700">{{ number_format($notices->total()) }}件</span>
    </div>

    <div class="space-y-3 md:hidden">
        @forelse($notices as $notice)
            @php
                [$statusLabel, $statusClasses, $statusBorder] = match($notice->display_status) {
                    'active' => ['表示中', 'bg-emerald-100 text-emerald-800', 'border-l-sky-500'],
                    'scheduled' => ['開始前', 'bg-blue-100 text-blue-800', 'border-l-blue-500'],
                    'expired' => ['期限切れ', 'bg-amber-100 text-amber-800', 'border-l-amber-500'],
                    default => ['非表示', 'bg-slate-200 text-slate-700', 'border-l-slate-400'],
                };
            @endphp
            <article class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm border-l-[6px] {{ $statusBorder }} {{ $notice->display_status === 'inactive' ? 'bg-slate-50' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><div class="flex flex-wrap gap-2">@if($notice->is_important)<span class="rounded-full bg-rose-100 px-2 py-1 text-[11px] font-black text-rose-800">重要</span>@endif @if($notice->is_new)<span class="rounded-full bg-blue-100 px-2 py-1 text-[11px] font-black text-blue-800">NEW</span>@endif</div><h3 class="mt-2 break-words font-black text-slate-950">{{ $notice->title }}</h3></div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-black {{ $statusClasses }}">{{ $statusLabel }}</span>
                </div>
                <div class="mt-3 rounded-xl border p-3 {{ $notice->target_type === 'all' ? 'border-blue-200 bg-blue-50 text-blue-900' : 'border-violet-200 bg-violet-50 text-violet-900' }}"><div class="text-xs font-black">{{ $notice->target_label }}</div></div>
                <div class="mt-3 text-xs font-semibold text-slate-600">表示期間：{{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }} 〜 {{ optional($notice->end_date)->format('Y/m/d') ?: '指定なし' }}</div>
                <div class="mt-4 grid grid-cols-2 gap-2"><a href="{{ route('admin.company-dashboard-notices.edit', $notice) }}" class="rounded-xl bg-amber-500 px-4 py-2.5 text-center text-sm font-black text-white hover:bg-amber-600">編集</a><form action="{{ route('admin.company-dashboard-notices.destroy', $notice) }}" method="POST" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-black text-white hover:bg-rose-700">削除</button></form></div>
            </article>
        @empty
            <div class="admin-panel py-10 text-center text-sm font-semibold text-slate-500">お知らせはまだありません。</div>
        @endforelse
    </div>

    <div class="admin-panel hidden overflow-hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-white"><tr><th class="p-4 text-left text-xs font-black">題名</th><th class="p-4 text-left text-xs font-black">公開対象</th><th class="p-4 text-left text-xs font-black">表示期間</th><th class="p-4 text-center text-xs font-black">状態</th><th class="p-4 text-center text-xs font-black">操作</th></tr></thead>
                <tbody>
                    @forelse($notices as $notice)
                        @php
                            [$statusLabel, $statusClasses] = match($notice->display_status) {
                                'active' => ['表示中', 'border-emerald-300 bg-emerald-100 text-emerald-800'],
                                'scheduled' => ['開始前', 'border-blue-300 bg-blue-100 text-blue-800'],
                                'expired' => ['期限切れ', 'border-amber-300 bg-amber-100 text-amber-800'],
                                default => ['非表示', 'border-slate-300 bg-slate-200 text-slate-700'],
                            };
                        @endphp
                        <tr class="notice-row {{ $notice->display_status === 'active' ? '' : 'notice-row-inactive' }} border-b-2 border-slate-200 align-top transition hover:bg-sky-50/60">
                            <td class="p-4"><div class="flex flex-wrap items-center gap-2">@if($notice->is_important)<span class="rounded-full bg-rose-100 px-2 py-1 text-xs font-black text-rose-800">重要</span>@endif @if($notice->is_new)<span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-black text-blue-800">NEW</span>@endif <span class="font-black text-slate-950">{{ $notice->title }}</span></div></td>
                            <td class="p-4"><span class="inline-flex rounded-full border px-3 py-1.5 text-xs font-black {{ $notice->target_type === 'all' ? 'border-blue-300 bg-blue-50 text-blue-800' : 'border-violet-300 bg-violet-50 text-violet-800' }}">{{ $notice->target_label }}</span></td>
                            <td class="whitespace-nowrap p-4 font-semibold text-slate-600">{{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }}<span class="mx-1 text-slate-400">〜</span>{{ optional($notice->end_date)->format('Y/m/d') ?: '指定なし' }}</td>
                            <td class="p-4 text-center"><span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses }}">{{ $statusLabel }}</span></td>
                            <td class="p-4"><div class="flex justify-center gap-2"><a href="{{ route('admin.company-dashboard-notices.edit', $notice) }}" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-black text-white hover:bg-amber-600">編集</a><form action="{{ route('admin.company-dashboard-notices.destroy', $notice) }}" method="POST" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-black text-white hover:bg-rose-700">削除</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center font-semibold text-slate-500">お知らせはまだありません。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-4 py-4">{{ $notices->links() }}</div>
    </div>

    <div class="mt-4 md:hidden">{{ $notices->links() }}</div>
</main>

@include('admin.partials.mobile_nav')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) window.lucide.createIcons(); });</script>
</body>
</html>
