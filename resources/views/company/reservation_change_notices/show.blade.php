@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

    $items = $notice->items ?? collect();
    $totalCount = $items->count();
    $pendingCount = $items->whereIn('response_status', ['waiting', 'mail_sent', 'no_response'])->count();
    $confirmedCount = $items->whereIn('response_status', ['closed', 'confirmed', 'phone_confirmed'])->count();

    $phoneCount = $items->filter(fn ($item) => ($item->contact_type ?? '') === 'phone')->count();
    $mailCount = $items->filter(fn ($item) => in_array(($item->contact_type ?? ''), ['mail', 'line+mail'], true))->count();
    $lineCount = $items->filter(fn ($item) => in_array(($item->contact_type ?? ''), ['line', 'line+mail'], true))->count();

    $progress = $totalCount > 0 ? min(100, round(($confirmedCount / $totalCount) * 100)) : 0;
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 md:py-8">

    <div class="mb-6">
        <a href="{{ route('company.reservation_change_notices.index') }}"
           class="group mb-4 flex items-center justify-between gap-4 rounded-[1.5rem] border-2 border-rose-200 bg-gradient-to-r from-white via-rose-50 to-amber-50 px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:px-5"
           style="color: {{ $theme }};">
            <span class="flex min-w-0 items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-white shadow-sm"
                      style="background: linear-gradient(135deg, {{ $theme }}, #be123c);">
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </span>
                <span class="min-w-0 text-left">
                    <span class="block text-[11px] font-bold tracking-[0.16em] text-rose-500">RESERVATION NOTICE</span>
                    <span class="mt-1 block truncate text-sm font-black text-slate-900">予約変更連絡管理へ戻る</span>
                    <span class="mt-0.5 block text-xs text-slate-500">未対応案件の一覧を確認できます</span>
                </span>
            </span>
            <i data-lucide="arrow-up-right" class="h-5 w-5 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5"></i>
        </a>

        <div class="overflow-hidden rounded-3xl border-2 border-slate-200 bg-white shadow-md">
            <div class="h-2" style="background: linear-gradient(90deg, {{ $theme }}, #be123c, #f59e0b);"></div>
            <div class="px-6 py-6 md:px-8 md:py-8">
                <div class="flex flex-col 2xl:flex-row 2xl:items-start 2xl:justify-between gap-6">

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                案件詳細
                            </span>

                            @if($notice->target_date)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    対象日 {{ optional($notice->target_date)->format('Y/m/d') }}
                                </span>
                            @endif

                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $pendingCount > 0 ? 'bg-rose-100 text-rose-700' : 'bg-green-100 text-green-700' }}">
                                {{ $pendingCount > 0 ? '確認待ちあり' : '全件確認済み' }}
                            </span>
                        </div>

                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 break-words">
                            {{ $notice->title }}
                        </h1>

                        @if(!empty($notice->reason_text))
                            <div class="mt-5 rounded-2xl border-2 border-amber-200 bg-amber-50 px-5 py-4">
                                <div class="mb-2 flex items-center gap-2 text-xs font-black text-amber-800">
                                    <i data-lucide="message-square-warning" class="h-4 w-4"></i>
                                    変更理由・案内内容
                                </div>
                                <p class="whitespace-pre-line text-sm font-medium leading-7 text-slate-800">
                                    {{ $notice->reason_text }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="2xl:w-80 shrink-0">
                        <div class="rounded-2xl border-2 border-blue-200 bg-blue-50 p-5 shadow-sm">
                            <div class="mb-3 flex items-center gap-2 text-sm font-black text-blue-950">
                                <i data-lucide="send" class="h-4 w-4"></i>
                                一括通知
                            </div>
                            <form method="POST" action="{{ route('company.reservation_change_notices.send_mails', $notice) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                        style="background: {{ $theme }};">
                                    未送信・未確認へ通知送信
                                </button>
                            </form>
                            <p class="mt-3 text-xs font-medium leading-6 text-blue-800">
                                LINEまたはメールで送信可能な未送信・未確認の顧客へ一括通知します。どちらも使えない顧客は電話対応です。
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-7 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div class="rounded-2xl border-2 border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                        <div class="text-xs font-bold text-slate-600">対象件数</div>
                        <div class="mt-1 text-2xl font-black text-slate-900">{{ $totalCount }}<span class="ml-1 text-sm">件</span></div>
                    </div>

                    <div class="rounded-2xl border-2 border-rose-200 bg-rose-50 px-4 py-4 shadow-sm">
                        <div class="text-xs font-bold text-rose-700">確認待ち</div>
                        <div class="mt-1 text-2xl font-black text-rose-800">{{ $pendingCount }}<span class="ml-1 text-sm">件</span></div>
                    </div>

                    <div class="rounded-2xl border-2 border-green-200 bg-green-50 px-4 py-4 shadow-sm">
                        <div class="text-xs font-bold text-green-700">確認済み</div>
                        <div class="mt-1 text-2xl font-black text-green-800">{{ $confirmedCount }}<span class="ml-1 text-sm">件</span></div>
                    </div>

                    <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-4 shadow-sm">
                        <div class="text-xs font-bold text-emerald-700">LINE対象</div>
                        <div class="mt-1 text-2xl font-black text-emerald-800">{{ $lineCount }}<span class="ml-1 text-sm">件</span></div>
                    </div>

                    <div class="rounded-2xl border-2 border-blue-200 bg-blue-50 px-4 py-4 shadow-sm">
                        <div class="text-xs font-bold text-blue-700">メール対象</div>
                        <div class="mt-1 text-2xl font-black text-blue-800">{{ $mailCount }}<span class="ml-1 text-sm">件</span></div>
                    </div>

                    <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-4 shadow-sm">
                        <div class="text-xs font-bold text-amber-700">電話対応</div>
                        <div class="mt-1 text-2xl font-black text-amber-800">{{ $phoneCount }}<span class="ml-1 text-sm">件</span></div>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                    <div class="mb-3 flex items-center justify-between text-sm font-bold text-slate-700">
                        <span>案件全体の進捗</span>
                        <span class="text-base font-black text-slate-900">{{ $confirmedCount }} / {{ $totalCount }}</span>
                    </div>
                    <div class="h-4 w-full overflow-hidden rounded-full border border-slate-200 bg-white">
                        <div class="h-full rounded-full"
                             style="width: {{ $progress }}%; background: {{ $theme }};"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky z-30 mb-5 rounded-[1.5rem] border-2 border-slate-300 bg-white/95 p-4 shadow-lg backdrop-blur"
         style="top: calc(var(--company-topbar-height, 6rem) + .75rem);">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm font-black text-slate-900">
                    <i data-lucide="list-filter" class="h-4 w-4"></i>
                    連絡対象を絞り込む
                </div>
                <div id="noticeFilterResult" class="mt-1 text-xs font-semibold text-slate-500"></div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" data-notice-filter="pending"
                        class="notice-filter-button rounded-2xl px-3 py-2.5 text-xs font-bold transition">
                    確認待ち {{ $pendingCount }}件
                </button>
                <button type="button" data-notice-filter="done"
                        class="notice-filter-button rounded-2xl px-3 py-2.5 text-xs font-bold transition">
                    確認済み {{ $confirmedCount }}件
                </button>
                <button type="button" data-notice-filter="all"
                        class="notice-filter-button rounded-2xl px-3 py-2.5 text-xs font-bold transition">
                    すべて {{ $totalCount }}件
                </button>
            </div>
        </div>
    </div>

    <div id="noticeItemList" class="space-y-5">
        @forelse($notice->items as $item)
            @php
                $status = $item->response_status ?? 'waiting';

                $statusLabel = match($status) {
                    'closed' => '完了',
                    'confirmed' => '確認済み',
                    'phone_confirmed' => '電話確認済み',
                    'mail_sent' => '通知送信済み',
                    'no_response' => '未返信',
                    default => '確認待ち',
                };

                $statusClass = match($status) {
                    'closed', 'confirmed', 'phone_confirmed' => 'bg-green-100 text-green-700',
                    'mail_sent' => 'bg-blue-100 text-blue-700',
                    'no_response' => 'bg-amber-100 text-amber-700',
                    default => 'bg-rose-100 text-rose-700',
                };

                $contactType = $item->contact_type ?? 'phone';

                $contactLabel = match($contactType) {
                    'line' => 'LINE中心',
                    'mail' => 'メール中心',
                    'line+mail' => 'LINE・メール',
                    default => '電話中心',
                };

                $contactClass = match($contactType) {
                    'line' => 'bg-emerald-100 text-emerald-700',
                    'mail' => 'bg-blue-100 text-blue-700',
                    'line+mail' => 'bg-cyan-100 text-cyan-700',
                    default => 'bg-amber-100 text-amber-700',
                };

                $contactStatus = $item->contact_status ?? 'pending';

                $contactStatusLabel = match($contactStatus) {
                    'line_sent' => 'LINE送信済み',
                    'mail_sent' => 'メール送信済み',
                    'line+mail_sent' => 'LINE・メール送信済み',
                    'phone_pending' => '電話対応待ち',
                    default => '未送信',
                };

                $reservationAt = optional(optional($item->reservation)->start_at)->format('Y/m/d H:i');
                $isStaffNominated = (bool) optional($item->reservation)->is_staff_nominated;
                $staffName = optional(optional($item->reservation)->staff)->name ?: '担当者未設定';
                $confirmedAt = optional($item->confirmed_at)->format('Y/m/d H:i');
                $isDone = in_array($item->response_status, ['closed', 'confirmed', 'phone_confirmed'], true);

                $lineUserId = optional(optional($item->reservation)->customer)->line_user_id;
                $hasLine = !empty($lineUserId);
            @endphp

            <div class="notice-item-card overflow-hidden rounded-3xl border-2 bg-white shadow-md {{ $isDone ? 'border-emerald-200' : 'border-rose-200' }}"
                 data-notice-group="{{ $isDone ? 'done' : 'pending' }}">
                <button type="button"
                        class="notice-item-toggle flex w-full items-center justify-between gap-4 border-b-2 px-5 py-5 text-left transition md:px-6 {{ $isDone ? 'border-emerald-200 bg-emerald-50/70 hover:bg-emerald-50' : 'border-rose-200 bg-rose-50/70 hover:bg-rose-50' }}"
                        aria-expanded="true">
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-lg font-black text-slate-950">{{ $item->customer_name }}</span>
                            @if($isStaffNominated)
                                <span class="inline-flex shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-800">
                                    担当者指名あり
                                </span>
                            @endif
                        </span>
                        <span class="mt-1.5 block text-sm font-semibold text-slate-600">{{ $reservationAt ?: '予約日時未登録' }}・{{ $contactLabel }}</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-2">
                        <span class="hidden rounded-full px-3 py-1.5 text-xs font-black sm:inline-flex {{ $statusClass }}">{{ $statusLabel }}</span>
                        <span class="notice-item-toggle-label rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm">閉じる</span>
                    </span>
                </button>
                <div class="notice-item-body bg-slate-50/70 p-5 md:p-6">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $contactClass }}">
                                    {{ $contactLabel }}
                                </span>

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ $contactStatusLabel }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                                <div class="rounded-2xl border-2 border-slate-200 bg-white px-5 py-5 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="mb-1 flex items-center gap-2 text-xs font-black text-slate-600">
                                                <i data-lucide="user-round" class="h-4 w-4"></i>
                                                顧客・予約情報
                                            </div>
                                            <div class="break-words text-xl font-black text-slate-950">
                                                {{ $item->customer_name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 rounded-2xl border-2 px-4 py-4 {{ $isStaffNominated ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50' }}">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <div class="text-xs font-semibold {{ $isStaffNominated ? 'text-amber-700' : 'text-gray-500' }}">
                                                    予約時の担当者選択
                                                </div>
                                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                                    @if($isStaffNominated)
                                                        <span class="inline-flex rounded-full bg-amber-500 px-2.5 py-1 text-xs font-black text-white">
                                                            担当者指名あり
                                                        </span>
                                                        <span class="font-black text-amber-950">{{ $staffName }}</span>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600">
                                                            指名なし
                                                        </span>
                                                        <span class="font-semibold text-gray-700">現在の担当：{{ $staffName }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="text-xs leading-5 {{ $isStaffNominated ? 'text-amber-700' : 'text-gray-400' }}">
                                                {{ $isStaffNominated ? '顧客が予約時に担当者を選択しています' : '空き状況に応じて担当者が割り当てられています' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div class="text-xs font-bold text-slate-500">予約日時</div>
                                            <div class="mt-1 font-black text-slate-900">{{ $reservationAt ?: '―' }}</div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div class="text-xs font-bold text-slate-500">LINE</div>
                                            <div class="mt-1 font-black {{ $hasLine ? 'text-emerald-700' : 'text-slate-700' }}">{{ $hasLine ? '連携あり' : '未連携' }}</div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div class="text-xs font-bold text-slate-500">メールアドレス</div>
                                            <div class="mt-1 break-all font-semibold text-slate-900">{{ $item->customer_email ?: '未登録' }}</div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                            <div class="text-xs font-bold text-slate-500">電話番号</div>
                                            <div class="mt-1 font-semibold text-slate-900">{{ $item->customer_phone ?: '未登録' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border-2 px-5 py-5 shadow-sm {{ $isDone ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }}">
                                    <div class="mb-4 flex items-center gap-2 text-sm font-black text-slate-900">
                                        <i data-lucide="clipboard-check" class="h-4 w-4"></i>
                                        対応状況・操作
                                    </div>

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                            <div class="text-xs font-bold text-slate-500">確認状況</div>
                                            <div class="mt-1 text-base font-black text-slate-900">{{ $statusLabel }}</div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                            <div class="text-xs font-bold text-slate-500">連絡手段</div>
                                            <div class="mt-1 text-base font-black text-slate-900">{{ $contactLabel }}</div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                            <div class="text-xs font-bold text-slate-500">送信回数</div>
                                            <div class="mt-1 text-base font-black text-slate-900">{{ $item->reminder_send_count ?? 0 }}回</div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                            <div class="text-xs font-bold text-slate-500">確認日時</div>
                                            <div class="mt-1 text-base font-black text-slate-900">{{ $confirmedAt ?: '未確認' }}</div>
                                        </div>
                                    </div>

                                    @if(!$isDone)
                                        <form method="POST"
                                              action="{{ route('company.reservation_change_notices.items.phone_confirmed', $item) }}"
                                              class="mt-4">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full px-4 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                                    style="background: #16a34a;">
                                                電話確認済みにする
                                            </button>
                                        </form>
                                        <p class="mt-3 text-xs font-medium leading-6 text-slate-600">
                                            LINE・メールで返答がなく、電話で確認が取れた場合はこちらで完了扱いにできます。
                                        </p>
                                    @else
                                        <div class="mt-4 rounded-2xl border-2 border-green-200 bg-white px-4 py-3">
                                            <p class="text-sm font-bold text-green-800">
                                                この顧客の確認対応は完了しています。
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 border-t-2 border-slate-200 pt-5">
                        <form method="POST" action="{{ route('company.reservation_change_notices.items.update_note', $item) }}">
                            @csrf
                            <label class="mb-2 flex items-center gap-2 text-sm font-black text-slate-800">
                                <i data-lucide="notebook-pen" class="h-4 w-4"></i>
                                対応メモ
                            </label>
                            <textarea name="note"
                                      rows="4"
                                      class="w-full rounded-2xl border-2 bg-white px-4 py-3 text-sm text-slate-900 shadow-inner focus:outline-none focus:ring-2"
                                      style="border-color: #cbd5e1; --tw-ring-color: {{ $theme }}33;">{{ $item->note }}</textarea>

                            <div class="mt-3 flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-2xl text-white font-semibold shadow hover:opacity-90 transition"
                                        style="background: {{ $theme }};">
                                    メモを保存
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-10 text-center">
                <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-white text-2xl font-bold mb-4"
                     style="background: {{ $theme }};">
                    i
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">対象の顧客がいません</h2>
                <p class="text-sm text-gray-500">
                    この案件にはまだ連絡対象の予約が登録されていません。
                </p>
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = Array.from(document.querySelectorAll('.notice-item-card'));
    const filterButtons = Array.from(document.querySelectorAll('[data-notice-filter]'));
    const result = document.getElementById('noticeFilterResult');
    const defaultFilter = @json($pendingCount > 0 ? 'pending' : 'all');

    const updateToggle = (card, collapsed) => {
        card.classList.toggle('is-collapsed', collapsed);
        card.querySelector('.notice-item-body')?.classList.toggle('hidden', collapsed);
        const button = card.querySelector('.notice-item-toggle');
        const label = card.querySelector('.notice-item-toggle-label');
        button?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        if (label) label.textContent = collapsed ? '詳細を見る' : '閉じる';
    };

    const applyFilter = (filter) => {
        let visibleCount = 0;
        cards.forEach(card => {
            const visible = filter === 'all' || card.dataset.noticeGroup === filter;
            card.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        filterButtons.forEach(button => {
            const active = button.dataset.noticeFilter === filter;
            button.classList.toggle('text-white', active);
            button.classList.toggle('bg-slate-900', active);
            button.classList.toggle('bg-gray-100', !active);
            button.classList.toggle('text-gray-600', !active);
        });

        if (result) result.textContent = `${visibleCount}件を表示中`;
    };

    cards.forEach(card => {
        updateToggle(card, true);
        card.querySelector('.notice-item-toggle')?.addEventListener('click', () => {
            updateToggle(card, !card.classList.contains('is-collapsed'));
        });
    });

    filterButtons.forEach(button => {
        button.addEventListener('click', () => applyFilter(button.dataset.noticeFilter));
    });

    applyFilter(defaultFilter);
    const firstVisibleCard = cards.find(card => !card.classList.contains('hidden'));
    if (firstVisibleCard) updateToggle(firstVisibleCard, false);
});
</script>
@endsection
