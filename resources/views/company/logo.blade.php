@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

<style>
    .logo-drop-zone {
        transition: border-color .15s ease, background-color .15s ease, transform .15s ease;
    }
    .logo-drop-zone.is-dragging {
        border-color: {{ $theme }};
        background: {{ $themeSoft }};
        transform: translateY(-1px);
    }
    .logo-drop-zone:focus-within {
        border-color: {{ $theme }};
        box-shadow: 0 0 0 3px {{ $themeSoft }};
    }
</style>

<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
    {{-- ヘッダー --}}
    <div class="relative mb-6 overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background: radial-gradient(circle at top right, #ffffff 0%, transparent 35%), radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);"></div>

        <div class="relative px-6 py-7 text-white sm:px-8 sm:py-8"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        <i data-lucide="image" class="h-3.5 w-3.5"></i>
                        BRAND ASSET
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight text-white sm:text-3xl">企業ロゴ</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-white/85 sm:text-base">
                        お客様が目にする予約画面や案内に表示するロゴを設定します。
                    </p>
                </div>

                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-white/15 px-5 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="mr-2 h-4 w-4"></i>
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._storefront_settings_nav', ['current' => 'logo'])
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700" role="alert">
            <div class="flex items-start gap-3">
                <i data-lucide="circle-alert" class="mt-0.5 h-5 w-5 shrink-0"></i>
                <div>
                    <p class="font-bold">ロゴを更新できませんでした</p>
                    <ul class="mt-1 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:items-start">
        {{-- 現在のロゴ --}}
        <section class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">現在のロゴ</h2>
                        <p class="mt-1 text-sm text-gray-500">現在公開されている画像です。</p>
                    </div>
                    <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        <span class="mr-1.5 h-2 w-2 rounded-full bg-emerald-500"></span>
                        公開中
                    </span>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div class="flex min-h-[260px] items-center justify-center rounded-[1.5rem] border border-gray-200 bg-[linear-gradient(45deg,#f8fafc_25%,transparent_25%),linear-gradient(-45deg,#f8fafc_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#f8fafc_75%),linear-gradient(-45deg,transparent_75%,#f8fafc_75%)] bg-[length:24px_24px] bg-[position:0_0,0_12px,12px_-12px,-12px_0px] p-8">
                    @if($company->logo_path)
                        <img src="{{ asset($company->logo_path) }}"
                             alt="現在の企業ロゴ"
                             class="max-h-48 max-w-full rounded-xl object-contain">
                    @else
                        <div class="text-center">
                            <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gray-200 text-3xl font-bold text-gray-700 shadow-inner">
                                {{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}
                            </div>
                            <p class="mt-4 text-sm font-semibold text-gray-500">ロゴはまだ登録されていません</p>
                        </div>
                    @endif
                </div>

                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">表示される場所</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">予約画面</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">管理画面</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">各種案内</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- 新しいロゴ --}}
        <section class="overflow-hidden rounded-[2rem] border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-bold text-gray-900">新しいロゴに変更</h2>
                <p class="mt-1 text-sm leading-6 text-gray-500">画像を選択し、プレビューを確認してから保存してください。</p>
            </div>

            <form id="logoForm" method="POST" action="{{ route('company.logo.update') }}" enctype="multipart/form-data" class="p-5 sm:p-6">
                @csrf

                <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_180px] sm:items-stretch">
                    <div>
                        <label id="logoDropZone" for="logoInput"
                               class="logo-drop-zone flex min-h-[220px] cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-gray-300 bg-gray-50 px-5 py-7 text-center hover:border-gray-400 hover:bg-gray-100/70">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-sm"
                                  style="background: {{ $theme }};">
                                <i data-lucide="image-plus" class="h-5 w-5"></i>
                            </span>
                            <span class="mt-4 text-sm font-bold text-gray-900">画像を選択またはドロップ</span>
                            <span class="mt-1 text-xs leading-5 text-gray-500">クリックしてファイルを選択できます</span>
                            <span class="mt-4 inline-flex rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 shadow-sm">画像を選択</span>
                            <input id="logoInput" type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                    </div>

                    <div id="previewPanel" class="hidden min-h-[220px] flex-col rounded-[1.5rem] border border-gray-200 bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-gray-400">プレビュー</p>
                        <div class="mt-3 flex min-h-[120px] flex-1 items-center justify-center rounded-xl bg-gray-50 p-3">
                            <img id="logoPreview" src="" alt="選択したロゴのプレビュー" class="max-h-28 max-w-full object-contain">
                        </div>
                        <p id="selectedFileName" class="mt-3 truncate text-xs font-semibold text-gray-700"></p>
                        <p id="selectedFileMeta" class="mt-1 text-xs text-gray-400"></p>
                    </div>
                </div>

                <div id="mobilePreviewPanel" class="mt-4 hidden rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:hidden">
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-white p-2">
                            <img id="mobileLogoPreview" src="" alt="選択したロゴのプレビュー" class="max-h-16 max-w-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-400">選択中</p>
                            <p id="mobileFileName" class="mt-1 truncate text-sm font-semibold text-gray-800"></p>
                            <p id="mobileFileMeta" class="mt-1 text-xs text-gray-500"></p>
                        </div>
                    </div>
                </div>

                <p id="clientLogoError" class="mt-3 hidden text-sm font-semibold text-red-600" role="alert"></p>

                <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600"></i>
                        <div>
                            <p class="text-sm font-bold text-blue-900">きれいに表示するための目安</p>
                            <ul class="mt-2 grid gap-1 text-xs leading-5 text-blue-800 sm:grid-cols-2">
                                <li>・PNG／JPG／WebP</li>
                                <li>・ファイル容量 4MB以下</li>
                                <li>・600 × 600px程度を推奨</li>
                                <li>・余白のある正方形画像がおすすめ</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 sm:w-auto">
                        キャンセル
                    </a>
                    <button id="logoSubmitButton" type="submit" disabled
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40 sm:w-auto"
                            style="background: {{ $theme }};">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        <span id="logoSubmitText">このロゴに変更</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('logoForm')
    const input = document.getElementById('logoInput')
    const dropZone = document.getElementById('logoDropZone')
    const previewPanel = document.getElementById('previewPanel')
    const preview = document.getElementById('logoPreview')
    const mobilePreviewPanel = document.getElementById('mobilePreviewPanel')
    const mobilePreview = document.getElementById('mobileLogoPreview')
    const fileName = document.getElementById('selectedFileName')
    const fileMeta = document.getElementById('selectedFileMeta')
    const mobileFileName = document.getElementById('mobileFileName')
    const mobileFileMeta = document.getElementById('mobileFileMeta')
    const clientError = document.getElementById('clientLogoError')
    const submitButton = document.getElementById('logoSubmitButton')
    const submitText = document.getElementById('logoSubmitText')
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp']
    const maxSize = 4 * 1024 * 1024
    let previewUrl = null

    function formatSize(bytes) {
        return bytes < 1024 * 1024
            ? `${Math.ceil(bytes / 1024)} KB`
            : `${(bytes / 1024 / 1024).toFixed(1)} MB`
    }

    function resetSelection(message) {
        input.value = ''
        submitButton.disabled = true
        previewPanel.classList.add('hidden')
        mobilePreviewPanel.classList.add('hidden')
        clientError.textContent = message
        clientError.classList.remove('hidden')
        if (previewUrl) URL.revokeObjectURL(previewUrl)
        previewUrl = null
    }

    function showPreview(file) {
        clientError.classList.add('hidden')

        if (!allowedTypes.includes(file.type)) {
            resetSelection('PNG、JPG、WebP形式の画像を選択してください。')
            return
        }
        if (file.size > maxSize) {
            resetSelection('ファイル容量が4MBを超えています。小さい画像を選択してください。')
            return
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl)
        previewUrl = URL.createObjectURL(file)
        preview.src = previewUrl
        mobilePreview.src = previewUrl
        fileName.textContent = file.name
        mobileFileName.textContent = file.name
        fileMeta.textContent = formatSize(file.size)
        mobileFileMeta.textContent = formatSize(file.size)
        previewPanel.classList.remove('hidden')
        previewPanel.classList.add('flex')
        mobilePreviewPanel.classList.remove('hidden')
        submitButton.disabled = false
    }

    input.addEventListener('change', function () {
        if (input.files && input.files[0]) showPreview(input.files[0])
    })

    ;['dragenter', 'dragover'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (event) {
            event.preventDefault()
            dropZone.classList.add('is-dragging')
        })
    })

    ;['dragleave', 'drop'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (event) {
            event.preventDefault()
            dropZone.classList.remove('is-dragging')
        })
    })

    dropZone.addEventListener('drop', function (event) {
        const file = event.dataTransfer.files && event.dataTransfer.files[0]
        if (!file) return

        const transfer = new DataTransfer()
        transfer.items.add(file)
        input.files = transfer.files
        showPreview(file)
    })

    form.addEventListener('submit', function () {
        submitButton.disabled = true
        submitText.textContent = 'アップロード中…'
    })
})
</script>

@endsection
