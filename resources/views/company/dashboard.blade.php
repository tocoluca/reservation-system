@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $settingWarnings = $settingWarnings ?? [];
    $businessWarning = $settingWarnings['business_calendar'] ?? [];
    $shiftWarning = $settingWarnings['staff_shifts'] ?? [];
    $hasBusinessAlert = ($businessWarning['has_alert'] ?? false) || ($businessWarning['has_warning'] ?? false);
    $hasShiftAlert = ($shiftWarning['has_alert'] ?? false) || ($shiftWarning['has_warning'] ?? false);
    $hasAnySettingAlert = $hasBusinessAlert || $hasShiftAlert;
    $changePending = (int) ($changeNoticePendingCount ?? 0);
    $changePhonePending = (int) ($changeNoticePhonePendingCount ?? 0);
    $changeConfirmed = (int) ($changeNoticeConfirmedCount ?? 0);
    $changeTotalActive = $changePending + $changePhonePending;
    $setupDoneCount = (int) ($setupDoneCount ?? 0);
    $setupTotalCount = (int) ($setupTotalCount ?? 0);
    $setupPercent = $setupTotalCount > 0 ? (int) floor(($setupDoneCount / $setupTotalCount) * 100) : 0;
    $setupStatusList = $setupStatusList ?? [];
    $supportReplyInquiries = $supportReplyInquiries ?? collect();
    $supportUnreadCount = (int) ($supportUnreadCount ?? 0);
    $notices = $notices ?? collect();
    $todayReservationCount = $todayReservations->count();
    $tomorrowReservationCount = $tomorrowReservations->count();
    $todayCustomerCount = $todayReservations->pluck('customer_name')->filter()->unique()->count();
    $dashboardPermissions = $dashboardPermissions ?? [];
    $can = function ($key, $default = false) use ($dashboardPermissions) {
        return (bool) ($dashboardPermissions[$key] ?? $default);
    };
    $canAny = function (array $keys) use ($can) {
        foreach ($keys as $key) {
            if ($can($key)) return true;
        }
        return false;
    };
@endphp

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>

<style>
body {
    background:
        radial-gradient(circle at top left, {{ $theme }}24, transparent 34rem),
        linear-gradient(180deg, #f7f5ef 0%, #edf1f6 48%, #f8fafc 100%);
}
.dashboard-shell { color: #0f172a; }
.lux-hero {
    background:
        radial-gradient(circle at top right, {{ $theme }}66, transparent 26rem),
        linear-gradient(135deg, rgba(15,23,42,.98), rgba(30,41,59,.94) 52%, rgba(17,24,39,.98));
    border: 1px solid rgba(255,255,255,.18);
    box-shadow: 0 30px 80px rgba(15,23,42,.23);
}
.card {
    padding: 24px;
    border-radius: 22px;
    background: rgba(255,255,255,.8);
    backdrop-filter: blur(18px);
    box-shadow: 0 12px 36px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.72);
    border: 1px solid rgba(255,255,255,.6);
    transition: .28s;
    position: relative;
}
.card:hover { transform: translateY(-3px); box-shadow: 0 24px 60px rgba(15,23,42,.14); }
.card-link { display: flex; align-items: center; gap: 14px; font-weight: 600; }
.card-icon {
    width: 42px; height: 42px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, {{ $theme }}, #111827 115%);
    color: #fff; box-shadow: 0 10px 24px {{ $theme }}55; flex: none;
}
.card-icon svg { width: 21px; height: 21px; }
.quick-card {
    border: 1px solid rgba(255,255,255,.18); background: rgba(255,255,255,.08);
    color: white; border-radius: 20px; padding: 16px; transition: .25s; text-align: left;
}
.quick-card:hover { transform: translateY(-2px); background: rgba(255,255,255,.14); }
.kpi {
    border-radius: 22px; padding: 24px;
    background: linear-gradient(135deg, rgba(255,255,255,.9), {{ $theme }}18);
    border: 1px solid rgba(255,255,255,.7); box-shadow: 0 12px 32px rgba(15,23,42,.07);
}
.tab-nav {
    border-radius: 26px;
    padding: 10px;
    background: rgba(255,255,255,.72);
    border: 1px solid rgba(255,255,255,.68);
    box-shadow: 0 18px 44px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.75);
    backdrop-filter: blur(16px);
    overflow-x: auto;
}
.tab-nav-grid {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: minmax(128px, 1fr);
    gap: 8px;
    min-width: max-content;
}
@media (min-width: 1024px) {
    .tab-nav-grid {
        grid-auto-flow: initial;
        grid-auto-columns: initial;
        grid-template-columns: repeat(9, minmax(0, 1fr));
        min-width: 0;
    }
}
.tab-btn {
    min-height: 74px;
    padding: 12px 10px;
    border-radius: 18px;
    font-weight: 800;
    background: rgba(248,250,252,.72);
    border: 1px solid rgba(148,163,184,.18);
    color: #475569;
    transition: .22s;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
}
.tab-btn svg { width: 19px; height: 19px; }
.tab-btn:hover {
    transform: translateY(-1px);
    background: rgba(255,255,255,.95);
    color: #111827;
}
.tab-btn.active {
    background: linear-gradient(135deg, #111827, #1f2937 70%, {{ $theme }});
    color: #fff;
    border-color: rgba(255,255,255,.16);
    box-shadow: 0 16px 34px rgba(15,23,42,.22);
}
.table-apple { width: 100%; border-spacing: 0 8px; border-collapse: separate; }
.table-apple tr { background: white; border-radius: 14px; box-shadow: 0 4px 12px rgba(15,23,42,.05); }
.table-apple td { padding: 14px; }
.section-title { font-size: 1.1rem; font-weight: 800; color: #111827; }
.metric-label { font-size: .78rem; font-weight: 700; color: #64748b; }
</style>

<div x-data="{ tab: 'dashboard', showTomorrow: false }" class="dashboard-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <section class="lux-hero rounded-[2rem] overflow-hidden mb-6">
        <div class="p-5 sm:p-7 lg:p-8">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-7">
                <div class="min-w-0 text-white">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold tracking-[0.18em] text-white/80">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        STORE DASHBOARD
                    </div>
                    <h1 class="mt-5 text-2xl sm:text-4xl font-black tracking-tight">{{ $staff->company->name ?? $company->name }}</h1>
                    <p class="mt-2 text-sm sm:text-base text-white/70">{{ $staff->name }} / {{ $roleLabel ?? $staff->role }}</p>
                    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3 max-w-3xl">
                        <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">今日の予約</div><div class="mt-1 text-3xl font-black">{{ number_format($todayReservationCount) }}</div></div>
                        <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">来店予定人数</div><div class="mt-1 text-3xl font-black">{{ number_format($todayCustomerCount) }}</div></div>
                        <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">予約変更連絡</div><div class="mt-1 text-3xl font-black {{ $changeTotalActive > 0 ? 'text-rose-200' : '' }}">{{ number_format($changeTotalActive) }}</div></div>
                        @if($can('dashboard.sales'))
                            <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">今日売上</div><div class="mt-1 text-3xl font-black">¥{{ number_format($todaySales) }}</div></div>
                        @else
                            <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">サポート回答</div><div class="mt-1 text-3xl font-black">{{ number_format($supportUnreadCount) }}</div></div>
                        @endif
                    </div>
                </div>
                <div class="xl:w-[430px]">
                    <div class="mb-3 text-xs font-bold tracking-[0.18em] text-white/55">QUICK LAUNCH</div>
                    <div class="grid grid-cols-2 gap-3">
                        @if($can('card.reserve'))<a href="{{ route('company.reserve') }}" class="quick-card"><i data-lucide="calendar-check"></i><div class="mt-3 font-bold">予約管理</div><div class="text-xs text-white/55 mt-1">本日の受付確認</div></a>@endif
                        @if($can('card.customers'))<a href="{{ route('company.customers') }}" class="quick-card"><i data-lucide="users"></i><div class="mt-3 font-bold">顧客管理</div><div class="text-xs text-white/55 mt-1">来店履歴を確認</div></a>@endif
                        @if($can('card.month_shift'))<a href="{{ route('company.staff-shifts') }}" class="quick-card"><i data-lucide="clock"></i><div class="mt-3 font-bold">勤務管理</div><div class="text-xs text-white/55 mt-1">シフト登録</div></a>@endif
                        @if($can('dashboard.sales'))<button type="button" @click="tab='analytics'" class="quick-card"><i data-lucide="bar-chart-3"></i><div class="mt-3 font-bold">売上分析</div><div class="text-xs text-white/55 mt-1">推移とランキング</div></button>@endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="space-y-4 mb-6">
        @if($showSetupGuide ?? false)
            <div class="card border-amber-200 bg-amber-50/80">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="flex-1">
                        <div class="flex items-start gap-3"><div class="card-icon"><i data-lucide="alert-circle"></i></div><div><h2 class="section-title text-amber-950">初期設定がまだ完了していません</h2><p class="text-sm text-amber-800 mt-1">予約受付をスムーズに始めるため、必要な設定を確認してください。</p></div></div>
                        <div class="mt-4 flex items-end gap-3"><span class="text-sm text-gray-600">必須設定</span><span class="text-2xl font-black">{{ $setupDoneCount }} / {{ $setupTotalCount }}</span><span class="text-sm text-gray-500 mb-1">完了</span></div>
                        <div class="mt-3 h-3 rounded-full bg-white/80 border border-white overflow-hidden"><div class="h-full rounded-full" style="width: {{ $setupPercent }}%; background: {{ $theme }};"></div></div>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3">
                            @foreach($setupStatusList as $item)
                                <div class="rounded-2xl border px-4 py-3 text-center {{ ($item['done'] ?? false) ? 'bg-green-50 border-green-200' : 'bg-white border-red-200' }}"><div class="text-xs font-bold {{ ($item['done'] ?? false) ? 'text-green-700' : 'text-red-600' }}">{{ ($item['done'] ?? false) ? '完了' : '未完了' }}</div><div class="mt-1 text-sm font-semibold text-gray-800">{{ $item['label'] ?? '-' }}</div></div>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ url('/company/setup') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold shadow-sm hover:opacity-90" style="background: {{ $theme }}">初期設定ガイドへ</a>
                </div>
            </div>
        @endif
        @if($hasAnySettingAlert)
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @if($hasBusinessAlert)
                    <div class="card {{ ($businessWarning['has_alert'] ?? false) ? 'border-red-200 bg-red-50/80' : 'border-amber-200 bg-amber-50/80' }}"><div class="flex items-start gap-3"><div class="card-icon"><i data-lucide="calendar-days"></i></div><div class="flex-1"><h3 class="font-bold {{ ($businessWarning['has_alert'] ?? false) ? 'text-red-900' : 'text-amber-900' }}">営業日{{ ($businessWarning['has_alert'] ?? false) ? 'の警告' : 'のワーニング' }}</h3><p class="text-sm mt-1 {{ ($businessWarning['has_alert'] ?? false) ? 'text-red-800' : 'text-amber-800' }}">営業日の登録期間を確認してください。</p><div class="mt-3 text-sm text-gray-700 leading-6">本日：{{ $settingWarnings['today'] ?? '-' }}<br>予約可能期間の末日：{{ $settingWarnings['alert_end'] ?? '-' }}<br>登録済み最終日：<b>{{ $businessWarning['last_date'] ?? '未登録' }}</b></div><a href="{{ route('company.calendar.index') }}" class="inline-flex mt-4 px-4 py-2.5 rounded-2xl text-white font-bold" style="background: {{ $theme }}">営業日を設定する</a></div></div></div>
                @endif
                @if($hasShiftAlert)
                    <div class="card {{ ($shiftWarning['has_alert'] ?? false) ? 'border-red-200 bg-red-50/80' : 'border-amber-200 bg-amber-50/80' }}"><div class="flex items-start gap-3"><div class="card-icon"><i data-lucide="clock"></i></div><div class="flex-1"><h3 class="font-bold {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-900' : 'text-amber-900' }}">勤務日程{{ ($shiftWarning['has_alert'] ?? false) ? 'の警告' : 'のワーニング' }}</h3><p class="text-sm mt-1 {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-800' : 'text-amber-800' }}">従業員の勤務日程登録期間を確認してください。</p><div class="mt-3 text-sm text-gray-700 leading-6">本日：{{ $settingWarnings['today'] ?? '-' }}<br>予約可能期間の末日：{{ $settingWarnings['alert_end'] ?? '-' }}<br>登録済み最終日：<b>{{ $shiftWarning['last_date'] ?? '未登録' }}</b></div><a href="{{ route('company.staff-shifts') }}" class="inline-flex mt-4 px-4 py-2.5 rounded-2xl text-white font-bold" style="background: {{ $theme }}">勤務日程を設定する</a></div></div></div>
                @endif
            </div>
        @endif
        @if($hasChangeNoticeAlert ?? false)
            <div class="card border-rose-200 bg-rose-50/80">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="flex-1"><div class="flex items-start gap-3"><div class="card-icon"><i data-lucide="refresh-cw"></i></div><div><h2 class="section-title text-rose-950">予約変更連絡の未対応があります</h2><p class="text-sm text-rose-800 mt-1">まだ確認や連絡が完了していない予約変更があります。</p></div></div>
                        <div class="grid sm:grid-cols-3 gap-3 mt-4"><div class="rounded-2xl bg-white/85 border border-rose-100 px-4 py-3"><div class="metric-label">確認待ち</div><div class="text-2xl font-black text-rose-700">{{ number_format($changePending) }}件</div></div><div class="rounded-2xl bg-white/85 border border-amber-100 px-4 py-3"><div class="metric-label">電話対応待ち</div><div class="text-2xl font-black text-amber-700">{{ number_format($changePhonePending) }}件</div></div><div class="rounded-2xl bg-white/85 border border-green-100 px-4 py-3"><div class="metric-label">確認済み</div><div class="text-2xl font-black text-green-700">{{ number_format($changeConfirmed) }}件</div></div></div>
                    </div>
                    <a href="{{ route('company.reservation_change_notices.index') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl text-white font-bold" style="background: {{ $theme }}">予約変更連絡管理を開く</a>
                </div>
            </div>
        @endif
    </div>

    <nav class="tab-nav mb-6" aria-label="ダッシュボードメニュー">
        <div class="tab-nav-grid">
            <button type="button" @click="tab='dashboard'" :class="tab==='dashboard' ? 'tab-btn active' : 'tab-btn'">
                <i data-lucide="layout-dashboard"></i><span>ダッシュボード</span>
            </button>
            @if($canAny(['card.reserve', 'card.customers']))
                <button type="button" @click="tab='reserve'" :class="tab==='reserve' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="calendar-check"></i><span>予約・顧客</span>
                </button>
            @endif
            @if($canAny(['card.reviews', 'card.style', 'card.notices', 'card.reservation_change_notices']))
                <button type="button" @click="tab='notice'" :class="tab==='notice' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="bell"></i><span>通知</span>
                </button>
            @endif
            @if($canAny(['card.staff', 'card.vacation', 'card.my_profile']))
                <button type="button" @click="tab='staff'" :class="tab==='staff' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="users"></i><span>スタッフ</span>
                </button>
            @endif
            @if($canAny(['card.business_calendar', 'card.month_shift', 'card.month_shift_view', 'card.default_shift', 'card.shift_patterns']))
                <button type="button" @click="tab='shift'" :class="tab==='shift' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="calendar-days"></i><span>営業日・シフト</span>
                </button>
            @endif
            @if($canAny(['card.menu_category_tag', 'card.menu', 'card.menu_staff']))
                <button type="button" @click="tab='menu'" :class="tab==='menu' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="list-tree"></i><span>メニュー</span>
                </button>
            @endif
            @if($canAny(['card.billing', 'card.support']))
                <button type="button" @click="tab='contract'" :class="tab==='contract' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="badge-help"></i><span>契約・QA</span>
                </button>
            @endif
            @if($canAny(['card.company_info', 'card.theme', 'card.logo', 'dashboard.manage']))
                <button type="button" @click="tab='settings'" :class="tab==='settings' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="settings"></i><span>設定</span>
                </button>
            @endif
            @if($can('dashboard.sales'))
                <button type="button" @click="tab='analytics'" :class="tab==='analytics' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="bar-chart-3"></i><span>分析</span>
                </button>
            @endif
        </div>
    </nav>

    <div x-show="tab==='dashboard'" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-4">
            <div class="kpi flex items-center justify-between gap-4"><div><div class="metric-label">今日の予約</div><div class="text-4xl font-black mt-1">{{ number_format($todayReservationCount) }}</div></div><div class="card-icon"><i data-lucide="calendar"></i></div></div>
            <div class="kpi flex items-center justify-between gap-4 cursor-pointer" @click="showTomorrow=!showTomorrow"><div><div class="metric-label">明日の予約</div><div class="text-4xl font-black mt-1">{{ number_format($tomorrowReservationCount) }}</div><div class="text-xs text-gray-400 mt-1">クリックで一覧表示</div></div><div class="card-icon"><i data-lucide="clock"></i></div></div>
        </div>
        <div class="grid xl:grid-cols-2 gap-6">
            <div class="card">
                <h2 class="section-title mb-4">今日の予約</h2>
                <table class="table-apple">
                    @forelse($todayReservations as $r)
                        <tr><td>{{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}</td><td><div class="font-bold">{{ $r->customer_name }}</div><div class="text-xs text-gray-400">{{ $r->menus->pluck('name')->join(', ') }}</div></td><td>{{ $r->staff->name ?? '-' }}</td></tr>
                    @empty
                        <tr><td class="text-center text-gray-400 py-6">予約はありません</td></tr>
                    @endforelse
                </table>
            </div>
            <div class="space-y-6">
                <div x-show="showTomorrow" class="card">
                    <h2 class="section-title mb-4">明日の予約</h2>
                    <table class="table-apple">
                        @forelse($tomorrowReservations as $r)
                            <tr><td>{{ \Carbon\Carbon::parse($r->start_at)->format('H:i') }}</td><td><div class="font-bold">{{ $r->customer_name }}</div><div class="text-xs text-gray-400">{{ $r->menus->pluck('name')->join(', ') }}</div></td><td>{{ $r->staff->name ?? '-' }}</td></tr>
                        @empty
                            <tr><td class="text-center text-gray-400 py-6">予約はありません</td></tr>
                        @endforelse
                    </table>
                </div>
                <div class="card">
                    <div class="flex items-center justify-between gap-3 mb-4"><div><h2 class="section-title">サポートからの回答</h2><p class="text-xs text-gray-400 mt-1">お問い合わせへの回答を確認できます</p></div>@if($supportUnreadCount > 0)<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">未読 {{ $supportUnreadCount }}件</span>@endif</div>
                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                        @forelse($supportReplyInquiries as $inquiry)
                            <div class="rounded-2xl border border-sky-100 bg-sky-50/60 p-4"><div class="flex flex-wrap items-center gap-2 mb-2"><span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-700">回答</span>@if(!$inquiry->is_read_by_company)<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">未読</span>@endif</div><div class="font-bold text-gray-800">{{ $inquiry->subject }}</div><div class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($inquiry->admin_reply, 120) }}</div><a href="{{ route('company.support.show', $inquiry) }}" class="inline-flex mt-3 px-4 py-2 rounded-2xl bg-white border border-sky-200 text-sky-700 font-bold text-sm">回答を見る</a></div>
                        @empty
                            <div class="text-sm text-gray-400 py-6 text-center">現在、サポートからの回答はありません</div>
                        @endforelse
                    </div>
                </div>
                <div class="card">
                    <div class="flex items-center justify-between gap-3 mb-4"><h2 class="section-title">企業向けお知らせ</h2><span class="text-xs text-gray-400">{{ $notices->count() }}件</span></div>
                    <div class="space-y-3 max-h-[320px] overflow-y-auto pr-1">
                        @forelse($notices as $notice)
                            <a href="{{ route('company.dashboard-notices.show', $notice) }}" class="block rounded-2xl border border-gray-200 hover:bg-white transition p-4"><div class="flex flex-wrap items-center gap-2 mb-2">@if($notice->is_important)<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">重要</span>@endif @if($notice->is_new)<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">NEW</span>@endif <span class="text-xs text-gray-500">{{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }}</span></div><div class="font-bold text-gray-800">{{ $notice->title }}</div><div class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 80) }}</div></a>
                        @empty
                            <div class="text-sm text-gray-400 py-6 text-center">現在表示中のお知らせはありません</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="tab==='reserve'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.reserve'))<a href="{{ route('company.reserve') }}" class="card card-link"><div class="card-icon"><i data-lucide="calendar-check"></i></div><div><div class="font-bold">予約管理</div><div class="text-sm text-gray-500">予約の確認・変更・キャンセル</div></div></a>@endif
        @if($can('card.customers'))<a href="{{ route('company.customers') }}" class="card card-link"><div class="card-icon"><i data-lucide="users"></i></div><div><div class="font-bold">顧客管理</div><div class="text-sm text-gray-500">来店履歴・顧客情報の管理</div></div></a>@endif
    </div>

    <div x-show="tab==='notice'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.reviews') && ($company->review_enabled ?? false))<a href="{{ route('company.reviews.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="star"></i></div><div><div class="font-bold">口コミ管理</div><div class="text-sm text-gray-500">評価確認・返信対応</div></div></a>@endif
        @if($can('card.style'))<a href="{{ route('company.style-posts.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="image"></i></div><div><div class="font-bold">最新スタイル投稿</div><div class="text-sm text-gray-500">ヘアスタイルの発信</div></div></a>@endif
        @if($can('card.notices'))<a href="{{ route('company.notices.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="megaphone"></i></div><div><div class="font-bold">お知らせ情報管理</div><div class="text-sm text-gray-500">キャンペーン・重要告知</div></div></a>@endif
        @if($can('card.reservation_change_notices'))
            <a href="{{ route('company.reservation_change_notices.index') }}" class="card card-link md:col-span-2 border-rose-100 bg-rose-50/60">
                <div class="card-icon"><i data-lucide="refresh-cw"></i></div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="font-bold">予約変更連絡管理</div>
                        @if($changeTotalActive > 0)
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">未対応 {{ number_format($changeTotalActive) }}件</span>
                        @else
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">未対応なし</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500 mt-1">予約変更の連絡、電話対応、確認状況を管理します</div>
                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <div class="rounded-2xl bg-white/85 border border-rose-100 px-3 py-2">
                            <div class="text-[11px] font-bold text-gray-500">確認待ち</div>
                            <div class="text-lg font-black text-rose-700">{{ number_format($changePending) }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/85 border border-amber-100 px-3 py-2">
                            <div class="text-[11px] font-bold text-gray-500">電話待ち</div>
                            <div class="text-lg font-black text-amber-700">{{ number_format($changePhonePending) }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/85 border border-green-100 px-3 py-2">
                            <div class="text-[11px] font-bold text-gray-500">確認済み</div>
                            <div class="text-lg font-black text-green-700">{{ number_format($changeConfirmed) }}</div>
                        </div>
                    </div>
                </div>
            </a>
        @endif
    </div>

    <div x-show="tab==='staff'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.staff'))<a href="{{ route('company.staff.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="user"></i></div><div><div class="font-bold">担当者管理</div><div class="text-sm text-gray-500">スタッフ登録・権限管理</div></div></a>@endif
        @if($can('card.vacation'))<a href="{{ route('company.vacation.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="calendar-x"></i></div><div><div class="font-bold">休暇管理</div><div class="text-sm text-gray-500">休み・有給の設定</div></div></a>@endif
        @if($can('card.my_profile'))<a href="{{ route('company.my-profile') }}" class="card card-link"><div class="card-icon"><i data-lucide="settings"></i></div><div><div class="font-bold">マイプロフィール</div><div class="text-sm text-gray-500">個人設定・アカウント管理</div></div></a>@endif
    </div>

    <div x-show="tab==='shift'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.business_calendar'))<a href="{{ route('company.calendar.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="calendar"></i></div><div><div class="font-bold">営業日管理</div><div class="text-sm text-gray-500">営業日・営業時間設定</div></div></a>@endif
        @if($can('card.month_shift'))<a href="{{ route('company.staff-shifts') }}" class="card card-link"><div class="card-icon"><i data-lucide="clock"></i></div><div><div class="font-bold">勤務管理</div><div class="text-sm text-gray-500">日別シフト登録</div></div></a>@endif
        @if($can('card.month_shift_view'))<a href="{{ route('company.staff-shifts.view') }}" class="card card-link"><div class="card-icon"><i data-lucide="layout-grid"></i></div><div><div class="font-bold">スタッフ別シフト表</div><div class="text-sm text-gray-500">稼働状況の確認</div></div></a>@endif
        @if($can('card.default_shift'))<a href="{{ route('company.staff-default-shifts') }}" class="card card-link"><div class="card-icon"><i data-lucide="repeat"></i></div><div><div class="font-bold">基本シフト</div><div class="text-sm text-gray-500">定期シフト設定</div></div></a>@endif
        @if($can('card.shift_patterns'))<a href="{{ route('company.shift-patterns') }}" class="card card-link"><div class="card-icon"><i data-lucide="layers"></i></div><div><div class="font-bold">シフトパターン</div><div class="text-sm text-gray-500">勤務時間テンプレート</div></div></a>@endif
    </div>

    <div x-show="tab==='menu'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.menu_category_tag'))<a href="{{ route('company.menu.settings') }}" class="card card-link"><div class="card-icon"><i data-lucide="tag"></i></div><div><div class="font-bold">カテゴリー・タグ管理</div><div class="text-sm text-gray-500">分類・検索用タグ設定</div></div></a>@endif
        @if($can('card.menu'))<a href="{{ route('company.menu.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="list"></i></div><div><div class="font-bold">メニュー管理</div><div class="text-sm text-gray-500">料金・施術時間の設定</div></div></a>@endif
        @if($can('card.menu_staff'))<a href="{{ route('company.menu-staff.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="users"></i></div><div><div class="font-bold">メニュー対応スタッフ設定</div><div class="text-sm text-gray-500">担当可能スタッフ設定</div></div></a>@endif
    </div>

    <div x-show="tab==='contract'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.billing'))<a href="{{ route('company.billing.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="credit-card"></i></div><div><div class="font-bold">契約管理</div><div class="text-sm text-gray-500">プラン・支払い情報</div>@if($billingWarning)<div class="mt-2 text-xs text-amber-700">{{ $billingWarning }}</div>@endif</div></a>@endif
        @if($can('card.support'))<a href="{{ route('company.support.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="help-circle"></i></div><div><div class="font-bold">よくあるご質問・お問い合わせ</div><div class="text-sm text-gray-500">サポート・FAQ</div>@if($supportUnreadCount > 0)<span class="inline-flex mt-2 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">{{ $supportUnreadCount }}件</span>@endif</div></a>@endif
    </div>

    <div x-show="tab==='settings'" class="grid md:grid-cols-2 gap-4">
        @if($can('card.company_info'))<a href="{{ route('company.info.edit') }}" class="card card-link"><div class="card-icon"><i data-lucide="building"></i></div><div><div class="font-bold">企業情報編集</div><div class="text-sm text-gray-500">店舗情報・基本設定</div></div></a>@endif
        @if($can('card.theme'))<a href="{{ route('company.theme') }}" class="card card-link"><div class="card-icon"><i data-lucide="palette"></i></div><div><div class="font-bold">テーマ設定</div><div class="text-sm text-gray-500">カラー・UI調整</div></div></a>@endif
        @if($can('card.logo'))<a href="{{ route('company.logo') }}" class="card card-link"><div class="card-icon"><i data-lucide="image"></i></div><div><div class="font-bold">ロゴ設定</div><div class="text-sm text-gray-500">ブランド設定</div></div></a>@endif
        @if($can('dashboard.manage'))<a href="{{ route('company.dashboard-settings.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="sliders-horizontal"></i></div><div><div class="font-bold">ダッシュボード管理</div><div class="text-sm text-gray-500">権限別カード設定</div></div></a>@endif
    </div>

    @if($can('dashboard.sales'))
        <div x-show="tab==='analytics'" class="space-y-5">
            <div class="card">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5"><div><h2 class="text-2xl font-black">売上ダッシュボード</h2><p class="text-sm text-gray-500 mt-1">必要な数字だけ見やすく確認できます。</p></div></div>
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <select name="period" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full"><option value="month" {{ $period=='month' ? 'selected':'' }}>月別</option><option value="year" {{ $period=='year' ? 'selected':'' }}>年別</option></select>
                    <select name="year" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">@for($y = now()->year; $y >= now()->year - 5; $y--)<option value="{{ $y }}" {{ $year==$y ? 'selected':'' }}>{{ $y }}年</option>@endfor</select>
                    <select name="month" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">@for($m = 1; $m <= 12; $m++)<option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>{{ $m }}月</option>@endfor</select>
                    <button class="rounded-2xl text-white font-bold px-4 py-2.5" style="background: {{ $theme }}">表示</button>
                </form>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4"><div class="kpi"><div class="metric-label">今日売上</div><div class="text-3xl font-black mt-2">¥{{ number_format($todaySales) }}</div></div><div class="kpi"><div class="metric-label">今月売上</div><div class="text-3xl font-black mt-2">¥{{ number_format($monthlySales) }}</div></div><div class="kpi"><div class="metric-label">今年売上</div><div class="text-3xl font-black mt-2">¥{{ number_format($yearlySales) }}</div></div><div class="kpi"><div class="metric-label">客単価</div><div class="text-3xl font-black mt-2">¥{{ number_format($averagePrice) }}</div></div></div>
            <div class="card"><h3 class="section-title mb-4">売上推移（{{ $year }}年）</h3><div class="w-full overflow-x-auto"><div class="min-w-[560px]"><canvas id="salesChart"></canvas></div></div></div>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="card"><h3 class="section-title mb-4">従業員売上ランキング</h3><div class="space-y-2">@forelse($staffRanking as $i => $row)<div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2"><span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->staff->name ?? '未設定' }}</span><span class="text-sm font-bold whitespace-nowrap">¥{{ number_format($row->total) }}</span></div>@empty<div class="text-sm text-gray-400">データがありません</div>@endforelse</div></div>
                <div class="card"><h3 class="section-title mb-4">指名ランキング</h3><div class="space-y-2">@forelse($nominationRanking as $i => $row)<div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2"><span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->staff->name ?? '未設定' }}</span><span class="text-sm font-bold whitespace-nowrap">{{ $row->nomination_count }}回</span></div>@empty<div class="text-sm text-gray-400">データがありません</div>@endforelse</div></div>
                <div class="card"><h3 class="section-title mb-4">人気メニュー</h3><div class="space-y-2">@forelse($menuRanking as $i => $row)<div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2"><span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->name }}</span><span class="text-sm font-bold whitespace-nowrap">{{ $row->total }}回</span></div>@empty<div class="text-sm text-gray-400">データがありません</div>@endforelse</div></div>
            </div>
        </div>
    @endif
</div>

<script>
const salesLabels = @json($monthlyChart->pluck('month')->values());
const salesData = @json($monthlyChart->pluck('total')->values());
@if($can('dashboard.sales'))
const salesCanvas = document.getElementById('salesChart');
if (salesCanvas) {
    new Chart(salesCanvas, {
        type: 'bar',
        data: {
            labels: salesLabels.map((m) => m + '月'),
            datasets: [{ label: '売上', data: salesData, backgroundColor: '{{ $theme }}', borderRadius: 10, maxBarThickness: 42 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: (value) => '¥' + Number(value).toLocaleString() } } }
        }
    });
}
@endif
</script>
@endsection
