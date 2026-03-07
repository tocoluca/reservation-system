@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-2xl mx-auto space-y-8">

{{-- タイトル --}}
<div class="flex justify-between items-center">

    <div>
        <h1 class="text-2xl font-bold">メニュー編集</h1>
        <p class="text-gray-500 text-sm mt-1">
            メニュー内容の変更
        </p>
    </div>

    <a href="{{ route('company.menu.index') }}"
       class="px-4 py-2 text-sm rounded-lg border hover:bg-gray-50 transition"
       style="border-color: {{ $theme }}; color: {{ $theme }}">
        ← メニュー一覧
    </a>

</div>



{{-- 編集フォーム --}}
<div class="bg-white rounded-xl shadow">

<form method="POST"
action="{{ route('company.menu.update',$menu->id) }}">

@csrf
@method('PUT')


<div class="p-6 space-y-6">


{{-- カテゴリー --}}
<div>

<label class="block text-sm font-medium mb-1">
カテゴリー
</label>

<select name="menu_category_id"
        class="border rounded-lg px-3 py-2 w-full">

@foreach($categories as $category)

<option value="{{ $category->id }}"
@if($menu->menu_category_id==$category->id) selected @endif>

{{ $category->name }}

</option>

@endforeach

</select>

</div>



{{-- メニュー名 --}}
<div>

<label class="block text-sm font-medium mb-1">
メニュー名
</label>

<input
name="name"
value="{{ $menu->name }}"
class="border rounded-lg px-3 py-2 w-full">

</div>



{{-- 内容 --}}
<div>

<label class="block text-sm font-medium mb-1">
内容
</label>

<textarea
name="description"
rows="4"
class="border rounded-lg px-3 py-2 w-full">{{ $menu->description }}</textarea>

</div>



{{-- タグ --}}
<div>

<label class="block text-sm font-medium mb-2">
タグ
</label>

<div class="flex flex-wrap gap-2">

@foreach($tags as $tag)

<label class="flex items-center gap-1 bg-gray-100 px-3 py-1 rounded cursor-pointer">

<input type="checkbox"
name="tags[]"
value="{{ $tag->id }}"
@if($menu->tags->contains($tag->id)) checked @endif>

<span class="text-sm">
{{ $tag->name }}
</span>

</label>

@endforeach

</div>

</div>



{{-- 時間 --}}
<div>

<label class="block text-sm font-medium mb-1">
時間（分）
</label>

<input
name="duration"
type="number"
value="{{ $menu->duration }}"
class="border rounded-lg px-3 py-2 w-full">

</div>



{{-- 料金 --}}
<div>

<label class="block text-sm font-medium mb-1">
料金
</label>

<input
name="price"
type="number"
value="{{ $menu->price }}"
class="border rounded-lg px-3 py-2 w-full">

</div>


</div>



{{-- フッター --}}
<div class="border-t p-6 flex justify-end">

<button
class="text-white px-6 py-2 rounded-lg"
style="background: {{ $theme }}">

更新する

</button>

</div>


</form>

</div>

</div>

@endsection