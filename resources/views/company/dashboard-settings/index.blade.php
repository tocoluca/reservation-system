@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
    $themeSoft = $theme . '15';

    $permissionGroups = [
        'management' => [
            'label' => '管理・分析',
            'description' => '管理画面と売上情報へのアクセス',
            'icon' => 'shield-check',
            'keys' => ['dashboard.manage', 'dashboard.sales'],
        ],
        'reservation' => [
            'label' => '予約・顧客',
            'description' => '予約受付、顧客、口コミに関する機能',
            'icon' => 'calendar-check',
            'keys' => ['card.reserve', 'card.business_calendar', 'card.customers', 'card.reservation_change_notices', 'card.reviews'],
        ],
        'staffing' => [
            'label' => 'スタッフ・勤務',
            'description' => 'スタッフ、シフト、休暇に関する機能',
            'icon' => 'users',
            'keys' => ['card.staff', 'card.month_shift', 'card.month_shift_view', 'card.shift_patterns', 'card.default_shift', 'card.vacation', 'card.my_profile'],
        ],
        'content' => [
            'label' => 'メニュー・情報発信',
            'description' => 'メニュー、投稿、お知らせ、サポート',
            'icon' => 'layout-grid',
            'keys' => ['card.menu_category_tag', 'card.menu', 'card.menu_staff', 'card.style', 'card.notices', 'card.support'],
        ],
        'store' => [
            'label' => '店舗設定・契約',
            'description' => '企業情報、ブランド、契約の設定',
            'icon' => 'store',
            'keys' => ['card.company_info', 'card.logo', 'card.theme', 'card.billing'],
        ],
    ];

    $permissionDescriptions = [
        'dashboard.manage' => '役職別の表示権限を変更できます',
        'dashboard.sales' => '売上集計と分析画面を表示します',
        'card.reserve' => '予約カレンダーと予約一覧を表示します',
        'card.business_calendar' => '営業日・休業日の設定を表示します',
        'card.customers' => '顧客情報と来店履歴を表示します',
        'card.reservation_change_notices' => '予約変更の連絡一覧を表示します',
        'card.reviews' => '口コミの確認と管理を表示します',
        'card.staff' => '担当者の登録・編集を表示します',
        'card.month_shift' => '月間シフトの作成機能を表示します',
        'card.month_shift_view' => 'スタッフ別シフト表を表示します',
        'card.shift_patterns' => 'シフトパターン設定を表示します',
        'card.default_shift' => '基本シフト設定を表示します',
        'card.vacation' => '休暇申請・管理を表示します',
        'card.my_profile' => '本人のプロフィール設定を表示します',
        'card.menu_category_tag' => 'カテゴリーとタグの管理を表示します',
        'card.menu' => '施術メニューの管理を表示します',
        'card.menu_staff' => 'メニューごとの対応スタッフ設定を表示します',
        'card.style' => '最新スタイルの投稿機能を表示します',
        'card.notices' => 'お知らせの作成・管理を表示します',
        'card.support' => 'よくある質問と問い合わせを表示します',
        'card.company_info' => '企業情報と予約条件の設定を表示します',
        'card.logo' => '企業ロゴの設定を表示します',
        'card.theme' => 'テーマカラー設定を表示します',
        'card.billing' => '契約プランと請求情報を表示します',
    ];

    $lockedPermissions = [
        'master' => ['dashboard.manage' => '常に有効'],
        'store_operator' => [
            'card.menu_staff' => '役職の仕様により対象外',
            'card.shift_patterns' => '役職の仕様により対象外',
            'card.default_shift' => '役職の仕様により対象外',
            'card.vacation' => '役職の仕様により対象外',
            'card.my_profile' => '役職の仕様により対象外',
        ],
    ];
@endphp

<style>
    .permission-checkbox { accent-color: {{ $theme }}; }
    .permission-table tbody tr[data-permission-row]:has(.permission-checkbox:focus-visible) {
        outline: 2px solid {{ $theme }};
        outline-offset: -2px;
    }
    @media (max-width: 1023px) {
        .permission-table { min-width: 520px; }
        .permission-table .role-column { display: none; }
        .permission-table .role-column.is-mobile-active { display: table-cell; }
    }
</style>

<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background: radial-gradient(circle at top right, #ffffff 0%, transparent 35%), radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);"></div>

        <div class="relative px-6 py-7 text-white sm:px-8 sm:py-8"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest opacity-80 sm:text-sm">Dashboard Settings</p>
                    <h1 class="mt-1 text-2xl font-bold sm:text-3xl">ダッシュボード管理</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 opacity-90 sm:text-base">
                        役職ごとに表示する機能を選び、必要な情報だけが届くダッシュボードを設定します。
                    </p>
                </div>

                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-white/15 px-4 py-3 text-sm font-medium backdrop-blur-sm transition hover:bg-white/20">
                    <i data-lucide="arrow-left" class="mr-2 h-4 w-4"></i>
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    </div>

    {{-- 概要 --}}
    <section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex max-w-2xl gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-white shadow-sm" style="background: {{ $theme }};">
                    <i data-lucide="shield-check" class="h-5 w-5"></i>
                </div>
                <div>
                    <h2 class="font-bold text-stone-900">役職ごとの表示範囲を設定</h2>
                    <p class="mt-1 text-sm leading-6 text-stone-600">
                        チェックを入れた機能だけが対象役職のダッシュボードに表示されます。マスターの管理権限など、一部の項目は安全のため固定されています。
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                @foreach($roleSettings as $role => $setting)
                    @php
                        $enabledCount = collect($setting['permissions'])->filter()->count();
                    @endphp
                    <button type="button" data-role-summary="{{ $role }}"
                            class="role-selector rounded-2xl border border-stone-200 bg-stone-50 px-3 py-3 text-left transition hover:border-stone-300 hover:bg-white lg:cursor-default">
                        <span class="block text-xs font-bold text-stone-800">{{ $setting['role_label'] }}</span>
                        <span class="mt-1 block text-[11px] text-stone-500"><strong data-role-count="{{ $role }}" class="text-sm text-stone-800">{{ $enabledCount }}</strong> 項目を表示</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <form id="dashboardPermissionForm" method="POST" action="{{ route('company.dashboard-settings.update') }}">
        @csrf

        <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
            {{-- 操作バー --}}
            <div class="sticky top-[var(--company-topbar-height)] z-30 border-b border-stone-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <label for="permissionSearch" class="sr-only">権限項目を検索</label>
                        <div class="relative max-w-xl">
                            <i data-lucide="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400"></i>
                            <input id="permissionSearch" type="search" placeholder="機能名や説明から検索"
                                   class="h-11 w-full rounded-2xl border border-stone-300 bg-stone-50 pl-10 pr-10 text-sm outline-none transition focus:bg-white focus:ring-2"
                                   style="--tw-ring-color: {{ $theme }}44;">
                            <button id="clearPermissionSearch" type="button" aria-label="検索をクリア"
                                    class="absolute right-2 top-1/2 hidden -translate-y-1/2 rounded-lg p-2 text-stone-400 hover:bg-stone-100 hover:text-stone-700">
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 xl:justify-end">
                        <span id="unsavedPermissionNotice" class="hidden text-xs font-bold text-amber-700" role="status" aria-live="polite">● 未保存の変更あり</span>
                        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90 sm:flex-none" style="background: {{ $theme }};">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            変更を保存
                        </button>
                    </div>
                </div>

                {{-- モバイル役職切替 --}}
                <div class="mt-4 lg:hidden">
                    <p class="mb-2 text-xs font-bold text-stone-500">表示する役職</p>
                    <div class="flex gap-2 overflow-x-auto pb-1" aria-label="役職を切り替え">
                        @foreach($roleSettings as $role => $setting)
                            <button type="button" data-mobile-role="{{ $role }}"
                                    class="mobile-role-button shrink-0 rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-xs font-bold text-stone-600">
                                {{ $setting['role_label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="permission-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-stone-600">
                            <th class="sticky left-0 z-20 min-w-[300px] border-r border-stone-200 bg-stone-50 p-4 text-left font-semibold">
                                機能・表示項目
                            </th>
                            @foreach($roleSettings as $role => $setting)
                                <th data-role-column="{{ $role }}" class="role-column min-w-[140px] p-4 text-center font-semibold">
                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-bold" style="background-color: {{ $themeSoft }}; color: {{ $theme }};">
                                        {{ $setting['role_label'] }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($permissionGroups as $groupKey => $group)
                            <tr data-group-header="{{ $groupKey }}">
                                <td data-group-heading-cell colspan="{{ 1 + count($roleSettings) }}" class="border-b border-stone-200 bg-stone-100 px-4 py-3 text-stone-700">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-stone-600 shadow-sm">
                                            <i data-lucide="{{ $group['icon'] }}" class="h-4 w-4"></i>
                                        </span>
                                        <div>
                                            <div class="font-bold">{{ $group['label'] }}</div>
                                            <div class="mt-0.5 text-xs font-normal text-stone-500">{{ $group['description'] }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            @foreach($group['keys'] as $permissionKey)
                                @php
                                    $permissionLabel = $permissionLabels[$permissionKey] ?? $permissionKey;
                                @endphp
                                <tr data-permission-row data-group="{{ $groupKey }}"
                                    data-search-text="{{ mb_strtolower($permissionLabel.' '.($permissionDescriptions[$permissionKey] ?? '').' '.$permissionKey) }}"
                                    class="border-b border-stone-100 transition hover:bg-stone-50/70">
                                    <td class="sticky left-0 z-10 border-r border-stone-100 bg-white p-4 align-middle">
                                        <div class="font-semibold text-stone-800">{{ $permissionLabel }}</div>
                                        <div class="mt-1 text-xs leading-5 text-stone-500">{{ $permissionDescriptions[$permissionKey] ?? '' }}</div>
                                    </td>

                                    @foreach($roleSettings as $role => $setting)
                                        @php
                                            $checked = $setting['permissions'][$permissionKey] ?? false;
                                            $lockLabel = $lockedPermissions[$role][$permissionKey] ?? null;
                                            $lockedValue = $role === 'master' && $permissionKey === 'dashboard.manage' ? 1 : 0;
                                        @endphp
                                        <td data-role-column="{{ $role }}" class="role-column p-4 text-center align-middle">
                                            @if($lockLabel)
                                                <input type="hidden" name="permissions[{{ $role }}][{{ $permissionKey }}]" value="{{ $lockedValue }}">
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold {{ $lockedValue ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                                    {{ $lockLabel }}
                                                </span>
                                            @else
                                                <input type="hidden" name="permissions[{{ $role }}][{{ $permissionKey }}]" value="0">
                                                <label class="inline-flex cursor-pointer items-center justify-center rounded-xl p-2 transition hover:bg-stone-100" aria-label="{{ $setting['role_label'] }}：{{ $permissionLabel }}">
                                                    <input type="checkbox"
                                                           name="permissions[{{ $role }}][{{ $permissionKey }}]"
                                                           value="1"
                                                           aria-label="{{ $setting['role_label'] }}：{{ $permissionLabel }}"
                                                           data-permission-checkbox
                                                           data-role="{{ $role }}"
                                                           class="permission-checkbox h-5 w-5 rounded border-stone-300"
                                                           @checked($checked)>
                                                </label>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="permissionEmptyState" class="hidden px-6 py-14 text-center">
                <i data-lucide="search-x" class="mx-auto h-8 w-8 text-stone-300"></i>
                <p class="mt-3 font-bold text-stone-700">該当する項目がありません</p>
                <p class="mt-1 text-sm text-stone-500">検索条件を変更してください。</p>
            </div>

            <div class="flex flex-col gap-4 border-t border-stone-100 bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-sm leading-6 text-stone-500">保存後、各役職の次回表示時から設定が反映されます。</p>
                <button type="submit" class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-2xl px-6 py-3 font-bold text-white shadow transition hover:opacity-90 sm:w-auto" style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    変更を保存
                </button>
            </div>
        </section>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('dashboardPermissionForm')
    const search = document.getElementById('permissionSearch')
    const clearSearch = document.getElementById('clearPermissionSearch')
    const emptyState = document.getElementById('permissionEmptyState')
    const table = document.querySelector('.permission-table')
    const unsavedNotice = document.getElementById('unsavedPermissionNotice')
    const roleButtons = Array.from(document.querySelectorAll('[data-mobile-role]'))
    const summaryButtons = Array.from(document.querySelectorAll('[data-role-summary]'))
    const rows = Array.from(document.querySelectorAll('[data-permission-row]'))
    const groupHeaders = Array.from(document.querySelectorAll('[data-group-header]'))
    let isDirty = false
    let activeRole = roleButtons[0] ? roleButtons[0].dataset.mobileRole : null

    function updateGroupColspan() {
        const colspan = window.innerWidth < 1024 ? 2 : {{ 1 + count($roleSettings) }}
        document.querySelectorAll('[data-group-heading-cell]').forEach(function (cell) {
            cell.colSpan = colspan
        })
    }

    function setActiveRole(role) {
        activeRole = role
        document.querySelectorAll('[data-role-column]').forEach(function (column) {
            column.classList.toggle('is-mobile-active', column.dataset.roleColumn === role)
        })
        roleButtons.forEach(function (button) {
            const active = button.dataset.mobileRole === role
            button.classList.toggle('text-white', active)
            button.classList.toggle('border-transparent', active)
            button.classList.toggle('bg-stone-50', !active)
            button.style.background = active ? @json($theme) : ''
            button.style.color = active ? '#ffffff' : ''
            button.setAttribute('aria-pressed', active ? 'true' : 'false')
        })
    }

    function updateCounts() {
        document.querySelectorAll('[data-role-count]').forEach(function (node) {
            const role = node.dataset.roleCount
            node.textContent = document.querySelectorAll(`[data-permission-checkbox][data-role="${role}"]:checked`).length
                + document.querySelectorAll(`input[type="hidden"][name^="permissions[${role}]"][value="1"]`).length
        })
    }

    function markDirty() {
        if (isDirty) return
        isDirty = true
        unsavedNotice.classList.remove('hidden')
    }

    function filterRows() {
        const query = search.value.trim().toLocaleLowerCase('ja')
        let visibleCount = 0

        rows.forEach(function (row) {
            const visible = !query || row.dataset.searchText.includes(query)
            row.classList.toggle('hidden', !visible)
            if (visible) visibleCount++
        })

        groupHeaders.forEach(function (header) {
            const group = header.dataset.groupHeader
            const hasVisibleRow = rows.some(function (row) {
                return row.dataset.group === group && !row.classList.contains('hidden')
            })
            header.classList.toggle('hidden', !hasVisibleRow)
        })

        table.classList.toggle('hidden', visibleCount === 0)
        emptyState.classList.toggle('hidden', visibleCount !== 0)
        clearSearch.classList.toggle('hidden', !query)
    }

    roleButtons.forEach(function (button) {
        button.addEventListener('click', function () { setActiveRole(button.dataset.mobileRole) })
    })
    summaryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (window.innerWidth >= 1024) return
            setActiveRole(button.dataset.roleSummary)
            document.querySelector('.permission-table').scrollIntoView({ behavior: 'smooth', block: 'start' })
        })
    })

    search.addEventListener('input', filterRows)
    clearSearch.addEventListener('click', function () {
        search.value = ''
        filterRows()
        search.focus()
    })
    form.addEventListener('change', function () {
        markDirty()
        updateCounts()
    })
    form.addEventListener('submit', function () { isDirty = false })
    window.addEventListener('beforeunload', function (event) {
        if (!isDirty) return
        event.preventDefault()
        event.returnValue = ''
    })
    window.addEventListener('resize', updateGroupColspan, { passive: true })

    setActiveRole(activeRole)
    updateCounts()
    updateGroupColspan()
})
</script>
@endsection
