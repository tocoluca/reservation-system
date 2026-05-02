@php
    $theme = $theme ?? '#3b82f6';
    $current = $current ?? 'index';
@endphp

<section class="mb-6 rounded-[1.75rem] border border-stone-200 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-stone-400">Style Post Workflow</p>
            <h2 class="mt-1 text-lg font-black text-stone-900">スタイル投稿ナビ</h2>
            <p class="mt-1 text-sm text-stone-500">投稿一覧と作成・編集を迷わず移動できます。</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:min-w-[520px]">
            <a href="{{ route('company.style-posts.index') }}"
               class="rounded-2xl border px-4 py-3 transition {{ $current === 'index' ? 'text-white shadow-sm' : 'bg-stone-50 text-stone-700 border-stone-100 hover:bg-stone-100' }}"
               style="{{ $current === 'index' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                <div class="text-sm font-black">一覧</div>
                <div class="mt-1 text-xs {{ $current === 'index' ? 'text-white/80' : 'text-stone-500' }}">投稿を確認</div>
            </a>
            <a href="{{ route('company.style-posts.create') }}"
               class="rounded-2xl border px-4 py-3 transition {{ $current === 'create' ? 'text-white shadow-sm' : 'bg-stone-50 text-stone-700 border-stone-100 hover:bg-stone-100' }}"
               style="{{ $current === 'create' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                <div class="text-sm font-black">新規投稿</div>
                <div class="mt-1 text-xs {{ $current === 'create' ? 'text-white/80' : 'text-stone-500' }}">写真を追加</div>
            </a>
            <div class="rounded-2xl border px-4 py-3 {{ $current === 'edit' ? 'text-white shadow-sm' : 'bg-stone-50 text-stone-500 border-stone-100' }}"
                 style="{{ $current === 'edit' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                <div class="text-sm font-black">編集中</div>
                <div class="mt-1 text-xs {{ $current === 'edit' ? 'text-white/80' : 'text-stone-500' }}">表示内容を更新</div>
            </div>
        </div>
    </div>
</section>
