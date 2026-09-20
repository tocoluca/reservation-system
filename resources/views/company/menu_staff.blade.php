@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
$themeSoft = $theme . '15';
$menuGroups = $menus->groupBy(fn ($menu) => $menu->menu_category_id
    ? 'category-'.$menu->menu_category_id
    : 'uncategorized');
@endphp

<style>
    .menu-staff-matrix {
        border-collapse: separate;
        border-spacing: 0;
    }

    .menu-staff-matrix th,
    .menu-staff-matrix td {
        border-right: 1px solid #d6d3d1;
    }

    .menu-staff-matrix th:last-child,
    .menu-staff-matrix td:last-child {
        border-right: 0;
    }

    .menu-staff-matrix-head th {
        border-bottom: 3px solid #78716c;
    }

    .menu-staff-category-row td {
        border-top: 2px solid #a8a29e;
        border-bottom: 2px solid #d6d3d1;
        background: #e7e5e4;
    }

    .menu-staff-category-row:first-child td {
        border-top: 0;
    }

    .menu-staff-menu-row td {
        border-bottom: 1px solid #d6d3d1;
        background: #ffffff;
    }

    .menu-staff-menu-row.is-alternate td {
        background: #fafaf9;
    }

    .menu-staff-menu-row:hover td {
        background: #fef3c7;
    }

    .menu-staff-bulk-control {
        min-width: 96px;
        border: 2px solid #d6d3d1;
        background: #ffffff;
    }

    .menu-staff-bulk-control:has(input:checked) {
        border-color: var(--matrix-theme);
        background: var(--matrix-theme-soft);
        color: #292524;
    }

    .menu-staff-bulk-control:has(input:indeterminate) {
        border-color: #f59e0b;
        background: #fffbeb;
    }

    .menu-staff-matrix input[type="checkbox"] {
        accent-color: var(--matrix-theme);
    }

    .menu-staff-relation-checkbox {
        width: 1.5rem;
        height: 1.5rem;
        cursor: pointer;
    }

    .menu-staff-relation-cell:hover {
        box-shadow: inset 0 0 0 2px var(--matrix-theme);
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: var(--company-theme-gradient);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Menu Staff Settings</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">メニュー対応スタッフ設定</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        各メニューを担当できるスタッフを設定し、予約時の候補表示に反映します。
                    </p>
                </div>

                <div>
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                       ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        @include('company.menu._setup_nav', [
            'currentStep' => 3,
            'links' => [
                ['label' => 'メニュー管理へ', 'route' => 'company.menu.index', 'icon' => 'arrow-left'],
            ],
        ])
    </div>

    <div class="mb-6">
        @include('company._staff_menu_nav', [
            'currentStep' => 'menu_staff',
        ])
    </div>

    {{-- ガイド --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">設定のポイント</h2>
                <p class="text-sm text-gray-500 mt-1">
                    担当者ごとに設定します。カテゴリー行の「一括」で、その担当者にカテゴリー内の全メニューをまとめて設定できます。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">カテゴリー × 担当者で一括</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">メニューごとに個別設定</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('company.menu-staff.update') }}">
        @csrf

        <div class="bg-white shadow-sm rounded-3xl border border-gray-100 overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-100"
                 style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">対応関係一覧</h2>
                        <p class="text-sm text-gray-500 mt-1">縦に担当者、横にメニューを確認します。カテゴリー行では担当者ごとに一括設定できます。</p>
                    </div>
                </div>
            </div>

            <div class="relative z-0 max-h-[72vh] overflow-auto">
                <table class="menu-staff-matrix min-w-full text-sm"
                       style="--matrix-theme: {{ $theme }}; --matrix-theme-soft: {{ $themeSoft }};">
                    <thead class="menu-staff-matrix-head" style="background: {{ $theme }}; color:white">
                        <tr>
                            <th class="p-4 text-left sticky top-0 left-0 bg-stone-100 text-stone-900 z-30 shadow-sm min-w-[260px]">
                                <span class="block text-xs font-bold text-stone-500">設定対象</span>
                                <span class="mt-1 block font-black">カテゴリー / メニュー</span>
                            </th>

                            @foreach($staffs as $staff)
                                <th class="p-4 text-center sticky top-0 z-20 shadow-sm min-w-[140px]"
                                    style="background: {{ $theme }}; color:white;">
                                    <span class="block text-[10px] font-bold tracking-wider text-white/70">担当者</span>
                                    <span class="mt-1 block font-black">{{ $staff->name }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($menuGroups as $categoryKey => $categoryMenus)
                            @php
                                $category = $categoryMenus->first()->category;
                                $categoryName = $category?->name ?? 'カテゴリー未設定';
                            @endphp

                            <tr class="menu-staff-category-row">
                                <td class="sticky left-0 z-10 p-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-black text-white"
                                              style="background: {{ $theme }};">C</span>
                                        <span>
                                            <span class="block font-black text-stone-900">{{ $categoryName }}</span>
                                            <span class="mt-1 block text-xs font-bold text-stone-600">
                                                {{ $categoryMenus->count() }}メニュー
                                            </span>
                                        </span>
                                    </div>
                                </td>

                                @foreach($staffs as $staff)
                                    <td class="p-2 text-center">
                                        <label class="menu-staff-bulk-control mx-auto inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2 text-[11px] font-black text-stone-700 transition"
                                               title="{{ $categoryName }}の全メニューを{{ $staff->name }}に一括設定">
                                            <input type="checkbox"
                                                   class="category-staff-toggle h-5 w-5 rounded"
                                                   data-category="{{ $categoryKey }}"
                                                   data-staff="{{ $staff->id }}"
                                                   aria-label="{{ $staff->name }}：{{ $categoryName }}の全メニューを一括設定"
                                                   onchange="toggleCategoryStaff(this)">
                                            <span>
                                                <span class="block">全メニュー</span>
                                                <span class="block text-[10px] font-medium text-stone-500">一括設定</span>
                                            </span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>

                            @foreach($categoryMenus as $menu)
                                <tr class="menu-staff-menu-row {{ $loop->even ? 'is-alternate' : '' }} transition-colors">
                                    <td class="sticky left-0 z-10 p-4 font-semibold shadow-sm">
                                        <div class="flex items-start gap-3 pl-3">
                                            <span class="mt-2 h-2 w-2 shrink-0 rounded-full" style="background: {{ $theme }};"></span>
                                            <div>
                                            <div class="font-bold leading-6 text-stone-900">{{ $menu->name }}</div>
                                            <div class="mt-1 text-xs text-stone-500">
                                                <span class="rounded-full bg-stone-100 px-2 py-0.5">{{ $categoryName }}</span>
                                            </div>
                                            </div>
                                        </div>
                                    </td>

                                    @foreach($staffs as $staff)
                                        @php
                                            $checked = $relations
                                                ->where('menu_id', $menu->id)
                                                ->where('staff_id', $staff->id)
                                                ->isNotEmpty();
                                        @endphp

                                        <td class="menu-staff-relation-cell p-4 text-center transition-shadow">
                                            <input type="checkbox"
                                                   name="relations[{{ $menu->id }}][]"
                                                   value="{{ $staff->id }}"
                                                   class="relation-checkbox menu-staff-relation-checkbox rounded"
                                                   data-category="{{ $categoryKey }}"
                                                   data-menu="{{ $menu->id }}"
                                                   data-staff="{{ $staff->id }}"
                                                   aria-label="{{ $staff->name }}：{{ $menu->name }}に対応"
                                                   @checked($checked)>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 border-t border-gray-100 bg-white flex justify-end">
                <button
                    type="submit"
                    class="text-white px-8 py-3 rounded-2xl shadow-lg hover:opacity-90 transition"
                    style="background: var(--company-theme-gradient);">
                    保存する
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function relationBoxes(filter = () => true) {
    return [...document.querySelectorAll('.relation-checkbox')].filter(filter)
}

function applyCheckedState(boxes, checked) {
    boxes.forEach(box => {
        box.checked = checked
    })
}

function syncToggle(toggle, boxes) {
    const checkedCount = boxes.filter(box => box.checked).length

    toggle.checked = boxes.length > 0 && checkedCount === boxes.length
    toggle.indeterminate = checkedCount > 0 && checkedCount < boxes.length
}

function syncBulkToggles() {
    document.querySelectorAll('.category-staff-toggle').forEach(toggle => {
        syncToggle(toggle, relationBoxes(box =>
            box.dataset.category === toggle.dataset.category
            && box.dataset.staff === toggle.dataset.staff
        ))
    })
}

function toggleCategoryStaff(el) {
    applyCheckedState(
        relationBoxes(box =>
            box.dataset.category === el.dataset.category
            && box.dataset.staff === el.dataset.staff
        ),
        el.checked
    )
    syncBulkToggles()
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.relation-checkbox').forEach(box => {
        box.addEventListener('change', syncBulkToggles)
    })

    syncBulkToggles()
})
</script>

@endsection
