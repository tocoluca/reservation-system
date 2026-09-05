<!DOCTYPE html>
<html lang="ja">
<head>
    @php
        $staff = auth()->guard('company')->user();
        $company = $staff ? $staff->company : null;
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
<header class="bg-white shadow">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4">

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">

            {{-- ================= 左：会社情報 ================= --}}
            <div class="flex items-center gap-3">

                @if($company->logo_path)
                    <img src="{{ asset('storage/'.$company->logo_path) }}"
                         class="h-10 w-10 object-cover rounded-full">
                @else
                    <div class="h-10 w-10 rounded-full bg-gray-200
                                flex items-center justify-center font-bold">
                        {{ strtoupper(substr($company->name,0,1)) }}
                    </div>
                @endif

                <div>
                    <div class="font-bold text-base sm:text-lg leading-tight">
                        {{ $company->name }}
                    </div>
                    <div class="text-xs text-gray-500">
                        管理画面
                    </div>
                </div>
            </div>

            {{-- ================= 右：担当者情報 ================= --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6 text-center sm:text-right">

                <div>
                    <div class="font-semibold text-sm">
                        {{ $staff->name }}
                    </div>
                    <div class="text-xs text-gray-500">
                        権限：{{ $staff->roleLabel() }}
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('company.logout') }}"
                      class="w-full sm:w-auto">
                    @csrf
                    <button
                        class="w-full sm:w-auto
                               bg-red-500 text-white
                               px-4 py-2 rounded-lg
                               hover:opacity-90 transition">
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
