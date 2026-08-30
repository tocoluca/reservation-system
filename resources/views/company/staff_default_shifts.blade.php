@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Default Shift</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">基本シフト</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        曜日ごとの基本シフトを設定して、月ごとの勤務表作成の土台にできます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._shift_setup_nav', [
            'currentStep' => 2,
            'links' => [
                ['label' => 'シフトパターンへ', 'route' => 'company.shift-patterns', 'icon' => 'arrow-left'],
                ['label' => '勤務管理へ', 'route' => 'company.staff-shifts', 'icon' => 'arrow-right'],
            ],
        ])
    </div>

    {{-- ガイド --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">使い方</h2>
                <p class="text-sm text-gray-500 mt-1">
                    曜日ごとの基本シフトを決めておくと、勤務管理画面の自動生成が使いやすくなります。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">月〜日を設定</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">休みも設定可能</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">保存して反映</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">入力内容を確認してください。</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="defaultShiftForm" method="POST" action="{{ route('company.staff-default-shifts') }}"
          data-busy-form="true" data-busy-label="保存中…">
        @csrf

        <div class="bg-white shadow-sm rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <h2 class="text-lg font-bold text-gray-900">スタッフ別 基本シフト設定</h2>
                <p class="text-sm text-gray-500 mt-1">各スタッフの曜日ごとの標準シフトを設定します。</p>
            </div>

            <div class="max-h-[72vh] overflow-auto p-4 sm:p-6">
                <table class="min-w-[980px] w-full text-sm border-separate border-spacing-0">
                    <thead>
                        <tr>
                            <th class="sticky top-0 left-0 z-40 p-4 text-left rounded-tl-2xl border-b border-white/30 shadow-sm min-w-[180px]"
                                style="background: {{ $theme }}; color: white;">
                                スタッフ
                            </th>

                            @foreach(['月','火','水','木','金','土','日'] as $i => $d)
                                <th class="sticky top-0 z-30 p-4 text-center border-b border-white/30 shadow-sm min-w-[120px] {{ $loop->last ? 'rounded-tr-2xl' : '' }}"
                                    style="background: {{ $theme }}; color: white;">
                                    {{ $d }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($staffs as $staff)
                            <tr class="odd:bg-stone-50 even:bg-white">
                                <td class="sticky left-0 z-20 p-4 font-semibold text-stone-800 border-b border-stone-200 border-r border-stone-200 shadow-sm min-w-[180px] {{ $loop->odd ? 'bg-stone-50' : 'bg-white' }}">
                                    {{ $staff->name }}
                                </td>

                                @for($w = 1; $w <= 7; $w++)
                                    @php
                                        $shift = $shifts
                                            ->where('staff_id', $staff->id)
                                            ->where('weekday', $w % 7)
                                            ->first();
                                    @endphp

                                    <td class="p-3 border-b border-stone-200 min-w-[120px]">
                                        <select
                                            name="shifts[{{ $staff->id }}][{{ $w % 7 }}]"
                                            class="default-shift-input border border-stone-300 rounded-xl p-3 w-full bg-white focus:outline-none focus:ring-2"
                                            style="--tw-ring-color: {{ $theme }}"
                                        >
                                            <option value="">休</option>

                                            @foreach($patterns as $p)
                                                <option
                                                    value="{{ $p->id }}"
                                                    @if($shift && $shift->shift_pattern_id == $p->id) selected @endif
                                                >
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 pb-6 flex justify-end">
                <button
                    type="submit"
                    class="px-8 py-3 text-white rounded-2xl shadow-lg hover:opacity-90 transition"
                    style="background: var(--company-theme-gradient);"
                >
                    保存する
                </button>
            </div>
        </div>
    </form>

    <div id="defaultShiftSaveBar"
         class="fixed bottom-24 left-1/2 z-[70] hidden w-[calc(100%-2rem)] max-w-xl -translate-x-1/2 rounded-[1.5rem] border border-amber-200 bg-white/95 p-3 shadow-2xl backdrop-blur lg:bottom-6">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="font-black text-gray-900">未保存の変更があります</div>
                <div class="truncate text-xs text-gray-500">画面を移動する前に保存してください</div>
            </div>
            <button type="submit" form="defaultShiftForm" data-busy-button
                    class="shrink-0 rounded-2xl px-5 py-3 text-sm font-black text-white shadow"
                    style="background: {{ $theme }};">
                <span data-busy-text>保存する</span>
            </button>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const saveBar = document.getElementById('defaultShiftSaveBar');
    document.querySelectorAll('.default-shift-input').forEach(input => {
        input.addEventListener('change', () => saveBar?.classList.remove('hidden'));
    });
});
</script>

@endsection
