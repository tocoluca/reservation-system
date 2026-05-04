<!DOCTYPE html>
<html lang="ja">
<head>
    @php
        $staff = auth()->guard('company')->user();
        $company = $staff ? $staff->company : null;
        $theme = $company->theme_color ?? '#3b82f6';
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company->name ?? '管理画面' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>

    <style>
        :root { --main-color: {{ $theme }}; }
        body {
            background:
                radial-gradient(circle at top left, {{ $theme }}1f, transparent 34rem),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 52%, #f8fafc 100%);
        }
        .company-topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid rgba(148,163,184,.28);
            background:
                linear-gradient(135deg, rgba(255,255,255,.94), rgba(248,250,252,.86)),
                radial-gradient(circle at top left, {{ $theme }}2b, transparent 26rem);
            backdrop-filter: blur(18px);
            box-shadow: 0 18px 46px rgba(15,23,42,.12);
        }
        .company-brand-shell {
            border-radius: 24px;
            border: 1px solid rgba(148,163,184,.22);
            background:
                linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,255,255,.76)),
                radial-gradient(circle at top right, {{ $theme }}38, transparent 18rem),
                linear-gradient(90deg, {{ $theme }}12, rgba(15,23,42,.04));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 14px 34px rgba(15,23,42,.09);
        }
        .company-logo-frame {
            width: 48px;
            height: 48px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid rgba(148,163,184,.2);
            box-shadow: 0 12px 24px rgba(15,23,42,.08);
            overflow: hidden;
            flex: none;
        }
        .company-user-chip {
            border-radius: 20px;
            border: 1px solid rgba(148,163,184,.18);
            background: rgba(255,255,255,.72);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.85);
        }
        .company-logout-btn {
            border-radius: 18px;
            color: #fff;
            background: linear-gradient(135deg, #111827 0%, #1f2937 62%, {{ $theme }} 130%);
            box-shadow: 0 14px 30px rgba(15,23,42,.18);
            transition: .22s;
        }
        .company-logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 36px rgba(15,23,42,.22);
            opacity: .96;
        }
        .company-primary-nav {
            display: none;
            align-items: center;
            gap: .5rem;
            min-width: 0;
            overflow-x: auto;
            padding: .55rem;
            border-radius: 20px;
            border: 1px solid rgba(148,163,184,.24);
            background:
                linear-gradient(135deg, rgba(241,245,249,.92), rgba(255,255,255,.78)),
                radial-gradient(circle at top right, {{ $theme }}1f, transparent 16rem);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 10px 24px rgba(15,23,42,.06);
        }
        .company-primary-nav a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            flex: none;
            border-radius: 16px;
            padding: .68rem 1rem;
            color: #334155;
            font-size: .84rem;
            font-weight: 900;
            white-space: nowrap;
            transition: .18s;
            border: 1px solid transparent;
        }
        .company-primary-nav a:hover {
            color: #0f172a;
            background: rgba(255,255,255,.96);
            border-color: rgba(148,163,184,.28);
            box-shadow: 0 8px 18px rgba(15,23,42,.07);
        }
        .company-primary-nav a.active {
            color: #fff;
            background:
                linear-gradient(135deg, var(--main-color), #111827 88%),
                linear-gradient(90deg, rgba(255,255,255,.2), transparent);
            box-shadow: 0 12px 26px rgba(15,23,42,.2), 0 8px 18px {{ $theme }}33;
            border-color: rgba(255,255,255,.26);
        }
        @media (min-width: 1024px) {
            .company-primary-nav {
                display: flex;
            }
        }
        .company-mobile-nav {
            position: fixed;
            left: .75rem;
            right: .75rem;
            bottom: .75rem;
            z-index: 50;
            border-radius: 22px;
            border: 1px solid rgba(226,232,240,.92);
            background: rgba(255,255,255,.9);
            backdrop-filter: blur(18px);
            box-shadow: 0 18px 44px rgba(15,23,42,.14);
        }
        .company-mobile-nav a {
            min-width: 0;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
        }
        .company-mobile-nav a.active {
            color: var(--main-color);
        }
    </style>
</head>

<body class="min-h-screen pb-24 lg:pb-0 text-slate-900">

@php
    $companyPrimaryNavItems = $company ? [
        ['label' => 'ダッシュボード', 'icon' => 'layout-dashboard', 'route' => 'company.dashboard', 'url' => route('company.dashboard'), 'active' => request()->routeIs('company.dashboard')],
        ['label' => '予約カレンダー', 'icon' => 'calendar-check', 'route' => 'company.reserve', 'url' => route('company.reserve'), 'active' => request()->routeIs('company.reserve')],
        ['label' => '予約一覧', 'icon' => 'list-checks', 'route' => 'company.reservations.index', 'url' => route('company.reservations.index'), 'active' => request()->routeIs('company.reservations.*')],
        ['label' => '顧客管理', 'icon' => 'users', 'route' => 'company.customers', 'url' => route('company.customers'), 'active' => request()->routeIs('company.customers*')],
        ['label' => '勤務管理', 'icon' => 'clock', 'route' => 'company.staff-shifts', 'url' => route('company.staff-shifts'), 'active' => request()->routeIs('company.staff-shifts*') || request()->routeIs('company.shift-patterns*') || request()->routeIs('company.staff-default-shifts*')],
    ] : [];

    if ($staff && $staff->isStoreOperator()) {
        $companyPrimaryNavItems = array_values(array_filter($companyPrimaryNavItems, function ($item) {
            return $item['route'] !== 'company.staff-shifts';
        }));
    }
@endphp

@if($company)
@if(session('admin_impersonating_company'))
    <div class="sticky top-0 z-50 bg-amber-500 px-4 py-2 text-center text-sm font-bold text-white shadow">
        管理者代理ログイン中です。終了する場合はログアウトしてください。
    </div>
@endif
<header class="company-topbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="company-brand-shell px-3 sm:px-4 py-3 space-y-3">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('company.dashboard') }}" class="flex items-center gap-3 min-w-0 group">
                    <div class="company-logo-frame">
                        @if($company->logo_path)
                            <img src="{{ asset($company->logo_path) }}" class="max-h-10 max-w-10 object-contain" alt="{{ $company->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white font-black text-lg" style="background: linear-gradient(135deg, {{ $theme }}, #111827);">
                                {{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <div class="font-black text-sm sm:text-base truncate text-slate-950 group-hover:opacity-80 transition">
                                {{ $company->name }}
                            </div>
                            <span class="hidden sm:inline-flex h-2 w-2 rounded-full" style="background: {{ $theme }}"></span>
                        </div>
                        <div class="text-[11px] sm:text-xs text-slate-500 tracking-wide">
                            Company Console
                        </div>
                    </div>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="company-user-chip hidden sm:flex items-center gap-3 px-4 py-2.5">
                        <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, {{ $theme }}, #111827);">
                            {{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}
                        </div>
                        <div class="leading-tight text-right">
                            <div class="font-bold text-sm text-slate-900">{{ $staff->name }}</div>
                            <div class="text-xs text-slate-500">{{ $roleLabel ?? $staff->role }}</div>
                        </div>
                    </div>

                    <a href="{{ url('/r/'.$company->company_code) }}"
                       target="_blank"
                       rel="noopener"
                       class="hidden md:inline-flex items-center gap-2 px-4 py-3 rounded-2xl text-white text-sm font-bold shadow-sm hover:opacity-90 transition"
                       style="background: linear-gradient(135deg, {{ $theme }}, #111827);">
                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        予約画面
                    </a>

                    <a href="{{ route('company.support.index') }}"
                       class="hidden md:inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/70 border border-slate-200 text-sm font-bold text-slate-600 hover:text-slate-950 hover:bg-white transition">
                        <i data-lucide="circle-help" class="w-4 h-4"></i>
                        ヘルプ
                    </a>

                    <form method="POST" action="{{ route('company.logout') }}">
                        @csrf
                        <button type="submit" class="company-logout-btn inline-flex items-center gap-2 px-4 py-3 text-sm font-bold">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">ログアウト</span>
                        </button>
                    </form>
                </div>
            </div>

            <nav class="company-primary-nav" aria-label="主要画面">
                @foreach($companyPrimaryNavItems as $item)
                    <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'active' : '' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</header>

<nav class="company-mobile-nav lg:hidden">
    <div class="grid grid-cols-5 gap-1 px-2 py-2">
        @foreach($companyPrimaryNavItems as $item)
            <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'active' : '' }} flex flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2">
                <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                <span class="truncate max-w-full">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-4">
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-2xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-2xl shadow-sm">
            {{ session('error') }}
        </div>
    @endif
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
</main>

@if($company)
<script>
(() => {
    let isInternalNavigation = false;
    const logoutUrl = @json(route('company.logout'));
    const csrfToken = @json(csrf_token());

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href') || '';
        const target = link.getAttribute('target') || '';

        if (
            target === '_blank' ||
            href.startsWith('#') ||
            href.startsWith('tel:') ||
            href.startsWith('mailto:') ||
            link.hasAttribute('download')
        ) {
            return;
        }

        isInternalNavigation = true;
    }, true);

    document.addEventListener('submit', () => {
        isInternalNavigation = true;
    }, true);

    window.addEventListener('pagehide', () => {
        if (isInternalNavigation) return;

        const payload = new FormData();
        payload.append('_token', csrfToken);
        navigator.sendBeacon(logoutUrl, payload);
    });
})();
</script>
@endif

</body>
</html>
