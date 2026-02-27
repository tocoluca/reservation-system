<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>企業ログイン</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">

    <form method="POST" action="{{ route('company.login') }}"
          class="bg-white w-full max-w-md p-6 sm:p-8 rounded-xl shadow-lg">

        @csrf

        <h1 class="text-2xl font-bold text-center mb-6">
            企業ログイン
        </h1>

        {{-- エラーメッセージ --}}
        @if(session('error'))
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- 企業コード --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
                企業コード
            </label>
            <input type="text"
                   name="company_code"
                   value="{{ old('company_code') }}"
                   required
                   class="w-full border border-gray-300 rounded-lg p-3 text-base
                          focus:outline-none focus:ring-2 focus:ring-blue-400
                          focus:border-blue-400 transition">
        </div>

        {{-- 担当者コード --}}
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
                担当者コード
            </label>
            <input type="text"
                   name="staff_code"
                   value="{{ old('staff_code') }}"
                   required
                   class="w-full border border-gray-300 rounded-lg p-3 text-base
                          focus:outline-none focus:ring-2 focus:ring-blue-400
                          focus:border-blue-400 transition">
        </div>

        {{-- パスワード --}}
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">
                パスワード
            </label>
            <input type="password"
                   name="password"
                   required
                   class="w-full border border-gray-300 rounded-lg p-3 text-base
                          focus:outline-none focus:ring-2 focus:ring-blue-400
                          focus:border-blue-400 transition">
        </div>

        {{-- ログインボタン --}}
        <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 active:scale-95
                       text-white font-semibold p-3 rounded-lg
                       transition duration-200">
            ログイン
        </button>

    </form>

</body>
</html>