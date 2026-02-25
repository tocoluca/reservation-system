<!DOCTYPE html>
<html>
<head>
    <title>申込み管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-4 md:p-10">

<h1 class="text-xl md:text-2xl font-bold mb-6">
    企業申込み一覧
</h1>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
    {{ session('success') }}
</div>
@endif

<div class="overflow-x-auto bg-white shadow rounded-lg">

<table class="min-w-full text-sm md:text-base">
    <thead class="bg-gray-100">
        <tr class="text-left">
            <th class="p-3">企業名</th>
            <th class="p-3">業種</th>
            <th class="p-3">担当者</th>
            <th class="p-3">メール</th>
            <th class="p-3 text-center">状態</th>
            <th class="p-3 text-center">操作</th>
        </tr>
    </thead>

    <tbody>
    @foreach($applications as $app)
        <tr class="border-t hover:bg-gray-50">
            <td class="p-3">{{ $app->company_name }}</td>
            <td class="p-3">{{ $app->industry_type }}</td>
            <td class="p-3">{{ $app->contact_person }}</td>
            <td class="p-3 break-all">{{ $app->email }}</td>

            <td class="p-3 text-center">
                @if($app->status == 'pending')
                    <span class="text-yellow-600 font-semibold">審査中</span>
                @elseif($app->status == 'approved')
                    <span class="text-green-600 font-semibold">承認済</span>
                @else
                    <span class="text-red-600 font-semibold">拒否</span>
                @endif
            </td>

            <td class="p-3 text-center space-y-2 md:space-y-0 md:space-x-2">

                @if($app->status == 'pending')

                <button onclick="openModal({{ $app->id }})"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                    詳細
                </button>

                <form method="POST"
                      action="{{ route('admin.applications.approve',$app->id) }}"
                      class="inline">
                    @csrf
                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                        承認
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.applications.reject',$app->id) }}"
                      class="inline">
                    @csrf
                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                        拒否
                    </button>
                </form>

                @endif

            </td>
        </tr>
    @endforeach
    </tbody>
</table>

</div>

<!-- モーダル -->
<div id="modal"
     class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
     onclick="closeModal(event)">

    <div class="bg-white w-full max-w-lg p-6 rounded-2xl shadow-xl"
         onclick="event.stopPropagation()">

        <h2 class="text-lg md:text-xl font-bold mb-4">
            申込み詳細
        </h2>

        <div id="modalContent" class="text-sm md:text-base space-y-2"></div>

        <button onclick="closeModal()"
            class="mt-6 w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
            閉じる
        </button>

    </div>
</div>

<script>
function openModal(id) {
    const app = @json($applications).find(a => a.id === id);

    document.getElementById('modalContent').innerHTML = `
        <p><b>企業名:</b> ${app.company_name}</p>
        <p><b>担当者:</b> ${app.contact_person}</p>
        <p><b>電話:</b> ${app.phone ?? ''}</p>
        <p><b>メール:</b> ${app.email}</p>
        <p><b>メッセージ:</b> ${app.message ?? ''}</p>
    `;

    document.getElementById('modal').classList.remove('hidden');
}

function closeModal(e) {
    if (!e || e.target.id === 'modal') {
        document.getElementById('modal').classList.add('hidden');
    }
}
</script>

</body>
</html>