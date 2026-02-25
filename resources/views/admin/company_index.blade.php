<!DOCTYPE html>
<html>
<head>
    <title>企業一覧</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-10 bg-white p-8 rounded shadow">

    <h2 class="text-xl font-bold mb-6">企業一覧</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- 検索 -->
    <form method="GET" class="mb-4 flex">
        <input type="text" name="keyword"
            placeholder="企業名または企業コード"
            class="border p-2 w-80 rounded-l">

        <button class="bg-blue-500 text-white px-4 rounded-r">
            検索
        </button>
    </form>

    <a href="{{ route('admin.company.create') }}"
       class="bg-green-500 text-white px-4 py-2 rounded">
        ＋ 新規登録
    </a>

    <table class="w-full mt-6 border">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2 border">ID</th>
                <th class="p-2 border">企業コード</th>
                <th class="p-2 border">企業名</th>
                <th class="p-2 border">業種</th>
                <th class="p-2 border">状態</th>
                <th class="p-2 border">操作</th>
            </tr>
        </thead>
        <tbody>
        @foreach($companies as $company)
            <tr class="text-center">
                <td class="border p-2">{{ $company->id }}</td>
                <td class="border p-2 font-mono">
                    {{ $company->company_code }}
                </td>
                <td class="border p-2">
                    {{ $company->name }}
                </td>
                <td class="border p-2">
                    {{ $company->industry_type }}
                </td>
                <td class="border p-2">
                    @if($company->is_active)
                        <span class="text-green-600 font-bold">利用中</span>
                    @else
                        <span class="text-red-600 font-bold">停止</span>
                    @endif
                </td>
                <td class="border p-2">
                    <form method="POST"
                          action="{{ route('admin.company.toggle', $company->id) }}">
                        @csrf
                        <button class="px-3 py-1 rounded
                            {{ $company->is_active ? 'bg-red-500' : 'bg-green-500' }}
                            text-white">
                            {{ $company->is_active ? '停止' : '再開' }}
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $companies->links() }}
    </div>

</div>

</body>
</html>