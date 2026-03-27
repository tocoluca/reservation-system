@extends('layouts.company')

@section('content')
@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">ダッシュボード管理</h1>
            <p class="text-gray-500 mt-2 text-sm">
                役職ごとに、カード単位で表示権限を設定できます。
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50 transition">
            ダッシュボードへ戻る
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

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
            'card.notices',
            'card.vacation',
            'card.theme',
            'card.company_info',
            'card.logo',
            'card.billing',
            'card.my_profile',
        ];
    @endphp

    <div class="bg-white shadow-lg rounded-2xl p-6">
        <form method="POST" action="{{ route('company.dashboard-settings.update') }}">
            @csrf

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-gray-600">
                            <th class="p-4 text-left">権限項目</th>
                            @foreach($roleSettings as $role => $setting)
                                <th class="p-4 text-center">{{ $setting['role_label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orderedPermissionKeys as $permissionKey)
                            @php
                                $permissionLabel = $permissionLabels[$permissionKey] ?? $permissionKey;
                            @endphp

                            <tr class="border-b">
                                <td class="p-4 font-semibold text-gray-800">
                                    {{ $permissionLabel }}
                                    <div class="text-xs text-gray-400 mt-1">{{ $permissionKey }}</div>
                                </td>

                                @foreach($roleSettings as $role => $setting)
                                    <td class="p-4 text-center">
                                        @php
                                            $checked = $setting['permissions'][$permissionKey] ?? false;
                                            $isMasterManage = $role === 'master' && $permissionKey === 'dashboard.manage';
                                        @endphp

                                        @if($isMasterManage)
                                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                                                常に有効
                                            </div>
                                            <input type="hidden" name="permissions[{{ $role }}][{{ $permissionKey }}]" value="1">
                                        @else
                                            <input type="hidden" name="permissions[{{ $role }}][{{ $permissionKey }}]" value="0">
                                            <input type="checkbox"
                                                   name="permissions[{{ $role }}][{{ $permissionKey }}]"
                                                   value="1"
                                                   class="w-5 h-5 rounded"
                                                   @checked($checked)>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 leading-7">
                ダッシュボード管理は初期状態でマスターのみ利用できます。<br>
                必要に応じて、他の役職にも管理権限を付与できます。<br>
                マスターのダッシュボード管理権限は常に有効です。
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-3 rounded-2xl text-white font-bold shadow hover:opacity-90 transition"
                        style="background: {{ $theme }};">
                    保存する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection