@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $today = now()->startOfDay();
    $statusOf = function ($notice) use ($today) {
        if (! $notice->is_active) return 'inactive';
        if ($notice->start_date?->gt($today)) return 'scheduled';
        if ($notice->end_date?->lt($today)) return 'expired';
        return 'published';
    };
    $statusCounts = collect($notices)->countBy($statusOf);
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <div class="rounded-3xl shadow-sm overflow-hidden mb-6 border border-gray-100 bg-white">
        <div class="p-6 sm:p-8 text-white" style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-sm opacity-90 mb-2">Notice Management</p>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">お知らせ管理</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2">公開状況を確認し、必要なお知らせをすぐ編集できます。</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}" class="inline-flex items-center justify-center px-4 py-3 rounded-xl bg-white/15 hover:bg-white/25 transition text-white border border-white/20">← ダッシュボード</a>
                    <a href="{{ route('company.notices.create') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-white text-sm font-bold shadow hover:opacity-90 transition" style="color: {{ $theme }};">＋ 新しいお知らせ</a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="mb-5">@include('company.notices._notice_nav', ['current' => 'index'])</div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="rounded-2xl bg-white border border-gray-100 p-4 shadow-sm"><div class="text-xs font-bold text-gray-500">全件</div><div class="mt-1 text-2xl font-black text-gray-900">{{ $notices->count() }}</div></div>
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 shadow-sm"><div class="text-xs font-bold text-emerald-700">掲載中</div><div class="mt-1 text-2xl font-black text-emerald-700">{{ $statusCounts->get('published', 0) }}</div></div>
        <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4 shadow-sm"><div class="text-xs font-bold text-blue-700">公開予約</div><div class="mt-1 text-2xl font-black text-blue-700">{{ $statusCounts->get('scheduled', 0) }}</div></div>
        <div class="rounded-2xl bg-gray-100 border border-gray-200 p-4 shadow-sm"><div class="text-xs font-bold text-gray-600">終了・停止</div><div class="mt-1 text-2xl font-black text-gray-700">{{ $statusCounts->get('expired', 0) + $statusCounts->get('inactive', 0) }}</div></div>
    </div>

    <section class="sticky top-3 z-20 mb-5 rounded-2xl border border-gray-200 bg-white/95 p-3 shadow-sm backdrop-blur">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <div class="flex gap-2 overflow-x-auto pb-1 lg:pb-0" aria-label="掲載状態で絞り込み">
                @foreach(['all' => 'すべて', 'published' => '掲載中', 'scheduled' => '公開予約', 'expired' => '掲載終了', 'inactive' => '停止中', 'important' => '重要'] as $filter => $label)
                    <button type="button" data-filter="{{ $filter }}" class="notice-filter whitespace-nowrap rounded-xl border px-3 py-2 text-sm font-bold transition {{ $filter === 'all' ? 'text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50' }}" style="{{ $filter === 'all' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">{{ $label }}</button>
                @endforeach
            </div>
            <label class="relative block lg:w-72">
                <span class="sr-only">お知らせを検索</span>
                <input id="noticeSearch" type="search" placeholder="タイトル・本文を検索" class="w-full rounded-xl border border-gray-200 py-2.5 pl-4 pr-10 text-sm focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $theme }};">
                <span class="pointer-events-none absolute right-3 top-2.5 text-gray-400">⌕</span>
            </label>
        </div>
    </section>

    <div id="noticeList" class="space-y-4">
        @forelse($notices as $notice)
            @php
                $status = $statusOf($notice);
                [$statusLabel, $statusClass] = match($status) {
                    'published' => ['掲載中', 'bg-emerald-50 text-emerald-700 border-emerald-100'],
                    'scheduled' => ['公開予約', 'bg-blue-50 text-blue-700 border-blue-100'],
                    'expired' => ['掲載終了', 'bg-gray-100 text-gray-600 border-gray-200'],
                    default => ['停止中', 'bg-amber-50 text-amber-700 border-amber-100'],
                };
            @endphp
            <article class="notice-card overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md"
                     data-status="{{ $status }}" data-important="{{ $notice->is_important ? '1' : '0' }}" data-search="{{ Str::lower($notice->title.' '.$notice->content) }}">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-5">
                        <div class="sm:w-48 lg:w-56 shrink-0">
                            @if($notice->image)
                                <div class="aspect-[4/3] overflow-hidden rounded-2xl border border-gray-100 bg-gray-50"><img src="{{ asset($notice->image) }}" alt="" class="h-full w-full object-contain" loading="lazy"></div>
                            @else
                                <div class="aspect-[4/3] rounded-2xl border border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center text-gray-400"><span class="text-2xl">🖼️</span><span class="mt-2 text-xs">画像なし</span></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                                @if($notice->is_important)<span class="inline-flex items-center rounded-full border border-red-100 bg-red-50 px-3 py-1 text-xs font-bold text-red-600">重要</span>@endif
                            </div>
                            <h2 class="mt-3 text-lg sm:text-xl font-black leading-snug text-gray-900">{{ $notice->title }}</h2>
                            @if($notice->content)<p class="mt-2 text-sm leading-6 text-gray-600">{{ Str::limit($notice->content, 150) }}</p>@endif
                            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-500">
                                <span><strong class="text-gray-700">開始：</strong>{{ $notice->start_date?->format('Y/m/d') ?? 'すぐ公開' }}</span>
                                <span><strong class="text-gray-700">終了：</strong>{{ $notice->end_date?->format('Y/m/d') ?? '期限なし' }}</span>
                                <span><strong class="text-gray-700">更新：</strong>{{ $notice->updated_at->format('Y/m/d H:i') }}</span>
                            </div>
                            <div class="mt-5 flex flex-col sm:flex-row sm:justify-end gap-2">
                                <a href="{{ route('company.notices.edit', $notice) }}" class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 font-bold text-white hover:opacity-90" style="background: {{ $theme }};">内容を編集</a>
                                <form method="POST" action="{{ route('company.notices.destroy', $notice) }}" onsubmit="return confirm('「{{ addslashes($notice->title) }}」を削除しますか？この操作は元に戻せません。')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl border border-red-200 px-5 py-2.5 font-bold text-red-600 hover:bg-red-50">削除</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-10 text-center shadow-sm">
                <div class="text-5xl">📢</div><h2 class="mt-3 text-lg font-bold text-gray-800">お知らせがまだありません</h2><p class="mt-2 text-sm text-gray-500">最初のお知らせを作成して、お客様へ情報を届けましょう。</p>
                <a href="{{ route('company.notices.create') }}" class="mt-6 inline-flex items-center justify-center rounded-xl px-5 py-3 font-bold text-white" style="background: {{ $theme }};">＋ お知らせを作成</a>
            </div>
        @endforelse
    </div>
    <div id="noticeEmptyFilter" class="hidden rounded-3xl border border-dashed border-gray-200 bg-white p-10 text-center text-sm text-gray-500">条件に合うお知らせはありません。絞り込みや検索語を変更してください。</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buttons = [...document.querySelectorAll('.notice-filter')];
    const cards = [...document.querySelectorAll('.notice-card')];
    const search = document.getElementById('noticeSearch');
    const empty = document.getElementById('noticeEmptyFilter');
    let filter = 'all';
    const theme = @json($theme);
    const refresh = () => {
        const query = search.value.trim().toLocaleLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const statusMatch = filter === 'all' || card.dataset.status === filter || (filter === 'important' && card.dataset.important === '1');
            const searchMatch = !query || card.dataset.search.toLocaleLowerCase().includes(query);
            card.classList.toggle('hidden', !(statusMatch && searchMatch));
            if (statusMatch && searchMatch) visible++;
        });
        empty.classList.toggle('hidden', visible !== 0 || cards.length === 0);
    };
    buttons.forEach(button => button.addEventListener('click', () => {
        filter = button.dataset.filter;
        buttons.forEach(item => {
            const active = item === button;
            item.classList.toggle('text-white', active);
            item.classList.toggle('border-gray-200', !active);
            item.classList.toggle('bg-white', !active);
            item.classList.toggle('text-gray-600', !active);
            item.style.background = active ? theme : '';
            item.style.borderColor = active ? theme : '';
        });
        refresh();
    }));
    search.addEventListener('input', refresh);
});
</script>
@endsection
