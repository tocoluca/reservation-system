<!DOCTYPE html>
<html lang="ja">
<head>
    <title>FAQ・お問い合わせ管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background: radial-gradient(circle at top left, rgba(14,165,233,.1), transparent 30rem), linear-gradient(180deg, #f8fafc, #eef2f7 55%, #f8fafc); }
        .admin-panel { border: 2px solid #cbd5e1; border-radius: 1.5rem; background: #fff; box-shadow: 0 12px 28px rgba(15,23,42,.07); }
        .inquiry-row { border-left: 5px solid #cbd5e1; }
        .inquiry-row-open { border-left-color: #e11d48; background: #fff7f8; }
        .inquiry-row-answered { border-left-color: #059669; }
        .inquiry-row-closed { border-left-color: #64748b; background: #f8fafc; }
    </style>
</head>
<body class="text-slate-800">
@include('admin.partials.navigation')

@php
    $categoryLabels = [
        'staff' => 'スタッフ表示',
        'business_day' => '営業日設定',
        'reservation' => '予約設定',
        'mail' => 'メール',
        'other' => 'その他',
    ];
@endphp

<div class="flex min-h-screen">

    <!-- スマホ用オーバーレイ -->


    <!-- メイン -->
    <div class="flex-1 w-full min-w-0">

        <!-- スマホ用ヘッダー -->


        <div class="mx-auto max-w-7xl p-4 md:p-10">

            @if(session('success'))
                <div class="mb-6 flex items-center gap-2 rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4 font-bold text-emerald-800">
                    <i data-lucide="circle-check" class="h-5 w-5"></i>
                    {{ session('success') }}
                </div>
            @endif

            <header class="mb-6 overflow-hidden rounded-3xl border border-slate-700 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 p-6 text-white shadow-xl md:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-sky-300"><i data-lucide="messages-square" class="h-4 w-4"></i>INQUIRY MANAGEMENT</div>
                        <h1 class="mt-3 text-2xl font-black md:text-3xl">FAQ・お問い合わせ管理</h1>
                        <p class="mt-2 text-sm font-medium leading-7 text-slate-300">企業から届いたお問い合わせを確認し、回答・完了処理を行います。</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.inquiries.index', ['status' => 'open']) }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-center text-sm font-black text-white shadow hover:bg-rose-500">
                            <i data-lucide="message-circle-question" class="h-4 w-4"></i>
                            未回答のみ表示
                        </a>

                        <a href="{{ route('admin.inquiries.index') }}"
                           class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-center text-sm font-black text-white hover:bg-white/20">
                            <i data-lucide="list" class="h-4 w-4"></i>
                            すべて表示
                        </a>
                    </div>
                </div>
            </header>

            <nav aria-label="お問い合わせの状態" class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach([
                    'all' => ['すべて', 'messages-square', 'border-sky-300 bg-sky-50 text-sky-950'],
                    'open' => ['未回答', 'message-circle-question', 'border-rose-300 bg-rose-50 text-rose-950'],
                    'answered' => ['回答済み', 'circle-check', 'border-emerald-300 bg-emerald-50 text-emerald-950'],
                    'closed' => ['完了', 'archive', 'border-slate-300 bg-slate-100 text-slate-900'],
                ] as $status => [$label, $icon, $tone])
                    @php $selectedStatus = request('status', '') === ($status === 'all' ? '' : $status); @endphp
                    <a href="{{ route('admin.inquiries.index', ['status' => $status === 'all' ? null : $status]) }}"
                       class="rounded-2xl border-2 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $tone }} {{ $selectedStatus ? 'ring-4 ring-sky-200' : '' }}">
                        <span class="flex items-center justify-between gap-2 text-sm font-black"><span>{{ $label }}</span><i data-lucide="{{ $icon }}" class="h-5 w-5"></i></span>
                        <span class="mt-2 block text-3xl font-black">{{ number_format($stats[$status] ?? 0) }}<span class="ml-1 text-xs">件</span></span>
                    </a>
                @endforeach
            </nav>

            <div class="admin-panel mb-6 p-5 md:p-6">
                <h2 class="mb-1 flex items-center gap-2 text-lg font-black text-slate-950"><i data-lucide="list-filter" class="h-5 w-5 text-sky-700"></i>絞り込み</h2>
                <p class="mb-4 text-xs font-medium text-slate-500">状態とカテゴリを組み合わせて表示できます。</p>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">状態</label>
                        <select name="status" class="w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-sky-600 focus:bg-white focus:outline-none">
                            <option value="">すべて</option>
                            <option value="open" @selected(request('status') === 'open')>受付中</option>
                            <option value="answered" @selected(request('status') === 'answered')>回答済み</option>
                            <option value="closed" @selected(request('status') === 'closed')>完了</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">カテゴリ</label>
                        <select name="category" class="w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-sm focus:border-sky-600 focus:bg-white focus:outline-none">
                            <option value="">すべて</option>
                            <option value="staff" @selected(request('category') === 'staff')>スタッフ表示</option>
                            <option value="business_day" @selected(request('category') === 'business_day')>営業日設定</option>
                            <option value="reservation" @selected(request('category') === 'reservation')>予約設定</option>
                            <option value="mail" @selected(request('category') === 'mail')>メール</option>
                            <option value="other" @selected(request('category') === 'other')>その他</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex items-end gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-700 px-5 py-3 text-center text-sm font-black text-white shadow hover:bg-sky-800">
                            <i data-lucide="filter" class="h-4 w-4"></i>
                            絞り込む
                        </button>

                        <a href="{{ route('admin.inquiries.index') }}"
                           class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-lg text-center font-semibold text-sm">
                            リセット
                        </a>
                    </div>
                </form>
            </div>

            <div class="admin-panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <h3 class="flex items-center gap-2 px-5 pt-5 text-lg font-black text-slate-950 md:px-6 md:text-xl">
                        <i data-lucide="clipboard-list" class="h-5 w-5 text-sky-700"></i>
                        一覧
                    </h3>

                    <div class="mr-5 mt-5 rounded-full border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-black text-slate-700 md:mr-6">
                        {{ $inquiries->total() }}件
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="p-4 text-left text-xs font-black whitespace-nowrap">受付日時</th>
                                <th class="p-4 text-left text-xs font-black whitespace-nowrap">会社名</th>
                                <th class="p-4 text-left text-xs font-black whitespace-nowrap">カテゴリ</th>
                                <th class="p-4 text-left text-xs font-black whitespace-nowrap">件名</th>
                                <th class="p-4 text-left text-xs font-black whitespace-nowrap">状態</th>
                                <th class="p-4 text-left text-xs font-black whitespace-nowrap">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inquiry)
                                <tr class="inquiry-row inquiry-row-{{ $inquiry->status }} border-b-2 border-slate-200 transition hover:bg-sky-50/70">
                                    <td class="p-4 whitespace-nowrap font-semibold text-slate-600">
                                        {{ optional($inquiry->created_at)->format('Y/m/d H:i') }}
                                    </td>

                                    <td class="p-4 whitespace-nowrap font-black text-slate-950">
                                        {{ $inquiry->company->name ?? '-' }}
                                    </td>

                                    <td class="p-4 whitespace-nowrap">
                                        <span class="inline-flex rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-700">{{ $categoryLabels[$inquiry->category] ?? ($inquiry->category ?: '-') }}</span>
                                    </td>

                                    <td class="min-w-[260px] p-4">
                                        <div class="font-black text-slate-950">
                                            {{ $inquiry->subject }}
                                        </div>
                                        <div class="mt-1 text-xs font-medium leading-5 text-slate-500">
                                            {{ \Illuminate\Support\Str::limit($inquiry->body, 70) }}
                                        </div>
                                    </td>

                                    <td class="p-4 whitespace-nowrap">
                                        @if($inquiry->status === 'answered')
                                            <span class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">
                                                回答済み
                                            </span>
                                        @elseif($inquiry->status === 'closed')
                                            <span class="inline-flex items-center rounded-full border border-slate-300 bg-slate-200 px-3 py-1 text-xs font-black text-slate-700">
                                                完了
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-rose-300 bg-rose-100 px-3 py-1 text-xs font-black text-rose-800">
                                                未回答
                                            </span>
                                        @endif
                                    </td>

                                    <td class="p-4 whitespace-nowrap">
                                        <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-center text-sm font-black text-white shadow {{ $inquiry->status === 'open' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-slate-700 hover:bg-slate-800' }}">
                                            <i data-lucide="{{ $inquiry->status === 'open' ? 'reply' : 'eye' }}" class="h-4 w-4"></i>
                                            {{ $inquiry->status === 'open' ? '回答する' : '詳細を見る' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-gray-400 py-10">
                                        お問い合わせはありません
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-4 py-4">
                    {{ $inquiries->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
@include('admin.partials.mobile_nav')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();
});
</script>
</body>
</html>
