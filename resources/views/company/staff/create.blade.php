@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '15';
@endphp

<style>
form div:has(> input[name="retired_at"]) {
    display: none;
}

form section:has(input[name="retired_at"]) > div:first-child p {
    display: none;
}
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6 sm:mb-8">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative p-6 sm:p-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Staff Management</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">
                        担当者新規登録
                    </h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        新しい担当者を登録します。<br class="hidden sm:block">
                        基本情報・権限・予約公開設定をまとめて登録できます。
                    </p>
                </div>

                <div class="bg-white/15 rounded-2xl px-4 py-3 text-sm backdrop-blur-sm">
                    <div class="font-semibold">登録の流れ</div>
                    <div class="opacity-90 mt-1">入力 → 確認 → 保存</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('company.staff.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- エラー一覧 --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                <div class="text-red-700 font-semibold mb-2">入力内容をご確認ください</div>
                <ul class="text-sm text-red-600 space-y-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- 左カラム --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- 基本情報 --}}
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100"
                         style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                        <h2 class="text-lg font-bold text-gray-800">基本情報</h2>
                        <p class="text-sm text-gray-500 mt-1">担当者名やログイン情報を設定します。</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">担当者コード</label>
                            <input type="text"
                                   disabled
                                   placeholder="権限に応じて自動採番されます"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 bg-gray-50 text-gray-500">
                            <div class="mt-2 text-xs text-gray-500 leading-6">
                                一般スタッフ・リーダー・エリアリーダーは <span class="font-mono">0001</span> 形式、<br>
                                店舗運営は <span class="font-mono">SHOP01</span> 形式、マスターは <span class="font-mono">MASTER01</span> 形式で自動採番されます。
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">担当者名 <span class="text-red-500">*</span></label>
                                <input type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="例：田中 花子"
                                       class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                       style="--tw-ring-color: {{ $theme }}">
                                @error('name')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">初期パスワード <span class="text-red-500">*</span></label>
                                <input type="password"
                                       name="password"
                                       placeholder="8文字以上で入力"
                                       class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                       style="--tw-ring-color: {{ $theme }}">
                                @error('password')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 権限・予約設定 --}}
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100"
                         style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                        <h2 class="text-lg font-bold text-gray-800">権限・予約設定</h2>
                        <p class="text-sm text-gray-500 mt-1">権限、予約公開、指名料、表示順を設定します。</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">権限</label>
                            <select name="role"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none bg-white"
                                    style="--tw-ring-color: {{ $theme }}">
                                <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>一般スタッフ</option>
                                <option value="leader" {{ old('role') === 'leader' ? 'selected' : '' }}>リーダー</option>
                                <option value="area_leader" {{ old('role') === 'area_leader' ? 'selected' : '' }}>エリアリーダー</option>
                                <option value="store_operator" {{ old('role') === 'store_operator' ? 'selected' : '' }}>店舗運営</option>
                                <option value="master" {{ old('role') === 'master' ? 'selected' : '' }}>マスター</option>
                            </select>

                            @error('role')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror

                            @php
                                $selectedRole = old('role', 'staff');
                            @endphp

                            <div class="mt-3 rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-amber-800">
                                @if($selectedRole === 'master')
                                    マスターは <span class="font-mono">MASTER01</span> 形式で採番されます。
                                @elseif($selectedRole === 'store_operator')
                                    店舗運営は <span class="font-mono">SHOP01</span> 形式で採番されます。
                                @else
                                    この権限は <span class="font-mono">0001</span> 形式で採番されます。
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 p-4">
                            <label class="flex items-start gap-3">
                                <input type="checkbox"
                                       name="is_reservable"
                                       value="1"
                                       class="mt-1 w-5 h-5 rounded"
                                       {{ old('is_reservable', '1') ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold text-gray-800">予約受付対象にする</span>
                                    <span class="block text-sm text-gray-500 mt-1">
                                        ONにすると公開予約画面で担当者として選択可能になります。
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">指名料</label>
                                <div class="relative">
                                    <input type="number"
                                           name="nomination_fee"
                                           min="0"
                                           value="{{ old('nomination_fee', 0) }}"
                                           class="w-full border border-gray-200 rounded-2xl px-4 py-3 pr-12 text-base focus:ring-2 focus:outline-none"
                                           style="--tw-ring-color: {{ $theme }}">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">円</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">0円なら指名料なし</p>
                                @error('nomination_fee')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">表示順</label>
                                <input type="number"
                                       name="priority_order"
                                       min="0"
                                       value="{{ old('priority_order', 0) }}"
                                       class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                       style="--tw-ring-color: {{ $theme }}">
                                <p class="text-xs text-gray-400 mt-2">数字が小さいほど上に表示されます</p>
                                @error('priority_order')
                                    <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 在籍・画像 --}}
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100"
                         style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                        <h2 class="text-lg font-bold text-gray-800">在籍・画像設定</h2>
                        <p class="text-sm text-gray-500 mt-1">退職予定日やプロフィール画像を設定します。</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">退職日</label>
                            <input type="date"
                                   name="retired_at"
                                   value="{{ old('retired_at') }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                   style="--tw-ring-color: {{ $theme }}">
                            <p class="text-xs text-gray-400 mt-2">
                                入力した日以降は予約対象から外す想定です。未定なら空欄で大丈夫です。
                            </p>
                            @error('retired_at')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">画像</label>
                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="block w-full rounded-2xl border border-gray-200 bg-gray-50 p-2 text-sm text-gray-700 shadow-sm
                                          file:mr-4 file:rounded-xl file:border-0 file:bg-stone-900
                                          file:px-5 file:py-3
                                          file:text-sm file:font-bold
                                          file:text-white
                                          hover:file:opacity-90"
                                   style="color-scheme: light; --tw-ring-color: {{ $theme }}; --file-bg: {{ $theme }};">
                            <p class="text-xs text-gray-400 mt-2">
                                担当者一覧や予約画面で使用するプロフィール画像です。
                            </p>
                            @error('image')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- 右カラム --}}
            <div class="space-y-6">
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-800">入力のポイント</h2>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 leading-6">
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <div class="font-semibold text-gray-800 mb-1">権限</div>
                            店舗運営やマスターは管理機能の利用範囲に影響します。
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <div class="font-semibold text-gray-800 mb-1">予約受付対象</div>
                            公開予約に出したくない場合はOFFにします。
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <div class="font-semibold text-gray-800 mb-1">表示順</div>
                            小さい番号ほど先頭に表示され、見つけてもらいやすくなります。
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-800">操作</h2>
                    <div class="mt-4 space-y-3">
                        <button type="submit"
                                class="w-full text-white px-6 py-3.5 rounded-2xl shadow-lg font-semibold hover:opacity-90 transition"
                                style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                            登録する
                        </button>

                        <a href="{{ route('company.staff.index') }}"
                           class="block w-full text-center px-6 py-3.5 rounded-2xl border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition">
                            一覧へ戻る
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>

@endsection
