@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 md:py-8">

    {{-- ヘッダー --}}
    <div class="rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-white mb-8">
        <div class="px-6 py-7 md:px-8 md:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-white/80">
                        Reservation Change Notice
                    </p>

                    <h1 class="text-2xl md:text-3xl font-bold mt-2">
                        予約変更連絡管理
                    </h1>

                    <p class="text-sm md:text-base text-white/85 mt-3 leading-7">
                        対応が必要な案件を上に表示しています。赤は要対応、緑は確認完了です。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/10 border border-white/20 text-white font-semibold hover:bg-white/20 transition">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 bg-slate-50 px-6 py-5 md:px-8">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border-2 border-rose-300 bg-rose-50 px-5 py-4 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-black text-rose-700">
                        <i data-lucide="circle-alert" class="h-4 w-4"></i>
                        対応が必要な案件
                    </div>
                    <div class="mt-1 text-3xl font-black text-rose-800">
                        {{ number_format($pendingNoticeCount ?? 0) }}<span class="ml-1 text-sm">件</span>
                    </div>
                    <div class="mt-1 text-xs font-semibold text-rose-700">優先して確認してください</div>
                </div>

                <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-black text-emerald-700">
                        <i data-lucide="circle-check" class="h-4 w-4"></i>
                        確認完了した案件
                    </div>
                    <div class="mt-1 text-3xl font-black text-emerald-800">
                        {{ number_format($completedNoticeCount ?? 0) }}<span class="ml-1 text-sm">件</span>
                    </div>
                    <div class="mt-1 text-xs font-semibold text-emerald-700">すべての対象者へ対応済み</div>
                </div>

                <div class="rounded-2xl border-2 border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <div class="text-xs font-bold text-slate-500">対象予約数</div>
                    <div class="mt-1 text-3xl font-black text-slate-900">
                        {{ number_format($targetReservationCount ?? 0) }}<span class="ml-1 text-sm">件</span>
                    </div>
                    <div class="mt-1 text-xs font-semibold text-slate-500">重複を除いた予約数</div>
                </div>

                <div class="rounded-2xl border-2 border-blue-200 bg-blue-50 px-5 py-4 shadow-sm">
                    <div class="text-xs font-bold text-blue-700">対応の流れ</div>
                    <div class="mt-2 text-sm font-black leading-6 text-blue-950">
                        対象抽出 → 通知 → 確認 → 完了
                    </div>
                    <div class="mt-1 text-xs font-semibold text-blue-700">店舗都合キャンセルなどの連絡</div>
                </div>
            </div>
        </div>
    </div>

    {{-- メッセージ --}}
    @if (session('success'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm shadow-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border px-4 py-3 text-sm shadow-sm"
             style="background-color: #fef2f2; border-color: #fecaca; color: #b91c1c;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm shadow-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>・{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($notices->count())
        <div class="mb-5 flex flex-col gap-3 rounded-2xl border-2 border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-sm font-black text-slate-900">対応状況の見方</div>
                <div class="mt-1 text-xs font-semibold text-slate-500">対応が必要な案件を先頭に表示しています。</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border-2 border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-800">
                    <i data-lucide="circle-alert" class="h-4 w-4"></i>
                    赤：対応が必要
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border-2 border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-800">
                    <i data-lucide="circle-check" class="h-4 w-4"></i>
                    緑：確認完了
                </span>
            </div>
        </div>

        <div class="space-y-5">
            @foreach($notices as $notice)
                @php
                    $pending = (int) ($notice->pending_count ?? 0);
                    $confirmed = (int) ($notice->confirmed_count ?? 0);
                    $total = (int) ($notice->items_count ?? 0);
                    $progress = $total > 0 ? min(100, round(($confirmed / $total) * 100)) : 0;
                    $isUrgent = $pending > 0;
                @endphp

                <div class="overflow-hidden rounded-3xl border-2 shadow-md transition hover:-translate-y-0.5 hover:shadow-lg
                    {{ $isUrgent ? 'border-rose-300 bg-rose-50/40' : 'border-emerald-200 bg-emerald-50/30' }}">
                    <div class="flex flex-col gap-2 border-b-2 px-5 py-3 sm:flex-row sm:items-center sm:justify-between md:px-6
                        {{ $isUrgent ? 'border-rose-300 bg-rose-100' : 'border-emerald-200 bg-emerald-100' }}">
                        <div class="flex items-center gap-2 text-sm font-black {{ $isUrgent ? 'text-rose-900' : 'text-emerald-900' }}">
                            <i data-lucide="{{ $isUrgent ? 'circle-alert' : 'circle-check' }}" class="h-5 w-5"></i>
                            {{ $isUrgent ? '対応が必要な案件です' : 'この案件は確認完了しています' }}
                        </div>
                        <div class="text-xs font-bold {{ $isUrgent ? 'text-rose-700' : 'text-emerald-700' }}">
                            {{ $isUrgent ? '未対応 ' . number_format($pending) . '件' : '対応済み ' . number_format($confirmed) . '件' }}
                        </div>
                    </div>
                    <div class="p-5 md:p-6">
                        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-4">
                                    <span class="inline-flex items-center rounded-full border-2 px-3 py-1 text-xs font-black
                                        {{ $isUrgent ? 'border-rose-300 bg-white text-rose-800' : 'border-emerald-200 bg-white text-emerald-800' }}">
                                        {{ $isUrgent ? '要対応' : '確認完了' }}
                                    </span>

                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        作成日 {{ $notice->created_at->format('Y/m/d') }}
                                    </span>

                                    @if(!empty($notice->target_date))
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            対象日 {{ \Carbon\Carbon::parse($notice->target_date)->format('Y/m/d') }}
                                        </span>
                                    @endif

                                </div>

                                <h2 class="break-words text-lg font-black leading-8 text-slate-950 md:text-xl">
                                    {{ $notice->title }}
                                </h2>

                                @if(!empty($notice->reason_text))
                                    <p class="mt-3 break-words text-sm font-medium leading-7 text-slate-600">
                                        {{ $notice->reason_text }}
                                    </p>
                                @endif

                                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl border-2 border-slate-200 bg-white px-4 py-4 shadow-sm">
                                        <div class="text-xs font-bold text-slate-500">対象件数</div>
                                        <div class="mt-1 text-2xl font-black text-slate-900">
                                            {{ $total }}
                                            <span class="text-xs font-medium text-gray-400">件</span>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-4 shadow-sm">
                                        <div class="text-xs font-bold text-emerald-700">確認済み</div>
                                        <div class="mt-1 text-2xl font-black text-emerald-800">
                                            {{ $confirmed }}
                                            <span class="text-xs font-medium text-gray-400">件</span>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border-2 px-4 py-4 shadow-sm {{ $isUrgent ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-white' }}">
                                        <div class="text-xs font-bold {{ $isUrgent ? 'text-rose-700' : 'text-slate-500' }}">確認待ち</div>
                                        <div class="mt-1 text-2xl font-black {{ $pending > 0 ? 'text-rose-800' : 'text-slate-800' }}">
                                            {{ $pending }}
                                            <span class="text-xs font-medium text-gray-400">件</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                        <span>対応進捗</span>
                                        <span>{{ $confirmed }} / {{ $total }}</span>
                                    </div>

                                    <div class="h-4 w-full overflow-hidden rounded-full border border-slate-200 bg-white">
                                        <div class="h-full rounded-full transition-all duration-300"
                                             style="width: {{ $progress }}%; background: {{ $isUrgent ? '#e11d48' : '#059669' }};"></div>
                                    </div>
                                </div>

                                @if($pending > 0)
                                    <div class="mt-4 rounded-2xl border-2 border-rose-300 bg-rose-100 px-4 py-3">
                                        <p class="flex items-center gap-2 text-sm font-black text-rose-900">
                                            <i data-lucide="bell-ring" class="h-4 w-4 shrink-0"></i>
                                            まだ {{ $pending }} 件の確認待ちがあります。優先して確認してください。
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="xl:w-56 shrink-0">
                                <div class="rounded-2xl border-2 bg-white p-4 shadow-sm {{ $isUrgent ? 'border-rose-300' : 'border-emerald-200' }}">
                                    <p class="mb-2 text-xs font-black {{ $isUrgent ? 'text-rose-700' : 'text-emerald-700' }}">{{ $isUrgent ? '対応してください' : '対応済み' }}</p>

                                    <a href="{{ route('company.reservation_change_notices.show', $notice) }}"
                                       class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                       style="background: {{ $isUrgent ? '#be123c' : '#047857' }};">
                                        {{ $isUrgent ? '未対応を確認する' : '完了内容を見る' }}
                                    </a>

                                    <form method="POST"
                                          action="{{ route('company.reservation_change_notices.destroy', $notice) }}"
                                          class="mt-3"
                                          onsubmit="return confirm('この予約変更連絡管理を削除しますか？\n\nタイトル：{{ str_replace(["\r", "\n", "'"], [' ', ' ', "\\'"], $notice->title) }}\n対象件数：{{ $total }}件\n\n削除すると、この案件の連絡状況・メモも削除されます。\n予約データ自体は削除されません。');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-red-50 border border-red-200 text-red-700 font-bold hover:bg-red-100 transition">
                                            削除する
                                        </button>
                                    </form>

                                    <p class="text-xs text-gray-400 mt-3 leading-6">
                                        顧客ごとの連絡手段、確認状況、メモを確認できます。
                                        削除しても予約データ自体は残ります。
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-white text-2xl font-bold mb-4"
                 style="background: {{ $theme }};">
                i
            </div>

            <h2 class="text-xl font-bold text-gray-800 mb-2">
                まだ案件はありません
            </h2>

            <p class="text-sm text-gray-500 leading-7 max-w-xl mx-auto">
                店舗都合キャンセル、営業日変更、休暇、シフト変更などで影響する予約が発生すると、ここに案件一覧が表示されます。
            </p>

            <div class="mt-6">
                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                   style="background: {{ $theme }};">
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    @endif

    <div class="mt-6">
        {{ $notices->links() }}
    </div>
</div>
@endsection
