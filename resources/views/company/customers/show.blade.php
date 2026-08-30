@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $mainPhoto = $customer->photos->first();
    $latestNote = $customer->notes->sortByDesc('created_at')->first();
    $nextReservation = $customer->reservations
        ->where('status', 'reserved')
        ->filter(fn ($reservation) => $reservation->start_at && \Carbon\Carbon::parse($reservation->start_at)->isFuture())
        ->sortBy('start_at')
        ->first();
    $lastReservation = $customer->reservations
        ->filter(fn ($reservation) => $reservation->start_at)
        ->sortByDesc('start_at')
        ->first();
    $noShowCount = $customer->reservations->where('status', 'no_show')->count();
    $lastRevisitReminderAt = optional($customer->latestRevisitReminderLog)->sent_at;
    $canSendRevisitReminder = $customer->canReceiveMailOrLine();
    $customerReservationUrl = route('company.reservations.index', ['customer_id' => $customer->id]);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- 戻る --}}
    <div class="mb-5 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
        <a href="{{ route('company.customers') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border shadow-sm hover:bg-gray-50 transition text-sm font-semibold"
           style="color: {{ $theme }}; border-color: {{ $theme }}22;">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7" />
            </svg>
            顧客一覧に戻る
        </a>

        <div class="flex flex-col sm:flex-row gap-2">
            <form method="POST"
                  action="{{ route('company.customers.revisit-reminder', $customer->id) }}"
                  data-busy-form="true"
                  data-busy-label="送信中..."
                  onsubmit="return confirm('再来店連絡を送信しますか？');">
                @csrf
                <button type="submit"
                        data-busy-button
                        @disabled(!$canSendRevisitReminder)
                        class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-2xl text-white shadow-sm transition text-sm font-bold {{ $canSendRevisitReminder ? 'hover:opacity-90' : 'opacity-50 cursor-not-allowed' }}"
                        style="background: {{ $theme }};">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span data-busy-text>再来店連絡を送信</span>
                </button>
            </form>

            <a href="{{ $customerReservationUrl }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white border shadow-sm hover:bg-gray-50 transition text-sm font-bold"
               style="color: {{ $theme }}; border-color: {{ $theme }}33;">
                <i data-lucide="list-checks" class="w-4 h-4"></i>
                この顧客の予約一覧
            </a>
        </div>
    </div>

    <div class="mb-6 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex gap-3 overflow-x-auto pb-1 md:grid md:grid-cols-5 md:overflow-visible md:pb-0">
            <div class="min-w-[12rem] rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 md:min-w-0">
                <div class="text-xs font-bold text-emerald-700">次回予約</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $nextReservation ? \Carbon\Carbon::parse($nextReservation->start_at)->format('Y/m/d H:i') : '未定' }}
                </div>
            </div>
            <div class="min-w-[12rem] rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3 md:min-w-0">
                <div class="text-xs font-bold text-gray-500">最終予約</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $lastReservation ? \Carbon\Carbon::parse($lastReservation->start_at)->format('Y/m/d H:i') : '-' }}
                </div>
            </div>
            <div class="min-w-[12rem] rounded-2xl {{ $noShowCount > 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100' }} border px-4 py-3 md:min-w-0">
                <div class="text-xs font-bold {{ $noShowCount > 0 ? 'text-red-700' : 'text-gray-500' }}">注意</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">無断キャンセル {{ number_format($noShowCount) }}回</div>
            </div>
            <div class="min-w-[12rem] rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3 md:min-w-0">
                <div class="text-xs font-bold text-amber-700">最新メモ</div>
                <div class="mt-1 text-sm font-semibold text-gray-900 truncate">
                    {{ $latestNote ? \Illuminate\Support\Str::limit($latestNote->note, 42) : '未登録' }}
                </div>
            </div>
            <div class="min-w-[12rem] rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3 md:min-w-0">
                <div class="text-xs font-bold text-blue-700">再来店連絡</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $lastRevisitReminderAt ? $lastRevisitReminderAt->format('Y-m-d') : '未送信' }}
                </div>
            </div>
        </div>
    </div>

    @unless($canSendRevisitReminder)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            再来店連絡を送信するには、送信可能なメールアドレスまたはLINE連携が必要です。
        </div>
    @endunless

    <nav class="sticky z-30 mb-6 rounded-[1.5rem] border border-white/80 bg-white/95 p-2 shadow-lg backdrop-blur"
         style="top: calc(var(--company-topbar-height, 6rem) + .75rem);"
         aria-label="顧客カルテ内メニュー">
        <div class="grid grid-cols-3 gap-2">
            <a href="#profile"
               class="inline-flex min-w-0 items-center justify-center gap-2 rounded-2xl bg-slate-100 px-3 py-3 text-xs font-black text-slate-700 transition hover:bg-slate-200 sm:text-sm">
                <i data-lucide="user-round" class="h-4 w-4 shrink-0"></i>
                <span class="truncate">基本情報</span>
            </a>
            <a href="#notes"
               class="inline-flex min-w-0 items-center justify-center gap-2 rounded-2xl bg-amber-50 px-3 py-3 text-xs font-black text-amber-800 transition hover:bg-amber-100 sm:text-sm">
                <i data-lucide="notebook-pen" class="h-4 w-4 shrink-0"></i>
                <span class="truncate">メモ {{ $customer->notes->count() }}</span>
            </a>
            <a href="#photos"
               class="inline-flex min-w-0 items-center justify-center gap-2 rounded-2xl px-3 py-3 text-xs font-black text-white shadow-sm transition hover:opacity-90 sm:text-sm"
               style="background: {{ $theme }};">
                <i data-lucide="images" class="h-4 w-4 shrink-0"></i>
                <span class="truncate">写真 {{ $customer->photos->count() }}</span>
            </a>
        </div>
    </nav>

    {{-- プロフィール --}}
    <div id="overview" class="relative scroll-mt-44 overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_240px] gap-6 items-center">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        CUSTOMER PROFILE
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-white tracking-tight">
                        {{ $customer->name }}
                    </h1>

                    <p class="mt-2 text-sm text-white/85">
                        顧客情報とメモ、写真をまとめて管理できます。
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mt-6">
                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">電話番号</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900 break-all">
                                {{ $customer->phone ?: '-' }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">来店回数</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">
                                {{ number_format($customer->visit_count) }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">最終来店</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                @if($customer->last_visit)
                                    {{ \Carbon\Carbon::parse($customer->last_visit)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">メール</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900 break-all">
                                {{ $customer->email ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center lg:justify-end">
                    <div class="w-full max-w-[240px]">
                        <div class="rounded-[1.75rem] bg-white p-4 shadow-sm border border-amber-100">
                            <div class="aspect-square overflow-hidden rounded-[1.5rem] bg-gray-100">
                                <img
                                    src="{{ $mainPhoto && $mainPhoto->path ? asset($mainPhoto->path) : asset('images/noimage.png') }}"
                                    alt="{{ $customer->name }}"
                                    class="w-full h-full object-contain">
                            </div>
                            <a href="#photos" class="mt-3 flex items-center justify-center gap-2 text-sm font-bold hover:opacity-75" style="color: {{ $theme }};">
                                <i data-lucide="images" class="h-4 w-4"></i>
                                写真 {{ $customer->photos->count() }}件を確認
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 下段 --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- 基本情報編集 --}}
        <div id="profile" class="order-2 scroll-mt-44 bg-white shadow-sm rounded-[1.75rem] border border-gray-100 overflow-hidden">
            <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-bold text-gray-900">顧客基本情報</h2>
                <p class="text-sm text-gray-500 mt-1">
                    顧客名、電話番号、メールアドレスを変更できます。
                </p>
            </div>

            <div class="p-5 sm:p-6">
                <form method="POST" action="{{ route('company.customers.profile',$customer->id) }}" data-customer-section="profile">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">顧客名</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $customer->name) }}"
                                   class="w-full border border-gray-300 rounded-2xl p-3 text-sm focus:outline-none focus:ring-4 focus:border-transparent"
                                   style="--tw-ring-color: {{ $theme }}22;">
                            @error('name')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">電話番号</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $customer->phone) }}"
                                   class="w-full border border-gray-300 rounded-2xl p-3 text-sm focus:outline-none focus:ring-4 focus:border-transparent"
                                   style="--tw-ring-color: {{ $theme }}22;">
                            @error('phone')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">メールアドレス</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $customer->email) }}"
                                   class="w-full border border-gray-300 rounded-2xl p-3 text-sm focus:outline-none focus:ring-4 focus:border-transparent"
                                   style="--tw-ring-color: {{ $theme }}22;">
                            @error('email')
                                <p class="text-sm text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                                style="background: {{ $theme }}"
                                class="text-white px-6 py-3 rounded-2xl font-semibold shadow-sm hover:opacity-90 transition">
                            保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- メモ --}}
        <div id="notes" class="order-3 scroll-mt-44 bg-white shadow-sm rounded-[1.75rem] border border-gray-100 overflow-hidden">
            <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-bold text-gray-900">顧客メモ</h2>
                <p class="text-sm text-gray-500 mt-1">
                    カウンセリング内容や対応履歴などを記録できます。
                </p>
            </div>

            <div class="p-5 sm:p-6">
                <form method="POST" action="{{ route('company.customers.note',$customer->id) }}" data-customer-section="notes">
                    @csrf

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        新しいメモを追加
                    </label>

                    <textarea
                        name="note"
                        rows="4"
                        class="w-full border border-gray-300 rounded-2xl p-4 text-sm focus:outline-none focus:ring-4 focus:border-transparent"
                        style="--tw-ring-color: {{ $theme }}22;"
                        placeholder="メモを入力してください"></textarea>

                    @error('note')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-4">
                        <button
                            type="submit"
                            style="background: {{ $theme }}"
                            class="text-white px-6 py-3 rounded-2xl font-semibold shadow-sm hover:opacity-90 transition">
                            保存する
                        </button>
                    </div>
                </form>

                <div class="mt-6 space-y-3">
                    @forelse($customer->notes as $note)
                        <div class="border border-gray-100 rounded-2xl p-4 bg-gray-50/80 flex justify-between items-start gap-4">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-800 whitespace-pre-line break-words">
                                    {{ $note->note }}
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ $note->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            <form method="POST"
                                  action="{{ route('company.customers.note.delete', $note->id) }}"
                                  onsubmit="return confirm('削除しますか？')"
                                  class="shrink-0"
                                  data-customer-section="notes">
                                @csrf
                                @method('DELETE')

                                <button class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-red-100 text-red-500 text-sm font-semibold hover:bg-red-50 transition">
                                    削除
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-gray-50 border border-dashed border-gray-200 p-6 text-center">
                            <div class="text-sm font-semibold text-gray-600">メモはまだありません</div>
                            <div class="text-xs text-gray-400 mt-1">必要な内容を追加するとここに表示されます。</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 写真 --}}
        <div id="photos" class="order-1 scroll-mt-44 bg-white shadow-sm rounded-[1.75rem] border border-gray-100 overflow-hidden xl:col-span-2">
            <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-bold text-gray-900">顧客写真</h2>
                <p class="text-sm text-gray-500 mt-1">
                    施術記録やスタイル履歴の写真を管理できます。
                </p>
            </div>

            <div class="p-5 sm:p-6">
                @error('photo')
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert">
                        {{ $message }}
                    </div>
                @enderror

                <form
                    method="POST"
                    action="{{ route('company.customers.photo',$customer->id) }}"
                    enctype="multipart/form-data"
                    class="grid gap-5 rounded-3xl border border-stone-200 bg-stone-50/70 p-4 sm:p-5 lg:grid-cols-[280px_1fr]"
                    data-photo-upload-form
                    data-customer-section="photos">
                    @csrf

                    <div class="mx-auto w-full max-w-[280px]">
                        <div class="relative aspect-square overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                            <div class="absolute inset-0 flex flex-col items-center justify-center px-5 text-center text-stone-400"
                                 data-photo-placeholder>
                                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100">
                                    <i data-lucide="image" class="h-7 w-7"></i>
                                </span>
                                <span class="mt-3 text-sm font-bold text-stone-600">選択した写真を確認</span>
                                <span class="mt-1 text-xs leading-5">ここにプレビューされます</span>
                            </div>
                            <img src=""
                                 alt="アップロードする写真のプレビュー"
                                 class="hidden h-full w-full object-contain"
                                 data-photo-preview>
                        </div>
                    </div>

                    <div class="flex min-w-0 flex-col justify-center">
                        <input id="customer-photo-input"
                               type="file"
                               name="photo"
                               accept="image/jpeg,image/png,image/webp"
                               class="sr-only"
                               data-photo-input>

                        <label for="customer-photo-input"
                               class="flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-stone-300 bg-white px-5 py-6 text-center transition hover:border-stone-400 hover:bg-stone-50"
                               data-photo-drop-zone>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-sm"
                                  style="background: {{ $theme }};">
                                <i data-lucide="image-plus" class="h-6 w-6"></i>
                            </span>
                            <span class="mt-3 text-sm font-black text-stone-800">写真を選ぶ</span>
                            <span class="mt-1 text-xs leading-5 text-stone-500">クリックして選択、またはここに写真をドロップ</span>
                        </label>

                        <div class="mt-3 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
                             role="alert"
                             data-photo-client-error></div>

                        <div class="mt-3 hidden rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3" data-photo-file-info>
                            <div class="flex items-start gap-3">
                                <i data-lucide="circle-check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"></i>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-emerald-900" data-photo-file-name></p>
                                    <p class="mt-0.5 text-xs text-emerald-700" data-photo-file-meta></p>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-stone-500">
                            JPG・PNG・WebP／10MBまで。写真の向きを自動補正し、縦横比を保ったまま軽量化します。
                        </p>

                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <button type="button"
                                    class="hidden min-h-11 items-center justify-center gap-2 rounded-2xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-stone-600 transition hover:bg-stone-100"
                                    data-photo-clear>
                                <i data-lucide="x" class="h-4 w-4"></i>
                                選び直す
                            </button>
                            <button type="submit"
                                    disabled
                                    style="background: {{ $theme }}"
                                    class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-2xl px-6 py-2.5 text-sm font-black text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                                    data-photo-submit>
                                <i data-lucide="upload" class="h-4 w-4"></i>
                                この写真を登録
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-8 flex items-end justify-between gap-4">
                    <div>
                        <h3 class="font-black text-gray-900">登録済み写真</h3>
                        <p class="mt-1 text-xs text-gray-500">新しい写真から順に表示しています。</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-stone-100 px-3 py-1.5 text-xs font-bold text-stone-600">
                        {{ $customer->photos->count() }}件
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @forelse($customer->photos as $photo)
                        <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <button type="button"
                                    class="block aspect-square w-full overflow-hidden bg-stone-100 focus:outline-none focus:ring-4"
                                    style="--tw-ring-color: {{ $theme }}33;"
                                    data-photo-lightbox
                                    data-photo-src="{{ asset($photo->path) }}"
                                    data-photo-label="{{ $photo->created_at->format('Y/m/d H:i') }}に登録した写真">
                                <img
                                    src="{{ asset($photo->path) }}"
                                    alt="{{ $photo->created_at->format('Y/m/d') }}に登録した顧客写真"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                            </button>

                            <div class="flex items-center justify-between gap-2 px-3 py-2.5">
                                <time datetime="{{ $photo->created_at->toIso8601String() }}" class="truncate text-[11px] font-semibold text-stone-500">
                                    {{ $photo->created_at->format('Y/m/d H:i') }}
                                </time>
                                <form method="POST"
                                      action="{{ route('company.customers.photo.delete', $photo->id) }}"
                                      onsubmit="return confirm('この写真を削除しますか？')"
                                      data-customer-section="photos">
                                    @csrf
                                    @method('DELETE')

                                    <button class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-red-50 text-red-500 transition hover:bg-red-100"
                                            aria-label="{{ $photo->created_at->format('Y/m/d H:i') }}の写真を削除">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl bg-gray-50 border border-dashed border-gray-200 p-8 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-stone-400 shadow-sm">
                                <i data-lucide="images" class="h-6 w-6"></i>
                            </span>
                            <div class="mt-3 text-sm font-semibold text-gray-600">写真はまだありません</div>
                            <div class="text-xs text-gray-400 mt-1">上の「写真を選ぶ」から最初の写真を登録できます。</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

<div class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/85 p-4 backdrop-blur-sm"
     role="dialog"
     aria-modal="true"
     aria-label="顧客写真の拡大表示"
     data-photo-modal>
    <button type="button"
            class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25"
            aria-label="拡大表示を閉じる"
            data-photo-modal-close>
        <i data-lucide="x" class="h-6 w-6"></i>
    </button>
    <div class="flex max-h-full max-w-5xl flex-col items-center gap-3">
        <img src="" alt="" class="max-h-[80vh] max-w-full rounded-2xl object-contain shadow-2xl" data-photo-modal-image>
        <p class="text-sm font-semibold text-white/85" data-photo-modal-caption></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const errorSection = @js(
        $errors->has('photo') ? 'photos' :
        ($errors->has('note') ? 'notes' :
        (($errors->has('name') || $errors->has('phone') || $errors->has('email')) ? 'profile' : null))
    );

    if (errorSection) {
        window.requestAnimationFrame(() => {
            document.getElementById(errorSection)?.scrollIntoView({ block: 'start' });
        });
    }

    const uploadForm = document.querySelector('[data-photo-upload-form]');
    const photoInput = uploadForm?.querySelector('[data-photo-input]');
    const preview = uploadForm?.querySelector('[data-photo-preview]');
    const placeholder = uploadForm?.querySelector('[data-photo-placeholder]');
    const fileInfo = uploadForm?.querySelector('[data-photo-file-info]');
    const fileName = uploadForm?.querySelector('[data-photo-file-name]');
    const fileMeta = uploadForm?.querySelector('[data-photo-file-meta]');
    const clientError = uploadForm?.querySelector('[data-photo-client-error]');
    const submitButton = uploadForm?.querySelector('[data-photo-submit]');
    const clearButton = uploadForm?.querySelector('[data-photo-clear]');
    const dropZone = uploadForm?.querySelector('[data-photo-drop-zone]');
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxFileSize = 10 * 1024 * 1024;
    let previewUrl = null;

    const showPhotoError = message => {
        if (!clientError) return;
        clientError.textContent = message;
        clientError.classList.remove('hidden');
    };

    const clearPhotoError = () => {
        if (!clientError) return;
        clientError.textContent = '';
        clientError.classList.add('hidden');
    };

    const resetPhoto = () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        if (photoInput) photoInput.value = '';
        if (preview) {
            preview.src = '';
            preview.classList.add('hidden');
        }
        placeholder?.classList.remove('hidden');
        fileInfo?.classList.add('hidden');
        clearButton?.classList.add('hidden');
        clearButton?.classList.remove('inline-flex');
        if (submitButton) submitButton.disabled = true;
        clearPhotoError();
    };

    const selectPhoto = file => {
        clearPhotoError();

        if (!file) {
            resetPhoto();
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            resetPhoto();
            showPhotoError('JPG・PNG・WebP形式の写真を選択してください。');
            return;
        }

        if (file.size > maxFileSize) {
            resetPhoto();
            showPhotoError('写真は10MB以内で選択してください。');
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        if (preview) {
            preview.src = previewUrl;
            preview.classList.remove('hidden');
        }
        placeholder?.classList.add('hidden');
        if (fileName) fileName.textContent = file.name;
        if (fileMeta) fileMeta.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
        fileInfo?.classList.remove('hidden');
        clearButton?.classList.remove('hidden');
        clearButton?.classList.add('inline-flex');
        if (submitButton) submitButton.disabled = false;
    };

    photoInput?.addEventListener('change', () => selectPhoto(photoInput.files?.[0]));
    clearButton?.addEventListener('click', resetPhoto);

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone?.addEventListener(eventName, event => {
            event.preventDefault();
            dropZone.classList.add('border-slate-500', 'bg-slate-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone?.addEventListener(eventName, event => {
            event.preventDefault();
            dropZone.classList.remove('border-slate-500', 'bg-slate-50');
        });
    });

    dropZone?.addEventListener('drop', event => {
        const file = event.dataTransfer?.files?.[0];
        if (!file || !photoInput) return;
        const transfer = new DataTransfer();
        transfer.items.add(file);
        photoInput.files = transfer.files;
        selectPhoto(file);
    });

    uploadForm?.addEventListener('submit', () => {
        if (!submitButton) return;
        submitButton.disabled = true;
        submitButton.textContent = '登録中…';
    });

    const modal = document.querySelector('[data-photo-modal]');
    const modalImage = modal?.querySelector('[data-photo-modal-image]');
    const modalCaption = modal?.querySelector('[data-photo-modal-caption]');
    const modalClose = modal?.querySelector('[data-photo-modal-close]');

    const closeModal = () => {
        modal?.classList.add('hidden');
        modal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        if (modalImage) modalImage.src = '';
    };

    document.querySelectorAll('[data-photo-lightbox]').forEach(button => {
        button.addEventListener('click', () => {
            if (!modal || !modalImage) return;
            modalImage.src = button.dataset.photoSrc;
            modalImage.alt = button.dataset.photoLabel || '顧客写真';
            if (modalCaption) modalCaption.textContent = button.dataset.photoLabel || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            modalClose?.focus();
        });
    });

    modalClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal?.classList.contains('hidden')) closeModal();
    });
});
</script>

@endsection
