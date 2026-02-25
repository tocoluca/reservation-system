<!DOCTYPE html>
<html>
<head>
    <title>企業登録</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-20 bg-white p-8 rounded shadow">

    <h2 class="text-xl font-bold mb-6">企業登録</h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.company.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block mb-1">企業名</label>
            <input type="text" name="name" value="{{ old('name') }}"
                class="border p-2 w-full rounded">
        </div>

        <div class="mb-4">
            <label class="block mb-1">業種</label>
            <select name="industry_type" class="border p-2 w-full rounded">
                <option value="beauty">美容院</option>
                <option value="dental">歯科</option>
            </select>
        </div>

	<hr class="my-8">

	<h2 class="text-lg font-bold mb-4">
	マスター担当者情報
	</h2>

	<div class="mb-4">
	    <label>担当者コード</label>
	    <input name="staff_code"
	           class="border p-2 w-full"
		   value="{{ old('staff_code') }}"
	           required>
	</div>

	<div class="mb-4">
	    <label>担当者名</label>
	    <input name="staff_name"
	           class="border p-2 w-full"
		   value="{{ old('staff_name') }}"
	           required>
	</div>

	<div class="mb-4">
	    <label>パスワード</label>
	    <input type="password"
	           name="staff_password"
	           class="border p-2 w-full"
	           required>
	</div>

	<div class="mb-4">
	    <label>マスター用メールアドレス</label>
	    <input type="email"
	           name="email"
	           value="{{ old('email') }}"
	           class="border p-2 w-full"
	           required>
	</div>

        <button class="bg-green-500 text-white px-4 py-2 rounded">
            登録
        </button>


    </form>

    <a href="{{ route('admin.dashboard') }}"
       class="block mt-4 text-blue-500">
        ← ダッシュボードへ戻る
    </a>

</div>

</body>
</html>