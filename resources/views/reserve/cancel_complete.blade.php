<div class="max-w-md mx-auto text-center p-6">

<h1 class="text-xl font-bold text-red-500 mb-6">
予約をキャンセルしました
</h1>

<div class="bg-white shadow rounded-xl p-5">

{{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}

</div>

</div>