@extends('layouts.company')

@php
    use Carbon\Carbon;

    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $current = auth()->guard('company')->user();

    $totalCount = $staffs->count();
    $activeCount = $staffs->filter(function ($s) {
        return empty($s->retired_at) || Carbon::parse($s->retired_at)->startOfDay()->gt(now()->startOfDay());
    })->count();
    $retiredCount = $staffs->filter(function ($s) {
        return !empty($s->retired_at) && Carbon::parse($s->retired_at)->startOfDay()->lte(now()->startOfDay());
    })->count();
    $reservableCount = $staffs->filter(function ($s) {
        $isRetired = !empty($s->retired_at) && Carbon::parse($s->retired_at)->startOfDay()->lte(now()->startOfDay());
        return !$isRetired && (bool) $s->is_reservable;
    })->count();
@endphp

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-white mb-6">
        <div class="px-5 sm:px-8 py-7 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #1f2937 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                        STAFF MANAGEMENT
                    </div>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-bold">
                        担当者一覧
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85">
                        担当者の状態確認、編集、退職管理をひと目で行えます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold transition">
                        ← ダッシュボード
                    </a>

                    <a href="{{ route('company.staff.create') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white text-gray-900 font-bold shadow hover:opacity-90 transition">
                        ＋ 新規登録
                    </a>
                </div>
            </div>
        </div>

        <div class="px-5 sm:px-8 py-5 bg-gray-50">
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">登録人数</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalCount) }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">在籍中</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($activeCount) }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">予約受付対象</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($reservableCount) }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">退職済み</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($retiredCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden lg:block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">担当者一覧</h2>
                    <p class="text-sm text-gray-500 mt-1">状態と操作をまとめて確認できます。</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead>
                    <tr class="text-left text-gray-500 bg-white border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">担当者</th>
                        <th class="px-4 py-4 font-semibold">コード</th>
                        <th class="px-4 py-4 font-semibold">権限</th>
                        <th class="px-4 py-4 font-semibold">状態</th>
                        <th class="px-4 py-4 font-semibold">予約受付</th>
                        <th class="px-4 py-4 font-semibold">表示順</th>
                        <th class="px-6 py-4 font-semibold text-right">操作</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($staffs as $s)
                    @php
                        $isRetired = !empty($s->retired_at) && Carbon::parse($s->retired_at)->startOfDay()->lte(now()->startOfDay());
                        $isRetiring = !empty($s->retired_at) && Carbon::parse($s->retired_at)->startOfDay()->gt(now()->startOfDay());
                    @endphp

                    <tr class="border-b border-gray-100 hover:bg-gray-50/70 transition">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm"
                                     style="background: {{ $theme }}">
                                    {{ mb_substr($s->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $s->name }}</div>
                                    @if(!empty($s->retired_at))
                                        <div class="text-xs text-gray-400 mt-1">
                                            退職日：{{ Carbon::parse($s->retired_at)->format('Y/m/d') }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 mt-1">
                                            退職日未設定
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-5">
                            <span class="inline-flex items-center rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 font-mono text-gray-700">
                                {{ $s->staff_code }}
                            </span>
                        </td>

                        <td class="px-4 py-5">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                {{ method_exists($s, 'roleLabel') ? $s->roleLabel() : $s->role }}
                            </span>
                        </td>

                        <td class="px-4 py-5">
                            @if($isRetired)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    退職済み
                                </span>
                            @elseif($isRetiring)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                    退職予定
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                    在籍中
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-5">
                            @if($isRetired)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                                    対象外
                                </span>
                            @elseif($s->is_reservable)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    受付中
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    停止中
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-5">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-700 font-semibold">
                                {{ $s->priority_order }}
                            </span>
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex justify-end items-center gap-2 flex-wrap">
                                <a href="{{ route('company.staff.edit', $s->id) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-white font-semibold shadow-sm hover:opacity-90 transition"
                                   style="background: {{ $theme }}">
                                    編集
                                </a>

                                @if($current->isMaster())
                                    <form method="POST"
                                          action="{{ route('company.staff.reset-password', $s->id) }}"
                                          class="m-0">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('パスワードを初期化しますか？')"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-500 text-white font-semibold shadow-sm hover:bg-red-600 transition">
                                            PW初期化
                                        </button>
                                    </form>
                                @endif

                                @if($current->isMaster() && (int) $s->id !== (int) $current->id)
                                    <form method="POST"
                                          action="{{ route('company.staff.destroy', $s->id) }}"
                                          class="m-0"
                                          onsubmit="return confirm('「{{ $s->name }}」を削除しますか？この操作は取り消せません。');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gray-900 text-white font-semibold shadow-sm hover:bg-black transition">
                                            削除
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="max-w-md mx-auto">
                                <div class="text-lg font-bold text-gray-700">担当者がまだ登録されていません</div>
                                <p class="text-sm text-gray-400 mt-2">まずは担当者を登録すると、予約やシフト管理が進めやすくなります。</p>
                                <div class="mt-6">
                                    <a href="{{ route('company.staff.create') }}"
                                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                       style="background: {{ $theme }}">
                                        ＋ 新規登録
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="lg:hidden space-y-4">
        @forelse($staffs as $s)
            @php
                $isRetired = !empty($s->retired_at) && Carbon::parse($s->retired_at)->startOfDay()->lte(now()->startOfDay());
                $isRetiring = !empty($s->retired_at) && Carbon::parse($s->retired_at)->startOfDay()->gt(now()->startOfDay());
            @endphp

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-5">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm shrink-0"
                             style="background: {{ $theme }}">
                            {{ mb_substr($s->name, 0, 1) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-lg font-bold text-gray-900 truncate">{{ $s->name }}</div>
                                    <div class="mt-1 text-sm font-mono text-gray-500">{{ $s->staff_code }}</div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                    {{ method_exists($s, 'roleLabel') ? $s->roleLabel() : $s->role }}
                                </span>

                                @if($isRetired)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        退職済み
                                    </span>
                                @elseif($isRetiring)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                        退職予定
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        在籍中
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            <div class="text-xs text-gray-500">予約受付</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">
                                @if($isRetired)
                                    対象外
                                @elseif($s->is_reservable)
                                    受付中
                                @else
                                    停止中
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            <div class="text-xs text-gray-500">表示順</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">{{ $s->priority_order }}</div>
                        </div>
                    </div>

                    @if(!empty($s->retired_at))
                        <div class="mt-4 text-sm text-gray-500">
                            退職日：{{ Carbon::parse($s->retired_at)->format('Y/m/d') }}
                        </div>
                    @endif
                </div>

                <div class="px-5 pb-5">
                    <div class="grid grid-cols-1 gap-3">
                        <a href="{{ route('company.staff.edit', $s->id) }}"
                           class="w-full text-center text-white py-3 rounded-2xl font-semibold shadow-sm"
                           style="background: {{ $theme }}">
                            編集する
                        </a>

                        @if($current->isMaster())
                            <form method="POST"
                                  action="{{ route('company.staff.reset-password', $s->id) }}">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('パスワードを初期化しますか？')"
                                        class="w-full bg-red-500 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                    パスワード初期化
                                </button>
                            </form>
                        @endif

                        @if($current->isMaster() && (int) $s->id !== (int) $current->id)
                            <form method="POST"
                                  action="{{ route('company.staff.destroy', $s->id) }}"
                                  onsubmit="return confirm('「{{ $s->name }}」を削除しますか？この操作は取り消せません。');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full bg-gray-900 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                    削除する
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 text-center">
                <div class="text-lg font-bold text-gray-700">担当者がまだ登録されていません</div>
                <p class="text-sm text-gray-400 mt-2">新規登録から担当者を追加してください。</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8 lg:hidden">
        <a href="{{ route('company.staff.create') }}"
           class="w-full inline-flex items-center justify-center text-white px-4 py-4 rounded-2xl shadow font-bold hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ＋ 新規登録
        </a>
    </div>

</div>

@endsection