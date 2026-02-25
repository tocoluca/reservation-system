<!DOCTYPE html>
<html>
<head>
    <title>企業ログイン</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<form method="POST" action="{{ route('company.login') }}"
      class="bg-white p-8 rounded shadow w-96">

    @csrf

    <h1 class="text-xl mb-6 text-center font-bold">企業ログイン</h1>

    @if(session('error'))
        <div class="text-red-500 mb-3">{{ session('error') }}</div>
    @endif

    <input type="text" name="company_code"
        placeholder="企業コード"
        class="w-full border p-2 mb-3 rounded">

    <input type="text" name="staff_code"
        placeholder="担当者コード"
        class="w-full border p-2 mb-3 rounded">

    <input type="password" name="password"
        placeholder="パスワード"
        class="w-full border p-2 mb-4 rounded">

    <button class="w-full bg-blue-500 text-white p-2 rounded">
        ログイン
    </button>

</form>

</body>
</html>