@php
    $theme = $theme ?? '#3b82f6';
    $current = $current ?? 'index';
@endphp

<section class="rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Notice Workflow</p>
            <h2 class="mt-1 text-lg font-black text-gray-900">お知らせ作業ナビ</h2>
            <p class="mt-1 text-sm text-gray-500">一覧確認、作成、編集の作業位置が分かります。</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:min-w-[520px]">
            <a href="{{ route('company.notices.index') }}"
               class="rounded-2xl border px-4 py-3 transition {{ $current === 'index' ? 'text-white shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-100 hover:bg-gray-100' }}"
               style="{{ $current === 'index' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                <div class="text-sm font-black">一覧</div>
                <div class="mt-1 text-xs {{ $current === 'index' ? 'text-white/80' : 'text-gray-500' }}">掲載状況を確認</div>
            </a>
            <a href="{{ route('company.notices.create') }}"
               class="rounded-2xl border px-4 py-3 transition {{ $current === 'create' ? 'text-white shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-100 hover:bg-gray-100' }}"
               style="{{ $current === 'create' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                <div class="text-sm font-black">新規作成</div>
                <div class="mt-1 text-xs {{ $current === 'create' ? 'text-white/80' : 'text-gray-500' }}">お知らせを追加</div>
            </a>
            <div class="rounded-2xl border px-4 py-3 {{ $current === 'edit' ? 'text-white shadow-sm' : 'bg-gray-50 text-gray-500 border-gray-100' }}"
                 style="{{ $current === 'edit' ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                <div class="text-sm font-black">編集中</div>
                <div class="mt-1 text-xs {{ $current === 'edit' ? 'text-white/80' : 'text-gray-500' }}">選択した内容を更新</div>
            </div>
        </div>
    </div>
</section>
