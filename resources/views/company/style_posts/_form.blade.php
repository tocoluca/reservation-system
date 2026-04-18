@php
    $editing = isset($style);
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-[24px] border border-stone-200 bg-stone-50/60 p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-stone-900">基本情報</h2>
                <p class="mt-1 text-sm text-stone-500">
                    予約画面に表示するタイトルやコメントを入力してください。
                </p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-semibold text-stone-800 mb-2">
                        タイトル
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $style->title ?? '') }}"
                           placeholder="例：やわらかいベージュカラー × 外ハネボブ"
                           class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-800 placeholder:text-stone-400 focus:border-transparent focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('title')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="comment" class="block text-sm font-semibold text-stone-800 mb-2">
                        コメント
                    </label>
                    <textarea id="comment"
                              name="comment"
                              rows="7"
                              placeholder="スタイルの特徴やおすすめポイント、似合う雰囲気などを入力してください。"
                              class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm leading-7 text-stone-800 placeholder:text-stone-400 focus:border-transparent focus:outline-none focus:ring-2"
                              style="--tw-ring-color: {{ $theme }};">{{ old('comment', $style->comment ?? '') }}</textarea>
                    @error('comment')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="rounded-[24px] border border-stone-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-stone-900">公開設定</h2>
                <p class="mt-1 text-sm text-stone-500">
                    表示順や公開状態を設定します。
                </p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-stone-800 mb-2">
                        並び順
                    </label>
                    <input type="number"
                           id="sort_order"
                           name="sort_order"
                           value="{{ old('sort_order', $style->sort_order ?? 0) }}"
                           class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-800 focus:border-transparent focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('sort_order')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                    <label class="inline-flex items-start gap-3 cursor-pointer">
                        <input type="checkbox"
                               name="is_public"
                               value="1"
                               class="mt-1 h-4 w-4 rounded border-stone-300"
                               {{ old('is_public', $style->is_public ?? true) ? 'checked' : '' }}>
                        <span>
                            <span class="block text-sm font-semibold text-stone-800">公開する</span>
                            <span class="mt-1 block text-xs leading-6 text-stone-500">
                                チェックを入れると予約画面に表示されます。
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[24px] border border-stone-200 bg-white p-5 sm:p-6">
        <div class="mb-5">
            <h2 class="text-lg font-bold text-stone-900">画像</h2>
            <p class="mt-1 text-sm text-stone-500">
                投稿に表示する写真を設定します。
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <div>
                <label for="image" class="block text-sm font-semibold text-stone-800 mb-2">
                    画像ファイル
                </label>

                <input type="file"
                       id="image"
                       name="image"
                       accept=".jpg,.jpeg,.png,.webp"
                       class="block w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-700 file:mr-4 file:rounded-xl file:border-0 file:bg-stone-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-stone-700 hover:file:bg-stone-200">

                <p class="mt-2 text-xs leading-6 text-stone-500">
                    jpg / jpeg / png / webp に対応しています。
                </p>

                @error('image')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror

                @if($editing && !empty($style->image_path))
                    <label class="mt-4 inline-flex items-center gap-2 text-sm text-stone-700">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-stone-300">
                        現在の画像を削除する
                    </label>
                @endif
            </div>

            <div>
                <div class="text-sm font-semibold text-stone-800 mb-2">プレビュー</div>

                <div class="overflow-hidden rounded-[22px] border border-stone-200 bg-stone-100 aspect-[4/3]">
                    @if($editing && !empty($style->image_path))
                        <img src="{{ asset($style->image_path) }}"
                             alt="{{ $style->title }}"
                             class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-sm text-stone-400">
                            画像を選択してください
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>