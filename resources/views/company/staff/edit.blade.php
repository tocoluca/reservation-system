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
            担当者編集
        </h1>

        <p class="text-sm opacity-90 mt-1">
            {{ $staff->name }}（{{ $staff->staff_code }}）
        </p>
    </div>

    <div class="bg-white shadow rounded-xl p-8">

        <form method="POST" action="{{ route('company.staff.update', $staff->id) }}">
            @csrf
            @method('PUT')

            {{-- 名前 --}}
            <div class="mb-6">
                <label class="block font-semibold mb-2">
                    担当者名
                </label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $staff->name) }}"
                       class="w-full border rounded p-2 focus:ring-2 focus:outline-none"
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

                    <option value="staff"
                        {{ $staff->role === 'staff' ? 'selected' : '' }}>
                        一般スタッフ
                    </option>

                    <option value="leader"
                        {{ $staff->role === 'leader' ? 'selected' : '' }}>
                        リーダー
                    </option>

                    <option value="area_leader"
                        {{ $staff->role === 'area_leader' ? 'selected' : '' }}>
                        エリアリーダー
                    </option>

                    <option value="master"
                        {{ $staff->role === 'master' ? 'selected' : '' }}>
                        マスター
                    </option>

                </select>
            </div>

            {{-- 予約可否 --}}
            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           name="is_reservable"
                           value="1"
                           {{ $staff->is_reservable ? 'checked' : '' }}>
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
                       value="{{ old('priority_order', $staff->priority_order) }}"
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
                    更新する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection