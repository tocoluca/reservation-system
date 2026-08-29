@php
    $titleValue = old('title', $notice?->title ?? '');
    $contentValue = old('content', $notice?->content ?? '');
    $startValue = old('start_date', $notice?->start_date?->format('Y-m-d') ?? '');
    $endValue = old('end_date', $notice?->end_date?->format('Y-m-d') ?? '');
    $importantValue = (bool) old('is_important', $notice?->is_important ?? false);
    $activeValue = (bool) old('is_active', $notice?->is_active ?? true);
    $hasCurrentImage = $editing && ! empty($notice?->image);
@endphp

@if($errors->any())
    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4" role="alert">
        <p class="font-bold text-red-800">入力内容を確認してください</p>
        <ul class="mt-2 list-disc pl-5 text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<form id="noticeForm" method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($editing) @method('PUT') @endif

    <nav class="sticky top-3 z-20 rounded-2xl border border-gray-200 bg-white/95 p-2 shadow-sm backdrop-blur" aria-label="入力項目へ移動">
        <div class="grid grid-cols-3 gap-2 text-xs sm:text-sm font-bold">
            <a href="#notice-content" class="rounded-xl px-2 py-2.5 text-center text-gray-700 hover:bg-gray-100">1. 内容</a>
            <a href="#notice-publish" class="rounded-xl px-2 py-2.5 text-center text-gray-700 hover:bg-gray-100">2. 公開設定</a>
            <a href="#notice-image" class="rounded-xl px-2 py-2.5 text-center text-gray-700 hover:bg-gray-100">3. 画像</a>
        </div>
    </nav>

    <section id="notice-content" class="scroll-mt-24 bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8">
        <div class="flex items-start gap-3 mb-6">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black text-white" style="background: {{ $theme }};">1</span>
            <div><h2 class="text-lg font-bold text-gray-900">お知らせの内容</h2><p class="text-sm text-gray-500 mt-1">お客様がひと目で分かるタイトルと本文を入力します。</p></div>
        </div>
        <div class="space-y-6">
            <div>
                <div class="flex items-end justify-between gap-3 mb-2">
                    <label for="noticeTitle" class="text-sm font-semibold text-gray-800">タイトル <span class="text-red-500">必須</span></label>
                    <span id="titleCount" class="text-xs text-gray-400">0 / 255</span>
                </div>
                <input id="noticeTitle" type="text" name="title" value="{{ $titleValue }}" maxlength="255" required placeholder="例：夏季休業のお知らせ"
                       class="w-full rounded-2xl border {{ $errors->has('title') ? 'border-red-300 bg-red-50' : 'border-gray-200' }} px-4 py-3.5 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $theme }};">
            </div>
            <div>
                <div class="flex items-end justify-between gap-3 mb-2">
                    <label for="noticeContent" class="text-sm font-semibold text-gray-800">本文</label>
                    <span id="contentCount" class="text-xs text-gray-400">0 / 10,000</span>
                </div>
                <textarea id="noticeContent" name="content" rows="8" maxlength="10000" placeholder="営業時間の変更、期間、お客様へのお願いなどを入力してください"
                          class="w-full rounded-2xl border {{ $errors->has('content') ? 'border-red-300 bg-red-50' : 'border-gray-200' }} px-4 py-3.5 leading-7 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $theme }};">{{ $contentValue }}</textarea>
                <p class="mt-2 text-xs text-gray-500">改行はお客様側の画面にも反映されます。</p>
            </div>
        </div>
    </section>

    <section id="notice-publish" class="scroll-mt-24 bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8">
        <div class="flex items-start gap-3 mb-6">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black text-white" style="background: {{ $theme }};">2</span>
            <div><h2 class="text-lg font-bold text-gray-900">公開設定</h2><p class="text-sm text-gray-500 mt-1">すぐ公開する場合、日付は空欄のままで構いません。</p></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="startDate" class="block text-sm font-semibold text-gray-800 mb-2">掲載開始日</label>
                <input id="startDate" type="date" name="start_date" value="{{ $startValue }}" class="w-full rounded-2xl border {{ $errors->has('start_date') ? 'border-red-300 bg-red-50' : 'border-gray-200' }} px-4 py-3.5 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $theme }};">
                <p class="mt-2 text-xs text-gray-500">空欄なら保存後すぐに掲載</p>
            </div>
            <div>
                <label for="endDate" class="block text-sm font-semibold text-gray-800 mb-2">掲載終了日</label>
                <input id="endDate" type="date" name="end_date" value="{{ $endValue }}" class="w-full rounded-2xl border {{ $errors->has('end_date') ? 'border-red-300 bg-red-50' : 'border-gray-200' }} px-4 py-3.5 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $theme }};">
                <p class="mt-2 text-xs text-gray-500">空欄なら期限なしで掲載</p>
            </div>
        </div>
        <div id="dateSummary" class="mt-5 rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3 text-sm font-semibold text-blue-800" aria-live="polite"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 px-4 py-4 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ $activeValue ? 'checked' : '' }} class="mt-1 h-5 w-5 rounded border-gray-300">
                <span><span class="block font-semibold text-gray-900">お客様に公開する</span><span class="block mt-1 text-sm text-gray-500">オフにすると期間内でも表示されません。</span></span>
            </label>
            <label class="flex items-start gap-3 rounded-2xl border border-gray-200 px-4 py-4 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="is_important" value="0"><input type="checkbox" name="is_important" value="1" {{ $importantValue ? 'checked' : '' }} class="mt-1 h-5 w-5 rounded border-gray-300">
                <span><span class="block font-semibold text-gray-900">重要なお知らせ</span><span class="block mt-1 text-sm text-gray-500">休業・緊急連絡などを目立たせます。</span></span>
            </label>
        </div>
    </section>

    <section id="notice-image" class="scroll-mt-24 bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8">
        <div class="flex items-start gap-3 mb-6">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black text-white" style="background: {{ $theme }};">3</span>
            <div><h2 class="text-lg font-bold text-gray-900">画像</h2><p class="text-sm text-gray-500 mt-1">画像は自動で向きを補正し、比率を保ったまま最大1600pxへ縮小します。</p></div>
        </div>
        <input id="removeImage" type="hidden" name="remove_image" value="0">
        <input id="noticeImageInput" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only">
        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(260px,0.8fr)] gap-5">
            <label id="imageDropzone" for="noticeImageInput" class="group min-h-52 rounded-3xl border-2 border-dashed border-gray-300 bg-gray-50 px-5 py-8 flex cursor-pointer flex-col items-center justify-center text-center transition hover:border-gray-400 hover:bg-gray-100">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm text-2xl">🖼️</span>
                <span class="mt-4 font-bold text-gray-800">クリックして画像を選択</span>
                <span class="mt-1 text-sm text-gray-500">または、ここへドラッグ＆ドロップ</span>
                <span class="mt-3 text-xs text-gray-400">JPG・PNG・WebP／10MBまで</span>
            </label>
            <div class="rounded-3xl border border-gray-200 bg-white p-3">
                <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-gray-100 flex items-center justify-center">
                    <img id="imagePreview" src="{{ $hasCurrentImage ? asset($notice->image) : '' }}" alt="お知らせ画像のプレビュー" class="{{ $hasCurrentImage ? '' : 'hidden' }} h-full w-full object-contain">
                    <div id="imagePlaceholder" class="{{ $hasCurrentImage ? 'hidden' : '' }} px-5 text-center text-sm text-gray-400">選択した画像をここで確認できます</div>
                </div>
                <div class="px-1 pt-3">
                    <p id="imageFileName" class="truncate text-sm font-semibold text-gray-700">{{ $hasCurrentImage ? '現在登録されている画像' : '画像は選択されていません' }}</p>
                    <p id="imageFileSize" class="mt-1 text-xs text-gray-400">{{ $hasCurrentImage ? '新しい画像を選ぶと差し替わります' : '画像なしでも登録できます' }}</p>
                    <button id="clearImageButton" type="button" class="{{ $hasCurrentImage ? '' : 'hidden' }} mt-3 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-bold text-gray-600 hover:bg-gray-50">{{ $hasCurrentImage ? '画像を削除する' : '選択を取り消す' }}</button>
                </div>
            </div>
        </div>
    </section>

    <div class="sticky bottom-3 z-20 rounded-2xl border border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('company.notices.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">一覧へ戻る</a>
            <button id="noticeSubmitButton" type="submit" class="inline-flex items-center justify-center rounded-xl px-6 py-3 font-bold text-white shadow-sm hover:opacity-90 disabled:cursor-wait disabled:opacity-60" style="background: {{ $theme }};"><span id="noticeSubmitLabel">{{ $editing ? '変更を保存する' : 'お知らせを登録する' }}</span></button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('noticeForm');
    const title = document.getElementById('noticeTitle');
    const content = document.getElementById('noticeContent');
    const titleCount = document.getElementById('titleCount');
    const contentCount = document.getElementById('contentCount');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const dateSummary = document.getElementById('dateSummary');
    const input = document.getElementById('noticeImageInput');
    const dropzone = document.getElementById('imageDropzone');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');
    const fileName = document.getElementById('imageFileName');
    const fileSize = document.getElementById('imageFileSize');
    const clearButton = document.getElementById('clearImageButton');
    const removeImage = document.getElementById('removeImage');
    const submitButton = document.getElementById('noticeSubmitButton');
    const submitLabel = document.getElementById('noticeSubmitLabel');
    const hasCurrentImage = @json($hasCurrentImage);
    let objectUrl = null;

    const updateCounts = () => {
        titleCount.textContent = `${title.value.length} / 255`;
        contentCount.textContent = `${content.value.length.toLocaleString()} / 10,000`;
    };
    const updateDateSummary = () => {
        if (startDate.value && endDate.value && endDate.value < startDate.value) {
            dateSummary.className = 'mt-5 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-sm font-semibold text-red-700';
            dateSummary.textContent = '掲載終了日は、掲載開始日以降の日付を選んでください。';
            return;
        }
        dateSummary.className = 'mt-5 rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3 text-sm font-semibold text-blue-800';
        dateSummary.textContent = `${startDate.value ? startDate.value + 'から' : '保存後すぐに'}、${endDate.value ? endDate.value + 'まで掲載' : '期限なしで掲載'}します。`;
    };
    const showFile = file => {
        if (!file || !file.type.startsWith('image/')) return;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        fileName.textContent = file.name;
        fileSize.textContent = `${(file.size / 1024 / 1024).toFixed(1)} MB・保存時にWebPへ自動変換`;
        clearButton.textContent = '選択を取り消す';
        clearButton.classList.remove('hidden');
        removeImage.value = '0';
    };
    input.addEventListener('change', () => showFile(input.files[0]));
    ['dragenter', 'dragover'].forEach(name => dropzone.addEventListener(name, event => {
        event.preventDefault();
        dropzone.classList.add('border-blue-400', 'bg-blue-50');
    }));
    ['dragleave', 'drop'].forEach(name => dropzone.addEventListener(name, event => {
        event.preventDefault();
        dropzone.classList.remove('border-blue-400', 'bg-blue-50');
    }));
    dropzone.addEventListener('drop', event => {
        const file = event.dataTransfer.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        showFile(file);
    });
    clearButton.addEventListener('click', () => {
        input.value = '';
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        fileName.textContent = '画像は選択されていません';
        fileSize.textContent = '画像なしでも登録できます';
        clearButton.classList.add('hidden');
        removeImage.value = hasCurrentImage ? '1' : '0';
    });
    title.addEventListener('input', updateCounts);
    content.addEventListener('input', updateCounts);
    startDate.addEventListener('change', updateDateSummary);
    endDate.addEventListener('change', updateDateSummary);
    form.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitLabel.textContent = '保存しています…';
    });
    updateCounts();
    updateDateSummary();
});
</script>
