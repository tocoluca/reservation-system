<!DOCTYPE html>

<html>
<head>
    @php
        $staff = auth()->guard('company')->user();
        $company = $staff ? $staff->company : null;
    @endphp
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
<div class="bg-white shadow p-4 flex justify-between items-center">

    <div class="flex items-center space-x-4">

        @if($company->logo_path)
	<img src="{{ asset('storage/'.$company->logo_path) }}"
	     class="h-10 w-10 object-cover rounded-full">
        @else
            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center font-bold">
                {{ strtoupper(substr($company->name,0,1)) }}
            </div>
        @endif

        <div>
            <div class="font-bold">
                {{ $company->name }}
            </div>
            <div class="text-xs text-gray-500">
                管理画面
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('company.logout') }}">
        @csrf
        <button class="bg-red-500 text-white px-4 py-1 rounded">
            ログアウト
        </button>
    </form>

</div>
@endif
@if(session('success'))
    <div class="max-w-5xl mx-auto mt-6">
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded shadow">
            {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="max-w-5xl mx-auto mt-6">
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded shadow">
            {{ session('error') }}
        </div>
    </div>
@endif
<div class="p-6">
    @yield('content')
</div>

</body>
</html>