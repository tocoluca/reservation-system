@php
    $shiftSetupSteps = [
        1 => [
            'label' => '勤務時間を登録',
            'screen' => 'シフトパターン',
            'description' => '「通常」「早番」などの時間を作る',
            'route' => 'company.shift-patterns',
        ],
        2 => [
            'label' => '曜日の基本を登録',
            'screen' => '基本シフト',
            'description' => 'スタッフごとに月〜日の基本を決める',
            'route' => 'company.staff-default-shifts',
        ],
        3 => [
            'label' => '毎月の勤務表を作成',
            'screen' => '勤務管理',
            'description' => '月の勤務表を作り、休みなどを調整する',
            'route' => 'company.staff-shifts',
        ],
    ];

    $currentStep = $currentStep ?? 1;
    $theme = $theme ?? '#3b82f6';
@endphp

<section class="rounded-[1.75rem] border border-gray-200 bg-white p-4 sm:p-5 shadow-sm" aria-labelledby="shift-flow-title">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-2">
        <div>
            <p class="text-xs font-black tracking-[0.12em] text-gray-400">シフト設定ガイド</p>
            <h2 id="shift-flow-title" class="mt-1 text-lg font-black text-gray-950">最初に ①②、毎月 ③ の順で使います</h2>
        </div>
        <p class="text-xs sm:text-sm text-gray-500">画面名ではなく「何をするか」で選べます</p>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-2">
        @foreach($shiftSetupSteps as $stepNumber => $step)
            @php $isActive = $currentStep === $stepNumber; @endphp
            <a href="{{ route($step['route']) }}"
               @if($isActive) aria-current="page" @endif
               class="group rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 {{ $isActive ? 'text-white shadow-sm' : 'bg-gray-50 text-gray-800 border-gray-200 hover:bg-white' }}"
               style="{{ $isActive ? 'background: '.$theme.'; border-color: '.$theme.';' : '' }} --tw-ring-color: {{ $theme }};">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-black {{ $isActive ? 'bg-white/20 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
                        {{ $stepNumber }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-black">{{ $step['label'] }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-black {{ $isActive ? 'bg-white/20 text-white' : ($stepNumber < 3 ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700') }}">
                                {{ $stepNumber < 3 ? '初回設定' : '毎月' }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs leading-5 {{ $isActive ? 'text-white/85' : 'text-gray-500' }}">{{ $step['description'] }}</div>
                        <div class="mt-2 inline-flex items-center gap-1 text-xs font-bold">
                            {{ $step['screen'] }}
                            @if($isActive)
                                <span class="opacity-75">（現在）</span>
                            @else
                                <i data-lucide="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>
