<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $company->name ?? '予約' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    @php
        $theme = $company->theme_color ?? 'blue';

        $colors = [
            'blue'   => '#3b82f6',
            'pink'   => '#ec4899',
            'green'  => '#10b981',
            'purple' => '#8b5cf6',
            'orange' => '#f97316',
        ];

        $main = $colors[$theme] ?? '#3b82f6';
    @endphp

    <style>
        .theme-bg {
            background: {{ $main }};
        }

        .theme-text {
            color: {{ $main }};
        }

        .theme-border {
            border-color: {{ $main }};
        }

        .theme-soft-bg {
            background-color: color-mix(in srgb, {{ $main }} 10%, white 90%);
        }

        .theme-soft-border {
            border-color: color-mix(in srgb, {{ $main }} 18%, white 82%);
        }

        .theme-header-bg {
            background: linear-gradient(
                135deg,
                color-mix(in srgb, {{ $main }} 16%, white 84%) 0%,
                #ffffff 55%,
                color-mix(in srgb, {{ $main }} 8%, white 92%) 100%
            );
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen bg-gradient-to-b from-white via-gray-50 to-gray-100 text-gray-800">

    {{-- ヘッダー --}}
    <header class="theme-header-bg border-b border-white/70 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-center py-5 sm:py-7">
                <div class="flex items-center gap-4 sm:gap-5">
                    @if(!empty($company->logo_path))
                        <div class="shrink-0 rounded-2xl bg-white/90 shadow-sm ring-1 ring-black/5 px-3 py-2">
                            <img
                                src="{{ asset($company->logo_path) }}"
                                alt="{{ $company->name ?? '店舗ロゴ' }}"
                                class="h-14 sm:h-16 w-auto object-contain"
                            >
                        </div>
                    @endif

                    <div class="min-w-0">
                        <div class="text-2xl sm:text-3xl font-bold tracking-wide text-gray-900 leading-tight">
                            {{ $company->name ?? '予約サイト' }}
                        </div>
                        <div class="mt-1 text-xs sm:text-sm text-gray-500">
                            ご予約受付
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- コンテンツ --}}
    <main class="py-6 sm:py-10 px-3 sm:px-4">
        <div class="max-w-3xl mx-auto">
            @yield('content')
        </div>
    </main>

    {{-- フッター --}}
    <footer class="mt-12 border-t bg-white/80 backdrop-blur-sm">
        <div class="max-w-4xl mx-auto px-4 py-6 text-center text-xs sm:text-sm text-gray-500">
            © {{ date('Y') }} {{ $company->name ?? 'Reservation System' }}
        </div>
    </footer>

    @stack('scripts')
</body>
</html>