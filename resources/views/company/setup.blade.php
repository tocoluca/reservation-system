<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>初期設定</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4 py-10">

<div class="bg-white w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-lg">

    <h1 class="text-2xl font-bold mb-8 text-center">
        初期設定
    </h1>

    <form method="POST" action="/company/setup">
        @csrf

        {{-- 時間刻み --}}
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                時間刻み（分）
            </label>

            <select name="slot_minutes"
                    class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
                <option value="15">15分</option>
                <option value="20">20分</option>
                <option value="30">30分</option>
            </select>
        </div>

        {{-- 同時予約数 --}}
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                同時予約数
            </label>

            <input type="number"
                   name="max_simultaneous_reservations"
                   value="1"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        {{-- 営業時間 --}}
        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                営業時間（開始）
            </label>

            <input type="time"
                   name="open_time"
                   value="09:00"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        <div class="mb-6">
            <label class="block mb-2 font-semibold text-sm">
                営業時間（終了）
            </label>

            <input type="time"
                   name="close_time"
                   value="18:00"
                   class="border p-3 w-full rounded-lg text-base focus:ring-2 focus:outline-none focus:ring-blue-400">
        </div>

        {{-- 休業日 --}}
        <div class="mb-6">
            <label class="block mb-3 font-semibold text-sm">
                休業日
            </label>

            @php
                $days = ['日','月','火','水','木','金','土'];
            @endphp

            <div class="grid grid-cols-4 gap-2 text-sm">
                @foreach($days as $i => $day)
                    <label class="flex items-center gap-2 border rounded-lg px-2 py-2 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox"
                               name="regular_holidays[]"
                               value="{{ $i }}">
                        <span>{{ $day }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 祝日設定 --}}
        <div class="mb-8">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox"
                       name="holiday_is_closed"
                       value="1">
                祝日を休業日にする
            </label>
        </div>

        {{-- ボタン --}}
        <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600
                       text-white py-4 rounded-lg shadow-lg
                       transition duration-200 text-base font-semibold">
            保存
        </button>

    </form>

</div>

</body>
</html>