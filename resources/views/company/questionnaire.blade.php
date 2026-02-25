<!DOCTYPE html>
<html>
<head>
    <title>問診票入力</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded shadow w-full max-w-lg">

@extends('customer.layout')

@section('content')

<div class="bg-white w-full max-w-md mx-auto p-6 rounded-2xl shadow-lg">

<h1 class="text-lg font-bold mb-4 text-center">
問診票入力
</h1>

<form method="POST">
@csrf

<textarea name="symptoms"
class="w-full border p-3 rounded-lg mb-4"
placeholder="現在の症状"></textarea>

<button
style="background-color: {{ $company->theme_color }}"
class="w-full text-white py-3 rounded-full">
送信する
</button>

</form>

    <div class="mb-4 text-gray-600">
        予約日時：
        {{ $reservation->start_at->format('Y年m月d日 H:i') }}
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif


</div>

@endsection


</body>
</html>