@extends('layouts.company')

@php
    use Carbon\Carbon;

    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $current = auth()->guard('company')->user();

    $totalCount = $staffs->count();
    $activeCount = $staffs->filter(function ($staff) {
        return empty($staff->retired_at) || Carbon::parse($staff->retired_at)->startOfDay()->gt(now()->startOfDay());
    })->count();
    $retiredCount = $staffs->filter(function ($staff) {
        return !empty($staff->retired_at) && Carbon::parse($staff->retired_at)->startOfDay()->lte(now()->startOfDay());
    })->count();
    $reservableCount = $staffs->filter(function ($staff) {
        $isRetired = !empty($staff->retired_at) && Carbon::parse($staff->retired_at)->startOfDay()->lte(now()->startOfDay());
        return !$isRetired && (bool) $staff->is_reservable && $staff->role !== 'store_operator';
    })->count();

    $canResetPassword = function ($target) use ($current) {
        if ((int) $current->id === (int) $target->id) {
            return false;
        }

        if ($current->isMaster()) {
            return true;
        }

        if (in_array($current->role, ['leader', 'area_leader'], true)) {
            return $target->role !== 'master';
        }

        return false;
    };

    $canManageStaff = !$current->isStoreOperator();
@endphp

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold text-white/90">
                        STAFF MANAGEMENT
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight">
                        担当者一覧
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85 leading-relaxed">
                        担当者の登録状況、予約受付、権限、表示順をまとめて確認できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm text-white font-semibold transition">
                        ダッシュボードへ
                    </a>

                    @if($current->isMaster())
                        <a href="{{ route('company.staff.create') }}"
                           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm text-white font-bold transition">
                            新規登録
                        </a>
                    @endif
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

    <div class="mb-6">
        @include('company._staff_menu_nav', [
            'currentStep' => 'staff',
        ])
    </div>

    <div class="sticky top-24 z-30 mb-6 rounded-[1.75rem] border border-white/80 bg-white/90 p-3 shadow-lg backdrop-blur">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-bold text-gray-900">状態別の確認</div>
                <div class="text-xs text-gray-500 mt-1">予約受付対象と退職予定を見落とさないための確認バーです。</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-2.5 text-sm font-bold text-emerald-700">在籍中 {{ number_format($activeCount) }}</span>
                <span class="rounded-2xl bg-green-50 border border-green-100 px-4 py-2.5 text-sm font-bold text-green-700">予約受付 {{ number_format($reservableCount) }}</span>
                <span class="rounded-2xl bg-gray-100 border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-700">退職済み {{ number_format($retiredCount) }}</span>
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

        <div class="max-h-[72vh] overflow-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead>
                    <tr class="text-left text-gray-500 bg-white border-b border-gray-100">
                        <th class="sticky top-0 z-20 bg-white px-6 py-4 font-semibold border-b border-gray-200 shadow-sm">担当者</th>
                        <th class="sticky top-0 z-20 bg-white px-4 py-4 font-semibold border-b border-gray-200 shadow-sm">コード</th>
                        <th class="sticky top-0 z-20 bg-white px-4 py-4 font-semibold border-b border-gray-200 shadow-sm">権限</th>
                        <th class="sticky top-0 z-20 bg-white px-4 py-4 font-semibold border-b border-gray-200 shadow-sm">状態</th>
                        <th class="sticky top-0 z-20 bg-white px-4 py-4 font-semibold border-b border-gray-200 shadow-sm">予約受付</th>
                        <th class="sticky top-0 z-20 bg-white px-4 py-4 font-semibold border-b border-gray-200 shadow-sm">表示順</th>
                        <th class="sticky top-0 z-20 bg-white px-6 py-4 font-semibold text-right border-b border-gray-200 shadow-sm">操作</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($staffs as $staff)
                    @php
                        $isRetired = !empty($staff->retired_at) && Carbon::parse($staff->retired_at)->startOfDay()->lte(now()->startOfDay());
                        $isRetiring = !empty($staff->retired_at) && Carbon::parse($staff->retired_at)->startOfDay()->gt(now()->startOfDay());
                        $roleLabel = method_exists($staff, 'roleLabel') ? $staff->roleLabel() : $staff->role;
                    @endphp

                    <tr class="border-b border-gray-100 hover:bg-gray-50/70 transition">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm"
                                     style="background: {{ $theme }}">
                                    {{ mb_substr($staff->name, 0, 1) }}
                                </div>

                                <div>
                                    <div class="font-bold text-gray-900">{{ $staff->name }}</div>

                                    @if(!empty($staff->retired_at))
                                        <div class="text-xs text-gray-400 mt-1">
                                            退職日: {{ Carbon::parse($staff->retired_at)->format('Y/m/d') }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 mt-1">退職日未設定</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-5">
                            <span class="inline-flex items-center rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 font-mono text-gray-700">
                                {{ $staff->staff_code }}
                            </span>
                        </td>

                        <td class="px-4 py-5">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                {{ $roleLabel }}
                            </span>
                        </td>

                        <td class="px-4 py-5">
                            @if($isRetired)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">退職済み</span>
                            @elseif($isRetiring)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">退職予定</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">在籍中</span>
                            @endif
                        </td>

                        <td class="px-4 py-5">
                            @if($isRetired || $staff->role === 'store_operator')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">対象外</span>
                            @elseif($staff->is_reservable)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">受付中</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">停止中</span>
                            @endif
                        </td>

                        <td class="px-4 py-5">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-700 font-semibold">
                                {{ $staff->priority_order }}
                            </span>
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex justify-end items-center gap-2 flex-wrap">
                                @if($canManageStaff)
                                    <a href="{{ route('company.staff.edit', $staff->id) }}"
                                       class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-white font-semibold shadow-sm hover:opacity-90 transition"
                                       style="background: {{ $theme }}">
                                        編集
                                    </a>
                                @endif

                                @if($canResetPassword($staff))
                                    <button type="button"
                                            onclick="openPasswordResetModal('{{ route('company.staff.reset-password', $staff->id) }}', @js($staff->name), @js($roleLabel))"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-500 text-white font-semibold shadow-sm hover:bg-red-600 transition">
                                        PW初期化
                                    </button>
                                @endif

                                @if($current->isMaster() && (int) $staff->id !== (int) $current->id)
                                    <form method="POST"
                                          action="{{ route('company.staff.destroy', $staff->id) }}"
                                          class="m-0"
                                          onsubmit="return confirm(@js('「' . $staff->name . '」を削除しますか？この操作は取り消せません。'));">
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
                                <p class="text-sm text-gray-400 mt-2">
                                    まずは担当者を登録すると、予約やシフト管理を進めやすくなります。
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="lg:hidden space-y-4">
        @forelse($staffs as $staff)
            @php
                $isRetired = !empty($staff->retired_at) && Carbon::parse($staff->retired_at)->startOfDay()->lte(now()->startOfDay());
                $isRetiring = !empty($staff->retired_at) && Carbon::parse($staff->retired_at)->startOfDay()->gt(now()->startOfDay());
                $roleLabel = method_exists($staff, 'roleLabel') ? $staff->roleLabel() : $staff->role;
            @endphp

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-5">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm shrink-0"
                             style="background: {{ $theme }}">
                            {{ mb_substr($staff->name, 0, 1) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-lg font-bold text-gray-900 truncate">{{ $staff->name }}</div>
                            <div class="mt-1 text-sm font-mono text-gray-500">{{ $staff->staff_code }}</div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $roleLabel }}</span>

                                @if($isRetired)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">退職済み</span>
                                @elseif($isRetiring)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">退職予定</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">在籍中</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            <div class="text-xs text-gray-500">予約受付</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">
                                @if($isRetired || $staff->role === 'store_operator')
                                    対象外
                                @elseif($staff->is_reservable)
                                    受付中
                                @else
                                    停止中
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            <div class="text-xs text-gray-500">表示順</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">{{ $staff->priority_order }}</div>
                        </div>
                    </div>

                    @if(!empty($staff->retired_at))
                        <div class="mt-4 text-sm text-gray-500">
                            退職日: {{ Carbon::parse($staff->retired_at)->format('Y/m/d') }}
                        </div>
                    @endif
                </div>

                <div class="px-5 pb-5">
                    <div class="grid grid-cols-1 gap-3">
                        @if($canManageStaff)
                            <a href="{{ route('company.staff.edit', $staff->id) }}"
                               class="w-full text-center text-white py-3 rounded-2xl font-semibold shadow-sm"
                               style="background: {{ $theme }}">
                                編集する
                            </a>
                        @endif

                        @if($canResetPassword($staff))
                            <button type="button"
                                    onclick="openPasswordResetModal('{{ route('company.staff.reset-password', $staff->id) }}', @js($staff->name), @js($roleLabel))"
                                    class="w-full bg-red-500 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                パスワード初期化
                            </button>
                        @endif

                        @if($current->isMaster() && (int) $staff->id !== (int) $current->id)
                            <form method="POST"
                                  action="{{ route('company.staff.destroy', $staff->id) }}"
                                  onsubmit="return confirm(@js('「' . $staff->name . '」を削除しますか？この操作は取り消せません。'));">
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

    @if($current->isMaster())
        <div class="mt-8 lg:hidden">
            <a href="{{ route('company.staff.create') }}"
               class="w-full inline-flex items-center justify-center text-white px-4 py-4 rounded-2xl shadow font-bold hover:opacity-90 transition"
               style="background: {{ $theme }}">
                新規登録
            </a>
        </div>
    @endif

    <div id="passwordResetModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 text-white"
                 style="background: var(--company-theme-gradient);">
                <div class="text-xs font-bold tracking-wide text-white/75">PASSWORD RESET</div>
                <h2 class="mt-2 text-xl font-black">パスワード初期化</h2>
                <p class="mt-2 text-sm text-white/85">対象者へ渡す初期パスワードを入力してください。</p>
            </div>

            <form id="passwordResetForm" method="POST" class="p-6 space-y-5">
                @csrf

                <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="text-xs font-bold text-gray-500">対象担当者</div>
                    <div id="passwordResetStaffName" class="mt-1 text-base font-black text-gray-900"></div>
                    <div id="passwordResetStaffRole" class="mt-1 text-xs font-semibold text-gray-500"></div>
                </div>

                <div>
                    <label for="passwordResetInput" class="block text-sm font-bold text-gray-700 mb-2">初期パスワード</label>
                    <input id="passwordResetInput"
                           type="password"
                           name="initial_password"
                           minlength="8"
                           required
                           autocomplete="new-password"
                           class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-200"
                           placeholder="8文字以上で入力">
                    <p class="mt-2 text-xs text-gray-500">初期化後、対象者は次回ログイン時にパスワード変更画面へ進みます。</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            onclick="closePasswordResetModal()"
                            class="rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        閉じる
                    </button>
                    <button type="submit"
                            onclick="return confirm('入力した初期パスワードで初期化しますか？')"
                            class="rounded-2xl bg-red-500 px-4 py-3 text-sm font-bold text-white hover:bg-red-600">
                        初期化
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPasswordResetModal(action, name, role) {
    const modal = document.getElementById('passwordResetModal');
    const form = document.getElementById('passwordResetForm');
    const input = document.getElementById('passwordResetInput');

    form.action = action;
    document.getElementById('passwordResetStaffName').textContent = name || '';
    document.getElementById('passwordResetStaffRole').textContent = role || '';
    input.value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => input.focus(), 50);
}

function closePasswordResetModal() {
    const modal = document.getElementById('passwordResetModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closePasswordResetModal();
    }
});
</script>

@endsection
