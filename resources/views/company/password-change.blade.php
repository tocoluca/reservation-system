@extends('layouts.company')
@section('content')

<div class="max-w-md mx-auto bg-white shadow rounded-xl p-8">

    <h1 class="text-xl font-bold mb-6">
        パスワード変更
    </h1>

    <form method="POST">
        @csrf

        <div class="mb-4">
            <input type="password"
                   name="password"
                   placeholder="新しいパスワード"
                   class="border p-3 w-full rounded">
        </div>
@error('current_password')
    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
@enderror
        <div class="mb-6">
            <input type="password"
                   name="password_confirmation"
                   placeholder="確認用パスワード"
                   class="border p-3 w-full rounded">
        </div>
@error('password')
    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
@enderror
        <button class="bg-blue-500 text-white px-4 py-2 rounded w-full">
            変更する
        </button>
    </form>

</div>

@endsection