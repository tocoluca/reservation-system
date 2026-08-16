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
           class="group flex items-center justify-between gap-4 rounded-[1.5rem] border border-rose-100 bg-gradient-to-r from-white via-rose-50/60 to-amber-50/60 px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:px-5"
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

        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
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
                            <div class="mt-4 rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                                <div class="text-xs font-semibold text-gray-500 mb-2">変更理由・案内内容</div>
                                <p class="text-sm leading-7 text-gray-700 whitespace-pre-line">
                                    {{ $notice->reason_text }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="2xl:w-80 shrink-0">
                        <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                            <form method="POST" action="{{ route('company.reservation_change_notices.send_mails', $notice) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                        style="background: {{ $theme }};">
                                    未送信・未確認へ通知送信
                                </button>
                            </form>
                            <p class="text-xs text-gray-400 mt-3 leading-6">
                                LINEまたはメールで送信可能な未送信・未確認の顧客へ一括通知します。どちらも使えない顧客は電話対応です。
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div class="rounded-2xl bg-gray-50 px-4 py-4 border border-gray-100">
                        <div class="text-[11px] text-gray-500">対象件数</div>
                        <div class="text-lg font-bold text-gray-800 mt-1">{{ $totalCount }}件</div>
                    </div>

                    <div class="rounded-2xl bg-rose-50 px-4 py-4 border border-rose-100">
                        <div class="text-[11px] text-rose-500">確認待ち</div>
                        <div class="text-lg font-bold text-rose-700 mt-1">{{ $pendingCount }}件</div>
                    </div>

                    <div class="rounded-2xl bg-green-50 px-4 py-4 border border-green-100">
                        <div class="text-[11px] text-green-500">確認済み</div>
                        <div class="text-lg font-bold text-green-700 mt-1">{{ $confirmedCount }}件</div>
                    </div>

                    <div class="rounded-2xl bg-emerald-50 px-4 py-4 border border-emerald-100">
                        <div class="text-[11px] text-emerald-500">LINE対象</div>
                        <div class="text-lg font-bold text-emerald-700 mt-1">{{ $lineCount }}件</div>
                    </div>

                    <div class="rounded-2xl bg-blue-50 px-4 py-4 border border-blue-100">
                        <div class="text-[11px] text-blue-500">メール対象</div>
                        <div class="text-lg font-bold text-blue-700 mt-1">{{ $mailCount }}件</div>
                    </div>

                    <div class="rounded-2xl bg-amber-50 px-4 py-4 border border-amber-100">
                        <div class="text-[11px] text-amber-500">電話対応</div>
                        <div class="text-lg font-bold text-amber-700 mt-1">{{ $phoneCount }}件</div>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                        <span>案件全体の進捗</span>
                        <span>{{ $confirmedCount }} / {{ $totalCount }}</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full"
                             style="width: {{ $progress }}%; background: {{ $theme }};"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky z-30 mb-5 rounded-[1.5rem] border border-white/80 bg-white/95 p-3 shadow-lg backdrop-blur"
         style="top: calc(var(--company-topbar-height, 6rem) + .75rem);">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-black text-gray-900">連絡対象を絞り込む</div>
                <div id="noticeFilterResult" class="mt-1 text-xs text-gray-500"></div>
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

                $reservationAt = optional($item->reservation->start_at)->format('Y/m/d H:i');
                $confirmedAt = optional($item->confirmed_at)->format('Y/m/d H:i');
                $isDone = in_array($item->response_status, ['closed', 'confirmed', 'phone_confirmed'], true);

                $lineUserId = optional(optional($item->reservation)->customer)->line_user_id;
                $hasLine = !empty($lineUserId);
            @endphp

            <div class="notice-item-card bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
                 data-notice-group="{{ $isDone ? 'done' : 'pending' }}">
                <button type="button"
                        class="notice-item-toggle flex w-full items-center justify-between gap-4 border-b border-gray-100 bg-white px-5 py-4 text-left hover:bg-gray-50 md:px-6"
                        aria-expanded="true">
                    <span class="min-w-0">
                        <span class="block truncate font-black text-gray-900">{{ $item->customer_name }}</span>
                        <span class="mt-1 block text-xs text-gray-500">{{ $reservationAt ?: '予約日時未登録' }}・{{ $statusLabel }}</span>
                    </span>
                    <span class="notice-item-toggle-label shrink-0 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600">閉じる</span>
                </button>
                <div class="notice-item-body p-5 md:p-6">
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

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs text-gray-500 mb-1">顧客名</div>
                                            <div class="text-lg font-bold text-gray-900 break-words">
                                                {{ $item->customer_name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                                        <div class="rounded-xl bg-white border border-gray-100 px-3 py-3">
                                            <div class="text-xs text-gray-500">予約日時</div>
                                            <div class="font-semibold text-gray-800 mt-1">{{ $reservationAt ?: '―' }}</div>
                                        </div>

                                        <div class="rounded-xl bg-white border border-gray-100 px-3 py-3">
                                            <div class="text-xs text-gray-500">LINE</div>
                                            <div class="font-semibold text-gray-800 mt-1">{{ $hasLine ? '連携あり' : '未連携' }}</div>
                                        </div>

                                        <div class="rounded-xl bg-white border border-gray-100 px-3 py-3">
                                            <div class="text-xs text-gray-500">メールアドレス</div>
                                            <div class="font-semibold text-gray-800 mt-1 break-all">{{ $item->customer_email ?: '未登録' }}</div>
                                        </div>

                                        <div class="rounded-xl bg-white border border-gray-100 px-3 py-3">
                                            <div class="text-xs text-gray-500">電話番号</div>
                                            <div class="font-semibold text-gray-800 mt-1">{{ $item->customer_phone ?: '未登録' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                                    <div class="text-sm font-bold text-gray-800 mb-3">対応状況</div>

                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                        <div class="rounded-xl bg-white px-3 py-3 border border-gray-100">
                                            <div class="text-[11px] text-gray-500">確認状況</div>
                                            <div class="text-sm font-bold text-gray-800 mt-1">{{ $statusLabel }}</div>
                                        </div>

                                        <div class="rounded-xl bg-white px-3 py-3 border border-gray-100">
                                            <div class="text-[11px] text-gray-500">連絡手段</div>
                                            <div class="text-sm font-bold text-gray-800 mt-1">{{ $contactLabel }}</div>
                                        </div>

                                        <div class="rounded-xl bg-white px-3 py-3 border border-gray-100">
                                            <div class="text-[11px] text-gray-500">送信回数</div>
                                            <div class="text-sm font-bold text-gray-800 mt-1">{{ $item->reminder_send_count ?? 0 }}回</div>
                                        </div>

                                        <div class="rounded-xl bg-white px-3 py-3 border border-gray-100">
                                            <div class="text-[11px] text-gray-500">確認日時</div>
                                            <div class="text-sm font-bold text-gray-800 mt-1">{{ $confirmedAt ?: '未確認' }}</div>
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
                                        <p class="text-xs text-gray-400 mt-2 leading-6">
                                            LINE・メールで返答がなく、電話で確認が取れた場合はこちらで完了扱いにできます。
                                        </p>
                                    @else
                                        <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3">
                                            <p class="text-sm font-semibold text-green-700">
                                                この顧客の確認対応は完了しています。
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <form method="POST" action="{{ route('company.reservation_change_notices.items.update_note', $item) }}">
                            @csrf
                            <label class="block text-sm font-bold text-gray-700 mb-2">対応メモ</label>
                            <textarea name="note"
                                      rows="4"
                                      class="w-full border rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2"
                                      style="border-color: #d1d5db; --tw-ring-color: {{ $theme }}33;">{{ $item->note }}</textarea>

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
