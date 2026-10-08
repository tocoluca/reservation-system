@extends('layouts.company')

@section('content')

@php
    $currentStaff = auth()->guard('company')->user();
    $company = $currentStaff?->company;
    $theme = $company?->theme_color ?? '#3b82f6';
    $planRestriction = request()->attributes->get('company_plan_restriction');
    $isPlanRestriction = is_array($planRestriction);
    $availablePlans = $planRestriction['available_plans'] ?? [];
    $availablePlanText = implode(' または ', array_map(
        fn ($plan) => $plan . 'プラン',
        $availablePlans
    ));
    $canManageBilling = $currentStaff?->canDashboard('card.billing') ?? false;
@endphp

<div class="min-h-[70vh] flex items-center justify-center px-4 sm:px-6 py-12">
    @if($isPlanRestriction)
        <section class="w-full max-w-2xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl">
            <div class="relative overflow-hidden px-6 py-8 text-white sm:px-10 sm:py-10"
                 style="background: var(--company-theme-gradient);">
                <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 -left-12 h-44 w-44 rounded-full bg-white/10"></div>

                <div class="relative">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-xs font-bold tracking-wide backdrop-blur-sm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6 2.1 2.1m0-12.8-2.1 2.1M7.7 16.3l-2.1 2.1"/>
                        </svg>
                        PLAN OPTION
                    </div>

                    <h1 class="mt-5 text-2xl font-black tracking-tight sm:text-3xl">
                        この機能は{{ $company?->planLabel() ?? '現在' }}プランの対象外です
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-7 text-white/85 sm:text-base">
                        {{ $availablePlanText ?: '上位プラン' }}へ変更すると、この機能をご利用いただけます。
                    </p>
                </div>
            </div>

            <div class="p-6 sm:p-10">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                        <div class="text-xs font-bold text-slate-500">現在のプラン</div>
                        <div class="mt-1 text-lg font-black text-slate-900">
                            {{ $planRestriction['current_plan'] ?? '-' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                        <div class="text-xs font-bold text-amber-700">利用できるプラン</div>
                        <div class="mt-1 text-lg font-black text-amber-950">
                            {{ $availablePlanText ?: '上位プラン' }}
                        </div>
                    </div>
                </div>

                <p class="mt-5 text-sm leading-6 text-slate-500">
                    プラン変更後は、同じメニューからこの機能を利用できます。
                </p>

                <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button"
                            onclick="history.back()"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 font-bold text-slate-700 transition hover:bg-slate-50">
                        前の画面に戻る
                    </button>

                    @if($canManageBilling)
                        <a href="{{ route('company.billing.index') }}"
                           class="inline-flex items-center justify-center rounded-xl px-5 py-3 font-bold text-white shadow-sm transition hover:opacity-90"
                           style="background: {{ $theme }}">
                            料金プランを確認する
                        </a>
                    @else
                        <a href="{{ route('company.dashboard') }}"
                           class="inline-flex items-center justify-center rounded-xl px-5 py-3 font-bold text-white shadow-sm transition hover:opacity-90"
                           style="background: {{ $theme }}">
                            ダッシュボードへ
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @else
        <section class="w-full max-w-xl rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-xl sm:p-12">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-2xl font-black text-red-600">
                403
            </div>

            <h1 class="mt-6 text-xl font-black text-slate-900 sm:text-2xl">
                この操作を実行する権限がありません
            </h1>

            <p class="mt-3 text-sm leading-7 text-slate-500 sm:text-base">
                操作権限を持つ担当者にご確認ください。
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <button type="button"
                        onclick="history.back()"
                        class="rounded-xl px-6 py-3 font-bold text-white shadow-sm transition hover:opacity-90"
                        style="background: {{ $theme }}">
                    前の画面に戻る
                </button>
                <a href="{{ route('company.dashboard') }}"
                   class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-center font-bold text-slate-700 transition hover:bg-slate-50">
                    ダッシュボードへ
                </a>
            </div>
        </section>
    @endif
</div>

@endsection
