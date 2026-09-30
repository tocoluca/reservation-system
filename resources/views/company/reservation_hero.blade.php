@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $hasHeroImage = !empty($company->reservation_hero_image_path);
    $heading = old('reservation_hero_heading', $company->reservation_hero_heading);
    $subheading = old('reservation_hero_subheading', $company->reservation_hero_subheading);
    $headingSize = old('reservation_hero_heading_size', $company->reservation_hero_heading_size ?? 40);
    $subheadingSize = old('reservation_hero_subheading_size', $company->reservation_hero_subheading_size ?? 16);
    $textColor = old('reservation_hero_text_color', $company->reservation_hero_text_color ?? '#ffffff');
@endphp

<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8">
    <div class="relative mb-6 overflow-hidden rounded-3xl shadow-lg">
        <div class="relative px-6 py-7 text-white sm:px-8 sm:py-8" style="background: var(--company-theme-gradient);">
            <p class="text-xs font-bold uppercase tracking-[0.18em] opacity-80">Reservation Screen</p>
            <h1 class="mt-1 text-2xl font-bold sm:text-3xl">予約画面設定</h1>
            <p class="mt-2 text-sm leading-6 opacity-90 sm:text-base">予約画面のメイン画像と、画像上に表示する文字を設定します。</p>
        </div>
    </div>

    <div class="mb-6">
        @include('company._storefront_settings_nav', ['current' => 'reservation-hero'])
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="mb-1 font-bold">入力内容をご確認ください。</div>
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="heroSettingsForm" method="POST" action="{{ route('company.reservation-hero.update') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(520px,1.1fr)]">
        @csrf

        <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <h2 class="text-lg font-bold text-gray-900">メイン画像</h2>
                <p class="mt-1 text-sm leading-6 text-gray-500">横長画像（推奨 16:6、1920×720px前後）を選択してください。保存時に長辺1920px以内へ縮小し、WebPへ圧縮します。</p>
            </div>

            <label for="hero_image" class="mt-5 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-stone-300 bg-stone-50 px-5 py-8 text-center hover:bg-stone-100">
                <span class="font-bold text-stone-700">画像を選択</span>
                <span class="mt-1 text-xs text-stone-500">JPEG / PNG / WebP、10MB以内</span>
            </label>
            <input id="hero_image" name="hero_image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only">
            <p id="selectedFileName" class="mt-2 min-h-5 text-sm text-stone-600"></p>

            @if($hasHeroImage)
                <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-4">
                    <span class="text-sm text-stone-600">現在のメイン画像が設定されています。</span>
                    <button type="button" id="removeImageButton" class="shrink-0 rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50">画像を削除</button>
                </div>
            @endif

            <div class="mt-7 border-t border-stone-100 pt-6">
                <h2 class="text-lg font-bold text-gray-900">画像上の文字</h2>

                <label for="reservation_hero_heading" class="mt-5 block text-sm font-bold text-gray-700">見出し</label>
                <input id="reservation_hero_heading" name="reservation_hero_heading" type="text" maxlength="120" value="{{ $heading }}" placeholder="例：ご予約はこちらから" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3">

                <label for="reservation_hero_subheading" class="mt-5 block text-sm font-bold text-gray-700">サブ見出し</label>
                <textarea id="reservation_hero_subheading" name="reservation_hero_subheading" maxlength="240" rows="3" placeholder="例：メニューと日時をお選びください" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3">{{ $subheading }}</textarea>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="reservation_hero_heading_size" class="block text-sm font-bold text-gray-700">見出しサイズ</label>
                        <div class="mt-2 flex items-center gap-3">
                            <input id="reservation_hero_heading_size" name="reservation_hero_heading_size" type="range" min="20" max="96" value="{{ $headingSize }}" class="w-full">
                            <output id="headingSizeOutput" class="w-14 text-right text-sm font-bold text-stone-700">{{ $headingSize }}px</output>
                        </div>
                    </div>
                    <div>
                        <label for="reservation_hero_subheading_size" class="block text-sm font-bold text-gray-700">サブ見出しサイズ</label>
                        <div class="mt-2 flex items-center gap-3">
                            <input id="reservation_hero_subheading_size" name="reservation_hero_subheading_size" type="range" min="12" max="48" value="{{ $subheadingSize }}" class="w-full">
                            <output id="subheadingSizeOutput" class="w-14 text-right text-sm font-bold text-stone-700">{{ $subheadingSize }}px</output>
                        </div>
                    </div>
                </div>

                <label for="reservation_hero_text_color" class="mt-5 block text-sm font-bold text-gray-700">文字色</label>
                <div class="mt-2 flex items-center gap-3">
                    <input id="reservation_hero_text_color" name="reservation_hero_text_color" type="color" value="{{ $textColor }}" class="h-12 w-16 cursor-pointer rounded-xl border border-stone-300 bg-white p-1">
                    <code id="textColorOutput" class="rounded-xl bg-stone-100 px-3 py-2 text-sm text-stone-700">{{ $textColor }}</code>
                </div>
            </div>

            <button type="submit" class="mt-7 w-full rounded-2xl px-5 py-3.5 font-bold text-white shadow-sm hover:opacity-90" style="background: var(--company-theme-gradient);">設定を保存</button>
        </section>

        <section class="xl:sticky xl:top-24 xl:self-start">
            <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">予約画面プレビュー</h2>
                        <p class="mt-1 text-sm text-gray-500">実際の表示では画面幅に合わせて上下左右が自然に切り抜かれます。</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">リアルタイム</span>
                </div>

                <div id="heroPreview" class="relative aspect-[16/6] min-h-[220px] overflow-hidden rounded-[24px] bg-stone-800 shadow-sm">
                    <img id="heroPreviewImage" src="{{ $hasHeroImage ? route('reserve.hero-image', ['company_code' => $company->company_code, 'v' => optional($company->updated_at)->timestamp]) : '' }}" alt="" class="absolute inset-0 h-full w-full object-cover {{ $hasHeroImage ? '' : 'hidden' }}">
                    <div id="heroPreviewFallback" class="absolute inset-0 {{ $hasHeroImage ? 'hidden' : '' }}" style="background: linear-gradient(135deg, {{ $theme }}, #243b53);"></div>
                    <div class="absolute inset-0 bg-black/35"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
                        <p id="heroPreviewEyebrow" class="mb-3 text-xs font-bold tracking-[0.12em] text-white/90">ONLINE RESERVATION</p>
                        <h3 id="heroPreviewHeading" class="font-bold leading-tight" style="font-size: {{ $headingSize }}px; color: {{ $textColor }};">{{ $heading ?: 'ご予約' }}</h3>
                        <p id="heroPreviewSubheading" class="mt-3 max-w-2xl whitespace-pre-line leading-relaxed" style="font-size: {{ $subheadingSize }}px; color: {{ $textColor }};">{{ $subheading ?: 'メニューを選んで、ご希望の日付・時間をお選びください。' }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-center text-xs text-stone-500">
                    <div class="rounded-xl bg-stone-50 px-3 py-2">PC: 横長表示</div>
                    <div class="rounded-xl bg-stone-50 px-3 py-2">スマホ: 中央を基準に表示</div>
                </div>
            </div>
        </section>
    </form>

    @if($hasHeroImage)
        <form id="removeImageForm" method="POST" action="{{ route('company.reservation-hero.image.destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('hero_image')
    const image = document.getElementById('heroPreviewImage')
    const fallback = document.getElementById('heroPreviewFallback')
    const heading = document.getElementById('reservation_hero_heading')
    const subheading = document.getElementById('reservation_hero_subheading')
    const headingSize = document.getElementById('reservation_hero_heading_size')
    const subheadingSize = document.getElementById('reservation_hero_subheading_size')
    const textColor = document.getElementById('reservation_hero_text_color')

    function updatePreview() {
        document.getElementById('heroPreviewHeading').textContent = heading.value || 'ご予約'
        document.getElementById('heroPreviewSubheading').textContent = subheading.value || 'メニューを選んで、ご希望の日付・時間をお選びください。'
        document.getElementById('heroPreviewHeading').style.fontSize = headingSize.value + 'px'
        document.getElementById('heroPreviewSubheading').style.fontSize = subheadingSize.value + 'px'
        document.getElementById('heroPreviewHeading').style.color = textColor.value
        document.getElementById('heroPreviewSubheading').style.color = textColor.value
        document.getElementById('headingSizeOutput').textContent = headingSize.value + 'px'
        document.getElementById('subheadingSizeOutput').textContent = subheadingSize.value + 'px'
        document.getElementById('textColorOutput').textContent = textColor.value
    }

    ;[heading, subheading, headingSize, subheadingSize, textColor].forEach(function (field) {
        field.addEventListener('input', updatePreview)
    })

    imageInput.addEventListener('change', function () {
        const file = imageInput.files && imageInput.files[0]
        document.getElementById('selectedFileName').textContent = file ? file.name : ''
        if (!file) return

        image.src = URL.createObjectURL(file)
        image.classList.remove('hidden')
        fallback.classList.add('hidden')
    })

    document.getElementById('removeImageButton')?.addEventListener('click', function () {
        if (window.confirm('現在のメイン画像を削除しますか？文字設定は残ります。')) {
            document.getElementById('removeImageForm').submit()
        }
    })
})
</script>
@endsection
