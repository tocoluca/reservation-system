@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-7xl mx-auto px-6 py-8">

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">顧客管理</h1>
    <a href="{{ route('company.dashboard') }}"
       class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
       style="border-color: {{ $theme }}; color: {{ $theme }}">
        ← ダッシュボード
    </a>
</div>

<div class="bg-white shadow rounded-xl p-4 mb-6">
    <form method="GET">
        <div class="flex gap-3">
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="名前・電話番号検索"
                class="border rounded-lg p-2 w-64">

            <button
                style="background: {{ $theme }}"
                class="text-white px-4 py-2 rounded-lg">
                検索
            </button>
        </div>
    </form>
</div>

<div class="bg-white shadow rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-white" style="background: {{ $theme }}">
            <tr>
                <th class="p-3 text-left">顧客</th>
                <th class="p-3">電話</th>
                <th class="p-3">来店回数</th>
                <th class="p-3">最終来店</th>
                <th class="p-3">次回来店</th>
                <th class="p-3">再来店促進</th>
                <th class="p-3">カルテ</th>
            </tr>
        </thead>

        <tbody>
            @forelse($customers as $customer)
                @php
                    $status = $customer->revisit_reminder_status;
                @endphp

                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-semibold">
                        <div class="flex items-center gap-3">
                            <img
                                src="{{ $customer->photo ? asset('storage/'.$customer->photo) : asset('images/noimage.png') }}"
                                class="w-10 h-10 rounded-full object-cover">

                            <div>
                                <div>{{ $customer->name }}</div>
                                @if($customer->email)
                                    <div class="text-xs text-gray-500">{{ $customer->email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="p-3">
                        {{ $customer->phone }}
                    </td>

                    <td class="p-3 text-center">
                        <span
                            class="px-3 py-1 rounded-full text-xs text-white"
                            style="background: {{ $theme }}">
                            {{ $customer->visit_count }}
                        </span>
                    </td>

                    <td class="p-3 text-center">
                        @if($customer->last_visit)
                            {{ \Carbon\Carbon::parse($customer->last_visit)->format('Y-m-d') }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="p-3 text-center">
                        @if($customer->next_visit_at)
                            {{ \Carbon\Carbon::parse($customer->next_visit_at)->format('Y-m-d') }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="p-3 text-center">
                        @if($status === '対象')
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                対象
                            </span>
                        @elseif($status === '送信済み')
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                送信済み
                            </span>
                        @elseif($status === '予約済み')
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                予約済み
                            </span>
                        @elseif($status === 'メール未登録')
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                メール未登録
                            </span>
                        @elseif($status === '来店日未登録')
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                来店日未登録
                            </span>
                        @else
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                対象外
                            </span>
                        @endif
                    </td>

                    <td class="p-3 text-center">
                        <a
                            href="{{ route('company.customers.show', $customer->id) }}"
                            class="px-4 py-1 rounded text-white"
                            style="background: {{ $theme }}">
                            カルテ
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center p-6 text-gray-500">
                        顧客がまだ登録されていません
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $customers->links() }}
</div>

</div>

@endsection