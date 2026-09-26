@extends('layouts.company')

@section('content')
<style>
    .salon-login-stage { background: #f7f3ee; border: 1px solid #e8ddd3; box-shadow: 0 28px 75px rgba(55, 45, 43, .12); }
    .salon-login-story { position: relative; isolation: isolate; overflow: hidden; background: linear-gradient(145deg, #23352f 0%, #35483e 58%, #21332e 100%); }
    .salon-login-story::before { content: ''; position: absolute; width: 480px; height: 480px; border: 1px solid rgba(241, 222, 195, .3); border-radius: 50%; top: 29%; right: -48%; transform: rotate(-20deg); z-index: -1; }
    .salon-login-story::after { content: ''; position: absolute; width: 300px; height: 300px; border: 1px solid rgba(241, 222, 195, .22); border-radius: 50%; top: 42%; right: -24%; z-index: -1; }
    .salon-login-field { width: 100%; min-height: 52px; border: 1px solid #d5cbc3; border-radius: 13px; background: #fff; padding: 13px 16px; color: #252d29; font-size: 16px; transition: border-color .15s, box-shadow .15s; }
    .salon-login-field::placeholder { color: #847e78; }
    .salon-login-field:focus { border-color: #486b59; box-shadow: 0 0 0 4px rgba(72, 107, 89, .16); outline: none; }
    .salon-login-button { background: #294939; box-shadow: 0 12px 25px rgba(35, 65, 48, .2); }
    .salon-login-button:hover { background: #1f3b2d; transform: translateY(-1px); }
    .salon-login-button:focus-visible, .salon-login-link:focus-visible { outline: 3px solid #bc9978; outline-offset: 3px; }
    @media (prefers-reduced-motion: reduce) { .salon-login-button { transition: none !important; } }
</style>

<div class="company-login-shell flex min-h-[calc(100vh-12rem)] items-center justify-center py-5 sm:py-10">
    <div class="salon-login-stage grid w-full max-w-5xl overflow-hidden rounded-[2rem] lg:grid-cols-[.9fr_1.1fr]">
        <div class="salon-login-story flex flex-col justify-between px-7 py-8 text-[#f7f1e8] sm:px-10 sm:py-10 lg:min-h-[640px] lg:px-12 lg:py-12">
            <div class="relative h-[72px] w-[240px] max-w-full overflow-hidden rounded-2xl border border-white/20 bg-[#fffaf3]/95 shadow-lg shadow-black/10">
                <img src="{{ asset('images/brand/tocoluca-reserve-system-logo.png') }}"
                     alt="Tocoluca Reserve System"
                     class="absolute left-1/2 top-1/2 h-auto w-[220px] max-w-none -translate-x-1/2 -translate-y-1/2 object-contain"
                     width="1774"
                     height="887">
            </div>
            <p class="mt-5 text-sm font-semibold tracking-wide text-[#e8ded0] lg:hidden">サロンの日々を、心地よく整える。</p>
            <div class="hidden max-w-sm lg:block">
                <span class="inline-flex rounded-full border border-[#d9c7ae]/30 bg-white/5 px-4 py-1.5 text-[11px] font-semibold tracking-[.18em] text-[#ead6bd]">FOR SALON TEAMS</span>
                <p class="mt-6 font-serif text-3xl leading-[1.5] tracking-wide sm:text-4xl">サロンの日々を、<br>心地よく整える。</p>
                <p class="mt-5 text-sm leading-7 text-[#dfdfd5]">予約、スタッフ、店舗の情報へ。<br>いつもの業務を、ここから始めましょう。</p>
            </div>
            <div class="hidden items-center gap-3 border-t border-white/20 pt-5 text-xs tracking-wide text-[#d8d6c9] lg:flex"><span class="h-px w-8 bg-[#cab293]"></span>店舗運営のための管理画面</div>
        </div>

        <div class="flex items-center bg-[#fdfbf8] px-6 py-9 sm:px-10 sm:py-12 lg:px-14">
            <div class="w-full max-w-md mx-auto">
                <p class="text-xs font-bold tracking-[.2em] text-[#8a6b5d]">WELCOME BACK</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-[#24352e]">企業管理ログイン</h1>
                <p class="mt-3 text-sm leading-6 text-[#635e58]">企業コード、担当者コード、パスワードを入力してください。</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3" role="alert">
                        <div class="mb-1 text-sm font-bold text-red-800">入力内容をご確認ください</div>
                        <ul class="space-y-1 text-sm text-red-700">@foreach ($errors->all() as $error)<li>・{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('company.login.post') }}" class="mt-8 space-y-5">
                    @csrf
                    <div><label for="company_code" class="mb-2 block text-sm font-bold text-[#344138]">企業コード</label><input type="text" id="company_code" name="company_code" value="{{ old('company_code') }}" autocomplete="organization" required class="salon-login-field" placeholder="企業コードを入力" aria-describedby="company-code-help"><p id="company-code-help" class="mt-1.5 text-xs text-[#776f68]">契約時に発行されたコード</p></div>
                    <div><label for="staff_code" class="mb-2 block text-sm font-bold text-[#344138]">担当者コード</label><input type="text" id="staff_code" name="staff_code" value="{{ old('staff_code') }}" autocomplete="username" required class="salon-login-field" placeholder="担当者コードを入力"></div>
                    <div><label for="password" class="mb-2 block text-sm font-bold text-[#344138]">パスワード</label><input type="password" id="password" name="password" autocomplete="current-password" required class="salon-login-field" placeholder="パスワードを入力"></div>
                    <button type="submit" class="salon-login-button flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white transition"><span>ログイン</span><span aria-hidden="true">→</span></button>
                </form>

                <div class="mt-7 border-t border-[#e8dfd6] pt-5 text-center">
                    <button id="openMasterReset" type="button" onclick="openMasterResetModal()" class="salon-login-link text-sm font-bold text-[#345744] underline decoration-[#a8bda9] underline-offset-4 hover:text-[#1d392b]">マスター権限のパスワードを忘れた方</button>
                </div>
                <p class="mt-6 text-center text-xs leading-5 text-[#746d67]">ログインできない場合は、入力したコードとパスワードをお確かめください。</p>
            </div>
        </div>
    </div>

    <div id="masterResetModal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-[#17251e]/65 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="master-reset-title">
        <div class="w-full max-w-md overflow-hidden rounded-3xl border border-[#e7ded5] bg-[#fdfbf8] shadow-2xl">
            <div class="bg-[#294939] px-6 py-5 text-white">
                <div class="text-xs font-bold tracking-[.16em] text-white/75">ACCOUNT SUPPORT</div>
                <h2 id="master-reset-title" class="mt-2 text-xl font-black">マスター権限のパスワード初期化</h2>
                <p class="mt-2 text-sm leading-6 text-white/85">
                    この操作はマスター権限の担当者だけが対象です。
                </p>
            </div>

            <form method="POST" action="{{ route('company.master-password-reset') }}" class="p-6 space-y-5">
                @csrf

                <div class="rounded-2xl border border-[#e3d5bf] bg-[#f8f2e8] px-4 py-3 text-sm leading-6 text-[#554c3f]">
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
                           class="salon-login-field"
                           placeholder="企業コードを入力">
                </div>

                <div>
                    <label for="reset_email" class="block text-sm font-bold text-gray-700 mb-2">登録済みメールアドレス</label>
                    <input id="reset_email"
                           type="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           class="salon-login-field"
                           placeholder="企業情報に登録されているメールアドレス">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button"
                            onclick="closeMasterResetModal()"
                            class="rounded-xl border border-[#d5cbc3] bg-white px-4 py-3 text-sm font-bold text-[#344138] hover:bg-[#f5f0e9]">
                        閉じる
                    </button>
                    <button type="submit"
                            onclick="return confirm('企業コードと登録済みメールアドレスを確認し、マスター権限のパスワードを初期化しますか？')"
                            class="salon-login-button rounded-xl px-4 py-3 text-sm font-bold text-white transition">
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
    document.getElementById('openMasterReset').focus();
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeMasterResetModal();
    }
});
@if(old('email') || $errors->has('email'))
document.addEventListener('DOMContentLoaded', openMasterResetModal);
@endif
</script>
@endsection
