<!DOCTYPE html>
<html>
<head>
    <title>企業一覧</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-7xl mx-auto mt-6 md:mt-10 bg-white p-4 md:p-8 rounded-xl shadow">

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
        企業一覧
    </h2>

</div>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
{{ session('success') }}
</div>
@endif

<!-- 検索 -->
<form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">

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
<th class="p-3 border text-left">予約URL</th>
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

<td class="border p-3">

<div class="space-y-2">

<input
id="url_{{ $company->id }}"
type="text"
value="{{ url('/r/'.$company->company_code) }}"
class="border p-2 rounded w-full text-xs"
readonly>

<div class="flex gap-2">

<button
onclick="copyUrl('url_{{ $company->id }}')"
class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">

コピー

</button>

<a
href="{{ url('/r/'.$company->company_code) }}"
target="_blank"
class="bg-gray-700 hover:bg-gray-800 text-white px-3 py-1 rounded text-xs">

開く

</a>

<button
onclick="showQR('{{ url('/r/'.$company->company_code) }}')"
class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">

QR

</button>

</div>

</div>

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


<script>

function copyUrl(id){

const input = document.getElementById(id)

input.select()
input.setSelectionRange(0,99999)

document.execCommand("copy")

alert("予約URLをコピーしました")

}


/* QR表示 */

function showQR(url){

const modal = document.getElementById("qrModal")
const img = document.getElementById("qrImage")

img.src = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" + encodeURIComponent(url)

modal.style.display="flex"

}


function closeQR(){

document.getElementById("qrModal").style.display = "none"

}
function downloadQR(){

const img = document.getElementById("qrImage").src

const link = document.createElement("a")

link.href = img
link.download = "reservation_qr.png"

document.body.appendChild(link)
link.click()
document.body.removeChild(link)

}
</script>
<div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">

<div class="bg-white p-6 rounded shadow text-center">

<h3 class="font-bold mb-4">予約QRコード</h3>

<img id="qrImage" class="mx-auto mb-4">

<div class="flex justify-center gap-3">

<button
onclick="downloadQR()"
class="bg-blue-500 text-white px-4 py-2 rounded">

ダウンロード

</button>

<button
onclick="closeQR()"
class="bg-gray-700 text-white px-4 py-2 rounded">

閉じる

</button>

</div>

</div>

</div>
</body>
</html>