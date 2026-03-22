@extends('layouts.app')

@section('content')

@php
    $company = $reservation->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="min-h-screen bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:py-10">

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 sm:px-8 py-8 text-center text-white"
                 style="background: linear-gradient(135deg, {{ $theme }}, #111827)">
                <div class="w-16 h-16 mx-auto rounded-full bg-white/15 flex items-center justify-center text-3xl mb-4">
                    !
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold mb-2">
                    予約をキャンセルしました
                </h1>

                <p class="text-sm sm:text-base text-white/80 leading-6">
                    キャンセル手続きが完了しました
                </p>
            </div>

            <div class="p-5 sm:p-8">

                <div class="bg-gray-50 rounded-3xl p-5 sm:p-6 mb-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-400 mb-1">店名</div>
                        <div class="font-semibold text-gray-900">
                            {{ $company->name }}
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-400 mb-1">キャンセルした日時</div>
                            <div class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-400 mb-1">お名前</div>
                            <div class="font-semibold text-gray-900">
                                {{ $reservation->customer_name }}
                            </div>
                        </div>
                    </div>

                    @if($reservation->menus && $reservation->menus->count())
                        <div>
                            <div class="text-sm text-gray-400 mb-1">メニュー</div>
                            <div class="space-y-1 font-semibold text-gray-900">
                                @foreach($reservation->menus as $rm)
                                    <div>
                                        {{ $rm->menu->name ?? '' }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-red-50 border border-red-100 p-4 text-sm text-red-700 leading-7 mb-6">
                    ご予約はキャンセル済みです。<br>
                    もう一度ご予約される場合は、予約画面からあらためてお手続きください。
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <a href="{{ url('/r/'.$company->company_code) }}"
                       class="block text-white py-4 rounded-2xl text-center font-semibold"
                       style="background: {{ $theme }}">
                        予約トップへ戻る
                    </a>

                    <button onclick="closePage()"
                            class="block border border-gray-200 py-4 rounded-2xl text-center font-semibold bg-white text-gray-700">
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
        location.href = "{{ url('/r/'.$company->company_code) }}";
    }, 200);
}
</script>

@endsection