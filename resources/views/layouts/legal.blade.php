<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '規約・法定表示') | tocoluca</title>

    <style>
        :root {
            color-scheme: light;
            --legal-ink: #0f172a;
            --legal-muted: #64748b;
            --legal-line: #dbe3ec;
            --legal-accent: #1d4ed8;
            --legal-surface: rgba(255, 255, 255, .94);
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            color: var(--legal-ink);
            background: #eef2f7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans JP", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }

        .legal-header {
            position: sticky;
            top: 0;
            z-index: 20;
            border-bottom: 1px solid var(--legal-line);
            background: var(--legal-surface);
            box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
            backdrop-filter: blur(16px);
        }

        .legal-header__inner,
        .legal-footer__inner {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .legal-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 76px;
        }

        .legal-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--legal-ink);
            text-decoration: none;
        }

        .legal-brand__mark {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 12px;
            color: #fff;
            background: linear-gradient(145deg, #2563eb, #1e3a8a);
            box-shadow: 0 8px 18px rgba(37, 99, 235, .2);
            font-size: 16px;
            font-weight: 800;
        }

        .legal-brand__name {
            display: block;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .legal-brand__label {
            display: block;
            margin-top: 2px;
            color: var(--legal-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .legal-nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .legal-nav a {
            padding: 9px 12px;
            border-radius: 10px;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background-color .18s ease, color .18s ease;
        }

        .legal-nav a:hover,
        .legal-nav a:focus-visible {
            color: var(--legal-accent);
            background: #eff6ff;
            outline: none;
        }

        .legal-nav a[aria-current="page"] {
            color: #fff;
            background: #1e40af;
        }

        .legal-main { min-height: calc(100vh - 250px); }

        .legal-footer {
            border-top: 1px solid #1e293b;
            color: #cbd5e1;
            background: #0f172a;
        }

        .legal-footer__inner {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 28px;
            padding-top: 32px;
            padding-bottom: 32px;
        }

        .legal-footer__name {
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .legal-footer__description {
            margin: 7px 0 0;
            color: #94a3b8;
            font-size: 13px;
        }

        .legal-footer__nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px 20px;
        }

        .legal-footer__nav a {
            color: #e2e8f0;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .legal-footer__nav a:hover,
        .legal-footer__nav a:focus-visible {
            color: #fff;
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .legal-footer__copyright {
            margin-top: 16px;
            color: #64748b;
            font-size: 12px;
            text-align: right;
        }

        @media (max-width: 760px) {
            .legal-header__inner {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
                padding-top: 14px;
                padding-bottom: 12px;
            }

            .legal-nav {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 2px;
            }

            .legal-nav a {
                flex: 0 0 auto;
                padding: 8px 10px;
                font-size: 12px;
            }

            .legal-footer__inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .legal-footer__nav { justify-content: flex-start; }
            .legal-footer__copyright { text-align: left; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <header class="legal-header">
        <div class="legal-header__inner">
            <a class="legal-brand" href="{{ url('/') }}" aria-label="tocoluca トップページ">
                <span class="legal-brand__mark" aria-hidden="true">T</span>
                <span>
                    <span class="legal-brand__name">tocoluca</span>
                    <span class="legal-brand__label">規約・法定表示</span>
                </span>
            </a>

            <nav class="legal-nav" aria-label="規約・法定表示">
                <a href="{{ route('terms') }}" @if(request()->routeIs('terms')) aria-current="page" @endif>利用規約</a>
                <a href="{{ route('privacy') }}" @if(request()->routeIs('privacy')) aria-current="page" @endif>プライバシーポリシー</a>
                <a href="{{ route('tokusho') }}" @if(request()->routeIs('tokusho')) aria-current="page" @endif>特定商取引法に基づく表記</a>
            </nav>
        </div>
    </header>

    <main class="legal-main">
        @yield('content')
    </main>

    <footer class="legal-footer">
        <div class="legal-footer__inner">
            <div>
                <div class="legal-footer__name">tocoluca</div>
                <p class="legal-footer__description">サービスに関する規約・法定表示</p>
            </div>

            <div>
                <nav class="legal-footer__nav" aria-label="フッターナビゲーション">
                    <a href="{{ route('terms') }}">利用規約</a>
                    <a href="{{ route('privacy') }}">プライバシーポリシー</a>
                    <a href="{{ route('tokusho') }}">特定商取引法に基づく表記</a>
                </nav>
                <div class="legal-footer__copyright">&copy; {{ date('Y') }} tocoluca</div>
            </div>
        </div>
    </footer>
</body>
</html>
