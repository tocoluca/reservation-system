<!DOCTYPE html>
<html>
<head>
    <title>企業登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-2xl mx-3 sm:mx-auto mt-3 md:mt-16 bg-white p-4 md:p-8 rounded-2xl shadow-lg">


<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">

    <a href="{{ route('admin.dashboard') }}"
       class="inline-flex items-center gap-2
              border border-gray-300
              text-gray-700
              px-4 py-2 rounded-lg
              hover:bg-gray-100
              transition text-sm md:text-base">

        <span class="text-lg">←</span> ダッシュボードへ戻る
    </a>

    <h2 class="text-xl md:text-2xl font-bold">
        企業登録
    </h2>

</div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 mb-6 rounded-lg text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.company.store') }}">
        @csrf

        <!-- 基本情報 -->
        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">
                企業名
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full border border-gray-300 p-3 rounded-lg
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">
                業種
            </label>
            <select name="industry_type"
                    class="w-full border border-gray-300 p-3 rounded-lg
                           focus:outline-none focus:ring-2 focus:ring-green-400">
                <option value="beauty">美容院</option>
                <option value="dental">歯科</option>
            </select>
        </div>

        <hr class="my-8">

        <!-- マスター担当者 -->
        <h3 class="text-lg md:text-xl font-bold mb-4">
            マスター担当者情報
        </h3>

        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">
                担当者コード
            </label>
            <input name="staff_code"
                   value="{{ old('staff_code') }}"
                   required
                   class="w-full border border-gray-300 p-3 rounded-lg
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">
                担当者名
            </label>
            <input name="staff_name"
                   value="{{ old('staff_name') }}"
                   required
                   class="w-full border border-gray-300 p-3 rounded-lg
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">
                パスワード
            </label>
            <input type="password"
                   name="staff_password"
                   required
                   class="w-full border border-gray-300 p-3 rounded-lg
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        <div class="mb-8">
            <label class="block text-sm font-semibold mb-1">
                マスター用メールアドレス
            </label>
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   class="w-full border border-gray-300 p-3 rounded-lg
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        <button class="w-full md:w-auto
                       bg-green-500 hover:bg-green-600
                       text-white px-6 py-3 rounded-lg
                       font-semibold transition">
            登録する
        </button>

    </form>
</div>

@include('admin.partials.mobile_nav')
</body>
</html>
