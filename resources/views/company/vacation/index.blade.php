@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-5xl mx-auto">

    {{-- ヘッダー --}}
    <div class="rounded-xl shadow mb-8 p-6 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl font-bold">
            休暇管理
        </h1>

        <p class="text-sm opacity-90 mt-1">
            休暇申請と承認管理
        </p>
    </div>

    {{-- 新規申請ボタン --}}
    <div class="mb-6 text-right">
        <a href="{{ route('company.vacation.create') }}"
           class="text-white px-6 py-2 rounded shadow hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ＋ 休暇申請
        </a>
    </div>

    <div class="bg-white shadow rounded-xl p-6">

        <table class="w-full table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">担当者</th>
                    <th class="border p-2">開始</th>
                    <th class="border p-2">終了</th>
                    <th class="border p-2">状態</th>
                    <th class="border p-2">操作</th>
                </tr>
            </thead>
            <tbody>

            @forelse($vacations ?? [] as $vacation)

                <tr>
                    <td class="border p-2">
                        {{ $vacation->staff->name }}
                    </td>

                    <td class="border p-2">
                        {{ $vacation->start_at }}
                    </td>

                    <td class="border p-2">
                        {{ $vacation->end_at }}
                    </td>

                    <td class="border p-2">
                        @if($vacation->status === 'pending')
                            <span class="text-yellow-600">申請中</span>
                        @elseif($vacation->status === 'approved')
                            <span class="text-green-600">承認済</span>
                        @else
                            <span class="text-red-600">却下</span>
                        @endif
                    </td>
			<td class="border p-2 text-center">

			    @php
			        $current = auth()->guard('company')->user();
			    @endphp

			    {{-- 承認・却下（pendingのみ） --}}
			    @if($current->role !== 'member' && $vacation->status === 'pending')

			        <form method="POST"
			              action="{{ route('company.vacation.approve',$vacation->id) }}"
			              class="inline">
			            @csrf
			            <button class="text-green-600 hover:underline">
			                承認
			            </button>
			        </form>

			        <form method="POST"
			              action="{{ route('company.vacation.reject',$vacation->id) }}"
			              class="inline ml-2">
			            @csrf
			            <button class="text-red-600 hover:underline">
			                却下
			            </button>
			        </form>

			    @endif

			    {{-- 承認済の取消（リーダー以上） --}}
			    @if(
			        in_array($current->role, ['leader','area_leader','master'])
			        && $vacation->status === 'approved'
			    )
			        <form method="POST"
			              action="{{ route('company.vacation.cancel',$vacation->id) }}"
			              class="inline ml-2">
			            @csrf
			            <button class="text-orange-600 hover:underline">
			                取消
			            </button>
			        </form>
			    @endif

			    {{-- 削除（必要なら条件追加可） --}}
			    <form method="POST"
			          action="{{ route('company.vacation.destroy',$vacation->id) }}"
			          class="inline ml-2">
			        @csrf
			        @method('DELETE')
			        <button class="text-gray-500 hover:underline">
			            削除
			        </button>
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

		<div class="flex justify-between items-center mt-6">

		<a href="{{ route('company.dashboard') }}"
		   class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200"
		   style="color: {{ $theme }}">

		    <svg xmlns="http://www.w3.org/2000/svg"
		         class="w-4 h-4 transition-transform duration-200"
		         fill="none" viewBox="0 0 24 24" stroke="currentColor">
		        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
		              d="M15 19l-7-7 7-7" />
		    </svg>

		    ダッシュボードへ戻る
		</a>

		</div>

</div>

@endsection