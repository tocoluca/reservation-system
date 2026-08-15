@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

    $customerCount = $customers->total();
    $revisitStatusCounts = $revisitStatusCounts ?? [];

    $targetCount = (int) ($revisitStatusCounts['対象'] ?? $customers->getCollection()->filter(fn ($customer) => $customer->revisit_reminder_status === '対象')->count());
    $reservedCount = (int) ($revisitStatusCounts['予約済み'] ?? $customers->getCollection()->filter(fn ($customer) => $customer->revisit_reminder_status === '予約済み')->count());
    $sentCount = (int) ($revisitStatusCounts['送信済み'] ?? $customers->getCollection()->filter(fn ($customer) => $customer->revisit_reminder_status === '送信済み')->count());
    $revisitStatusUrl = fn (string $status) => route('company.customers', array_filter([
        'revisit_status' => $status,
        'keyword' => request('keyword'),
    ], fn ($value) => filled($value)));

    function revisitBadge($status) {
        return match ($status) {
            '対象' => 'bg-red-50 text-red-700 border border-red-100',
            '送信済み' => 'bg-blue-50 text-blue-700 border border-blue-100',
            '予約済み' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'メール未登録' => 'bg-gray-100 text-gray-600 border border-gray-200',
            '来店日未登録' => 'bg-gray-100 text-gray-600 border border-gray-200',
            default => 'bg-gray-100 text-gray-600 border border-gray-200',
        };
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        CUSTOMER MANAGEMENT
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-white tracking-tight">
                        顧客管理
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85 leading-relaxed">
                        顧客情報、来店状況、再来店フォロー状況を見やすくまとめて確認できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-semibold text-white">
                        ダッシュボードへ戻る
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- サマリー --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-[1.75rem] shadow-sm border border-rose-100 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold tracking-wide text-gray-500">表示中の顧客</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($customerCount) }}</div>
                    <div class="mt-2 text-sm text-gray-500">検索条件に一致した件数</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-rose-50 flex items-center justify-center text-lg">👥</div>
            </div>
        </div>

        @if($targetCount > 0)
            <a href="{{ $revisitStatusUrl('対象') }}" class="block bg-white rounded-[1.75rem] shadow-sm border border-red-100 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
        @else
            <div class="bg-white rounded-[1.75rem] shadow-sm border border-red-100 p-5">
        @endif
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold tracking-wide text-gray-500">再来店促進 対象</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ number_format($targetCount) }}</div>
                    <div class="mt-2 text-sm text-gray-500">{{ $targetCount > 0 ? 'クリックで対象顧客を表示' : '該当顧客なし' }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-red-50 flex items-center justify-center text-lg">📣</div>
            </div>
        @if($targetCount > 0)</a>@else</div>@endif

        @if($sentCount > 0)
            <a href="{{ $revisitStatusUrl('送信済み') }}" class="block bg-white rounded-[1.75rem] shadow-sm border border-blue-100 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
        @else
            <div class="bg-white rounded-[1.75rem] shadow-sm border border-blue-100 p-5">
        @endif
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold tracking-wide text-gray-500">送信済み</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($sentCount) }}</div>
                    <div class="mt-2 text-sm text-gray-500">{{ $sentCount > 0 ? 'クリックで送信済み顧客を表示' : '該当顧客なし' }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-blue-50 flex items-center justify-center text-lg">✉️</div>
            </div>
        @if($sentCount > 0)</a>@else</div>@endif

        @if($reservedCount > 0)
            <a href="{{ $revisitStatusUrl('予約済み') }}" class="block bg-white rounded-[1.75rem] shadow-sm border border-emerald-100 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
        @else
            <div class="bg-white rounded-[1.75rem] shadow-sm border border-emerald-100 p-5">
        @endif
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold tracking-wide text-gray-500">予約済み</div>
                    <div class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($reservedCount) }}</div>
                    <div class="mt-2 text-sm text-gray-500">{{ $reservedCount > 0 ? 'クリックで予約済み顧客を表示' : '該当顧客なし' }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center text-lg">📅</div>
            </div>
        @if($reservedCount > 0)</a>@else</div>@endif
    </div>

    <div class="sticky top-24 z-30 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur mb-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-bold text-gray-900">接客前チェック</div>
                <div class="text-xs text-gray-500 mt-1">表示中の顧客から、フォロー対象や注意が必要な顧客を見つけやすくします。</div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($targetCount > 0)
                    <a href="{{ $revisitStatusUrl('対象') }}" class="inline-flex items-center rounded-2xl bg-red-50 border border-red-100 px-4 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-100">
                @else
                    <span class="inline-flex items-center rounded-2xl bg-red-50 border border-red-100 px-4 py-2.5 text-sm font-bold text-red-700">
                @endif
                    再来店フォロー {{ number_format($targetCount) }}件
                @if($targetCount > 0)</a>@else</span>@endif
                @if($reservedCount > 0)
                    <a href="{{ $revisitStatusUrl('予約済み') }}" class="inline-flex items-center rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-2.5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">
                @else
                    <span class="inline-flex items-center rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-2.5 text-sm font-bold text-emerald-700">
                @endif
                    次回来店あり {{ number_format($reservedCount) }}件
                @if($reservedCount > 0)</a>@else</span>@endif
                @if($sentCount > 0)
                    <a href="{{ $revisitStatusUrl('送信済み') }}" class="inline-flex items-center rounded-2xl bg-blue-50 border border-blue-100 px-4 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-blue-100">
                @else
                    <span class="inline-flex items-center rounded-2xl bg-blue-50 border border-blue-100 px-4 py-2.5 text-sm font-bold text-blue-700">
                @endif
                    送信済み {{ number_format($sentCount) }}件
                @if($sentCount > 0)</a>@else</span>@endif
            </div>
    </div>

    {{-- 検索 --}}
    <div class="bg-white shadow-sm rounded-[1.75rem] border border-gray-100 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-4">
            <div>
                <div class="text-base font-bold text-gray-900">顧客を検索</div>
                <p class="text-sm text-gray-500 mt-1">
                    名前、電話番号、メールアドレスで絞り込みできます。
                </p>
            </div>

            @if(request('keyword'))
                <div class="text-sm text-gray-500">
                    検索中:
                    <span class="font-semibold text-gray-800">「{{ request('keyword') }}」</span>
                </div>
            @endif
        </div>

        <form method="GET">
            <div class="flex flex-col lg:flex-row gap-3">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        placeholder="名前・電話番号・メールアドレスで検索"
                        class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3.5 text-sm focus:outline-none focus:ring-4 focus:border-transparent"
                        style="--tw-ring-color: {{ $theme }}22;">
                </div>

                <button
                    type="submit"
                    class="px-6 py-3.5 rounded-2xl text-white font-semibold shadow-sm hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    検索する
                </button>

                @if(request('keyword'))
                    <a href="{{ route('company.customers') }}"
                       class="inline-flex items-center justify-center px-6 py-3.5 rounded-2xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">
                        クリア
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- 一覧 --}}
    <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
        <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">顧客一覧</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        顧客ごとの状態を見ながら、必要に応じてカルテを確認できます。
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    全 {{ number_format($customerCount) }} 件
                </div>
            </div>
        </div>

        @forelse($customers as $customer)
            @php
                $status = $customer->revisit_reminder_status;
                $photo = $customer->photos->first();
            @endphp

            <div class="px-4 sm:px-6 py-5 border-b last:border-b-0 hover:bg-amber-50/40 transition">
                <div class="flex flex-col xl:flex-row xl:items-center gap-5">

                    {{-- 左：基本情報 --}}
                    <div class="flex items-center gap-4 min-w-0 xl:w-[320px]">
                        <div class="relative shrink-0">
                            <img
                                src="{{ $photo && $photo->path ? asset($photo->path) : asset('images/noimage.png') }}"
                                class="w-16 h-16 rounded-2xl object-cover border border-amber-100 shadow-sm bg-white">
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                                    {{ $customer->name }}
                                </h3>

                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold {{ revisitBadge($status) }}">
                                    {{ $status ?: '対象外' }}
                                </span>
                            </div>

                            <div class="mt-2 space-y-1">
                                @if($customer->email)
                                    <div class="text-sm text-gray-600 truncate">
                                        <span class="text-gray-400">メール</span>
                                        <span class="ml-2">{{ $customer->email }}</span>
                                    </div>
                                @endif

                                <div class="text-sm text-gray-600 truncate">
                                    <span class="text-gray-400">電話</span>
                                    <span class="ml-2">{{ $customer->phone ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 中央：来店情報 --}}
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 flex-1">
                        <div class="rounded-2xl bg-rose-50/60 px-4 py-3 border border-rose-100">
                            <div class="text-[11px] font-semibold text-gray-500">来店回数</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">
                                {{ number_format($customer->visit_count) }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 px-4 py-3 border border-gray-100">
                            <div class="text-[11px] font-semibold text-gray-500">最終来店</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                @if($customer->last_visit)
                                    {{ \Carbon\Carbon::parse($customer->last_visit)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 px-4 py-3 border border-gray-100">
                            <div class="text-[11px] font-semibold text-gray-500">次回来店</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                @if($customer->next_visit_at)
                                    {{ \Carbon\Carbon::parse($customer->next_visit_at)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-amber-50/70 px-4 py-3 border border-amber-100">
                            <div class="text-[11px] font-semibold text-gray-500">再来店連絡</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                {{ optional($customer->latestRevisitReminderLog)->sent_at?->format('Y-m-d') ?? '未送信' }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-red-50/70 px-4 py-3 border border-red-100">
                            <div class="text-[11px] font-semibold text-gray-500">無断キャンセル回数</div>
                            <div class="mt-1 text-lg font-bold text-red-700">
                                {{ number_format($customer->no_show_count ?? 0) }}
                            </div>
                        </div>
                    </div>

                    {{-- 右：導線 --}}
                    <div class="xl:w-[170px]">
                        <a href="{{ route('company.customers.show', $customer->id) }}"
                           class="inline-flex w-full items-center justify-center px-4 py-3.5 rounded-2xl text-white font-semibold shadow-sm hover:opacity-90 transition"
                           style="background: {{ $theme }}">
                            カルテを見る
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-5 py-14 text-center bg-gradient-to-b from-white to-gray-50">
                <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-2xl">
                    👤
                </div>
                <div class="mt-4 text-base font-semibold text-gray-700">
                    顧客がまだ登録されていません
                </div>
                <div class="text-sm text-gray-500 mt-2">
                    予約が入ると顧客情報がここに表示されます。
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $customers->links() }}
    </div>

</div>

@endsection
