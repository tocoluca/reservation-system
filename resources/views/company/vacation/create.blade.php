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
    const fullDayField = document.getElementById('fullDayField');
    const timeFields = document.getElementById('timeFields');
    const fullModeCard = document.getElementById('fullModeCard');
    const timeModeCard = document.getElementById('timeModeCard');

    if (fullDayField) {
        fullDayField.style.display = isFull ? 'block' : 'none';
    }

    if (timeFields) {
        timeFields.style.display = isFull ? 'none' : 'block';
    }

    if (fullModeCard) {
        fullModeCard.classList.toggle('ring-2', isFull);
        fullModeCard.classList.toggle('ring-offset-2', isFull);
    }

    if (timeModeCard) {
        timeModeCard.classList.toggle('ring-2', !isFull);
        timeModeCard.classList.toggle('ring-offset-2', !isFull);
    }

    if (fullModeCard && isFull) {
        fullModeCard.style.setProperty('--tw-ring-color', '{{ $theme }}');
    }

    if (timeModeCard && !isFull) {
        timeModeCard.style.setProperty('--tw-ring-color', '{{ $theme }}');
    }
}
</script>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="rounded-3xl overflow-hidden shadow-sm border border-gray-100 bg-white mb-6">
        <div class="px-5 sm:px-8 py-7 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #1f2937 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                        VACATION REQUEST
                    </div>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-bold">
                        休暇申請
                    </h1>

                    <p class="mt-2 text-sm sm:text-base text-white/85">
                        終日休暇または時間指定休暇を選んで、わかりやすく申請できます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.vacation.index') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold transition">
                        ← 一覧へ戻る
                    </a>

                    <div class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white text-gray-900 font-bold shadow">
                        申請者：{{ $staff->name }}
                    </div>
                </div>
            </div>
        </div>

        <div class="px-5 sm:px-8 py-5 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">申請者</div>
                    <div class="mt-2 text-base font-bold text-gray-900">{{ $staff->name }}</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">申請方法</div>
                    <div class="mt-2 text-base font-bold text-gray-900">終日 / 時間指定</div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 px-4 py-4 shadow-sm">
                    <div class="text-xs text-gray-500 font-semibold">入力のポイント</div>
                    <div class="mt-2 text-sm font-medium text-gray-700 leading-6">
                        終日は1日単位、時間指定は開始と終了を入力します。
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._vacation_shift_nav', ['current' => 'create'])
    </div>

    <form method="POST" action="{{ route('company.vacation.store') }}">
        @csrf
        <input type="hidden" name="staff_id" value="{{ $staff->id }}">

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <div class="xl:col-span-2 space-y-6">

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4 flex-col lg:flex-row lg:items-center">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900">申請方法を選択</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                休暇が1日単位なら終日休暇、時間だけ休む場合は時間指定を選択してください。
                            </p>
                        </div>

                        <label class="inline-flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer">
                            <input type="checkbox"
                                   name="is_full_day"
                                   id="is_full_day"
                                   value="1"
                                   class="w-5 h-5"
                                   onchange="toggleVacationMode()"
                                   {{ old('is_full_day') ? 'checked' : '' }}>
                            <span class="font-semibold text-gray-800">終日休暇にする</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                        <div id="fullModeCard"
                             class="rounded-2xl border border-gray-200 bg-white p-5 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl text-white flex items-center justify-center font-bold shadow-sm"
                                     style="background: {{ $theme }}">
                                    1日
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">終日休暇</div>
                                    <div class="text-sm text-gray-500 mt-1">1日まるごと休む場合はこちら</div>
                                </div>
                            </div>
                        </div>

                        <div id="timeModeCard"
                             class="rounded-2xl border border-gray-200 bg-white p-5 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl text-white flex items-center justify-center font-bold shadow-sm"
                                     style="background: {{ $theme }}">
                                    時間
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900">時間指定休暇</div>
                                    <div class="text-sm text-gray-500 mt-1">半休や数時間の休みはこちら</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="fullDayField" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6" style="display:none;">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">終日休暇の入力</h2>
                    <p class="text-sm text-gray-500 mt-1">休暇日を1日分だけ指定します。</p>

                    <div class="mt-5">
                        <label class="block font-semibold mb-3 text-gray-800">
                            休暇日
                        </label>

                        <input type="date"
                               name="vacation_date"
                               value="{{ old('vacation_date') }}"
                               class="w-full border rounded-2xl p-3.5 text-base focus:ring-2 focus:outline-none"
                               style="--tw-ring-color: {{ $theme }}">

                        @error('vacation_date')
                            <div class="text-red-500 text-sm mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div id="timeFields" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">時間指定休暇の入力</h2>
                    <p class="text-sm text-gray-500 mt-1">開始日時と終了日時を入力してください。</p>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-5">
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <label class="block font-semibold mb-3 text-gray-800">
                                開始
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <input type="date"
                                       name="start_date"
                                       value="{{ old('start_date') }}"
                                       class="w-full border rounded-2xl p-3 text-base focus:ring-2 focus:outline-none bg-white"
                                       style="--tw-ring-color: {{ $theme }}">

                                <select name="start_time"
                                        class="w-full border rounded-2xl p-3 text-base focus:ring-2 focus:outline-none bg-white"
                                        style="--tw-ring-color: {{ $theme }}">
                                    @for($h=0; $h<24; $h++)
                                        @foreach(['00','30'] as $m)
                                            @php $t = sprintf('%02d:%s', $h, $m); @endphp
                                            <option value="{{ $t }}" {{ old('start_time') === $t ? 'selected' : '' }}>
                                                {{ $t }}
                                            </option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>

                            @error('start_date')
                                <div class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                            @error('start_time')
                                <div class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <label class="block font-semibold mb-3 text-gray-800">
                                終了
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <input type="date"
                                       name="end_date"
                                       value="{{ old('end_date') }}"
                                       class="w-full border rounded-2xl p-3 text-base focus:ring-2 focus:outline-none bg-white"
                                       style="--tw-ring-color: {{ $theme }}">

                                <select name="end_time"
                                        class="w-full border rounded-2xl p-3 text-base focus:ring-2 focus:outline-none bg-white"
                                        style="--tw-ring-color: {{ $theme }}">
                                    @for($h=0; $h<24; $h++)
                                        @foreach(['00','30'] as $m)
                                            @php $t = sprintf('%02d:%s', $h, $m); @endphp
                                            <option value="{{ $t }}" {{ old('end_time') === $t ? 'selected' : '' }}>
                                                {{ $t }}
                                            </option>
                                        @endforeach
                                    @endfor
                                </select>
                            </div>

                            @error('end_date')
                                <div class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                            @error('end_time')
                                <div class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <div class="space-y-6">

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-900">申請内容の目安</h2>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                            <div class="text-xs text-gray-500 font-semibold">終日休暇</div>
                            <div class="mt-1 text-sm text-gray-700 leading-6">
                                1日まるごと休むときに使います。
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-4">
                            <div class="text-xs text-gray-500 font-semibold">時間指定休暇</div>
                            <div class="mt-1 text-sm text-gray-700 leading-6">
                                通院や私用など、数時間だけ休むときに使います。
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-900">入力時の注意</h2>
                    <ul class="mt-4 space-y-3 text-sm text-gray-600 leading-6">
                        <li class="rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3">
                            終了時刻は開始時刻より後になるようにしてください。
                        </li>
                        <li class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            終日休暇を選ぶと、時間指定の入力は不要です。
                        </li>
                        <li class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                            申請後は一覧画面で承認状況を確認できます。
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6">
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('company.vacation.index') }}"
                           class="w-full inline-flex items-center justify-center px-4 py-3 rounded-2xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 transition">
                            ← 一覧へ戻る
                        </a>

                        <button type="submit"
                                class="w-full inline-flex items-center justify-center text-white px-6 py-3.5 rounded-2xl shadow-lg font-bold hover:opacity-90 transition"
                                style="background: {{ $theme }}">
                            申請する
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

@endsection
