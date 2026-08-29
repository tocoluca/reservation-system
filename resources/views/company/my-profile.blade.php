@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
    $staffInitial = mb_strtoupper(mb_substr($staff->name, 0, 1));
@endphp

<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
    <header class="relative mb-6 overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background: radial-gradient(circle at top right, #ffffff 0%, transparent 35%), radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);"></div>
        <div class="relative px-6 py-7 text-white sm:px-8 sm:py-8"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-3xl border border-white/30 bg-white/15 shadow-lg">
                        @if($staff->image_path)
                            <img src="{{ asset($staff->image_path) }}" alt="{{ $staff->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-2xl font-black text-white">{{ $staffInitial }}</div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-widest text-white/75">My Profile</p>
                        <h1 class="mt-1 truncate text-2xl font-black sm:text-3xl">{{ $staff->name }}</h1>
                        <p class="mt-1 text-sm text-white/80">{{ $roleLabel ?? $staff->role }}</p>
                    </div>
                </div>

                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-white/15 px-4 py-3 text-sm font-bold backdrop-blur-sm transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    ダッシュボード
                </a>
            </div>
        </div>
    </header>

    <nav class="sticky z-30 mb-6 rounded-[1.5rem] border border-white/80 bg-white/95 p-2 shadow-lg backdrop-blur"
         style="top: calc(var(--company-topbar-height, 6rem) + .75rem);"
         aria-label="マイプロフィール内メニュー">
        <div class="grid grid-cols-3 gap-2">
            <a href="#profile-image" class="inline-flex min-w-0 items-center justify-center gap-1 rounded-2xl px-2 py-3 text-[11px] font-black text-white shadow-sm transition hover:opacity-90 sm:gap-2 sm:px-3 sm:text-sm" style="background: {{ $theme }};">
                <i data-lucide="camera" class="h-4 w-4 shrink-0"></i><span class="truncate">画像</span>
            </a>
            <a href="#profile-comment" class="inline-flex min-w-0 items-center justify-center gap-1 rounded-2xl bg-amber-50 px-2 py-3 text-[11px] font-black text-amber-800 transition hover:bg-amber-100 sm:gap-2 sm:px-3 sm:text-sm">
                <i data-lucide="message-square-text" class="h-4 w-4 shrink-0"></i><span class="truncate">コメント</span>
            </a>
            <a href="#profile-password" class="inline-flex min-w-0 items-center justify-center gap-1 rounded-2xl bg-slate-100 px-2 py-3 text-[11px] font-black text-slate-700 transition hover:bg-slate-200 sm:gap-2 sm:px-3 sm:text-sm">
                <i data-lucide="key-round" class="h-4 w-4 shrink-0"></i><span class="truncate">パスワード</span>
            </a>
        </div>
    </nav>

    <div class="space-y-6">
        <section id="profile-image" class="scroll-mt-44 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-white" style="background: {{ $theme }};"><i data-lucide="image-plus" class="h-5 w-5"></i></span>
                    <div><h2 class="text-lg font-black text-gray-900">プロフィール画像</h2><p class="mt-1 text-sm leading-6 text-gray-500">画像を確認してから、安全に差し替えられます。</p></div>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                @error('image')
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert">{{ $message }}</div>
                @enderror

                <form method="POST" action="{{ route('company.my-profile.image.update') }}" enctype="multipart/form-data"
                      class="grid gap-5 rounded-3xl border border-stone-200 bg-stone-50/70 p-4 sm:p-5 lg:grid-cols-[280px_1fr]"
                      data-profile-image-form data-busy-form="true" data-busy-label="更新中…">
                    @csrf
                    @method('PUT')

                    <div class="mx-auto w-full max-w-[280px]">
                        <div class="relative aspect-square overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                            <div class="absolute inset-0 {{ $staff->image_path ? 'hidden' : 'flex' }} items-center justify-center text-5xl font-black text-white"
                                 style="background: linear-gradient(135deg, {{ $theme }}, #111827);" data-profile-placeholder>{{ $staffInitial }}</div>
                            <img src="{{ $staff->image_path ? asset($staff->image_path) : '' }}"
                                 alt="アップロードするプロフィール画像のプレビュー"
                                 class="{{ $staff->image_path ? '' : 'hidden' }} h-full w-full object-cover"
                                 data-profile-preview data-original-src="{{ $staff->image_path ? asset($staff->image_path) : '' }}">
                        </div>
                        <div class="mt-3 text-center">
                            @if($staff->image_path)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700"><i data-lucide="circle-check" class="h-3.5 w-3.5"></i>設定済み</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-stone-200 px-3 py-1.5 text-xs font-bold text-stone-600"><i data-lucide="user-round" class="h-3.5 w-3.5"></i>画像未設定</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col justify-center">
                        <input id="profile-image-input" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" data-profile-image-input>
                        <label for="profile-image-input" class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-stone-300 bg-white px-5 py-6 text-center transition hover:border-stone-400 hover:bg-stone-50" data-profile-drop-zone>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-sm" style="background: {{ $theme }};"><i data-lucide="camera" class="h-6 w-6"></i></span>
                            <span class="mt-3 text-sm font-black text-stone-800">画像を選ぶ</span>
                            <span class="mt-1 text-xs leading-5 text-stone-500">クリックして選択、またはここに画像をドロップ</span>
                        </label>

                        <div class="mt-3 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert" data-profile-client-error></div>
                        <div class="mt-3 hidden rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3" data-profile-file-info>
                            <div class="flex items-start gap-3"><i data-lucide="circle-check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"></i><div class="min-w-0"><p class="truncate text-sm font-bold text-emerald-900" data-profile-file-name></p><p class="mt-0.5 text-xs text-emerald-700" data-profile-file-meta></p></div></div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-stone-500">JPG・PNG・WebP／10MBまで。向きを補正し、640×640pxへ自動調整します。</p>
                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <button type="button" class="hidden min-h-11 items-center justify-center gap-2 rounded-2xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-stone-600 transition hover:bg-stone-100" data-profile-clear><i data-lucide="x" class="h-4 w-4"></i>選び直す</button>
                            <button type="submit" disabled data-busy-button class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-2xl px-6 py-2.5 text-sm font-black text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40" style="background: {{ $theme }};" data-profile-submit>
                                <i data-lucide="upload" class="h-4 w-4"></i><span data-busy-text>この画像に更新</span>
                            </button>
                        </div>
                    </div>
                </form>

                @if($staff->image_path)
                    <div class="mt-4 flex justify-end">
                        <form method="POST" action="{{ route('company.my-profile.image.delete') }}" onsubmit="return confirm('プロフィール画像を削除しますか？')">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600 transition hover:bg-red-100"><i data-lucide="trash-2" class="h-4 w-4"></i>現在の画像を削除</button>
                        </form>
                    </div>
                @endif
            </div>
        </section>

        <section id="profile-comment" class="scroll-mt-44 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"><i data-lucide="message-square-text" class="h-5 w-5"></i></span><div><h2 class="text-lg font-black text-gray-900">プロフィールコメント</h2><p class="mt-1 text-sm leading-6 text-gray-500">自己紹介や得意な施術を分かりやすく伝えます。</p></div></div>
            </div>
            <form method="POST" action="{{ route('company.my-profile.update') }}" class="p-5 sm:p-6" data-busy-form="true" data-busy-label="保存中…">
                @csrf
                <input type="hidden" name="section" value="comment">
                <div class="flex items-center justify-between gap-3"><label for="profile-comment-input" class="text-sm font-bold text-gray-700">コメント</label><span class="text-xs font-semibold text-gray-400"><span data-comment-count>0</span> / 500文字</span></div>
                <textarea id="profile-comment-input" name="comment" rows="6" maxlength="500"
                          class="mt-2 w-full rounded-2xl border border-stone-300 p-4 text-sm leading-7 focus:border-transparent focus:outline-none focus:ring-4"
                          style="--tw-ring-color: {{ $theme }}22;" placeholder="例：ショートカットと髪質に合わせたカラーが得意です。" data-comment-input>{{ old('comment', $staff->comment) }}</textarea>
                @error('comment')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                <div class="mt-4 flex justify-end"><button type="submit" data-busy-button class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl px-6 py-2.5 text-sm font-black text-white shadow-sm transition hover:opacity-90 sm:w-auto" style="background: {{ $theme }};"><i data-lucide="save" class="h-4 w-4"></i><span data-busy-text>コメントを保存</span></button></div>
            </form>
        </section>

        <section id="profile-password" class="scroll-mt-44 overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"><i data-lucide="shield-check" class="h-5 w-5"></i></span><div><h2 class="text-lg font-black text-gray-900">パスワード変更</h2><p class="mt-1 text-sm leading-6 text-gray-500">パスワードを変更するときだけ操作してください。</p></div></div>
            </div>
            <form method="POST" action="{{ route('company.my-profile.update') }}" class="p-5 sm:p-6" data-busy-form="true" data-busy-label="更新中…">
                @csrf
                <input type="hidden" name="section" value="password">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="new-password" class="mb-2 block text-sm font-bold text-gray-700">新しいパスワード</label>
                        <div class="relative"><input id="new-password" type="password" name="password" minlength="8" autocomplete="new-password" class="w-full rounded-2xl border border-stone-300 py-3 pl-4 pr-12 text-sm focus:border-transparent focus:outline-none focus:ring-4" style="--tw-ring-color: {{ $theme }}22;" data-password-input><button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-stone-400 hover:text-stone-700" aria-label="パスワードを表示" data-password-toggle><i data-lucide="eye" class="h-5 w-5"></i></button></div>
                    </div>
                    <div>
                        <label for="new-password-confirmation" class="mb-2 block text-sm font-bold text-gray-700">確認用パスワード</label>
                        <div class="relative"><input id="new-password-confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" class="w-full rounded-2xl border border-stone-300 py-3 pl-4 pr-12 text-sm focus:border-transparent focus:outline-none focus:ring-4" style="--tw-ring-color: {{ $theme }}22;" data-password-input><button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-stone-400 hover:text-stone-700" aria-label="確認用パスワードを表示" data-password-toggle><i data-lucide="eye" class="h-5 w-5"></i></button></div>
                    </div>
                </div>
                @error('password')<p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                <p class="mt-3 text-xs text-gray-500">8文字以上で入力してください。</p>
                <div class="mt-4 flex justify-end"><button type="submit" data-busy-button class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-slate-800 sm:w-auto"><i data-lucide="key-round" class="h-4 w-4"></i><span data-busy-text>パスワードを更新</span></button></div>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const errorSection = @js($errors->has('image') ? 'profile-image' : ($errors->has('comment') ? 'profile-comment' : ($errors->has('password') ? 'profile-password' : null)));
    if (errorSection) window.requestAnimationFrame(() => document.getElementById(errorSection)?.scrollIntoView({ block: 'start' }));

    const imageForm = document.querySelector('[data-profile-image-form]');
    const imageInput = imageForm?.querySelector('[data-profile-image-input]');
    const preview = imageForm?.querySelector('[data-profile-preview]');
    const placeholder = imageForm?.querySelector('[data-profile-placeholder]');
    const fileInfo = imageForm?.querySelector('[data-profile-file-info]');
    const fileName = imageForm?.querySelector('[data-profile-file-name]');
    const fileMeta = imageForm?.querySelector('[data-profile-file-meta]');
    const clientError = imageForm?.querySelector('[data-profile-client-error]');
    const submitButton = imageForm?.querySelector('[data-profile-submit]');
    const clearButton = imageForm?.querySelector('[data-profile-clear]');
    const dropZone = imageForm?.querySelector('[data-profile-drop-zone]');
    const originalSrc = preview?.dataset.originalSrc || '';
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxFileSize = 10 * 1024 * 1024;
    let previewUrl = null;

    const showError = message => { if (clientError) { clientError.textContent = message; clientError.classList.remove('hidden'); } };
    const clearError = () => { if (clientError) { clientError.textContent = ''; clientError.classList.add('hidden'); } };
    const resetImage = () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        if (imageInput) imageInput.value = '';
        if (preview) { preview.src = originalSrc; preview.classList.toggle('hidden', !originalSrc); }
        placeholder?.classList.toggle('hidden', Boolean(originalSrc));
        fileInfo?.classList.add('hidden');
        clearButton?.classList.add('hidden');
        clearButton?.classList.remove('inline-flex');
        if (submitButton) submitButton.disabled = true;
        clearError();
    };
    const selectImage = file => {
        clearError();
        if (!file) return resetImage();
        if (!allowedTypes.includes(file.type)) { resetImage(); showError('JPG・PNG・WebP形式の画像を選択してください。'); return; }
        if (file.size > maxFileSize) { resetImage(); showError('画像は10MB以内で選択してください。'); return; }
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        if (preview) { preview.src = previewUrl; preview.classList.remove('hidden'); }
        placeholder?.classList.add('hidden');
        if (fileName) fileName.textContent = file.name;
        if (fileMeta) fileMeta.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
        fileInfo?.classList.remove('hidden');
        clearButton?.classList.remove('hidden');
        clearButton?.classList.add('inline-flex');
        if (submitButton) submitButton.disabled = false;
    };

    imageInput?.addEventListener('change', () => selectImage(imageInput.files?.[0]));
    clearButton?.addEventListener('click', resetImage);
    ['dragenter', 'dragover'].forEach(name => dropZone?.addEventListener(name, event => { event.preventDefault(); dropZone.classList.add('border-slate-500', 'bg-slate-50'); }));
    ['dragleave', 'drop'].forEach(name => dropZone?.addEventListener(name, event => { event.preventDefault(); dropZone.classList.remove('border-slate-500', 'bg-slate-50'); }));
    dropZone?.addEventListener('drop', event => {
        const file = event.dataTransfer?.files?.[0];
        if (!file || !imageInput) return;
        const transfer = new DataTransfer(); transfer.items.add(file); imageInput.files = transfer.files; selectImage(file);
    });

    const commentInput = document.querySelector('[data-comment-input]');
    const commentCount = document.querySelector('[data-comment-count]');
    const updateCommentCount = () => { if (commentCount) commentCount.textContent = commentInput?.value.length || 0; };
    commentInput?.addEventListener('input', updateCommentCount); updateCommentCount();

    document.querySelectorAll('[data-password-toggle]').forEach(toggle => toggle.addEventListener('click', () => {
        const input = toggle.closest('.relative')?.querySelector('[data-password-input]');
        if (!input) return;
        const showing = input.type === 'text'; input.type = showing ? 'password' : 'text';
        toggle.setAttribute('aria-label', showing ? 'パスワードを表示' : 'パスワードを隠す');
        const icon = toggle.querySelector('i'); if (icon) icon.setAttribute('data-lucide', showing ? 'eye' : 'eye-off');
        if (window.lucide) lucide.createIcons();
    }));
});
</script>

@endsection
