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
            border-bottom: 1px solid rgba(255,255,255,.72);
            background: rgba(248,250,252,.78);
            backdrop-filter: blur(18px);
            box-shadow: 0 16px 42px rgba(15,23,42,.08);
        }
        .company-brand-shell {
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,.72);
            background:
                linear-gradient(135deg, rgba(255,255,255,.92), rgba(255,255,255,.68)),
                radial-gradient(circle at top right, {{ $theme }}24, transparent 18rem);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 12px 32px rgba(15,23,42,.06);
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
    </style>
</head>

<body class="min-h-screen text-slate-900">

@if($company)
<header class="company-topbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="company-brand-shell px-3 sm:px-4 py-3">
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
        </div>
    </div>
</header>
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

</body>
</html>