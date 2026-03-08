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
'blue'=>'#3b82f6',
'pink'=>'#ec4899',
'green'=>'#10b981',
'purple'=>'#8b5cf6',
'orange'=>'#f97316'
];

$main = $colors[$theme] ?? '#3b82f6';
@endphp

<style>

.theme-bg{
background: {{ $main }};
}

.theme-text{
color: {{ $main }};
}

.theme-border{
border-color: {{ $main }};
}

</style>

</head>

<body class="bg-gray-100 text-gray-800">

{{-- ヘッダー --}}
<header class="bg-white shadow">
<div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">

<div class="flex items-center gap-3">

@if(!empty($company->logo_path))
<img src="{{ asset($company->logo_path) }}" class="h-10">
@endif

<div class="text-lg font-bold">
{{ $company->name ?? '予約サイト' }}
</div>

</div>

</div>
</header>



{{-- ステップ --}}
<div class="bg-gray-50 border-b">

<div class="max-w-3xl mx-auto px-4 py-4">

<div class="flex justify-between text-xs sm:text-sm">

{{-- STEP1 --}}
<div class="flex flex-col items-center">

<div class="w-7 h-7 rounded-full flex items-center justify-center text-xs
{{ ($step ?? 1) == 1 ? 'theme-bg text-white' : 'bg-gray-300 text-white' }}">
1
</div>

<div class="{{ ($step ?? 1) == 1 ? 'theme-text font-bold' : '' }}">
予約
</div>

</div>

{{-- STEP2 --}}
<div class="flex flex-col items-center">

<div class="w-7 h-7 rounded-full flex items-center justify-center text-xs
{{ ($step ?? 1) == 2 ? 'theme-bg text-white' : 'bg-gray-300 text-white' }}">
2
</div>

<div class="{{ ($step ?? 1) == 2 ? 'theme-text font-bold' : '' }}">
確認
</div>

</div>

{{-- STEP3 --}}
<div class="flex flex-col items-center">

<div class="w-7 h-7 rounded-full flex items-center justify-center text-xs
{{ ($step ?? 1) == 3 ? 'theme-bg text-white' : 'bg-gray-300 text-white' }}">
3
</div>

<div class="{{ ($step ?? 1) == 3 ? 'theme-text font-bold' : '' }}">
完了
</div>

</div>

</div>

</div>

</div>

{{-- コンテンツ --}}
<main class="py-6 sm:py-10 px-3">

<div class="max-w-3xl mx-auto">

@yield('content')

</div>

</main>



{{-- フッター --}}
<footer class="mt-10 border-t bg-white">

<div class="max-w-4xl mx-auto px-4 py-6 text-center text-xs sm:text-sm text-gray-500">

© {{ date('Y') }}
{{ $company->name ?? 'Reservation System' }}

</div>

</footer>


{{-- JSはここに置く --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>

</body>

</html>