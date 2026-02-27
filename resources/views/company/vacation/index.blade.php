@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $current = auth()->guard('company')->user();
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    {{-- ================= ヘッダー ================= --}}
    <div class="rounded-2xl shadow mb-8 p-6 sm:p-8 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl sm:text-3xl font-bold">
            休暇管理
        </h1>

        <p class="text-sm opacity-90 mt-2">
            休暇申請と承認管理
        </p>
    </div>

    {{-- 新規申請ボタン --}}
    <div class="mb-8 text-center sm:text-right">
        <a href="{{ route('company.vacation.create') }}"
           class="w-full sm:w-auto inline-block text-center text-white px-6 py-3 rounded-lg shadow hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ＋ 休暇申請
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
@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<a href="{{ route('company.dashboard') }}"
   class="group inline-flex items-center justify-center gap-2
          w-full sm:w-auto
          px-6 py-3
          rounded-xl
          text-white font-semibold
          shadow-lg
          transition-all duration-200
          hover:shadow-xl hover:-translate-y-0.5"
   style="background: {{ $theme }}">

    {{-- 左矢印アイコン --}}
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-5 h-5 transition-transform duration-200 group-hover:-translate-x-1"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"/>
    </svg>

    ダッシュボードに戻る

</a>
    </div>

</div>

@endsection