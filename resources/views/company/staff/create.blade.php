@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-3xl mx-auto">

    {{-- ヘッダー --}}
    <div class="rounded-xl shadow mb-8 p-6 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl font-bold">
            担当者新規登録
        </h1>

        <p class="text-sm opacity-90 mt-1">
            新しい担当者を登録します
        </p>
    </div>

    <div class="bg-white shadow rounded-xl p-8">

        <form method="POST" action="{{ route('company.staff.store') }}">
            @csrf

            {{-- 担当者コード --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    担当者コード
                </label>
		<input type="text" disabled placeholder="自動採番されます">
            </div>

            {{-- 名前 --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    担当者名
                </label>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full border rounded p-2 focus:ring-2"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- パスワード --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    初期パスワード
                </label>
                <input type="password"
                       name="password"
                       class="w-full border rounded p-2 focus:ring-2"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- 権限 --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    権限
                </label>

                <select name="role"
                        class="w-full border rounded p-2 focus:ring-2"
                        style="--tw-ring-color: {{ $theme }}">

                    <option value="staff">一般スタッフ</option>
                    <option value="leader">リーダー</option>
                    <option value="area_leader">エリアリーダー</option>
                    <option value="master">マスター</option>

                </select>
            </div>

            {{-- 予約可否 --}}
            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           name="is_reservable"
                           value="1"
                           checked>
                    予約受付対象にする
                </label>
            </div>

            {{-- 表示順 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-2">
                    表示順
                </label>
                <input type="number"
                       name="priority_order"
                       value="0"
                       class="w-full border rounded p-2 focus:ring-2"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- ボタン --}}
            <div class="flex justify-between items-center">

                <a href="{{ route('company.staff.index') }}"
                   class="text-gray-500 hover:text-gray-700">
                    ← 一覧へ戻る
                </a>

                <button
                    class="text-white px-6 py-2 rounded shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    登録する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection