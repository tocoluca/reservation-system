@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-6xl mx-auto px-6 py-8">

<div class="mb-6">

<a
href="{{ route('company.customers') }}"
class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border hover:shadow transition"
style="color: {{ $theme }}; border-color: {{ $theme }}">

<svg xmlns="http://www.w3.org/2000/svg"
class="w-4 h-4"
fill="none"
viewBox="0 0 24 24"
stroke="currentColor">

<path stroke-linecap="round"
stroke-linejoin="round"
stroke-width="2"
d="M15 19l-7-7 7-7" />

</svg>

顧客一覧に戻る

</a>

</div>

<div class="bg-white shadow rounded-xl p-6 mb-8">

<h1 class="text-2xl font-bold mb-4">
{{ $customer->name }}
</h1>

<div class="grid grid-cols-3 gap-6 text-sm">

<div>
<div class="text-gray-500">電話</div>
<div class="font-semibold">
{{ $customer->phone }}
</div>
</div>

<div>
<div class="text-gray-500">来店回数</div>
<div class="font-semibold">
{{ $customer->visit_count }}
</div>
</div>

<div>
<div class="text-gray-500">最終来店</div>
<div class="font-semibold">

@if($customer->last_visit)
{{ \Carbon\Carbon::parse($customer->last_visit)->format('Y-m-d') }}
@endif

</div>
</div>

</div>

</div>


{{-- メモ --}}
<div class="bg-white shadow rounded-xl p-6 mb-8">

<h2 class="text-xl font-bold mb-4">
顧客メモ
</h2>

<form method="POST"
action="{{ route('company.customers.note',$customer->id) }}">

@csrf

<textarea
name="note"
rows="3"
class="border rounded-lg p-3 w-full"
placeholder="メモを入力"></textarea>

<button
style="background: {{ $theme }}"
class="text-white px-6 py-2 rounded-lg mt-3">

保存

</button>

</form>

<div class="mt-6 space-y-3">

@forelse($customer->notes as $note)

<div class="border rounded-lg p-3 bg-gray-50 flex justify-between items-start">

    <div>
        <div>{{ $note->note }}</div>
        <div class="text-xs text-gray-500 mt-1">
            {{ $note->created_at->format('Y-m-d H:i') }}
        </div>
    </div>

    {{-- 削除ボタン --}}
    <form method="POST"
          action="{{ route('company.customers.note.delete', $note->id) }}"
          onsubmit="return confirm('削除しますか？')">
        @csrf
        @method('DELETE')

        <button class="text-red-500 text-sm">
            削除
        </button>
    </form>

</div>

@empty

<div class="text-gray-400">
メモはまだありません
</div>

@endforelse

</div>

</div>


{{-- 写真 --}}
<div class="bg-white shadow rounded-xl p-6">

<h2 class="text-xl font-bold mb-4">
顧客写真
</h2>

<form
method="POST"
action="{{ route('company.customers.photo',$customer->id) }}"
enctype="multipart/form-data">

@csrf

<input
type="file"
name="photo"
class="border p-2 rounded">

<button
style="background: {{ $theme }}"
class="text-white px-6 py-2 rounded-lg ml-3">

アップロード

</button>

</form>

<div class="grid grid-cols-4 gap-4 mt-6">

@forelse($customer->photos as $photo)

<div class="relative">

    <img
    src="{{ asset($photo->path) }}"
    class="rounded-lg shadow w-full">

    {{-- 削除ボタン --}}
    <form method="POST"
          action="{{ route('company.customers.photo.delete', $photo->id) }}"
          onsubmit="return confirm('削除しますか？')"
          class="absolute top-2 right-2">
        @csrf
        @method('DELETE')

        <button class="bg-black/50 text-white px-2 py-1 text-xs rounded">
            ✕
        </button>
    </form>

</div>

@empty

<div class="text-gray-400">
写真はまだありません
</div>

@endforelse

</div>

</div>

</div>

@endsection