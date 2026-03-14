@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto">

<div class="flex justify-between items-center mb-6">

<h1 class="text-xl font-bold">
シフトパターン
</h1>

<a href="{{ route('company.dashboard') }}"
class="px-3 py-1 border rounded"
style="border-color:{{ $theme }};color:{{ $theme }}">
← ダッシュボード
</a>

</div>


<form method="POST" action="/company/shift-patterns/store">
@csrf

<div class="flex gap-3 mb-6">

<input
name="name"
placeholder="早番"
class="border p-2 rounded">

<input
type="time"
name="start_time"
class="border p-2 rounded">

<input
type="time"
name="end_time"
class="border p-2 rounded">

<button
class="px-4 py-2 text-white rounded"
style="background:{{ $theme }}">
追加
</button>

</div>

</form>


<div class="bg-white shadow rounded-xl p-6">

<table class="w-full border text-sm">

<tr style="background:{{ $theme }};color:white">

<th class="p-3">名前</th>
<th class="p-3">時間</th>
<th></th>

</tr>

@foreach($patterns as $p)

<tr>

<td class="p-3">{{ $p->name }}</td>

<td class="p-3">
{{ $p->start_time }} - {{ $p->end_time }}
</td>

<td class="p-3">

<a
href="/company/shift-patterns/delete/{{ $p->id }}"
class="text-red-500">
削除
</a>

</td>

</tr>

@endforeach

</table>

</div>

</div>

@endsection