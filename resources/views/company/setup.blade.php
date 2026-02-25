<!DOCTYPE html>
<html>
<head>
    <title>初期設定</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded shadow w-96">

<h1 class="text-xl font-bold mb-6">初期設定</h1>

<form method="POST" action="/company/setup">
@csrf

<label class="block mb-1">時間刻み（分）</label>
<select name="slot_minutes" class="border p-2 w-full mb-4">
    <option value="15">15分</option>
    <option value="20">20分</option>
    <option value="30">30分</option>
</select>

<label class="block mb-1">同時予約数</label>
<input type="number" name="max_simultaneous_reservations"
       class="border p-2 w-full mb-4" value="1">
<label class="block mb-1">営業時間（開始）</label>
<input type="time" name="open_time" value="09:00"
    class="border p-2 w-full mb-4">

<label class="block mb-1">営業時間（終了）</label>
<input type="time" name="close_time" value="18:00"
    class="border p-2 w-full mb-4">

<label class="block mb-1">休業日</label>
@php
$days = ['日','月','火','水','木','金','土'];
@endphp

<div class="mb-4">
@foreach($days as $i => $day)
<label class="mr-3">
    <input type="checkbox" name="regular_holidays[]"
        value="{{ $i }}"> {{ $day }}
</label>
@endforeach
</div>

<label>
<input type="checkbox" name="holiday_is_closed" value="1">
祝日を休業日にする
</label>

<button class="w-full bg-blue-500 text-white p-2 rounded">
    保存
</button>

</form>

</div>

</body>
</html>