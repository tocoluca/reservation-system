@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $current = auth()->guard('company')->user();
@endphp

    {{-- ================= ヘッダー ================= --}}

<div class="max-w-6xl mx-auto">

    {{-- タイトル --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold">休暇管理</h1>
            <p class="text-gray-500 text-sm mt-1">休暇申請と承認管理</p>
        </div>
<a href="{{ route('company.dashboard') }}"
   class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50 transition"
   style="border-color: {{ $theme }}; color: {{ $theme }}">
    ← ダッシュボード
</a>

    </div>

    {{-- ================= PC表示：テーブル ================= --}}
    <div class="hidden md:block bg-white shadow rounded-xl overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">担当者</th>
                    <th class="text-left">開始</th>
                    <th class="text-left">終了</th>
                    <th class="text-left">状態</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>

            @forelse($vacations ?? [] as $vacation)

                <tr class="border-t">
                    <td class="p-3">{{ $vacation->staff->name }}</td>
                    <td>{{ $vacation->start_at }}</td>
                    <td>{{ $vacation->end_at }}</td>
                    <td>
                        @if($vacation->status === 'pending')
                            <span class="text-yellow-600 font-semibold">申請中</span>
                        @elseif($vacation->status === 'approved')
                            <span class="text-green-600 font-semibold">承認済</span>
                        @else
                            <span class="text-red-600 font-semibold">却下</span>
                        @endif
                    </td>

                    <td class="text-center space-x-2">
                        @if($current->role !== 'member' && $vacation->status === 'pending')
                            <form method="POST"
                                  action="{{ route('company.vacation.approve',$vacation->id) }}"
                                  class="inline">@csrf
                                <button class="text-green-600 hover:underline">承認</button>
                            </form>

                            <form method="POST"
                                  action="{{ route('company.vacation.reject',$vacation->id) }}"
                                  class="inline ml-2">@csrf
                                <button class="text-red-600 hover:underline">却下</button>
                            </form>
                        @endif

                        @if(in_array($current->role,['leader','area_leader','master'])
                            && $vacation->status === 'approved')
                            <form method="POST"
                                  action="{{ route('company.vacation.cancel',$vacation->id) }}"
                                  class="inline ml-2">@csrf
                                <button class="text-orange-600 hover:underline">取消</button>
                            </form>
                        @endif

                        <form method="POST"
                              action="{{ route('company.vacation.destroy',$vacation->id) }}"
                              class="inline ml-2">@csrf
                            @method('DELETE')
                            <button class="text-gray-500 hover:underline">削除</button>
                        </form>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center p-6 text-gray-400">
                        休暇申請はありません
                    </td>
                </tr>
            @endforelse

            </tbody>
        </table>
    </div>


    {{-- ================= スマホ表示：カード ================= --}}
    <div class="md:hidden space-y-4">

        @forelse($vacations ?? [] as $vacation)

        <div class="bg-white shadow rounded-2xl p-4">

            <div class="font-bold text-lg mb-1">
                {{ $vacation->staff->name }}
            </div>

            <div class="text-sm text-gray-600 mb-2">
                {{ $vacation->start_at }} 〜 {{ $vacation->end_at }}
            </div>

            <div class="mb-4">
                @if($vacation->status === 'pending')
                    <span class="text-yellow-600 font-semibold">申請中</span>
                @elseif($vacation->status === 'approved')
                    <span class="text-green-600 font-semibold">承認済</span>
                @else
                    <span class="text-red-600 font-semibold">却下</span>
                @endif
            </div>

            <div class="space-y-2">

                @if($current->role !== 'member' && $vacation->status === 'pending')

                    <form method="POST"
                          action="{{ route('company.vacation.approve',$vacation->id) }}">
                        @csrf
                        <button class="w-full bg-green-500 text-white py-2 rounded-lg">
                            承認
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('company.vacation.reject',$vacation->id) }}">
                        @csrf
                        <button class="w-full bg-red-500 text-white py-2 rounded-lg">
                            却下
                        </button>
                    </form>

                @endif

                @if(in_array($current->role,['leader','area_leader','master'])
                    && $vacation->status === 'approved')
                    <form method="POST"
                          action="{{ route('company.vacation.cancel',$vacation->id) }}">
                        @csrf
                        <button class="w-full bg-orange-500 text-white py-2 rounded-lg">
                            取消
                        </button>
                    </form>
                @endif

                <form method="POST"
                      action="{{ route('company.vacation.destroy',$vacation->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="w-full bg-gray-400 text-white py-2 rounded-lg">
                        削除
                    </button>
                </form>

            </div>

        </div>

        @empty
            <div class="text-center text-gray-400">
                休暇申請はありません
            </div>
        @endforelse

    </div>


    {{-- 戻る --}}
    <div class="mt-10 text-center sm:text-left">
    {{-- 新規申請ボタン --}}
        <a href="{{ route('company.vacation.create') }}"
           class="w-full sm:w-auto inline-block text-center text-white px-6 py-3 rounded-lg shadow hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ＋ 休暇申請
        </a>

    </div>

</div>

@endsection