@extends('layouts.app')

@section('content')
@php
    $theme = $company->theme_color ?? '#b7875c';
@endphp

<div class="min-h-screen bg-[#f7f3ee] pb-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-5 py-6 sm:py-10">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfd3] bg-white shadow-sm mb-6">
            <div class="relative px-5 sm:px-8 py-8 sm:py-10 text-white" style="background: linear-gradient(135deg, {{ $theme }}, #2f261f);">
                <div class="absolute inset-0 opacity-20" style="background: radial-gradient(circle at 18% 20%, rgba(255,255,255,.9), transparent 28%), radial-gradient(circle at 90% 10%, rgba(255,255,255,.45), transparent 22%);"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5">
                    <div>
                        <div class="text-[11px] tracking-[0.18em] font-bold text-white/75">STYLE GALLERY</div>
                        <h1 class="mt-2 text-2xl sm:text-4xl font-bold leading-tight">最新スタイル</h1>
                        <p class="mt-3 text-sm sm:text-base leading-7 text-white/85 max-w-2xl">
                            登録されているスタイル画像を一覧で確認できます。画像を押すとコメントが表示されます。
                        </p>
                    </div>

                    <a href="{{ route('reserve.index', $company->company_code) }}" class="inline-flex items-center justify-center rounded-full bg-white/95 px-5 py-3 text-sm font-bold text-[#4b3f35] shadow-sm hover:bg-white transition">
                        予約画面へ戻る
                    </a>
                </div>
            </div>
        </div>

        @if($styles->count() > 0)
            <div class="grid grid-cols-2 gap-3 sm:gap-5">
                @foreach($styles as $style)
                    @php
                        $styleImage = $style->image_url ?? asset('images/noimage.png');
                        $title = $style->title ?: 'スタイル画像';
                        $comment = $style->comment ?: 'コメントは登録されていません。';
                    @endphp

                    <button type="button"
                            class="style-open group overflow-hidden rounded-[22px] border border-[#eadfd3] bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-[#eadfd3]"
                            data-title="{{ e($title) }}"
                            data-comment="{{ e($comment) }}"
                            data-image="{{ $styleImage }}">
                        <span class="block aspect-square overflow-hidden bg-[#f1e8df]">
                            <img src="{{ $styleImage }}" alt="{{ $title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                        </span>
                        <span class="block px-3 sm:px-4 py-3">
                            <span class="block truncate text-sm sm:text-base font-bold text-[#4b3f35]">{{ $title }}</span>
                            <span class="mt-1 block text-[11px] sm:text-xs font-bold text-[#9a7d63]">コメントを見る</span>
                        </span>
                    </button>
                @endforeach
            </div>

            @if($styles->hasPages())
                <div class="mt-8 flex items-center justify-between gap-3">
                    @if($styles->previousPageUrl())
                        <a href="{{ $styles->previousPageUrl() }}" class="inline-flex flex-1 items-center justify-center rounded-full border border-[#d9cabb] bg-white px-5 py-3 text-sm font-bold text-[#6b533f] hover:bg-[#fcf8f4] transition">
                            前の40件
                        </a>
                    @else
                        <span class="inline-flex flex-1 items-center justify-center rounded-full border border-[#eadfd3] bg-[#eee7df] px-5 py-3 text-sm font-bold text-[#b7a493]">
                            前の40件
                        </span>
                    @endif

                    <div class="shrink-0 text-xs font-bold text-[#9a7d63]">
                        {{ $styles->currentPage() }} / {{ $styles->lastPage() }}
                    </div>

                    @if($styles->nextPageUrl())
                        <a href="{{ $styles->nextPageUrl() }}" class="inline-flex flex-1 items-center justify-center rounded-full px-5 py-3 text-sm font-bold text-white shadow-sm hover:opacity-95 transition" style="background: {{ $theme }};">
                            次の40件
                        </a>
                    @else
                        <span class="inline-flex flex-1 items-center justify-center rounded-full border border-[#eadfd3] bg-[#eee7df] px-5 py-3 text-sm font-bold text-[#b7a493]">
                            次の40件
                        </span>
                    @endif
                </div>
            @endif
        @else
            <div class="rounded-[24px] border border-[#eadfd3] bg-white px-5 py-8 text-center text-[#7b6654] shadow-sm">
                現在公開中のスタイル画像はありません。
            </div>
        @endif
    </div>
</div>

<div id="styleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/65 px-4 py-6">
    <div class="absolute inset-0" data-style-close></div>
    <div class="relative w-full max-w-2xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
        <button type="button" class="absolute right-3 top-3 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-xl font-bold text-[#4b3f35] shadow-sm" data-style-close>
            &times;
        </button>
        <img id="styleModalImage" src="" alt="" class="max-h-[68vh] w-full object-contain bg-[#f1e8df]">
        <div class="px-5 sm:px-6 py-5">
            <h2 id="styleModalTitle" class="text-xl sm:text-2xl font-bold text-[#4b3f35]"></h2>
            <div id="styleModalComment" class="mt-3 whitespace-pre-line text-sm sm:text-base leading-8 text-[#6b5b4d]"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('styleModal');
    const image = document.getElementById('styleModalImage');
    const title = document.getElementById('styleModalTitle');
    const comment = document.getElementById('styleModalComment');

    document.querySelectorAll('.style-open').forEach(function (button) {
        button.addEventListener('click', function () {
            image.src = button.dataset.image || '';
            image.alt = button.dataset.title || 'スタイル画像';
            title.textContent = button.dataset.title || 'スタイル画像';
            comment.textContent = button.dataset.comment || 'コメントは登録されていません。';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        });
    });

    document.querySelectorAll('[data-style-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            image.src = '';
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            image.src = '';
        }
    });
});
</script>
@endsection
