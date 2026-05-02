@php
    $theme = $theme ?? '#3b82f6';
    $current = $current ?? 'vacation';
    $items = [
        'vacation' => [
            'label' => '休暇管理',
            'description' => '申請状況を確認',
            'route' => 'company.vacation.index',
        ],
        'create' => [
            'label' => '休暇申請',
            'description' => '休みを登録',
            'route' => 'company.vacation.create',
        ],
        'shift' => [
            'label' => '勤務管理',
            'description' => 'シフトを調整',
            'route' => 'company.staff-shifts',
        ],
    ];
@endphp

<section class="rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Vacation & Shift</p>
            <h2 class="mt-1 text-lg font-black text-gray-900">休暇と勤務調整</h2>
            <p class="mt-1 text-sm text-gray-500">休暇の確認後、そのまま勤務管理でシフトを調整できます。</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:min-w-[560px]">
            @foreach($items as $key => $item)
                @php($active = $current === $key)
                <a href="{{ route($item['route']) }}"
                   class="rounded-2xl border px-4 py-3 transition {{ $active ? 'text-white shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-100 hover:bg-gray-100' }}"
                   style="{{ $active ? 'background: '.$theme.'; border-color: '.$theme : '' }}">
                    <div class="text-sm font-black">{{ $item['label'] }}</div>
                    <div class="mt-1 text-xs {{ $active ? 'text-white/80' : 'text-gray-500' }}">{{ $item['description'] }}</div>
                </a>
            @endforeach
        </div>
    </div>
</section>
