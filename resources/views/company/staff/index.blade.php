@extends('layouts.company')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    {{-- ヘッダー --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold">
            担当者一覧
        </h1>

        <a href="{{ route('company.staff.create') }}"
           class="w-full sm:w-auto text-center text-white px-4 py-3 rounded-lg shadow hover:opacity-90 transition"
           style="background: {{ $theme }}">
            ＋ 新規登録
        </a>
    </div>

    {{-- メッセージ --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-6 rounded-lg">
            {{ session('success') }}
        </div>
    @endif


    {{-- ========================= --}}
    {{-- PC表示：テーブル --}}
    {{-- ========================= --}}
    <div class="hidden md:block bg-white shadow rounded-xl overflow-hidden">

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
                    <td class="p-3 font-mono">{{ $s->staff_code }}</td>
                    <td class="font-semibold">{{ $s->name }}</td>
                    <td>{{ $s->role }}</td>
                    <td>
                        {!! $s->is_reservable
                            ? '<span class="text-green-600 font-bold">○</span>'
                            : '<span class="text-red-500 font-bold">×</span>' !!}
                    </td>
                    <td>{{ $s->priority_order }}</td>

                    <td class="text-right pr-4 space-x-2">
                        <a href="{{ route('company.staff.edit',$s->id) }}"
                           class="px-3 py-1 text-sm rounded-lg text-white shadow"
                           style="background: {{ $theme }}">
                            編集
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>


    {{-- ========================= --}}
    {{-- スマホ表示：カード --}}
    {{-- ========================= --}}
    <div class="md:hidden space-y-4">

        @foreach($staffs as $s)

        <div class="bg-white shadow rounded-2xl p-4">

            <div class="flex justify-between items-center mb-2">
                <div class="font-bold text-lg">
                    {{ $s->name }}
                </div>

                <span class="text-sm font-mono text-gray-500">
                    {{ $s->staff_code }}
                </span>
            </div>

            <div class="text-sm text-gray-600 space-y-1 mb-4">
                <div>権限：{{ $s->role }}</div>
                <div>
                    予約可：
                    {!! $s->is_reservable
                        ? '<span class="text-green-600 font-bold">○</span>'
                        : '<span class="text-red-500 font-bold">×</span>' !!}
                </div>
                <div>優先順：{{ $s->priority_order }}</div>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('company.staff.edit',$s->id) }}"
                   class="flex-1 text-center text-white py-2 rounded-lg shadow"
                   style="background: {{ $theme }}">
                    編集
                </a>

                @if(auth()->guard('company')->user()->role !== 'staff')
                <form method="POST"
                      action="{{ route('company.staff.reset-password',$s->id) }}"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('パスワードを初期化しますか？')"
                            class="w-full bg-red-500 text-white py-2 rounded-lg shadow">
                        PW初期化
                    </button>
                </form>
                @endif

            </div>

        </div>

        @endforeach

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