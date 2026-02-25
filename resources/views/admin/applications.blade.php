<!DOCTYPE html>
<html>
<head>
    <title>申込み管理</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

<h1 class="text-2xl font-bold mb-6">企業申込み一覧</h1>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
    {{ session('success') }}
</div>
@endif

<table class="w-full bg-white shadow rounded">
    <thead class="bg-gray-200">
        <tr>
            <th class="p-3">企業名</th>
            <th>業種</th>
            <th>担当者</th>
            <th>メール</th>
            <th>状態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
    @foreach($applications as $app)
        <tr class="border-t text-center">
            <td class="p-3">{{ $app->company_name }}</td>
            <td>{{ $app->industry_type }}</td>
            <td>{{ $app->contact_person }}</td>
            <td>{{ $app->email }}</td>
            <td>
                @if($app->status == 'pending')
                    <span class="text-yellow-600 font-bold">審査中</span>
                @elseif($app->status == 'approved')
                    <span class="text-green-600 font-bold">承認済</span>
                @else
                    <span class="text-red-600 font-bold">拒否</span>
                @endif
            </td>

            <td class="space-x-2">
                @if($app->status == 'pending')

                <!-- 詳細ボタン -->
                <button onclick="openModal({{ $app->id }})"
                    class="bg-blue-500 text-white px-3 py-1 rounded">
                    詳細
                </button>

                <form method="POST"
                      action="{{ route('admin.applications.approve',$app->id) }}"
                      class="inline">
                    @csrf
                    <button class="bg-green-500 text-white px-3 py-1 rounded">
                        承認
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('admin.applications.reject',$app->id) }}"
                      class="inline">
                    @csrf
                    <button class="bg-red-500 text-white px-3 py-1 rounded">
                        拒否
                    </button>
                </form>

                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- モーダル -->
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded w-96">
        <h2 class="text-xl font-bold mb-4">申込み詳細</h2>
        <div id="modalContent"></div>
        <button onclick="closeModal()"
            class="mt-4 bg-gray-500 text-white px-4 py-2 rounded">
            閉じる
        </button>
    </div>
</div>

<script>
function openModal(id) {
    fetch('/admin/applications')
    .then(() => {
        const app = @json($applications).find(a => a.id === id);
        document.getElementById('modalContent').innerHTML = `
            <p><b>企業名:</b> ${app.company_name}</p>
            <p><b>担当者:</b> ${app.contact_person}</p>
            <p><b>電話:</b> ${app.phone}</p>
            <p><b>メッセージ:</b> ${app.message ?? ''}</p>
        `;
        document.getElementById('modal').classList.remove('hidden');
    });
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}
</script>

</body>
</html>