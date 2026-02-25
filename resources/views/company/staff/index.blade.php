@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- ヘッダー --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">
            担当者一覧
        </h1>

        <a href="{{ route('company.staff.create') }}"
           style="background: {{ $theme }};"
           class="text-white px-4 py-2 rounded-lg shadow hover:opacity-90 transition">
            ＋ 新規登録
        </a>
    </div>

    {{-- メッセージ --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- 一覧テーブル --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">

        <table class="w-full text-sm">

            <thead style="background: {{ $theme }}20;">
                <tr class="text-left">
                    <th class="p-3">コード</th>
                    <th>名前</th>
                    <th>権限</th>
                    <th>予約可</th>
                    <th>優先順</th>
                    <th class="text-right pr-4">操作</th>
                </tr>
            </thead>

            <tbody>

            @foreach($staffs as $s)
                <tr class="border-t hover:bg-gray-50 transition">

                    <td class="p-3 font-mono">
                        {{ $s->staff_code }}
                    </td>

                    <td class="font-semibold">
                        {{ $s->name }}
                    </td>

                    <td>
                        {{ $s->role }}
                    </td>

                    <td>
                        @if($s->is_reservable)
                            <span class="text-green-600 font-bold">○</span>
                        @else
                            <span class="text-red-500 font-bold">×</span>
                        @endif
                    </td>

                    <td>
                        {{ $s->priority_order }}
                    </td>

                    <td class="text-right pr-4 space-x-2">

                        {{-- 編集 --}}
                        <a href="{{ route('company.staff.edit',$s->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold shadow transition duration-200"
                           style="background: {{ $theme }}; color: white;"
                           onmouseover="this.style.opacity=0.85"
                           onmouseout="this.style.opacity=1">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-4 h-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M11 5h2m-1-1v2m7 7l-8 8H4v-3l8-8m0 0l3-3a2.121 2.121 0 013 3l-3 3m-3-3l3 3"/>
                            </svg>

                            編集
                        </a>

                        {{-- パスワード初期化 --}}
                        @if(auth()->guard('company')->user()->role !== 'staff')
                            <form method="POST"
                                  action="{{ route('company.staff.reset-password',$s->id) }}"
                                  class="inline">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('パスワードを初期化しますか？')"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold shadow transition duration-200"
                                    style="background: #ef4444; color: white;"
                                    onmouseover="this.style.opacity=0.85"
                                    onmouseout="this.style.opacity=1">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-4 h-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M12 11c1.657 0 3-1.343 3-3V7a3 3 0 10-6 0v1c0 1.657 1.343 3 3 3z"/>
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M5 11h14v9H5z"/>
                                    </svg>

                                    ＰＷ初期化
                                </button>
                            </form>
                        @endif

                    </td>

                </tr>
            @endforeach

            </tbody>
        </table>

    </div>

    {{-- 戻るリンク --}}
    <div class="mt-6">
        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition duration-200"
           style="color: {{ $theme }}">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7" />
            </svg>

            ダッシュボードへ戻る
        </a>
    </div>

</div>

@endsection