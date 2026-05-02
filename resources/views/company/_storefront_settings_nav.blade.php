@php
    $theme = $theme ?? '#3b82f6';
    $current = $current ?? 'info';
    $items = [
        'info' => [
            'label' => '企業情報設定',
            'description' => '基本情報・営業時間',
            'route' => 'company.info.edit',
        ],
        'logo' => [
            'label' => '企業ロゴ',
            'description' => 'ロゴ画像を変更',
            'route' => 'company.logo',
        ],
        'theme' => [
            'label' => 'テーマカラー',
            'description' => '画面の色を変更',
            'route' => 'company.theme',
        ],
    ];
@endphp

<section class="rounded-[1.75rem] border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-xs font-bold tracking-[0.18em] uppercase text-gray-400">Storefront Settings</p>
            <h2 class="mt-1 text-lg font-black text-gray-900">店舗設定ナビ</h2>
            <p class="mt-1 text-sm text-gray-500">店舗情報、ロゴ、テーマカラーをまとめて設定できます。</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 lg:min-w-[580px]">
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
