@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';

    $customerCount = $customers->total();

    $targetCount = $customers->getCollection()->filter(function ($customer) {
        return $customer->revisit_reminder_status === '対象';
    })->count();

    $reservedCount = $customers->getCollection()->filter(function ($customer) {
        return $customer->revisit_reminder_status === '予約済み';
    })->count();

    $sentCount = $customers->getCollection()->filter(function ($customer) {
        return $customer->revisit_reminder_status === '送信済み';
    })->count();

    function revisitBadge($status) {
        return match ($status) {
            '対象' => 'bg-red-100 text-red-700',
            '送信済み' => 'bg-blue-100 text-blue-700',
            '予約済み' => 'bg-green-100 text-green-700',
            'メール未登録' => 'bg-gray-100 text-gray-600',
            '来店日未登録' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-3 py-1 text-xs font-medium text-gray-500 shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full" style="background: {{ $theme }}"></span>
                顧客管理
            </div>

            <h1 class="mt-3 text-2xl sm:text-3xl font-bold text-gray-900">顧客管理</h1>

            <p class="text-gray-500 text-sm mt-2">
                顧客情報・来店状況・再来店フォローをまとめて確認できます
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-2xl border text-sm font-semibold bg-white shadow-sm hover:bg-gray-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ダッシュボード
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500">表示中の顧客</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($customerCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">検索条件に一致した件数</div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500">再来店促進 対象</div>
            <div class="mt-2 text-3xl font-bold text-red-600">{{ number_format($targetCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">フォローが必要な顧客</div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500">送信済み</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($sentCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">メール送信済みの顧客</div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-xs font-semibold text-gray-500">予約済み</div>
            <div class="mt-2 text-3xl font-bold text-green-600">{{ number_format($reservedCount) }}</div>
            <div class="mt-2 text-sm text-gray-500">次回来店が決まっている顧客</div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-3xl border border-gray-100 p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
            <div class="flex-1">
                <div class="text-sm font-bold text-gray-900">顧客を検索</div>
                <p class="text-xs text-gray-500 mt-1">名前、電話番号、メールアドレスで検索できます。</p>
            </div>
        </div>

        <form method="GET" class="mt-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    placeholder="名前・電話番号・メールアドレスで検索"
                    class="flex-1 border border-gray-300 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100">

                <button
                    type="submit"
                    class="px-5 py-3 rounded-2xl text-white font-semibold shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    検索する
                </button>

                @if(request('keyword'))
                    <a href="{{ route('company.customers') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">
                        クリア
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white shadow-sm rounded-3xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900">顧客一覧</h2>
            <p class="text-sm text-gray-500 mt-1">顧客の状態を確認し、必要に応じてカルテを開いて詳細を確認できます。</p>
        </div>

        @forelse($customers as $customer)
            @php
                $status = $customer->revisit_reminder_status;
			    $photo = $customer->photos->first();
            @endphp

            <div class="border-b last:border-b-0 px-4 sm:px-5 py-4 hover:bg-gray-50 transition">
                <div class="flex flex-col xl:flex-row xl:items-center gap-4 xl:gap-6">

                    <div class="flex items-center gap-3 min-w-0 xl:w-[300px]">
                        <img
						    src="{{ $photo && $photo->path ? asset($photo->path) : asset('images/noimage.png') }}"
						    class="w-14 h-14 rounded-full object-cover border border-gray-200 shrink-0">
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900 truncate">{{ $customer->name }}</div>

                            @if($customer->email)
                                <div class="text-sm text-gray-500 truncate">{{ $customer->email }}</div>
                            @endif

                            <div class="text-sm text-gray-500 mt-1">
                                電話：{{ $customer->phone ?: '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 flex-1">
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-[11px] text-gray-500">来店回数</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">
                                {{ number_format($customer->visit_count) }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-[11px] text-gray-500">最終来店</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                @if($customer->last_visit)
                                    {{ \Carbon\Carbon::parse($customer->last_visit)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-[11px] text-gray-500">次回来店</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                @if($customer->next_visit_at)
                                    {{ \Carbon\Carbon::parse($customer->next_visit_at)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-[11px] text-gray-500">再来店促進</div>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ revisitBadge($status) }}">
                                    {{ $status ?: '対象外' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="xl:w-[140px]">
                        <a href="{{ route('company.customers.show', $customer->id) }}"
                           class="inline-flex w-full items-center justify-center px-4 py-3 rounded-2xl text-white font-semibold shadow hover:opacity-90 transition"
                           style="background: {{ $theme }}">
                            カルテを見る
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <div class="text-base font-semibold text-gray-700">顧客がまだ登録されていません</div>
                <div class="text-sm text-gray-500 mt-2">予約が入ると顧客情報がここに表示されます。</div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $customers->links() }}
    </div>

</div>

@endsection