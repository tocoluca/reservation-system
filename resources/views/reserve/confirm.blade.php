@extends('layouts.app')

@section('content')

@php
    $theme = $company->theme_color ?? '#3b82f6';
    $totalPrice = $menus->sum('price');
    $staffFee = $staff->nomination_fee ?? 0;
    $finalPrice = $totalPrice + $staffFee;
@endphp

<div class="min-h-screen bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:py-10">

        <div class="text-center mb-6">
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white mb-3"
                 style="background: {{ $theme }}">
                CONFIRM RESERVATION
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                予約内容の確認
            </h1>

            <p class="text-sm sm:text-base text-gray-500 mt-2 leading-6">
                内容をご確認のうえ、お客様情報を入力してください
            </p>
        </div>

        {{-- 入力エラー --}}
        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
                <div class="font-semibold mb-2 text-sm">入力内容を確認してください</div>
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 予約内容 --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 sm:px-8 py-6 text-white"
                 style="background: linear-gradient(135deg, {{ $theme }}, #111827)">
                <h2 class="text-xl sm:text-2xl font-bold mb-2">
                    ご予約内容
                </h2>
                <p class="text-sm text-white/80">
                    まだ予約は確定していません。次のボタンで完了します。
                </p>
            </div>

            <div class="p-5 sm:p-8">
                <div class="space-y-5">

                    <div>
                        <div class="text-sm text-gray-400 mb-2">メニュー</div>
                        <div class="space-y-2">
                            @foreach($menus as $menu)
                                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $menu->name }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">
                                            {{ $menu->duration }}分
                                        </div>
                                        <div class="text-sm font-semibold text-gray-800">
                                            {{ number_format($menu->price) }}円
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="rounded-2xl bg-gray-50 px-4 py-4">
                            <div class="text-sm text-gray-400 mb-1">担当</div>
                            <div class="font-semibold text-gray-900">
                                @if($staff)
                                    {{ $staff->name }}
                                @else
                                    指名なし
                                @endif
                            </div>
                            @if($staff && $staffFee > 0)
                                <div class="text-sm text-amber-600 mt-1">
                                    指名料 +{{ number_format($staffFee) }}円
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl bg-gray-50 px-4 py-4">
                            <div class="text-sm text-gray-400 mb-1">日時</div>
                            <div class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($start_at)->format('Y年m月d日 H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-900 text-white px-5 py-5">
                        <div class="text-sm text-white/70 mb-1">合計料金（目安）</div>
                        <div class="text-3xl font-bold">{{ number_format($finalPrice) }}円</div>
                    </div>

                    <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-800 leading-7">
                        ※ 表示料金は目安です。施術内容や髪の状態により前後する場合があります。<br>
                        ※ ご来店時に内容と料金の最終確認を行いますのでご安心ください。
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($lineProfile))
            <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5">
                <div class="font-semibold text-emerald-900 mb-1">
                    LINEログイン中
                </div>
                <div class="text-sm text-emerald-700 leading-6">
                    LINEでログイン済みです。入力しやすいように、お客様情報を自動で反映しています。
                </div>
            </div>
        @endif

        {{-- 入力フォーム --}}
        <form method="POST" action="/r/{{ $company->company_code }}/store" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8 space-y-6" id="confirmForm">
            @csrf

            @foreach($menus as $menu)
                <input type="hidden" name="menu_ids[]" value="{{ $menu->id }}">
            @endforeach

            <input type="hidden" name="staff_id" value="{{ $staff->id ?? '' }}">
            <input type="hidden" name="start_at" value="{{ $start_at }}">

            <div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-1">
                    お客様情報
                </h2>
                <p class="text-sm text-gray-500">
                    ご予約確認や必要時のご連絡のために使用します
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    お名前 <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="customer_name"
                    value="{{ old('customer_name', $lineCustomer->name ?? ($lineProfile['name'] ?? '')) }}"
                    placeholder="例：山田 花子"
                    class="border border-gray-300 rounded-2xl p-3.5 w-full focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color: {{ $theme }}33;"
                    required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    電話番号 <span class="text-red-500">*</span>
                </label>
                <input
                    type="tel"
                    name="customer_phone"
                    value="{{ old('customer_phone', $lineCustomer->phone ?? '') }}"
                    placeholder="090-1234-5678"
                    pattern="[0-9\-]+"
                    inputmode="numeric"
                    class="border border-gray-300 rounded-2xl p-3.5 w-full focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color: {{ $theme }}33;"
                    oninput="formatTel(this)"
                    required>
                <p class="text-xs text-gray-400 mt-2">
                    半角数字と - を入力してください
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    メールアドレス
                </label>
                <input
                    type="email"
                    name="customer_email"
                    value="{{ old('customer_email', $lineCustomer->email ?? ($lineProfile['email'] ?? '')) }}"
                    placeholder="example@example.com"
                    class="border border-gray-300 rounded-2xl p-3.5 w-full focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color: {{ $theme }}33;">
                <p class="text-xs text-gray-400 mt-2">
                    任意入力です。予約確認やご連絡に使用する場合があります
                </p>
            </div>

            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4 text-xs sm:text-sm text-gray-500 leading-7">
                入力内容を確認のうえ、下のボタンを押すと予約が確定します。<br>
                送信後は完了画面が表示されます。
            </div>

            <button
                type="submit"
                id="submitButton"
                style="background: {{ $theme }}"
                class="text-white w-full py-4 rounded-2xl font-bold text-base sm:text-lg shadow-lg">
                この内容で予約を確定する
            </button>
        </form>

        <div class="grid sm:grid-cols-2 gap-3 mt-4">
            <button
                onclick="history.back()"
                class="border border-gray-200 py-4 rounded-2xl w-full text-gray-700 font-semibold bg-white">
                予約内容を変更する
            </button>

            <a
                href="{{ url('/r/'.$company->company_code) }}"
                class="border border-gray-200 py-4 rounded-2xl w-full text-gray-700 font-semibold bg-white text-center">
                予約トップへ戻る
            </a>
        </div>

    </div>
</div>

<script>
function formatTel(input) {
    input.value = input.value.replace(/[^0-9\-]/g, '');
}

document.getElementById('confirmForm').addEventListener('submit', function () {
    const button = document.getElementById('submitButton');
    button.disabled = true;
    button.textContent = '送信中...';
    button.classList.add('opacity-70', 'cursor-not-allowed');
});
</script>

@endsection