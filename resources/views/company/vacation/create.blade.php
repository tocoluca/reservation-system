@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    toggleVacationMode();
});

function toggleVacationMode() {
    const isFull = document.getElementById('is_full_day').checked;
    document.getElementById('fullDayField').style.display = isFull ? 'block' : 'none';
    document.getElementById('timeFields').style.display = isFull ? 'none' : 'block';
}
</script>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    {{-- ================= ヘッダー ================= --}}
    <div class="rounded-2xl shadow mb-8 p-6 sm:p-8 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl sm:text-3xl font-bold">
            休暇申請
        </h1>

        <p class="text-sm opacity-90 mt-2">
            休暇期間を入力してください
        </p>
    </div>

    {{-- ================= フォーム ================= --}}
    <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8">

        <form method="POST" action="{{ route('company.vacation.store') }}">
            @csrf

            {{-- 申請者 --}}
            <div class="mb-8">
                <label class="block font-semibold mb-3">
                    申請者
                </label>

                <input type="hidden" name="staff_id" value="{{ $staff->id }}">

                <div class="w-full border rounded-lg p-3 bg-gray-100 text-gray-700">
                    {{ $staff->name }}
                </div>
            </div>

            {{-- 終日休暇 --}}
            <div class="mb-8">
                <label class="flex items-center gap-3 text-base">
                    <input type="checkbox"
                           name="is_full_day"
                           id="is_full_day"
                           value="1"
                           class="w-5 h-5"
                           onchange="toggleVacationMode()">
                    終日休暇
                </label>
            </div>

            {{-- 終日用 --}}
            <div id="fullDayField" class="mb-8">
                <label class="block font-semibold mb-3">
                    休暇日
                </label>
                <input type="date"
                       name="vacation_date"
                       class="w-full border rounded-lg p-3 text-base focus:ring-2 focus:outline-none"
                       style="--tw-ring-color: {{ $theme }}">
            </div>

            {{-- 時間指定用 --}}
            <div id="timeFields">

                {{-- 開始 --}}
                <div class="mb-8">
                    <label class="block font-semibold mb-3">
                        開始
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="date"
                               name="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full border rounded-lg p-3 text-base focus:ring-2 focus:outline-none"
                               style="--tw-ring-color: {{ $theme }}">

                        <select name="start_time"
                                class="w-full border rounded-lg p-3 text-base focus:ring-2 focus:outline-none"
                                style="--tw-ring-color: {{ $theme }}">
                            @for($h=0; $h<24; $h++)
                                @foreach(['00','30'] as $m)
                                    @php $t = sprintf('%02d:%s',$h,$m); @endphp
                                    <option value="{{ $t }}"
                                        {{ old('start_time') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- 終了 --}}
                <div class="mb-10">
                    <label class="block font-semibold mb-3">
                        終了
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="date"
                               name="end_date"
                               value="{{ old('end_date') }}"
                               class="w-full border rounded-lg p-3 text-base focus:ring-2 focus:outline-none"
                               style="--tw-ring-color: {{ $theme }}">

                        <select name="end_time"
                                class="w-full border rounded-lg p-3 text-base focus:ring-2 focus:outline-none"
                                style="--tw-ring-color: {{ $theme }}">
                            @for($h=0; $h<24; $h++)
                                @foreach(['00','30'] as $m)
                                    @php $t = sprintf('%02d:%s',$h,$m); @endphp
                                    <option value="{{ $t }}"
                                        {{ old('end_time') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            @endfor
                        </select>
                    </div>
                </div>

            </div>

            {{-- ================= ボタン ================= --}}
            <div class="flex flex-col sm:flex-row justify-between gap-4">

                <a href="{{ route('company.vacation.index') }}"
                   class="w-full sm:w-auto text-center sm:text-left
                          px-4 py-3 rounded-lg text-gray-600 hover:text-gray-800 transition">
                    ← 一覧へ戻る
                </a>

                <button type="submit"
                        class="w-full sm:w-auto text-white px-6 py-3 rounded-lg
                               shadow-lg hover:opacity-90 transition"
                        style="background: {{ $theme }}">
                    申請する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection