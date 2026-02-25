<!DOCTYPE html>
<html>
<head>
    <title>企業一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 md:mt-10 bg-white p-4 md:p-8 rounded-xl shadow">

    <h2 class="text-xl md:text-2xl font-bold mb-6">
        企業一覧
    </h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- 検索 -->
    <form method="GET"
          class="mb-6 flex flex-col sm:flex-row gap-3">

        <input type="text"
               name="keyword"
               placeholder="企業名または企業コード"
               class="border border-gray-300 p-3 rounded-lg w-full sm:w-80
                      focus:outline-none focus:ring-2 focus:ring-blue-400">

        <button class="bg-blue-500 hover:bg-blue-600
                       text-white px-6 py-3 rounded-lg transition">
            検索
        </button>
    </form>

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('admin.company.create') }}"
           class="bg-green-500 hover:bg-green-600
                  text-white px-5 py-2 rounded-lg transition">
            ＋ 新規登録
        </a>
    </div>

    <!-- テーブル -->
    <div class="overflow-x-auto">

        <table class="min-w-full border border-gray-200 text-sm md:text-base">

            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 border text-left">ID</th>
                    <th class="p-3 border text-left">企業コード</th>
                    <th class="p-3 border text-left">企業名</th>
                    <th class="p-3 border text-left">業種</th>
                    <th class="p-3 border text-center">状態</th>
                    <th class="p-3 border text-center">操作</th>
                </tr>
            </thead>

            <tbody>
            @foreach($companies as $company)
                <tr class="hover:bg-gray-50">

                    <td class="border p-3">{{ $company->id }}</td>

                    <td class="border p-3 font-mono">
                        {{ $company->company_code }}
                    </td>

                    <td class="border p-3">
                        {{ $company->name }}
                    </td>

                    <td class="border p-3">
                        {{ $company->industry_type }}
                    </td>

                    <td class="border p-3 text-center">
                        @if($company->is_active)
                            <span class="text-green-600 font-semibold">
                                利用中
                            </span>
                        @else
                            <span class="text-red-600 font-semibold">
                                停止
                            </span>
                        @endif
                    </td>

                    <td class="border p-3 text-center">
                        <form method="POST"
                              action="{{ route('admin.company.toggle', $company->id) }}">
                            @csrf
                            <button class="px-4 py-2 rounded-lg text-white transition
                                {{ $company->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                                {{ $company->is_active ? '停止' : '再開' }}
                            </button>
                        </form>
                    </td>

                </tr>
            @endforeach
            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $companies->links() }}
    </div>

</div>

</body>
</html>