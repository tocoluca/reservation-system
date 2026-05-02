@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
$themeSoft = $theme . '15';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg mb-6">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
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
                    行単位・列単位・全体でまとめてチェックできるので、大量の設定も効率よく行えます。
                </p>
            </div>

            <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">全体一括</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">スタッフ列一括</span>
                <span class="inline-flex items-center rounded-full bg-stone-100 px-3 py-1 text-stone-700">メニュー行一括</span>
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
                        <p class="text-sm text-gray-500 mt-1">チェックが入っているスタッフが、そのメニューを担当できます。</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            onclick="toggleAll()"
                            class="px-4 py-2.5 text-sm rounded-2xl text-white shadow hover:opacity-90 transition"
                            style="background: {{ $theme }}">
                            全チェック切替
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-h-[72vh] overflow-auto">
                <table class="min-w-full text-sm">
                    <thead style="background: {{ $theme }}; color:white">
                        <tr>
                            <th class="p-4 text-left sticky top-0 left-0 bg-white text-stone-800 z-40 border-r border-b border-stone-200 shadow-sm min-w-[220px]">
                                メニュー
                            </th>

                            @foreach($staffs as $staff)
                                <th class="p-4 text-center sticky top-0 z-30 border-b border-white/30 shadow-sm min-w-[120px]"
                                    style="background: {{ $theme }}; color:white;">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="font-semibold">{{ $staff->name }}</span>

                                        <label class="inline-flex items-center gap-2 text-xs text-white/90">
                                            <input
                                                type="checkbox"
                                                class="staff-toggle mt-0.5"
                                                data-staff="{{ $staff->id }}"
                                                onclick="toggleStaff(this)">
                                            一括
                                        </label>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-200">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="p-4 font-semibold sticky left-0 bg-white z-20 border-r border-stone-200 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            class="menu-toggle"
                                            data-menu="{{ $menu->id }}"
                                            onclick="toggleMenu(this)"
                                        >

                                        <div>
                                            <div class="font-semibold text-stone-800">{{ $menu->name }}</div>
                                            <div class="text-xs text-stone-400 mt-1">この行をまとめて切替</div>
                                        </div>
                                    </div>
                                </td>

                                @foreach($staffs as $staff)
                                    @php
                                        $checked = $relations
                                            ->where('menu_id',$menu->id)
                                            ->where('staff_id',$staff->id)
                                            ->count();
                                    @endphp

                                    <td class="p-4 text-center">
                                        <input
                                            type="checkbox"
                                            name="relations[{{ $menu->id }}][]"
                                            value="{{ $staff->id }}"
                                            class="relation-checkbox w-5 h-5 rounded"
                                            data-menu="{{ $menu->id }}"
                                            data-staff="{{ $staff->id }}"
                                            @if($checked) checked @endif
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 border-t border-gray-100 bg-white flex justify-end">
                <button
                    type="submit"
                    class="text-white px-8 py-3 rounded-2xl shadow-lg hover:opacity-90 transition"
                    style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                    保存する
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function toggleAll() {
    let boxes = document.querySelectorAll('.relation-checkbox')
    let checked = [...boxes].every(b => b.checked)
    boxes.forEach(b => b.checked = !checked)
}

function toggleStaff(el) {
    let staff = el.dataset.staff
    document.querySelectorAll('.relation-checkbox').forEach(box => {
        if (box.dataset.staff == staff) {
            box.checked = el.checked
        }
    })
}

function toggleMenu(el) {
    let menu = el.dataset.menu
    document.querySelectorAll('.relation-checkbox').forEach(box => {
        if (box.dataset.menu == menu) {
            box.checked = el.checked
        }
    })
}
</script>

@endsection
