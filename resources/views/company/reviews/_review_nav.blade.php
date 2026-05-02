@php
    $theme = $theme ?? '#3b82f6';
    $current = $current ?? 'index';
    $backStatus = request('from_status') ?: request('status');
    $reviewListParams = $backStatus ? ['status' => $backStatus] : [];
@endphp

<section class="rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Review Workflow</p>
            <h2 class="mt-1 text-lg font-black text-gray-900">口コミ対応ナビ</h2>
            <p class="mt-1 text-sm text-gray-500">状態別一覧と詳細確認を行き来できます。</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('company.reviews.index') }}"
               class="rounded-2xl px-4 py-2.5 text-sm font-bold {{ $current === 'index' && !request('status') ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
               style="{{ $current === 'index' && !request('status') ? 'background: '.$theme : '' }}">すべて</a>
            <a href="{{ route('company.reviews.index', ['status' => 'pending']) }}"
               class="rounded-2xl px-4 py-2.5 text-sm font-bold {{ ($current === 'index' && request('status') === 'pending') || ($current === 'detail' && $backStatus === 'pending') ? 'text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}"
               style="{{ ($current === 'index' && request('status') === 'pending') || ($current === 'detail' && $backStatus === 'pending') ? 'background: '.$theme : '' }}">確認待ち</a>
            <a href="{{ route('company.reviews.index', ['status' => 'approved']) }}"
               class="rounded-2xl px-4 py-2.5 text-sm font-bold {{ ($current === 'index' && request('status') === 'approved') || ($current === 'detail' && $backStatus === 'approved') ? 'text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
               style="{{ ($current === 'index' && request('status') === 'approved') || ($current === 'detail' && $backStatus === 'approved') ? 'background: '.$theme : '' }}">公開中</a>
            <a href="{{ route('company.reviews.index', ['status' => 'rejected']) }}"
               class="rounded-2xl px-4 py-2.5 text-sm font-bold {{ ($current === 'index' && request('status') === 'rejected') || ($current === 'detail' && $backStatus === 'rejected') ? 'text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
               style="{{ ($current === 'index' && request('status') === 'rejected') || ($current === 'detail' && $backStatus === 'rejected') ? 'background: '.$theme : '' }}">非公開</a>

            @if($current === 'detail')
                <a href="{{ route('company.reviews.index', $reviewListParams) }}"
                   class="inline-flex items-center justify-center rounded-2xl border bg-white px-4 py-2.5 text-sm font-bold transition hover:bg-gray-50"
                   style="border-color: {{ $theme }}33; color: {{ $theme }};">一覧へ戻る</a>
            @endif
        </div>
    </div>
</section>
