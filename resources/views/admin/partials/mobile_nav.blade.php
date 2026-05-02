@php
    $items = [
        [
            'label' => '企業一覧',
            'href' => route('admin.company.index'),
            'active' => request()->routeIs('admin.company.*'),
            'icon' => 'building',
        ],
        [
            'label' => '申請',
            'href' => route('admin.applications'),
            'active' => request()->routeIs('admin.applications*'),
            'icon' => 'file',
        ],
        [
            'label' => '問い合わせ',
            'href' => route('admin.inquiries.index'),
            'active' => request()->routeIs('admin.inquiries.*'),
            'icon' => 'chat',
        ],
    ];
@endphp

<div class="h-24 md:hidden"></div>

<nav class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white/95 px-3 pb-[calc(env(safe-area-inset-bottom)+0.5rem)] pt-2 shadow-[0_-8px_24px_rgba(15,23,42,0.12)] backdrop-blur md:hidden">
    <div class="mx-auto grid max-w-md grid-cols-3 gap-2">
        @foreach($items as $item)
            <a href="{{ $item['href'] }}"
               class="flex min-h-14 flex-col items-center justify-center rounded-2xl px-2 py-2 text-xs font-bold transition {{ $item['active'] ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <span class="mb-1 flex h-5 items-center justify-center">
                    @if($item['icon'] === 'building')
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l8-4v18"></path>
                            <path d="M19 21V11l-6-4"></path>
                            <path d="M9 9h1"></path>
                            <path d="M9 13h1"></path>
                            <path d="M9 17h1"></path>
                        </svg>
                    @elseif($item['icon'] === 'file')
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <path d="M14 2v6h6"></path>
                            <path d="M8 13h8"></path>
                            <path d="M8 17h5"></path>
                        </svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                        </svg>
                    @endif
                </span>
                <span class="leading-none">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
