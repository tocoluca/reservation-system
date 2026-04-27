@extends('layouts.app')

@section('content')

@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="min-h-screen bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-2xl mx-auto px-4 py-8">

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-6 text-white"
                 style="background: linear-gradient(135deg, {{ $theme }}, #111827)">
                <h1 class="text-2xl font-bold">ご予約のキャンセル</h1>
                <p class="text-sm text-white/80 mt-2">
                    内容をご確認のうえ、お手続きください
                </p>
            </div>

            <div class="p-6 space-y-5">
                @if(session('error'))
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-4 text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="rounded-2xl bg-gray-50 p-4 space-y-2 text-sm">
                    <div><span class="text-gray-400">お名前：</span>{{ $reservation->customer_name }}</div>
                    <div><span class="text-gray-400">日時：</span>{{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}</div>
                    <div><span class="text-gray-400">ステータス：</span>{{ $reservation->status }}</div>
                </div>

                @if($canCancel)
                    <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4 text-sm text-blue-900 leading-7">
                        Webでのキャンセルは、{{ $cancelDescription }}可能です。<br>
                        この予約は現在、Webからキャンセルできます。
                    </div>

                    <form method="POST" action="{{ route('reserve.cancel.execute', ['token' => $reservation->cancel_token]) }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full py-4 rounded-2xl text-white font-bold"
                            style="background: {{ $theme }}">
                            この予約をキャンセルする
                        </button>
                    </form>
                @else
                    <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-800 leading-7">
                        Webでのキャンセル受付時間を過ぎています。<br>
                        お手数ですが、お電話にてご連絡をお願いいたします。
                    </div>

                    @if($company->phone)
                        <div class="text-center">
                            <a href="tel:{{ preg_replace('/[^0-9]/', '', $company->phone) }}"
                               class="inline-block px-6 py-3 rounded-2xl text-white font-bold"
                               style="background: {{ $theme }}">
                                {{ $company->phone }} に電話する
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
