@extends('layouts.company')
@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp
@section('content')

<h1 class="text-xl font-bold mb-6">担当者一覧</h1>

<a href="{{ route('company.staff.create') }}"
   class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
    新規登録
</a>

<table class="w-full bg-white shadow rounded">
<tr class="bg-gray-100">
<th class="p-2">コード</th>
<th>名前</th>
<th>権限</th>
<th>予約可</th>
<th>優先順</th>
<th></th>
</tr>

@foreach($staff as $s)
<tr class="border-t">
<td class="p-2">{{ $s->staff_code }}</td>
<td>{{ $s->name }}</td>
<td>{{ $s->role }}</td>
<td>{{ $s->is_reservable ? '○' : '×' }}</td>
<td>{{ $s->priority_order }}</td>
<td>
{{-- 編集 --}}
<a href="{{ route('company.staff.edit',$s->id) }}"
   class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold shadow transition-all duration-200"
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
      class="inline ml-2">
    @csrf
    <button type="submit"
        onclick="return confirm('パスワードを初期化しますか？')"
        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold shadow transition-all duration-200"
        style="background: #ef4444; color: white;"
        onmouseover="this.style.opacity=0.85"
        onmouseout="this.style.opacity=1">

        {{-- 鍵アイコン --}}
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
@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp
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

</table>

@endsection