@php
    $staffMenuSteps = [
        'staff' => [
            'label' => '担当者管理',
            'description' => 'スタッフの登録・編集',
            'route' => 'company.staff.index',
        ],
        'menu_staff' => [
            'label' => 'メニュー対応スタッフ設定',
            'description' => '担当できるメニューを設定',
            'route' => 'company.menu-staff.index',
        ],
    ];

    $currentStep = $currentStep ?? 'staff';
    $theme = $theme ?? '#3b82f6';
@endphp

<section class="rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Staff Menu Settings</p>
            <h2 class="mt-1 text-lg font-black text-gray-900">担当者と対応メニュー</h2>
            <p class="mt-1 text-sm text-gray-500">スタッフ登録とメニュー担当設定を続けて確認できます。</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 lg:min-w-[520px]">
            @foreach($staffMenuSteps as $key => $step)
                @php
                    $isActive = $currentStep === $key;
                @endphp
                @if($isActive)
                    <div class="rounded-2xl border px-4 py-3 text-white shadow-sm"
                         style="background: {{ $theme }}; border-color: {{ $theme }};">
                        <div class="text-sm font-black">{{ $step['label'] }}</div>
                        <div class="mt-1 text-xs text-white/80">{{ $step['description'] }}</div>
                    </div>
                @else
                    <a href="{{ route($step['route']) }}"
                       class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-700 transition hover:bg-gray-100">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-black">{{ $step['label'] }}</div>
                                <div class="mt-1 truncate text-xs text-gray-500">{{ $step['description'] }}</div>
                            </div>
                            <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-gray-400"></i>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>
