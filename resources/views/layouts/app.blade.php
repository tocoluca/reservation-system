<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $company->name ?? '予約ページ' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    @php
        $rawTheme = $company->theme_color ?? '#b7875c';

        $colors = [
            'blue'   => '#3b82f6',
            'pink'   => '#ec4899',
            'green'  => '#10b981',
            'purple' => '#8b5cf6',
            'orange' => '#f97316',
        ];

        $main = $colors[$rawTheme] ?? $rawTheme;
        if (!is_string($main) || !preg_match('/^#[0-9a-fA-F]{6}$/', $main)) {
            $main = '#b7875c';
        }

        $logoPath = $company->logo_path ?? null;
        $logoUrl = null;

        if ($logoPath) {
            $logoPath = ltrim($logoPath, '/');

            if (preg_match('/^https?:\/\//', $logoPath)) {
                $logoUrl = $logoPath;
            } else {
                $logoUrl = asset($logoPath);
            }
        }

        $businessHours = $company->business_hours_text ?? null;
        $accessInfo = $company->access_info ?? null;
        $salonNote = $company->salon_note ?? null;
    @endphp

    <style>
        :root {
            --main-color: {{ $main }};
            --ink: #342b24;
            --muted: #7b6654;
            --paper: #fbf7f2;
            --line: #e8d9c9;
        }

        body {
            color: var(--ink);
            background:
                linear-gradient(180deg, rgba(255,255,255,.78), rgba(247,243,238,.92) 38%, rgba(242,235,226,1)),
                radial-gradient(circle at 8% 0%, color-mix(in srgb, var(--main-color) 15%, transparent) 0, transparent 28rem),
                radial-gradient(circle at 92% 8%, rgba(52,43,36,.10) 0, transparent 26rem);
        }

        .theme-bg {
            background: var(--main-color);
        }

        .theme-text {
            color: var(--main-color);
        }

        .theme-border {
            border-color: var(--main-color);
        }

        .theme-soft-bg {
            background-color: color-mix(in srgb, var(--main-color) 10%, white 90%);
        }

        .theme-soft-border {
            border-color: color-mix(in srgb, var(--main-color) 18%, white 82%);
        }

        .customer-shell {
            position: relative;
            isolation: isolate;
        }

        .customer-shell::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                linear-gradient(90deg, rgba(255,255,255,.34) 1px, transparent 1px),
                linear-gradient(180deg, rgba(255,255,255,.26) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,.55), transparent 68%);
        }

        .luxury-topbar {
            background:
                linear-gradient(135deg, rgba(255,255,255,.95), rgba(255,255,255,.82)),
                linear-gradient(135deg, color-mix(in srgb, var(--main-color) 15%, white 85%), #fff 58%, #f3ebe2);
            border-bottom: 1px solid rgba(232,217,201,.9);
            box-shadow: 0 14px 42px rgba(52,43,36,.08);
        }

        .logo-frame {
            background: linear-gradient(145deg, rgba(255,255,255,.98), rgba(248,242,235,.92));
            border: 1px solid rgba(232,217,201,.95);
            box-shadow: 0 12px 26px rgba(52,43,36,.10), inset 0 1px 0 rgba(255,255,255,.9);
        }

        .header-pill {
            border: 1px solid rgba(232,217,201,.9);
            background: rgba(255,255,255,.72);
            color: var(--muted);
        }

        .footer-panel {
            background:
                linear-gradient(145deg, rgba(52,43,36,.98), rgba(82,65,51,.96)),
                linear-gradient(135deg, var(--main-color), #342b24);
            box-shadow: 0 -18px 54px rgba(52,43,36,.14);
        }

        @media (max-width: 640px) {
            .customer-shell::before {
                background-size: 48px 48px;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="customer-shell min-h-screen antialiased">

    <header class="luxury-topbar sticky top-0 z-30 backdrop-blur-xl">
        <div class="max-w-6xl mx-auto px-3 sm:px-6">
            <div class="py-3 sm:py-5">
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ isset($company) ? url('/r/'.$company->company_code) : url('/') }}" class="logo-frame flex h-12 w-12 sm:h-16 sm:w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl group">
                        @if($logoUrl)
                            <img
                                src="{{ $logoUrl }}"
                                onerror="this.style.display='none';this.nextElementSibling?.classList.remove('hidden');"
                                alt="{{ $company->name ?? '店舗ロゴ' }}"
                                class="h-full w-full object-contain p-1.5 transition duration-300 group-hover:scale-105"
                            >
                            <span class="hidden text-lg sm:text-2xl font-bold theme-text">
                                {{ mb_substr($company->name ?? 'R', 0, 1) }}
                            </span>
                        @else
                            <span class="text-lg sm:text-2xl font-bold theme-text">
                                {{ mb_substr($company->name ?? 'R', 0, 1) }}
                            </span>
                        @endif
                    </a>

                    <div class="min-w-0 flex-1">
                        <a href="{{ isset($company) ? url('/r/'.$company->company_code) : url('/') }}" class="block min-w-0">
                            <div class="text-[9px] sm:text-[11px] tracking-[0.16em] font-bold uppercase text-[#9a7d63]">
                                Online Reservation
                            </div>
                            <div class="mt-0.5 truncate text-lg sm:text-2xl font-bold tracking-wide text-[#342b24]">
                                {{ $company->name ?? '予約ページ' }}
                            </div>
                        </a>
                    </div>

                </div>

                @if($businessHours || isset($company))
                    <div class="mt-3 grid gap-2 sm:flex sm:items-center sm:justify-between">
                        @if($businessHours)
                            <div class="header-pill rounded-2xl px-3 py-2 text-[11px] sm:text-xs font-semibold leading-5 break-words">
                                <span class="mr-1 text-[#9a7d63]">営業時間</span>
                                <span>{{ $businessHours }}</span>
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </header>

    <main class="px-0 py-0">
        @yield('content')
    </main>

    <footer class="mt-12">
        <div class="footer-panel text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
                <div class="grid gap-6 md:grid-cols-[1.2fr_1fr] md:items-end">
                    <div class="min-w-0">
                        <div class="text-[11px] tracking-[0.18em] font-bold text-white/55 uppercase">Reservation Salon</div>
                        <div class="mt-2 break-words text-2xl sm:text-3xl font-bold tracking-wide">
                            {{ $company->name ?? '予約ページ' }}
                        </div>

                        @if($accessInfo || $salonNote)
                            <div class="mt-4 max-w-2xl space-y-2 text-sm leading-7 text-white/72">
                                @if($accessInfo)
                                    <div class="break-words">{{ $accessInfo }}</div>
                                @endif
                                @if($salonNote)
                                    <div class="break-words">{{ $salonNote }}</div>
                                @endif
                            </div>
                        @else
                            <div class="mt-4 text-sm leading-7 text-white/68">
                                ご予約内容をご確認のうえ、安心してご来店ください。
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 md:text-right">
                        @if($businessHours)
                            <div class="inline-block max-w-full rounded-2xl border border-white/12 bg-white/8 px-4 py-3 text-left text-sm leading-6 text-white/78 break-words md:text-right">
                                <span class="font-bold text-white/90">営業時間</span>
                                <span class="ml-1">{{ $businessHours }}</span>
                            </div>
                        @endif

                        <div class="mt-5 text-xs text-white/42">
                            &copy; {{ date('Y') }} {{ $company->name ?? 'Reservation System' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
