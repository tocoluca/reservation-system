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
             style="background: linear-gradient(135deg, {{ $theme }}, {{ $theme }}cc);">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <p class="text-xs font-semibold tracking-[0.2em] uppercase text-white/80">
                        Reservation Change Notice
                    </p>
                    <h1 class="text-2xl md:text-3xl font-bold mt-2">
                        予約変更連絡管理
                    </h1>
                    <p class="text-sm md:text-base text-white/85 mt-3 leading-7">
                        店都合による予約変更の連絡状況を、案件ごとに分かりやすく確認できます。
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

        <div class="px-6 py-5 md:px-8 bg-amber-50 border-t border-amber-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-amber-100 px-4 py-4">
                    <div class="text-xs text-gray-500">案件数</div>
                    <div class="text-2xl font-bold text-gray-800 mt-1">{{ $notices->total() }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 px-4 py-4">
                    <div class="text-xs text-gray-500">対応の流れ</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">対象抽出 → 送信 → 確認 → 完了</div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 px-4 py-4">
                    <div class="text-xs text-gray-500">用途</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">休業・シフト変更・営業時間変更時の連絡</div>
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

    @if($notices->count())
        <div class="space-y-5">
            @foreach($notices as $notice)
                @php
                    $pending = (int) ($notice->pending_count ?? 0);
                    $confirmed = (int) ($notice->confirmed_count ?? 0);
                    $total = (int) ($notice->items_count ?? 0);
                    $progress = $total > 0 ? min(100, round(($confirmed / $total) * 100)) : 0;
                    $isUrgent = $pending > 0;
                @endphp

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="p-5 md:p-6">
                        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                        {{ $isUrgent ? 'bg-rose-100 text-rose-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $isUrgent ? '優先対応あり' : '確認完了' }}
                                    </span>

                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        作成日 {{ $notice->created_at->format('Y/m/d') }}
                                    </span>

                                    @if($notice->target_date)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            対象日 {{ optional($notice->target_date)->format('Y/m/d') }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-xl md:text-2xl font-bold text-gray-900 leading-snug break-words">
                                    {{ $notice->title }}
                                </h2>

                                <div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div class="rounded-2xl bg-gray-50 px-4 py-4 border border-gray-100">
                                        <div class="text-[11px] text-gray-500">対象件数</div>
                                        <div class="text-lg font-bold text-gray-800 mt-1">{{ number_format($total) }}件</div>
                                    </div>

                                    <div class="rounded-2xl bg-rose-50 px-4 py-4 border border-rose-100">
                                        <div class="text-[11px] text-rose-500">確認待ち</div>
                                        <div class="text-lg font-bold text-rose-700 mt-1">{{ number_format($pending) }}件</div>
                                    </div>

                                    <div class="rounded-2xl bg-green-50 px-4 py-4 border border-green-100">
                                        <div class="text-[11px] text-green-500">確認済み</div>
                                        <div class="text-lg font-bold text-green-700 mt-1">{{ number_format($confirmed) }}件</div>
                                    </div>

                                    <div class="rounded-2xl px-4 py-4 text-white"
                                         style="background: linear-gradient(135deg, {{ $theme }}, {{ $theme }}cc);">
                                        <div class="text-[11px] text-white/80">進捗率</div>
                                        <div class="text-lg font-bold mt-1">{{ $progress }}%</div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                        <span>対応進捗</span>
                                        <span>{{ $confirmed }} / {{ $total }}</span>
                                    </div>
                                    <div class="w-full h-3 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300"
                                             style="width: {{ $progress }}%; background: {{ $theme }};"></div>
                                    </div>
                                </div>

                                @if($pending > 0)
                                    <div class="mt-4 rounded-2xl bg-rose-50 border border-rose-100 px-4 py-3">
                                        <p class="text-sm font-semibold text-rose-700">
                                            まだ {{ $pending }} 件の確認待ちがあります。優先して確認してください。
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="xl:w-56 shrink-0">
                                <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                                    <p class="text-xs text-gray-500 mb-2">操作</p>
                                    <a href="{{ route('company.reservation_change_notices.show', $notice) }}"
                                       class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                       style="background: {{ $theme }};">
                                        詳細を見る
                                    </a>
                                    <p class="text-xs text-gray-400 mt-3 leading-6">
                                        顧客ごとの連絡手段、確認状況、メモを確認できます。
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
            <h2 class="text-xl font-bold text-gray-800 mb-2">まだ案件はありません</h2>
            <p class="text-sm text-gray-500 leading-7 max-w-xl mx-auto">
                営業日変更、休暇、シフト変更などで影響する予約が発生すると、ここに案件一覧が表示されます。
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