<!DOCTYPE html>
<html>
<head>
    @php
        $staff = auth()->guard('company')->user();
        $company = $staff ? $staff->company : null;
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company->name ?? '管理画面' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --main-color: {{ $company->theme_color ?? '#3b82f6' }};
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

@if($company)
<header class="bg-white shadow-sm">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3">

        <div class="flex items-center justify-between">

            {{-- ================= 左：会社情報 ================= --}}
            <div class="flex items-center gap-3">

                @if($company->logo_path)
                    <img src="{{ asset($company->logo_path) }}"
                         class="h-10 sm:h-12 w-auto object-contain bg-white rounded">
                @else
                    <div class="h-9 w-9 rounded-full bg-gray-200
                                flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr($company->name,0,1)) }}
                    </div>
                @endif

                <div class="leading-tight">
                    <div class="font-semibold text-sm sm:text-base">
                        {{ $company->name }}
                    </div>
                    <div class="text-xs text-gray-400">
                        管理画面
                    </div>
                </div>

            </div>

            {{-- ================= 右：担当者情報 ================= --}}
            <div class="flex items-center gap-4 text-right">

                <div class="hidden sm:block leading-tight">
                    <div class="font-medium text-sm">
                        {{ $staff->name }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $staff->role }}
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('company.logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-xs text-gray-400
                                   hover:text-red-500
                                   transition">
                        ログアウト
                    </button>
                </form>

            </div>

        </div>

    </div>

</header>
@endif


{{-- ================= メッセージ ================= --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 mt-6 space-y-4">

    @if(session('success'))
        <div class="bg-green-100 border border-green-300
                    text-green-800 px-4 py-3 rounded-lg shadow">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-300
                    text-red-800 px-4 py-3 rounded-lg shadow">
            {{ session('error') }}
        </div>
    @endif

</div>


{{-- ================= コンテンツ ================= --}}
<main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    @yield('content')
</main>

</body>
</html>