@extends('layouts.company')

@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ============================= --}}
    {{-- ヘッダー --}}
    {{-- ============================= --}}
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold">
            ダッシュボード
        </h1>

        <p class="text-gray-500 mt-2 text-sm sm:text-base">
            {{ $staff->company->name }} ｜ {{ $staff->name }}（{{ $staff->role }}）
        </p>
    </div>


    {{-- ============================= --}}
    {{-- 管理メニュー --}}
    {{-- ============================= --}}
    <div class="mb-12">

        <h2 class="text-lg sm:text-xl font-bold mb-6">
            管理メニュー
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- 予約カレンダー --}}
            <a href="{{ route('company.reserve') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-blue-500">

                <div class="text-blue-500 text-xs font-semibold mb-2">
                    RESERVATION
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    予約カレンダー
                </div>

                <div class="text-gray-500 text-sm">
                    予約の確認・登録・管理
                </div>
            </a>

            {{-- 担当者管理 --}}
            @if(in_array($staff->role, ['master','area_leader','leader']))
            {{-- 営業日カレンダー --}}
            <a href="{{ route('company.calendar.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-blue-500">

                <div class="text-blue-500 text-xs font-semibold mb-2">
                    BUSINESS
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    営業日カレンダー
                </div>

                <div class="text-gray-500 text-sm">
                    営業日の確認・登録・管理
                </div>
            </a>


            <a href="{{ route('company.staff.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-indigo-500">

                <div class="text-indigo-500 text-xs font-semibold mb-2">
                    STAFF
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    担当者管理
                </div>

                <div class="text-gray-500 text-sm">
                    担当者の登録・編集・権限設定
                </div>
            </a>
            @endif


            {{-- 休暇管理 --}}
            <a href="{{ route('company.vacation.index') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-green-500">

                <div class="text-green-500 text-xs font-semibold mb-2">
                    VACATION
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    休暇管理
                </div>

                <div class="text-gray-500 text-sm">
                    休暇申請・承認管理
                </div>
            </a>


            {{-- テーマ設定 --}}
            @if($staff->role === 'master')
            <a href="{{ route('company.theme') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-purple-500">

                <div class="text-purple-500 text-xs font-semibold mb-2">
                    DESIGN
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    テーマ設定
                </div>

                <div class="text-gray-500 text-sm">
                    顧客画面のカラー変更
                </div>
            </a>
            @endif


            {{-- 企業情報編集 --}}
            @if($staff->role === 'master')
            <a href="{{ route('company.info.edit') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-orange-500">

                <div class="text-orange-500 text-xs font-semibold mb-2">
                    COMPANY
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    企業情報編集
                </div>

                <div class="text-gray-500 text-sm">
                    会社情報・営業時間変更
                </div>
            </a>
            @endif


            {{-- ロゴ設定 --}}
            @if($staff->role === 'master')
            <a href="{{ route('company.logo') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-gray-500">

                <div class="text-gray-500 text-xs font-semibold mb-2">
                    BRAND
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    ロゴ設定
                </div>

                <div class="text-gray-500 text-sm">
                    企業ロゴ変更
                </div>
            </a>
            @endif


            {{-- マイプロフィール --}}
            <a href="{{ route('company.my-profile') }}"
               class="bg-white shadow hover:shadow-lg active:scale-95 transition rounded-xl p-6 border-l-4 border-teal-500">

                <div class="text-teal-500 text-xs font-semibold mb-2">
                    MYPAGE
                </div>

                <div class="text-lg sm:text-xl font-bold mb-2">
                    マイプロフィール
                </div>

                <div class="text-gray-500 text-sm">
                    プロフィール・パスワード変更
                </div>
            </a>

        </div>
    </div>


    {{-- ============================= --}}
    {{-- 予約テスト（開発用） --}}
    {{-- ============================= --}}
    <div class="bg-white shadow rounded-xl p-6 mb-12">

        <h2 class="font-bold mb-6 text-lg">
            予約テスト（開発用）
        </h2>

        <form method="POST" action="/company/reservation"
              class="flex flex-col sm:flex-row gap-3">

            @csrf

            <input type="datetime-local"
                   name="start_at"
                   required
                   class="border rounded-lg p-3 w-full">

            <input name="customer_name"
                   placeholder="名前"
                   required
                   class="border rounded-lg p-3 w-full">

            <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg">
                予約テスト
            </button>
        </form>
    </div>


    {{-- ============================= --}}
    {{-- サマリー --}}
    {{-- ============================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">
                本日の予約数
            </div>
            <div class="text-3xl font-bold mt-2">
                {{ $todayCount }}
            </div>
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">
                今月の予約数
            </div>
            <div class="text-3xl font-bold mt-2">
                {{ $monthlyCount }}
            </div>
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <div class="text-gray-500 text-sm">
                稼働率
            </div>
            <div class="text-3xl font-bold mt-2">
                {{ $utilizationRate }}%
            </div>
            <div class="text-gray-400 text-xs mt-2">
                総予約時間 ÷ (営業時間合計 × スタッフ数)
            </div>
        </div>

    </div>

</div>

@endsection