<!DOCTYPE html>
<html>
<head>
    <title>管理者ログイン</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4">

<form method="POST"
      action="{{ route('admin.login') }}"
      class="bg-white w-full max-w-md p-6 md:p-8 rounded-2xl shadow-lg">

    @csrf

    <h1 class="text-2xl font-bold text-center mb-6">
        管理者ログイン
    </h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-600 text-sm p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <label class="block text-sm text-gray-600 mb-1">
            メールアドレス
        </label>
        <input type="email"
               name="email"
               placeholder="example@mail.com"
               class="w-full border border-gray-300 p-3 rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-blue-400
                      transition">
    </div>

    <div class="mb-6">
        <label class="block text-sm text-gray-600 mb-1">
            パスワード
        </label>
        <input type="password"
               name="password"
               placeholder="••••••••"
               class="w-full border border-gray-300 p-3 rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-blue-400
                      transition">
    </div>

    <button class="w-full bg-blue-500 hover:bg-blue-600
                   text-white font-semibold p-3 rounded-lg
                   transition">
        ログイン
    </button>

</form>

</body>
</html>