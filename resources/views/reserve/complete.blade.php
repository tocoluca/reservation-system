@extends('layouts.app')

@section('content')

@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="min-h-screen bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:py-10">

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 sm:px-8 py-8 text-center text-white"
                 style="background: linear-gradient(135deg, {{ $theme }}, #111827)">
                <div class="w-16 h-16 mx-auto rounded-full bg-white/15 flex items-center justify-center text-3xl mb-4">
                    ✓
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold mb-2">
                    予約が完了しました
                </h1>

                <p class="text-sm sm:text-base text-white/80 leading-6">
                    ご予約ありがとうございます
                </p>
            </div>

            <div class="p-5 sm:p-8">

                <div class="rounded-2xl bg-sky-50 border border-sky-100 p-5 mb-6 text-center">
                    <div class="text-sm text-sky-700 font-semibold mb-2">
                        予約番号
                    </div>
                    <div class="text-3xl font-bold text-sky-900 tracking-wider">
                        {{ $reservation->id }}
                    </div>
                    <div class="text-xs text-sky-700 mt-2">
                        お問い合わせや確認時に必要になる場合があります
                    </div>
                </div>

                <div class="bg-gray-50 rounded-3xl p-5 sm:p-6 mb-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-400 mb-1">店名</div>
                        <div class="font-semibold text-gray-900">
                            {{ $company->name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400 mb-1">メニュー</div>
                        <div class="space-y-1 font-semibold text-gray-900">
                            @foreach($menus as $rm)
                                <div>{{ $rm->menu->name }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-400 mb-1">担当</div>
                            <div class="font-semibold text-gray-900">
                                @if($staff)
                                    {{ $staff->name }}
                                @else
                                    指名なし
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-400 mb-1">日時</div>
                            <div class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-400 mb-1">合計料金（目安）</div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ number_format($reservation->total_price) }}円
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-800 leading-7 mb-6">
                    ※ 表示料金は目安です。施術内容や髪の状態により前後する場合があります。<br>
                    ※ ご来店時に内容と料金の最終確認を行いますのでご安心ください。
                </div>

                <div class="rounded-2xl bg-white border border-gray-200 p-5 mb-6">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-3">
                        ご来店前のご案内
                    </h2>

                    <ul class="space-y-2 text-sm text-gray-600 leading-7">
                        <li>・ご予約日時の少し前を目安にご来店ください</li>
                        <li>・変更やキャンセルが必要な場合は、お早めにお手続きください</li>
                        <li>・確認メールが届いている場合は、あわせてご確認ください</li>
                    </ul>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <a
                        href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ urlencode($company->name.' 予約') }}&dates={{ \Carbon\Carbon::parse($reservation->start_at)->format('Ymd\THis') }}/{{ \Carbon\Carbon::parse($reservation->end_at)->format('Ymd\THis') }}&details={{ urlencode($menus->pluck('menu.name')->implode(',')) }}"
                        target="_blank"
                        class="block text-white py-4 rounded-2xl text-center font-semibold"
                        style="background: {{ $theme }}">
                        Googleカレンダーに追加
                    </a>

                    <a
                        href="{{ url('/cancel/'.$reservation->cancel_token) }}"
                        class="block border border-red-200 text-red-500 py-4 rounded-2xl text-center font-semibold bg-red-50">
                        予約をキャンセルする
                    </a>
                </div>

                <div class="grid sm:grid-cols-2 gap-3 mt-3">
                    <a
                        href="{{ url('/r/'.$company->company_code) }}"
                        class="block border border-gray-200 py-4 rounded-2xl text-center font-semibold bg-white text-gray-700">
                        予約トップへ戻る
                    </a>

                    <button
                        onclick="closePage()"
                        class="border border-gray-200 py-4 rounded-2xl w-full text-gray-700 font-semibold bg-white">
                        画面を閉じる
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function closePage() {
    window.close();
    setTimeout(function () {
        location.href = "/r/{{ $company->company_code }}";
    }, 200);
}
</script>

@endsection