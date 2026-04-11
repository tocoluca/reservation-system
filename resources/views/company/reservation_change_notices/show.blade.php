@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

    $items = $notice->items ?? collect();
    $totalCount = $items->count();
    $pendingCount = $items->whereIn('response_status', ['waiting', 'mail_sent', 'no_response'])->count();
    $confirmedCount = $items->whereIn('response_status', ['closed', 'confirmed', 'phone_confirmed'])->count();
    $phoneCount = $items->where('contact_type', 'phone')->count();
    $mailCount = $items->where('contact_type', 'mail')->count();
    $progress = $totalCount > 0 ? min(100, round(($confirmedCount / $totalCount) * 100)) : 0;
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 md:py-8">

    <div class="mb-6">
        <a href="{{ route('company.reservation_change_notices.index') }}"
           class="inline-flex items-center text-sm font-semibold mb-4 hover:opacity-80 transition"
           style="color: {{ $theme }};">
            ← 予約変更連絡管理へ戻る
        </a>

        {{-- 案件ヘッダー --}}
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
                                    未送信・未確認へメール送信
                                </button>
                            </form>
                            <p class="text-xs text-gray-400 mt-3 leading-6">
                                メールアドレスが登録されている未送信・未確認の顧客へ一括送信します。
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-3">
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

                    <div class="rounded-2xl bg-blue-50 px-4 py-4 border border-blue-100">
                        <div class="text-[11px] text-blue-500">メール対象</div>
                        <div class="text-lg font-bold text-blue-700 mt-1">{{ $mailCount }}件</div>
                    </div>

                    <div class="rounded-2xl bg-amber-50 px-4 py-4 border border-amber-100">
                        <div class="text-[11px] text-amber-500">電話対象</div>
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

    @if(session('success'))
        <div class="mb-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- 顧客一覧 --}}
    <div class="space-y-5">
        @forelse($notice->items as $item)
            @php
                $status = $item->response_status ?? 'waiting';

                $statusLabel = match($status) {
                    'closed' => '完了',
                    'confirmed' => '確認済み',
                    'phone_confirmed' => '電話確認済み',
                    'mail_sent' => 'メール送信済み',
                    'no_response' => '未返信',
                    default => '確認待ち',
                };

                $statusClass = match($status) {
                    'closed', 'confirmed', 'phone_confirmed' => 'bg-green-100 text-green-700',
                    'mail_sent' => 'bg-blue-100 text-blue-700',
                    'no_response' => 'bg-amber-100 text-amber-700',
                    default => 'bg-rose-100 text-rose-700',
                };

                $reservationAt = optional($item->reservation->start_at)->format('Y/m/d H:i');
                $confirmedAt = optional($item->confirmed_at)->format('Y/m/d H:i');
                $isDone = in_array($item->response_status, ['closed', 'confirmed', 'phone_confirmed'], true);
            @endphp

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 md:p-6">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ $item->contact_type === 'phone' ? '電話中心' : 'メール中心' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                {{-- 顧客情報 --}}
                                <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs text-gray-500 mb-1">顧客名</div>
                                            <div class="text-lg font-bold text-gray-900 break-words">
                                                {{ $item->customer_name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                        <div class="rounded-xl bg-white border border-gray-100 px-3 py-3">
                                            <div class="text-xs text-gray-500">予約日時</div>
                                            <div class="font-semibold text-gray-800 mt-1">{{ $reservationAt ?: '―' }}</div>
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

                                {{-- 対応状況 --}}
                                <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                                    <div class="text-sm font-bold text-gray-800 mb-3">対応状況</div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="rounded-xl bg-white px-3 py-3 border border-gray-100">
                                            <div class="text-[11px] text-gray-500">確認状況</div>
                                            <div class="text-sm font-bold text-gray-800 mt-1">{{ $statusLabel }}</div>
                                        </div>

                                        <div class="rounded-xl bg-white px-3 py-3 border border-gray-100">
                                            <div class="text-[11px] text-gray-500">リマインド回数</div>
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
                                            電話で確認が取れた場合はこちらで完了扱いにできます。
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

                    {{-- メモ --}}
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
@endsection