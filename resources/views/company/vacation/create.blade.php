@extends('layouts.company')

@section('content')

@php
    $theme = auth()->guard('company')->user()->company->theme_color ?? '#3b82f6';
@endphp
<script>
function toggleVacationMode() {

    const isFull = document.getElementById('is_full_day').checked;

    document.getElementById('fullDayField').style.display =
        isFull ? 'block' : 'none';

    document.getElementById('timeFields').style.display =
        isFull ? 'none' : 'block';
}
</script>
<div class="max-w-3xl mx-auto">

    {{-- ヘッダー --}}
    <div class="rounded-xl shadow mb-8 p-6 text-white"
         style="background: {{ $theme }}">

        <h1 class="text-2xl font-bold">
            休暇申請
        </h1>

        <p class="text-sm opacity-90 mt-1">
            休暇期間を入力してください
        </p>
    </div>

    <div class="bg-white shadow rounded-xl p-8">

        <form method="POST" action="{{ route('company.vacation.store') }}">
            @csrf

            {{-- 担当者 --}}
	<div class="mb-6">
	    <label class="block font-semibold mb-2">
	        申請者
	    </label>

	    {{-- hiddenで送信 --}}
	    <input type="hidden" name="staff_id" value="{{ $staff->id }}">

	    {{-- 表示だけ --}}
	    <div class="w-full border rounded p-2 bg-gray-100">
	        {{ $staff->name }}
	    </div>
	</div>	
	{{-- 終日休暇 --}}
	<div class="mb-6">
	    <label class="flex items-center gap-2">
	        <input type="checkbox"
	               name="is_full_day"
	               id="is_full_day"
	               value="1"
	               onchange="toggleVacationMode()">
	        終日休暇
	    </label>
	</div>
<div id="fullDayField" style="display:none;">
    <div class="mb-6">
        <label class="block font-semibold mb-2">
            休暇日
        </label>
        <input type="date"
               name="vacation_date"
               class="w-full border rounded p-2">
    </div>
</div>

<div id="timeFields">
{{-- 開始 --}}
<div class="mb-6">
    <label class="block font-semibold mb-2">開始</label>

    <div class="grid grid-cols-2 gap-4">
        {{-- 日付 --}}
        <input type="date"
               name="start_date"
               value="{{ old('start_date') }}"
               class="w-full border rounded p-2 focus:ring-2"
               style="--tw-ring-color: {{ $theme }}">

        {{-- 時間（00 / 30のみ） --}}
        <select name="start_time"
                class="w-full border rounded p-2 focus:ring-2"
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
<div class="mb-8">
    <label class="block font-semibold mb-2">終了</label>

    <div class="grid grid-cols-2 gap-4">
        <input type="date"
               name="end_date"
               value="{{ old('end_date') }}"
               class="w-full border rounded p-2 focus:ring-2"
               style="--tw-ring-color: {{ $theme }}">

        <select name="end_time"
                class="w-full border rounded p-2 focus:ring-2"
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
            {{-- ボタン --}}
            <div class="flex justify-between items-center">

                <a href="{{ route('company.vacation.index') }}"
                   class="text-gray-500 hover:text-gray-700">
                    ← 一覧へ戻る
                </a>

                <button
                    class="text-white px-6 py-2 rounded shadow hover:opacity-90 transition"
                    style="background: {{ $theme }}">
                    申請する
                </button>

            </div>

        </form>

    </div>

</div>

@endsection