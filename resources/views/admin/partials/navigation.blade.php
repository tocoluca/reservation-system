@php
    $adminNav = [
        ['admin.dashboard', 'ダッシュボード', 'admin.dashboard'],
        ['admin.company.index', '企業管理', 'admin.company.*'],
        ['admin.applications', '利用申請', 'admin.applications*'],
        ['admin.inquiries.index', 'お問い合わせ', 'admin.inquiries.*'],
        ['admin.company-dashboard-notices.index', 'お知らせ', 'admin.company-dashboard-notices.*'],
    ];
@endphp
<style>
    :focus-visible { outline: 3px solid #0284c7; outline-offset: 3px; }
    html { scroll-padding-top: 100px; }
</style>
<header class="bg-slate-900 text-white md:sticky md:top-0 z-30 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="flex items-center justify-between gap-3 py-3">
            <a href="{{ route('admin.dashboard') }}" class="font-bold tracking-wide">予約システム <span class="text-xs text-slate-300 ml-2">管理画面</span></a>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="rounded-lg border border-slate-600 px-3 py-2 text-xs hover:bg-slate-700">ログアウト</button>
            </form>
        </div>
        <nav aria-label="管理メニュー" class="hidden md:flex flex-wrap gap-1 pb-3">
            @foreach($adminNav as [$route, $label, $pattern])
                <a href="{{ route($route) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif
                   class="rounded-lg px-3 py-2.5 text-xs sm:text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-white text-slate-900' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</header>
