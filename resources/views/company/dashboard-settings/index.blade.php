@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                 style="background-color: {{ $theme }}15; color: {{ $theme }};">
                Dashboard Settings
            </div>

            <h1 class="mt-3 text-2xl sm:text-3xl font-bold text-stone-800">
                ダッシュボード管理
            </h1>

            <p class="text-stone-500 mt-2 text-sm leading-6">
                役職ごとに、ダッシュボード上の各カードや管理機能の表示権限を設定できます。
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border bg-white text-sm font-medium shadow-sm hover:bg-stone-50 transition"
           style="border-color: {{ $theme }}; color: {{ $theme }};">
            ← ダッシュボード
        </a>
    </div>

    <div class="rounded-2xl border p-5 sm:p-6 shadow-sm"
         style="border-color: {{ $theme }}30; background: linear-gradient(135deg, {{ $theme }}10 0%, #ffffff 55%);">
        <div class="flex flex-col md:flex-row md:items-start gap-4">
            <div class="shrink-0">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-bold shadow"
                     style="background: {{ $theme }};">
                    設定
                </div>
            </div>

            <div class="text-sm leading-7 text-stone-700">
                <p class="font-semibold text-stone-800 mb-1">権限設定について</p>
                <p>
                    ダッシュボード管理は初期状態でマスターのみ利用できます。
                    必要に応じて、他の役職にも管理権限を付与できます。
                    なお、マスターのダッシュボード管理権限は常に有効です。
                </p>
            </div>
        </div>
    </div>

    @php
        $orderedPermissionKeys = [
            'dashboard.manage',
            'dashboard.sales',

            'card.reserve',
            'card.business_calendar',
            'card.staff',
            'card.menu_category_tag',
            'card.menu',
            'card.menu_staff',
            'card.shift_patterns',
            'card.default_shift',
            'card.month_shift',
            'card.customers',
			'card.reviews',
            'card.notices',
            'card.vacation',
            'card.theme',
            'card.company_info',
            'card.logo',
            'card.billing',
            'card.my_profile',
        ];
    @endphp

    <div class="bg-white shadow-sm rounded-2xl border border-stone-200 overflow-hidden">
        <form method="POST" action="{{ route('company.dashboard-settings.update') }}">
            @csrf

            <div class="px-6 py-5 border-b border-stone-200 bg-stone-50">
                <h2 class="text-lg font-bold text-stone-800">表示権限一覧</h2>
                <p class="text-sm text-stone-500 mt-1">
                    チェックを入れた役職にのみ、該当機能を表示します。
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-stone-600">
                            <th class="p-4 text-left font-semibold">権限項目</th>
                            @foreach($roleSettings as $role => $setting)
                                <th class="p-4 text-center font-semibold">
                                    <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-bold"
                                          style="background-color: {{ $theme }}15; color: {{ $theme }};">
                                        {{ $setting['role_label'] }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orderedPermissionKeys as $permissionKey)
                            @php
                                $permissionLabel = $permissionLabels[$permissionKey] ?? $permissionKey;
                            @endphp

                            <tr class="border-b border-stone-100 hover:bg-stone-50/70 transition">
                                <td class="p-4 align-middle">
                                    <div class="font-semibold text-stone-800">
                                        {{ $permissionLabel }}
                                    </div>
                                    <div class="text-xs text-stone-400 mt-1">
                                        {{ $permissionKey }}
                                    </div>
                                </td>

                                @foreach($roleSettings as $role => $setting)
                                    <td class="p-4 text-center align-middle">
                                        @php
                                            $checked = $setting['permissions'][$permissionKey] ?? false;
                                            $isMasterManage = $role === 'master' && $permissionKey === 'dashboard.manage';
                                        @endphp

                                        @if($isMasterManage)
                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                                 style="background-color: {{ $theme }}18; color: {{ $theme }};">
                                                常に有効
                                            </div>
                                            <input type="hidden" name="permissions[{{ $role }}][{{ $permissionKey }}]" value="1">
                                        @else
                                            <input type="hidden" name="permissions[{{ $role }}][{{ $permissionKey }}]" value="0">
                                            <label class="inline-flex items-center justify-center cursor-pointer">
                                                <input type="checkbox"
                                                       name="permissions[{{ $role }}][{{ $permissionKey }}]"
                                                       value="1"
                                                       class="w-5 h-5 rounded border-stone-300"
                                                       @checked($checked)>
                                            </label>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 bg-white border-t border-stone-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-stone-500">
                        設定変更後は「保存する」を押して反映してください。
                    </p>

                    <button type="submit"
                            class="inline-flex items-center justify-center px-6 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                            style="background: {{ $theme }};">
                        保存する
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection