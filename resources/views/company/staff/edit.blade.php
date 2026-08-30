@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '15';
    $current = auth()->guard('company')->user();
    $canDelete = $current->isMaster() && (int) $staff->id !== (int) $current->id;
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6 sm:mb-8">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative p-6 sm:p-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Staff Management</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">担当者編集</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2">
                        {{ $staff->name }}（{{ $staff->staff_code }}）
                    </p>
                </div>

                <div class="bg-white/15 rounded-2xl px-4 py-3 text-sm backdrop-blur-sm">
                    <div class="font-semibold">現在の状態</div>
                    <div class="opacity-90 mt-1">
                        {{ $staff->is_reservable ? '予約受付中' : '予約受付対象外' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company._staff_menu_nav', [
            'currentStep' => 'staff',
        ])
    </div>

    <div class="sticky top-24 z-30 mb-6 rounded-[1.75rem] border border-white/80 bg-white/90 p-4 shadow-lg backdrop-blur">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="rounded-2xl bg-stone-50 border border-stone-100 px-4 py-3">
                <div class="text-xs font-bold text-stone-500">担当者</div>
                <div class="mt-1 text-sm font-bold text-stone-900 truncate">{{ $staff->name }}</div>
            </div>
            <div class="rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3">
                <div class="text-xs font-bold text-blue-700">コード</div>
                <div class="mt-1 text-sm font-bold text-blue-900">{{ $staff->staff_code }}</div>
            </div>
            <div class="rounded-2xl {{ $staff->is_reservable ? 'bg-emerald-50 border-emerald-100' : 'bg-gray-50 border-gray-100' }} border px-4 py-3">
                <div class="text-xs font-bold {{ $staff->is_reservable ? 'text-emerald-700' : 'text-gray-500' }}">予約受付</div>
                <div class="mt-1 text-sm font-bold text-gray-900">{{ $staff->is_reservable ? '受付中' : '停止中' }}</div>
            </div>
            <div class="rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3">
                <div class="text-xs font-bold text-amber-700">権限</div>
                <div class="mt-1 text-sm font-bold text-amber-900">{{ $staff->role }}</div>
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('company.staff.update', $staff->id) }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PUT')

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

            {{-- 左 --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- 基本情報 --}}
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100"
                         style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                        <h2 class="text-lg font-bold text-gray-800">基本情報</h2>
                        <p class="text-sm text-gray-500 mt-1">担当者の基本プロフィールを編集します。</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">担当者コード</label>
                            <input type="text"
                                   value="{{ $staff->staff_code }}"
                                   disabled
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 bg-gray-50 text-gray-600 font-mono">
                            <p class="text-xs text-gray-400 mt-2">
                                担当者コードは自動採番のため変更できません。
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">担当者名</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $staff->name) }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                   style="--tw-ring-color: {{ $theme }}">
                            @error('name')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- 権限・予約設定 --}}
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100"
                         style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                        <h2 class="text-lg font-bold text-gray-800">権限・予約設定</h2>
                        <p class="text-sm text-gray-500 mt-1">権限、公開状態、指名料、表示順を調整します。</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">権限</label>
                            <select name="role"
                                    class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none bg-white"
                                    style="--tw-ring-color: {{ $theme }}">
                                <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>一般スタッフ</option>
                                <option value="leader" {{ old('role', $staff->role) === 'leader' ? 'selected' : '' }}>リーダー</option>
                                <option value="area_leader" {{ old('role', $staff->role) === 'area_leader' ? 'selected' : '' }}>エリアリーダー</option>
                                <option value="store_operator" {{ old('role', $staff->role) === 'store_operator' ? 'selected' : '' }}>店舗運営</option>
                                <option value="master" {{ old('role', $staff->role) === 'master' ? 'selected' : '' }}>マスター</option>
                            </select>

                            @error('role')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror

                            @if(in_array(old('role', $staff->role), ['master', 'store_operator']))
                                <div class="mt-3 rounded-2xl bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-amber-800">
                                    {{ old('role', $staff->role) === 'master' ? 'マスターは MASTER01 形式で採番されます。' : '店舗運営は SHOP01 形式で採番されます。' }}
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-gray-200 p-4">
                            <label class="flex items-start gap-3">
                                <input type="checkbox"
                                       name="is_reservable"
                                       value="1"
                                       class="mt-1 w-5 h-5 rounded"
                                       {{ old('is_reservable', $staff->is_reservable) ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold text-gray-800">予約受付対象にする</span>
                                    <span class="block text-sm text-gray-500 mt-1">
                                        OFFにすると公開予約画面では担当者として表示されません。
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
                                           value="{{ old('nomination_fee', $staff->nomination_fee ?? 0) }}"
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
                                       value="{{ old('priority_order', $staff->priority_order) }}"
                                       class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                       style="--tw-ring-color: {{ $theme }}">
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
                        <p class="text-sm text-gray-500 mt-1">退職日やプロフィール画像を管理します。</p>
                    </div>

                    <div class="p-5 sm:p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">退職日</label>
                            <input type="date"
                                   name="retired_at"
                                   value="{{ old('retired_at', optional($staff->retired_at)->format('Y-m-d')) }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-base focus:ring-2 focus:outline-none"
                                   style="--tw-ring-color: {{ $theme }}">
                            <p class="text-xs text-gray-400 mt-2">
                                この日以降は予約対象から外れ、ログイン停止対象にする運用がおすすめです。
                            </p>
                            @error('retired_at')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">画像</label>

                            @if(!empty($staff->image_path))
                                <div class="mb-4 flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                    <img src="{{ asset($staff->image_path) }}"
                                         alt="{{ $staff->name }}"
                                         class="w-20 h-20 rounded-2xl object-cover border border-gray-200">
                                    <div>
                                        <div class="font-semibold text-gray-800">現在の画像</div>
                                        <div class="text-sm text-gray-500 mt-1">新しい画像を選ぶと上書きされます。</div>
                                    </div>
                                </div>
                            @endif

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
                            @error('image')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- 右 --}}
            <div class="space-y-6">
                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-800">現在の情報</h2>

                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <div class="rounded-2xl bg-gray-50 p-4">
                            <div class="text-xs text-gray-500">担当者コード</div>
                            <div class="font-semibold text-gray-800 mt-1">{{ $staff->staff_code }}</div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 p-4">
                            <div class="text-xs text-gray-500">権限</div>
                            <div class="font-semibold text-gray-800 mt-1">{{ $staff->role }}</div>
                        </div>

                        <div class="rounded-2xl bg-gray-50 p-4">
                            <div class="text-xs text-gray-500">予約公開</div>
                            <div class="font-semibold text-gray-800 mt-1">
                                {{ $staff->is_reservable ? '公開中' : '非公開' }}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-gray-800">操作</h2>
                    <div class="mt-4 space-y-3">
                        <button type="submit"
                                class="w-full text-white px-6 py-3.5 rounded-2xl shadow-lg font-semibold hover:opacity-90 transition"
                                style="background: var(--company-theme-gradient);">
                            更新する
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
