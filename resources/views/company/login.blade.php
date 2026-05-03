@extends('layouts.company')

@section('content')
@php
    $theme = '#7c3aed';
@endphp

<div class="min-h-screen bg-gradient-to-br from-white via-purple-50 to-fuchsia-50 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-2">
                企業管理ログイン
            </h1>
            <p class="text-sm text-gray-500">
                企業コード、担当者コード、パスワードを入力してください。
            </p>
        </div>

        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-white/70 p-6 sm:p-8">

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                    <div class="text-sm font-semibold text-red-700 mb-2">
                        入力内容をご確認ください
                    </div>
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.login.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="company_code" class="block text-sm font-semibold text-gray-700 mb-2">
                        企業コード
                    </label>
                    <input
                        type="text"
                        id="company_code"
                        name="company_code"
                        value="{{ old('company_code') }}"
                        autocomplete="username"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        placeholder="例: TOCOLUCA"
                    >
                    @error('company_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="staff_code" class="block text-sm font-semibold text-gray-700 mb-2">
                        担当者コード
                    </label>
                    <input
                        type="text"
                        id="staff_code"
                        name="staff_code"
                        value="{{ old('staff_code') }}"
                        autocomplete="username"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        placeholder="担当者コードを入力"
                    >
                    @error('staff_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        パスワード
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"
                        placeholder="パスワードを入力"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-90"
                        style="background: {{ $theme }};"
                    >
                        ログイン
                    </button>
                </div>
            </form>

            <div class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4">
                <div class="text-sm font-bold text-red-800">マスター権限の方がパスワードを忘れた場合</div>
                <p class="mt-1 text-xs leading-6 text-red-700">
                    マスター権限のみ、この画面から初期化できます。他の権限の担当者は、担当者管理画面からマスター、エリアリーダー、リーダーに初期化を依頼してください。
                </p>
                <button type="button"
                        onclick="openMasterResetModal()"
                        class="mt-3 w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition">
                    マスターのパスワード初期化
                </button>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-5">
            ログインできない場合は、企業コード、担当者コード、パスワードをご確認ください。
        </p>
    </div>

    <div id="masterResetModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="px-6 py-5 text-white" style="background: linear-gradient(135deg, #dc2626 0%, #7f1d1d 100%);">
                <div class="text-xs font-bold tracking-wide text-white/80">MASTER PASSWORD RESET</div>
                <h2 class="mt-2 text-xl font-black">マスター権限のパスワード初期化</h2>
                <p class="mt-2 text-sm leading-6 text-white/85">
                    この操作はマスター権限の担当者だけが対象です。
                </p>
            </div>

            <form method="POST" action="{{ route('company.master-password-reset') }}" class="p-6 space-y-5">
                @csrf

                <div class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm leading-6 text-red-800">
                    他の権限の担当者は、ログイン後の担当者管理画面からパスワード初期化を行ってください。
                    初期化メールは <span class="font-bold">system@tocoluca.com</span> から送信されます。
                </div>

                <div>
                    <label for="reset_company_code" class="block text-sm font-bold text-gray-700 mb-2">企業コード</label>
                    <input id="reset_company_code"
                           type="text"
                           name="company_code"
                           value="{{ old('company_code') }}"
                           required
                           class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-200"
                           placeholder="企業コードを入力">
                </div>

                <div>
                    <label for="reset_email" class="block text-sm font-bold text-gray-700 mb-2">登録済みメールアドレス</label>
                    <input id="reset_email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-200"
                           placeholder="企業情報に登録されているメールアドレス">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            onclick="closeMasterResetModal()"
                            class="rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">
                        閉じる
                    </button>
                    <button type="submit"
                            onclick="return confirm('企業コードと登録済みメールアドレスを確認し、マスター権限のパスワードを初期化しますか？')"
                            class="rounded-2xl bg-red-600 px-4 py-3 text-sm font-bold text-white hover:bg-red-700">
                        初期化
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMasterResetModal() {
    const modal = document.getElementById('masterResetModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('reset_company_code').focus(), 50);
}

function closeMasterResetModal() {
    const modal = document.getElementById('masterResetModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeMasterResetModal();
    }
});
</script>
@endsection
