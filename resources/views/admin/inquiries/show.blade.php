<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>FAQ・お問い合わせ詳細</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background: radial-gradient(circle at top left, rgba(14,165,233,.1), transparent 30rem), linear-gradient(180deg, #f8fafc, #eef2f7 55%, #f8fafc); }
        .admin-panel { overflow: hidden; border: 2px solid #cbd5e1; border-radius: 1.5rem; background: #fff; box-shadow: 0 12px 28px rgba(15,23,42,.07); }
        .form-control { width: 100%; border: 2px solid #cbd5e1; border-radius: 1rem; background: #f8fafc; padding: .85rem 1rem; color: #0f172a; }
        .form-control:focus { border-color: #0284c7; background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(14,165,233,.12); }
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
    $isOpen = $inquiry->status === 'open';
    $statusLabel = match($inquiry->status) {
        'answered' => '回答済み',
        'closed' => '完了',
        default => '未回答',
    };
    $statusClasses = match($inquiry->status) {
        'answered' => 'border-emerald-300 bg-emerald-100 text-emerald-800',
        'closed' => 'border-slate-300 bg-slate-200 text-slate-700',
        default => 'border-rose-300 bg-rose-100 text-rose-800',
    };
@endphp

<main class="mx-auto max-w-5xl px-4 py-6 md:px-6 md:py-10">
    <a href="{{ route('admin.inquiries.index') }}" class="mb-4 inline-flex items-center gap-2 rounded-2xl border-2 border-slate-300 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:border-sky-400 hover:bg-sky-50"><i data-lucide="arrow-left" class="h-4 w-4"></i>お問い合わせ一覧へ戻る</a>

    @if(session('success'))
        <div role="status" class="mb-5 flex items-center gap-3 rounded-2xl border-2 border-emerald-300 bg-emerald-50 p-4 font-bold text-emerald-900"><i data-lucide="circle-check" class="h-5 w-5"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-5 rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-red-900"><div class="flex items-center gap-2 font-black"><i data-lucide="circle-alert" class="h-5 w-5"></i>入力内容を確認してください</div><ul class="mt-2 list-disc pl-6 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <header class="mb-6 overflow-hidden rounded-3xl border border-slate-700 bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 text-white shadow-xl">
        <div class="p-6 md:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 text-xs font-bold tracking-[0.16em] text-sky-300"><i data-lucide="message-circle-question" class="h-4 w-4"></i>INQUIRY DETAIL</div>
                    <h1 class="mt-3 break-words text-2xl font-black leading-tight md:text-3xl">{{ $inquiry->subject }}</h1>
                    <p class="mt-2 text-sm font-medium text-slate-300">企業から届いたお問い合わせの内容と対応履歴です。</p>
                </div>
                <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full border-2 px-4 py-2 text-sm font-black {{ $statusClasses }}">
                    <i data-lucide="{{ $isOpen ? 'circle-alert' : 'circle-check' }}" class="h-4 w-4"></i>{{ $statusLabel }}
                </span>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">会社名</div><div class="mt-1 break-words font-black text-white">{{ $inquiry->company->name ?? '-' }}</div></div>
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">カテゴリ</div><div class="mt-1 font-black text-white">{{ $categoryLabels[$inquiry->category] ?? ($inquiry->category ?: '-') }}</div></div>
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">受付日時</div><div class="mt-1 font-black text-white">{{ optional($inquiry->created_at)->format('Y/m/d H:i') ?: '-' }}</div></div>
            <div class="bg-slate-900/80 px-5 py-4"><div class="text-xs font-bold text-slate-400">回答日時</div><div class="mt-1 font-black text-white">{{ optional($inquiry->replied_at)->format('Y/m/d H:i') ?: '未回答' }}</div></div>
        </div>
    </header>

    <section class="admin-panel mb-6">
        <div class="border-b-2 border-sky-200 bg-sky-50 px-5 py-4 md:px-6"><h2 class="flex items-center gap-2 font-black text-sky-950"><i data-lucide="message-square-text" class="h-5 w-5"></i>お問い合わせ内容</h2></div>
        <div class="p-5 md:p-6"><div class="whitespace-pre-line rounded-2xl border-2 border-slate-200 bg-slate-50 p-5 text-sm font-medium leading-8 text-slate-800">{{ $inquiry->body }}</div></div>
    </section>

    @if($inquiry->admin_reply)
        <section class="admin-panel mb-6">
            <div class="border-b-2 border-emerald-200 bg-emerald-50 px-5 py-4 md:px-6"><div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"><h2 class="flex items-center gap-2 font-black text-emerald-950"><i data-lucide="message-square-reply" class="h-5 w-5"></i>現在の回答</h2>@if($inquiry->repliedAdmin)<span class="text-xs font-bold text-emerald-700">回答者：{{ $inquiry->repliedAdmin->name }}</span>@endif</div></div>
            <div class="p-5 md:p-6"><div class="whitespace-pre-line rounded-2xl border-2 border-emerald-200 bg-emerald-50/60 p-5 text-sm font-medium leading-8 text-slate-800">{{ $inquiry->admin_reply }}</div></div>
        </section>
    @endif

    <section class="admin-panel {{ $isOpen ? 'border-rose-300' : '' }}">
        <div class="border-b-2 px-5 py-4 md:px-6 {{ $isOpen ? 'border-rose-200 bg-rose-50' : 'border-violet-200 bg-violet-50' }}">
            <h2 class="flex items-center gap-2 font-black {{ $isOpen ? 'text-rose-950' : 'text-violet-950' }}"><i data-lucide="pen-line" class="h-5 w-5"></i>{{ $inquiry->admin_reply ? '回答を更新' : '回答を登録' }}</h2>
            <p class="mt-1 text-xs font-medium {{ $isOpen ? 'text-rose-700' : 'text-violet-700' }}">{{ $isOpen ? '未回答のお問い合わせです。内容を確認して回答してください。' : '必要に応じて回答内容や状態を更新できます。' }}</p>
        </div>
        <form action="{{ route('admin.inquiries.reply', $inquiry) }}" method="POST" class="space-y-5 p-5 md:p-6">
            @csrf
            <div><label for="admin_reply" class="mb-2 block text-sm font-black text-slate-800">回答内容 <span class="text-red-600">必須</span></label><textarea id="admin_reply" name="admin_reply" rows="10" class="form-control" required placeholder="企業へ案内する回答内容を入力してください。">{{ old('admin_reply', $inquiry->admin_reply) }}</textarea>@error('admin_reply')<div class="mt-2 text-sm font-bold text-red-600">{{ $message }}</div>@enderror</div>
            <div><label for="status" class="mb-2 block text-sm font-black text-slate-800">回答後の状態</label><select id="status" name="status" class="form-control"><option value="answered" @selected(old('status', $inquiry->status) === 'answered')>回答済み</option><option value="closed" @selected(old('status', $inquiry->status) === 'closed')>完了</option></select><p class="mt-2 text-xs font-medium text-slate-500">追加対応の可能性がある場合は「回答済み」、対応を終了する場合は「完了」を選択します。</p></div>
            <div class="flex flex-col-reverse gap-3 border-t-2 border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between"><a href="{{ route('admin.inquiries.index') }}" class="inline-flex items-center justify-center rounded-xl border-2 border-slate-300 px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">キャンセル</a><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-black text-white shadow {{ $isOpen ? 'bg-rose-600 hover:bg-rose-700' : 'bg-violet-700 hover:bg-violet-800' }}"><i data-lucide="send" class="h-4 w-4"></i>{{ $inquiry->admin_reply ? '回答を更新する' : '回答を登録する' }}</button></div>
        </form>
    </section>
</main>

@include('admin.partials.mobile_nav')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) window.lucide.createIcons(); });</script>
</body>
</html>
