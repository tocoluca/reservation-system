@extends('layouts.company')

@section('content')

@php
    use Carbon\Carbon;

    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $current = auth()->guard('company')->user();

    $vacationCollection = collect($vacations ?? []);
    $pendingCount = $vacationCollection->where('status', 'pending')->count();
    $approvedCount = $vacationCollection->where('status', 'approved')->count();
    $rejectedCount = $vacationCollection->where('status', 'rejected')->count();
    $cancelledCount = $vacationCollection->where('status', 'cancelled')->count();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-white mb-6">
        <div class="px-5 sm:px-8 py-7 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #1f2937 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                        VACATION MANAGEMENT
                    </div>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-bold">
                        休暇管理
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85">
                        休暇申請の確認、承認、取消、削除をわかりやすく管理できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold transition">
                        ← ダッシュボード
                    </a>

                    <a href="{{ route('company.vacation.create') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white text-gray-900 font-bold shadow hover:opacity-90 transition">
                        ＋ 休暇申請
                    </a>
                </div>
            </div>
        </div>

        <div class="px-5 sm:px-8 py-5 bg-gray-50">
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">申請中</div>
                    <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($pendingCount) }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">承認済み</div>
                    <div class="mt-2 text-2xl font-bold text-green-600">{{ number_format($approvedCount) }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">却下</div>
                    <div class="mt-2 text-2xl font-bold text-red-600">{{ number_format($rejectedCount) }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">取消済み</div>
                    <div class="mt-2 text-2xl font-bold text-gray-600">{{ number_format($cancelledCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden lg:block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900">申請一覧</h2>
            <p class="text-sm text-gray-500 mt-1">状態確認と対応操作をまとめて行えます。</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead>
                    <tr class="text-left text-gray-500 bg-white border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">担当者</th>
                        <th class="px-4 py-4 font-semibold">開始</th>
                        <th class="px-4 py-4 font-semibold">終了</th>
                        <th class="px-4 py-4 font-semibold">状態</th>
                        <th class="px-6 py-4 font-semibold text-right">操作</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($vacationCollection as $vacation)
                    @php
                        $startAt = Carbon::parse($vacation->start_at);
                        $endAt = Carbon::parse($vacation->end_at);
                    @endphp

                    <tr class="border-b border-gray-100 hover:bg-gray-50/70 transition">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm"
                                     style="background: {{ $theme }}">
                                    {{ mb_substr($vacation->staff->name ?? '-', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">{{ $vacation->staff->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        申請ID：#{{ $vacation->id }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-5 text-gray-700">
                            <div class="font-semibold">{{ $startAt->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $startAt->format('H:i') }}</div>
                        </td>

                        <td class="px-4 py-5 text-gray-700">
                            <div class="font-semibold">{{ $endAt->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $endAt->format('H:i') }}</div>
                        </td>

                        <td class="px-4 py-5">
                            @if($vacation->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                    申請中
                                </span>
                            @elseif($vacation->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    承認済み
                                </span>
                            @elseif($vacation->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                    取消済み
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    却下
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex justify-end items-center gap-2 flex-wrap">
                                @if($current->role !== 'member' && $vacation->status === 'pending')
                                    <form method="POST"
                                          action="{{ route('company.vacation.approve', $vacation->id) }}"
                                          class="m-0">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-green-500 text-white font-semibold shadow-sm hover:bg-green-600 transition">
                                            承認
                                        </button>
                                    </form>

                                    <form method="POST"
                                          action="{{ route('company.vacation.reject', $vacation->id) }}"
                                          class="m-0">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-500 text-white font-semibold shadow-sm hover:bg-red-600 transition">
                                            却下
                                        </button>
                                    </form>
                                @endif

                                @if(in_array($current->role, ['leader', 'area_leader', 'master']) && $vacation->status === 'approved')
                                    <form method="POST"
                                          action="{{ route('company.vacation.cancel', $vacation->id) }}"
                                          class="m-0">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-orange-500 text-white font-semibold shadow-sm hover:bg-orange-600 transition">
                                            取消
                                        </button>
                                    </form>
                                @endif

                                <form method="POST"
                                      action="{{ route('company.vacation.destroy', $vacation->id) }}"
                                      class="m-0"
                                      onsubmit="return confirm('この休暇申請を削除しますか？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gray-900 text-white font-semibold shadow-sm hover:bg-black transition">
                                        削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="max-w-md mx-auto">
                                <div class="text-lg font-bold text-gray-700">休暇申請はまだありません</div>
                                <p class="text-sm text-gray-400 mt-2">必要になったら休暇申請を登録してください。</p>
                                <div class="mt-6">
                                    <a href="{{ route('company.vacation.create') }}"
                                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                                       style="background: {{ $theme }}">
                                        ＋ 休暇申請
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
        @forelse($vacationCollection as $vacation)
            @php
                $startAt = Carbon::parse($vacation->start_at);
                $endAt = Carbon::parse($vacation->end_at);
            @endphp

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-5">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm shrink-0"
                             style="background: {{ $theme }}">
                            {{ mb_substr($vacation->staff->name ?? '-', 0, 1) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-lg font-bold text-gray-900 truncate">
                                {{ $vacation->staff->name ?? '-' }}
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                @if($vacation->status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                        申請中
                                    </span>
                                @elseif($vacation->status === 'approved')
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        承認済み
                                    </span>
                                @elseif($vacation->status === 'cancelled')
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                        取消済み
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        却下
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            <div class="text-xs text-gray-500">開始</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">{{ $startAt->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $startAt->format('H:i') }}</div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            <div class="text-xs text-gray-500">終了</div>
                            <div class="mt-1 text-sm font-bold text-gray-900">{{ $endAt->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $endAt->format('H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <div class="grid grid-cols-1 gap-3">
                        @if($current->role !== 'member' && $vacation->status === 'pending')
                            <form method="POST"
                                  action="{{ route('company.vacation.approve', $vacation->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-green-500 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                    承認
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('company.vacation.reject', $vacation->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-red-500 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                    却下
                                </button>
                            </form>
                        @endif

                        @if(in_array($current->role, ['leader', 'area_leader', 'master']) && $vacation->status === 'approved')
                            <form method="POST"
                                  action="{{ route('company.vacation.cancel', $vacation->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-orange-500 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                    取消
                                </button>
                            </form>
                        @endif

                        <form method="POST"
                              action="{{ route('company.vacation.destroy', $vacation->id) }}"
                              onsubmit="return confirm('この休暇申請を削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full bg-gray-900 text-white py-3 rounded-2xl font-semibold shadow-sm">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 text-center">
                <div class="text-lg font-bold text-gray-700">休暇申請はまだありません</div>
                <p class="text-sm text-gray-400 mt-2">新規申請から登録してください。</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8 lg:hidden">
        <a href="{{ route('company.vacation.create') }}"
           class="w-full inline-flex items-center justify-center text-white px-4 py-4 rounded-2xl shadow font-bold hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ＋ 休暇申請
        </a>
    </div>

</div>

@endsection