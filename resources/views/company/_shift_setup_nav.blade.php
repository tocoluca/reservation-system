@php
    $shiftSetupSteps = [
        1 => [
            'label' => 'シフトパターン',
            'description' => '勤務時間の型を作る',
        ],
        2 => [
            'label' => '基本シフト',
            'description' => '曜日ごとの標準を決める',
        ],
        3 => [
            'label' => '勤務管理',
            'description' => '月ごとの勤務表を調整する',
        ],
    ];

    $currentStep = $currentStep ?? 1;
    $links = $links ?? [];
    $theme = $theme ?? '#3b82f6';
@endphp

<section class="rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Shift Setup Flow</p>
            <h2 class="mt-1 text-lg font-black text-gray-900">勤務設定の流れ</h2>
            <p class="mt-1 text-sm text-gray-500">関連するシフト設定へ、ここから直接移動できます。</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 xl:min-w-[620px]">
            @foreach($shiftSetupSteps as $stepNumber => $step)
                @php
                    $isActive = $currentStep === $stepNumber;
                @endphp
                <div class="rounded-2xl border px-4 py-3 {{ $isActive ? 'text-white shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-100' }}"
                     style="{{ $isActive ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-black {{ $isActive ? 'bg-white/20 text-white' : 'bg-white text-gray-500 border border-gray-200' }}">
                            {{ $stepNumber }}
                        </span>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-black">{{ $step['label'] }}</div>
                            <div class="truncate text-xs {{ $isActive ? 'text-white/80' : 'text-gray-500' }}">{{ $step['description'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if(!empty($links))
        <div class="mt-4 flex flex-col sm:flex-row sm:justify-end gap-2">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
                   style="background: {{ $theme }};">
                    @if(!empty($link['icon']))
                        <i data-lucide="{{ $link['icon'] }}" class="h-4 w-4"></i>
                    @endif
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</section>
