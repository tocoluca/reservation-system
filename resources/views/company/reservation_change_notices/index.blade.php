@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider uppercase text-gray-400">Reservation Change Notice</p>
                <h1 class="text-2xl md:text-3xl font-bold mt-1">予約変更連絡管理</h1>
                <p class="text-sm text-gray-500 mt-2">
                    店都合の予約変更連絡・確認状況を見やすく管理できます。
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center px-4 py-3 rounded-2xl border font-semibold transition hover:bg-gray-50"
                   style="border-color: {{ $theme }}; color: {{ $theme }};">
                    ← ダッシュボード
                </a>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
                    <div class="bg-white rounded-2xl border shadow-sm px-4 py-3">
                        <div class="text-xs text-gray-500">案件数</div>
                        <div class="text-xl font-bold text-gray-800 mt-1">{{ $notices->total() }}</div>
                    </div>
                    <div class="bg-white rounded-2xl border shadow-sm px-4 py-3">
                        <div class="text-xs text-gray-500">対応の流れ</div>
                        <div class="text-sm font-semibold text-gray-800 mt-1">抽出 → 送信 → 確認</div>
                    </div>
                    <div class="rounded-2xl text-white shadow-sm px-4 py-3"
                         style="background: linear-gradient(135deg, {{ $theme }}, {{ $theme }}cc);">
                        <div class="text-xs text-white/80">用途</div>
                        <div class="text-sm font-semibold mt-1">休業・休暇・時間変更時の連絡管理</div>
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

    @if($notices->count())
        <div class="space-y-4">
            @foreach($notices as $notice)
                @php
                    $pending = (int) ($notice->pending_count ?? 0);
                    $confirmed = (int) ($notice->confirmed_count ?? 0);
                    $total = (int) ($notice->items_count ?? 0);
                    $progress = $total > 0 ? min(100, round(($confirmed / $total) * 100)) : 0;
                    $isUrgent = $pending > 0;
                @endphp

                <div class="bg-white rounded-3xl border shadow-sm overflow-hidden">
                    <div class="p-5 md:p-6">
                        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                        {{ $isUrgent ? 'bg-rose-100 text-rose-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $isUrgent ? '対応中' : '確認完了' }}
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

                                <h2 class="text-lg md:text-xl font-bold text-gray-800 leading-snug break-words">
                                    {{ $notice->title }}
                                </h2>

                                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                        <div class="text-[11px] text-gray-500">対象件数</div>
                                        <div class="text-lg font-bold text-gray-800 mt-1">{{ number_format($total) }}件</div>
                                    </div>

                                    <div class="rounded-2xl bg-rose-50 px-4 py-3">
                                        <div class="text-[11px] text-rose-500">確認待ち</div>
                                        <div class="text-lg font-bold text-rose-700 mt-1">{{ number_format($pending) }}件</div>
                                    </div>

                                    <div class="rounded-2xl bg-green-50 px-4 py-3">
                                        <div class="text-[11px] text-green-500">確認済み</div>
                                        <div class="text-lg font-bold text-green-700 mt-1">{{ number_format($confirmed) }}件</div>
                                    </div>

                                    <div class="rounded-2xl px-4 py-3 text-white"
                                         style="background: linear-gradient(135deg, {{ $theme }}, {{ $theme }}cc);">
                                        <div class="text-[11px] text-white/80">進捗</div>
                                        <div class="text-lg font-bold mt-1">{{ $progress }}%</div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                        <span>対応進捗</span>
                                        <span>{{ $confirmed }} / {{ $total }}</span>
                                    </div>
                                    <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full rounded-full"
                                             style="width: {{ $progress }}%; background: {{ $theme }};"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="xl:w-48 shrink-0">
                                <a href="{{ route('company.reservation_change_notices.show', $notice) }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                   style="background: {{ $theme }};">
                                    詳細を見る
                                </a>
                                <p class="text-xs text-gray-400 mt-2 text-center xl:text-left">
                                    顧客ごとの確認状況やメモを確認できます
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-3xl border shadow-sm p-10 text-center">
            <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-white text-2xl font-bold mb-4"
                 style="background: {{ $theme }};">
                i
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">まだ案件はありません</h2>
            <p class="text-sm text-gray-500">
                営業日変更・休暇承認・シフト変更などで影響予約が発生すると、ここに一覧表示されます。
            </p>

            <div class="mt-6">
                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                   style="background: {{ $theme }};">
                    ダッシュボード
                </a>
            </div>
        </div>
    @endif

    <div class="mt-6">
        {{ $notices->links() }}
    </div>
</div>
@endsection