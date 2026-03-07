@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    {{-- ================= ヘッダー ================= --}}
    <div class="rounded-2xl shadow mb-8 p-6 sm:p-8 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl sm:text-3xl font-bold">
            担当者編集
        </h1>

        <p class="text-sm opacity-90 mt-2">
            {{ $staff->name }}（{{ $staff->staff_code }}）
        </p>
    </div>


    {{-- ================= フォーム ================= --}}
    <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8">

        <form method="POST"
              action="{{ route('company.staff.update', $staff->id) }}">
            @csrf
            @method('PUT')

            {{-- 名前 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    担当者名
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $staff->name) }}"
                       class="w-full border rounded-lg p-3 text-base
                              focus:ring-2 focus:outline-none"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- 権限 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    権限
                </label>

                <select name="role"
                        class="w-full border rounded-lg p-3 text-base
                               focus:ring-2 focus:outline-none"
                        style="--tw-ring-color: {{ $theme }}">

                    <option value="staff" {{ $staff->role === 'staff' ? 'selected' : '' }}>
                        一般スタッフ
                    </option>

                    <option value="leader" {{ $staff->role === 'leader' ? 'selected' : '' }}>
                        リーダー
                    </option>

                    <option value="area_leader" {{ $staff->role === 'area_leader' ? 'selected' : '' }}>
                        エリアリーダー
                    </option>

                    <option value="master" {{ $staff->role === 'master' ? 'selected' : '' }}>
                        マスター
                    </option>

                </select>
            </div>

            {{-- 予約可否 --}}
            <div class="mb-8">
                <label class="flex items-center gap-3 text-base">
                    <input type="checkbox"
                           name="is_reservable"
                           value="1"
                           class="w-5 h-5"
                           {{ $staff->is_reservable ? 'checked' : '' }}>
                    予約受付対象にする
                </label>
            </div>
<label>指名料</label>

<input type="number"
name="nomination_fee"
value="{{ old('nomination_fee',$staff->nomination_fee ?? 0) }}"
class="border p-2 w-full">

<p class="text-xs text-gray-400">
0円なら指名料なし
</p>
            {{-- 表示順 --}}
            <div class="mb-10">
                <label class="block font-semibold mb-3">
                    表示順
                </label>

                <input type="number"
                       name="priority_order"
                       value="{{ old('priority_order', $staff->priority_order) }}"
                       class="w-full border rounded-lg p-3 text-base
                              focus:ring-2 focus:outline-none"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- ================= ボタン ================= --}}
            <div class="flex flex-col sm:flex-row justify-between gap-4">

                <a href="{{ route('company.staff.index') }}"
                   class="w-full sm:w-auto text-center sm:text-left
                          px-4 py-3 rounded-lg text-gray-600 hover:text-gray-800 transition">
                    ← 一覧へ戻る
                </a>

                <button type="submit"
                        class="w-full sm:w-auto text-white px-6 py-3 rounded-lg
                               shadow-lg hover:opacity-90 transition"
                        style="background: {{ $theme }}">
                    更新する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection