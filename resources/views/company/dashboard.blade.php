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
    // 予約変更連絡の代表件数は、重複し得る電話対応待ちを足さず確認待ちで集計する。
    $changeTotalActive = $changePending;
    $setupDoneCount = (int) ($setupDoneCount ?? 0);
    $setupTotalCount = (int) ($setupTotalCount ?? 0);
    $setupPercent = $setupTotalCount > 0 ? (int) floor(($setupDoneCount / $setupTotalCount) * 100) : 0;
    $setupStatusList = $setupStatusList ?? [];
    $supportReplyInquiries = $supportReplyInquiries ?? collect();
    $supportUnreadCount = (int) ($supportUnreadCount ?? 0);
    $notices = $notices ?? collect();
    $attentionTaskCount = (int) (($showSetupGuide ?? false) && $setupTotalCount > 0)
        + (int) ($changeTotalActive > 0)
        + (int) ($supportUnreadCount > 0)
        + (int) $hasBusinessAlert
        + (int) $hasShiftAlert;
    $todayReservationCount = $todayReservations->count();
    $tomorrowReservationCount = $tomorrowReservations->count();
    $todayReservationListUrl = route('company.reservations.index', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->toDateString(),
        'status' => 'reserved',
    ]);
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
    $availableFeatureTabs = [];
    if ($canAny(['card.reserve', 'card.customers'])) $availableFeatureTabs[] = 'daily';
    if ($canAny(['card.reviews', 'card.style', 'card.notices', 'card.reservation_change_notices', 'card.menu_category_tag', 'card.menu', 'card.menu_staff'])) $availableFeatureTabs[] = 'outreach';
    if ($canAny(['card.staff', 'card.vacation', 'card.my_profile', 'card.business_calendar', 'card.month_shift', 'card.month_shift_view', 'card.default_shift', 'card.shift_patterns'])) $availableFeatureTabs[] = 'staffwork';
    if ($canAny(['card.billing', 'card.support'])) $availableFeatureTabs[] = 'support';
    if ($canAny(['card.company_info', 'card.theme', 'card.logo', 'dashboard.manage'])) $availableFeatureTabs[] = 'settings';
    if ($can('dashboard.sales')) $availableFeatureTabs[] = 'analytics';
    $defaultFeatureTab = $availableFeatureTabs[0] ?? 'dashboard';
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
    /* Keep white headings readable even if the theme or color-mix is unsupported. */
    background-color: #172238;
    background-image: linear-gradient(135deg, #0f172a, #1e293b 52%, #111827);
    background-image:
        radial-gradient(circle at top right, color-mix(in srgb, var(--main-color) 25%, transparent), transparent 26rem),
        linear-gradient(135deg, #0f172a, #1e293b 52%, #111827);
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
.card.card-link {
    min-height: 118px;
    padding: 20px 64px 20px 20px;
    overflow: hidden;
    border: 2px solid #d6d3d1;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15,23,42,.08);
}
.card.card-link::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 6px;
    background: var(--feature-accent, {{ $theme }});
}
.card.card-link::after {
    content: "›";
    position: absolute;
    right: 18px;
    top: 50%;
    display: flex;
    width: 32px;
    height: 32px;
    align-items: center;
    justify-content: center;
    border: 1px solid #d6d3d1;
    border-radius: 999px;
    background: #f5f5f4;
    color: #57534e;
    font-size: 1.5rem;
    font-weight: 900;
    line-height: 1;
    transform: translateY(-50%);
    transition: .22s;
}
.card.card-link:hover {
    border-color: var(--feature-accent, {{ $theme }});
    background: var(--feature-soft, #f8fafc);
    box-shadow: 0 18px 38px rgba(15,23,42,.14);
}
.card.card-link:hover::after {
    border-color: var(--feature-accent, {{ $theme }});
    background: var(--feature-accent, {{ $theme }});
    color: #fff;
    transform: translate(3px, -50%);
}
.card.card-link:focus-visible {
    outline: 3px solid {{ $theme }}55;
    outline: 3px solid color-mix(in srgb, var(--feature-accent, {{ $theme }}) 30%, transparent);
    outline-offset: 3px;
}
.card-link > .card-icon + div {
    min-width: 0;
}
.card-link > .card-icon + div > .font-bold {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.4;
}
.card-link > .card-icon + div > .text-sm {
    margin-top: .35rem;
    color: #64748b;
    line-height: 1.55;
}
.card-icon {
    width: 50px; height: 50px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, {{ $theme }}, #111827 115%);
    color: #fff; box-shadow: 0 10px 24px {{ $theme }}55; flex: none;
}
.card-icon svg { width: 23px; height: 23px; }
.feature-card-grid {
    --feature-accent: {{ $theme }};
    --feature-soft: #f8fafc;
    padding: 18px;
    border: 1px solid #cbd5e1;
    border-radius: 28px;
    background: rgba(255,255,255,.72);
    box-shadow: 0 14px 34px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.9);
}
.feature-card-grid .card-icon {
    background: linear-gradient(135deg, var(--feature-accent), #111827 125%);
    box-shadow: 0 10px 24px color-mix(in srgb, var(--feature-accent) 35%, transparent);
}
.feature-group-heading {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: 0 2px 14px;
    border-bottom: 2px solid #d6d3d1;
}
.feature-group-heading::before {
    content: "";
    width: 6px;
    height: 42px;
    flex: none;
    border-radius: 999px;
    background: var(--feature-accent);
}
.feature-group-heading h3 {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 900;
}
.feature-group-heading p {
    margin-top: .2rem;
    color: #64748b;
    font-size: .75rem;
}
.feature-group-daily { --feature-accent: #2563eb; --feature-soft: #eff6ff; }
.feature-group-outreach { --feature-accent: #e11d48; --feature-soft: #fff1f2; }
.feature-group-staff { --feature-accent: #059669; --feature-soft: #ecfdf5; }
.feature-group-prep { --feature-accent: #d97706; --feature-soft: #fffbeb; }
.feature-group-menu { --feature-accent: #7c3aed; --feature-soft: #f5f3ff; }
.feature-group-support { --feature-accent: #0284c7; --feature-soft: #f0f9ff; }
.feature-group-settings { --feature-accent: #475569; --feature-soft: #f8fafc; }
.feature-card-alert.card.card-link {
    border-color: #fecdd3;
    background: #fff1f2;
}
.change-notice-attention {
    overflow: hidden;
    border: 3px solid #e11d48 !important;
    background:
        radial-gradient(circle at top right, rgba(251, 191, 36, .2), transparent 22rem),
        linear-gradient(135deg, #fff1f2 0%, #fff7ed 100%) !important;
    box-shadow: 0 18px 44px rgba(190, 18, 60, .2), inset 0 1px 0 rgba(255,255,255,.9) !important;
}
.change-notice-attention::before {
    content: "";
    position: absolute;
    inset: 0 0 auto;
    height: 8px;
    background: linear-gradient(90deg, #be123c, #e11d48 48%, #f59e0b);
}
.change-notice-alert-icon {
    display: flex;
    width: 58px;
    height: 58px;
    flex: none;
    align-items: center;
    justify-content: center;
    border: 2px solid #fecdd3;
    border-radius: 18px;
    background: #be123c;
    color: #fff;
    box-shadow: 0 10px 24px rgba(190,18,60,.28);
}
.change-notice-alert-icon svg { width: 28px; height: 28px; }
.change-notice-alert-badge {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    border-radius: 999px;
    background: #be123c;
    padding: .4rem .75rem;
    color: #fff;
    font-size: .75rem;
    font-weight: 900;
    letter-spacing: .08em;
}
.change-notice-alert-dot {
    width: .55rem;
    height: .55rem;
    border-radius: 999px;
    background: #fff;
    box-shadow: 0 0 0 0 rgba(255,255,255,.7);
    animation: changeNoticePulse 1.8s infinite;
}
.change-notice-action-button {
    background: #be123c;
    box-shadow: 0 10px 22px rgba(190,18,60,.25);
}
.change-notice-action-button:hover { background: #9f1239; transform: translateY(-1px); }
@keyframes changeNoticePulse {
    70% { box-shadow: 0 0 0 8px rgba(255,255,255,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
}
@media (prefers-reduced-motion: reduce) {
    .change-notice-alert-dot { animation: none; }
}
.action-panel {
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.08);
    color: white;
    border-radius: 24px;
    padding: 18px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
}
.action-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-radius: 18px;
    padding: 13px 14px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    transition: .22s;
}
.action-item:hover { transform: translateY(-1px); background: rgba(255,255,255,.14); }
.action-count {
    min-width: 3rem;
    text-align: center;
    border-radius: 999px;
    padding: .35rem .65rem;
    background: rgba(255,255,255,.16);
    font-size: .85rem;
    font-weight: 900;
}
.kpi {
    border-radius: 22px; padding: 24px;
    background: linear-gradient(135deg, rgba(255,255,255,.9), {{ $theme }}18);
    border: 1px solid rgba(255,255,255,.7); box-shadow: 0 12px 32px rgba(15,23,42,.07);
}
.tab-nav {
    border-radius: 26px;
    padding: 14px;
    background:
        linear-gradient(135deg, rgba(255,255,255,.96), rgba(241,245,249,.84)),
        radial-gradient(circle at top right, {{ $theme }}36, transparent 18rem),
        linear-gradient(90deg, {{ $theme }}14, rgba(15,23,42,.04));
    border: 1px solid rgba(148,163,184,.24);
    box-shadow: 0 20px 50px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.82);
    backdrop-filter: blur(16px);
    overflow: hidden;
    position: relative;
}
.tab-nav::before {
    content: "";
    position: absolute;
    left: 18px;
    right: 18px;
    top: 0;
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, {{ $theme }}, #111827);
    opacity: .78;
}
.tab-nav-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    min-width: 0;
}
@media (min-width: 640px) {
    .tab-nav-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (min-width: 1024px) {
    .tab-nav-grid {
        grid-template-columns: repeat(6, minmax(0, 1fr));
        min-width: 0;
    }
}
.tab-btn {
    min-height: 86px;
    padding: 13px 10px;
    border-radius: 18px;
    font-weight: 800;
    background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(248,250,252,.74));
    border: 1px solid rgba(148,163,184,.26);
    color: #334155;
    transition: .22s;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
    position: relative;
}
.tab-btn svg { width: 19px; height: 19px; }
.tab-btn > svg {
    padding: 7px;
    box-sizing: content-box;
    border-radius: 14px;
    background: {{ $theme }}14;
    color: {{ $theme }};
}
.tab-btn .tab-sub {
    font-size: 10px;
    line-height: 1;
    font-weight: 800;
    color: #94a3b8;
}
.tab-btn:hover {
    transform: translateY(-1px);
    background: rgba(255,255,255,.95);
    color: #111827;
    border-color: {{ $theme }}55;
    box-shadow: 0 12px 26px rgba(15,23,42,.08);
}
.tab-btn.active {
    background: linear-gradient(135deg, #111827, #1f2937 70%, {{ $theme }});
    color: #fff;
    border-color: rgba(255,255,255,.16);
    box-shadow: 0 16px 34px rgba(15,23,42,.22);
}
.tab-btn.active > svg {
    background: rgba(255,255,255,.16);
    color: #fff;
}
.tab-btn.active .tab-sub { color: rgba(255,255,255,.68); }
.tab-btn.active::after {
    content: "";
    position: absolute;
    left: 18px;
    right: 18px;
    bottom: -8px;
    height: 4px;
    border-radius: 999px;
    background: {{ $theme }};
    box-shadow: 0 8px 18px {{ $theme }}66;
}
.tab-category-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
    border-radius: 24px;
    border: 1px solid {{ $theme }}38;
    background:
        linear-gradient(135deg, rgba(255,255,255,.96), rgba(248,250,252,.88)),
        radial-gradient(circle at 8% 20%, {{ $theme }}30, transparent 18rem);
    padding: 18px 20px;
    box-shadow: 0 16px 36px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.9);
    position: relative;
    overflow: hidden;
}
.tab-category-heading::after {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, {{ $theme }}, #111827);
}
.tab-category-heading h2 {
    font-size: 1.15rem;
    font-weight: 900;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    gap: .55rem;
}
.tab-category-heading h2::before {
    content: "◆";
    font-size: .72rem;
    color: {{ $theme }};
    filter: drop-shadow(0 5px 8px {{ $theme }}55);
}
.tab-category-heading p {
    margin-top: .25rem;
    font-size: .8rem;
    color: #64748b;
}
.feature-menu-toggle {
    min-height: 46px;
    border: 1px solid rgba(255,255,255,.22);
    background: linear-gradient(135deg, {{ $theme }}, #111827);
    color: #fff;
    box-shadow: 0 12px 26px {{ $theme }}35, 0 8px 18px rgba(15,23,42,.12);
}
.feature-menu-toggle:hover {
    transform: translateY(-1px);
    box-shadow: 0 15px 30px {{ $theme }}42, 0 10px 22px rgba(15,23,42,.15);
}
.feature-menu-toggle:focus-visible {
    outline: 3px solid {{ $theme }}42;
    outline-offset: 3px;
}
@media (max-width: 640px) {
    .feature-card-grid {
        padding: 12px;
        border-radius: 22px;
    }
    .card.card-link {
        min-height: 104px;
        padding: 16px 54px 16px 16px;
    }
    .card.card-link::after {
        right: 12px;
        width: 28px;
        height: 28px;
    }
    .card-icon {
        width: 44px;
        height: 44px;
    }
    .tab-category-heading {
        align-items: stretch;
        flex-direction: column;
        padding: 14px;
    }
    .feature-menu-toggle {
        width: 100%;
    }
}
.table-apple { width: 100%; border-spacing: 0 8px; border-collapse: separate; }
.table-apple tr { background: white; border-radius: 14px; box-shadow: 0 4px 12px rgba(15,23,42,.05); }
.table-apple td { padding: 14px; }
.section-title { font-size: 1.1rem; font-weight: 800; color: #111827; }
.metric-label { font-size: .78rem; font-weight: 700; color: #64748b; }
</style>

<div x-data="{
        tab: @js(request()->hasAny(['period', 'year', 'month']) ? 'analytics' : 'dashboard'),
        showTomorrow: false,
        showFeatureCards: true,
        allowedTabs: @js($availableFeatureTabs),
        defaultTab: @js($defaultFeatureTab),
        toggleFeatureCards() {
            if (this.showFeatureCards) {
                this.showFeatureCards = false;
                this.tab = 'dashboard';
                return;
            }
            this.showFeatureCards = true;
            let nextTab = this.defaultTab;
            try {
                const storedTab = localStorage.getItem('company-dashboard-feature-tab');
                if (storedTab && this.allowedTabs.includes(storedTab)) nextTab = storedTab;
            } catch (error) {}
            this.tab = nextTab;
        },
        init() {
            const analyticsRequested = @js(request()->hasAny(['period', 'year', 'month']));
            try {
                const storedTab = localStorage.getItem('company-dashboard-feature-tab');
                if (!analyticsRequested && storedTab && this.allowedTabs.includes(storedTab)) this.tab = storedTab;
            } catch (error) {}
            if (this.showFeatureCards && this.tab === 'dashboard') this.tab = this.defaultTab;
            if (!this.showFeatureCards) this.tab = 'dashboard';
            this.$watch('tab', value => {
                if (this.allowedTabs.includes(value)) {
                    try { localStorage.setItem('company-dashboard-feature-tab', value); } catch (error) {}
                }
            });
        }
     }"
     class="dashboard-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <section class="lux-hero rounded-[2rem] overflow-hidden mb-6">
        <div class="p-5 sm:p-7 lg:p-8">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-7">
                <div class="min-w-0 text-white">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold tracking-[0.18em] text-white/80">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        STORE DASHBOARD
                    </div>
                    <h1 class="mt-5 text-2xl sm:text-4xl font-black tracking-tight">{{ $staff->company->name ?? $company->name }}</h1>
                    <p class="mt-2 text-sm sm:text-base text-white/70">{{ $staff->name }} / {{ $roleLabel ?? $staff->roleLabel() }}</p>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-w-3xl">
                        <a href="{{ $todayReservationListUrl }}"
                           class="rounded-2xl bg-white/10 border border-white/15 p-4 hover:bg-white/15 transition block">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs text-white/60">今日の予約</div>
                                    <div class="mt-1 text-3xl font-black">{{ number_format($todayReservationCount) }}</div>
                                </div>
                                <i data-lucide="arrow-up-right" class="w-5 h-5 text-white/55"></i>
                            </div>
                        </a>
                        <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">予約変更連絡</div><div class="mt-1 text-3xl font-black {{ $changeTotalActive > 0 ? 'text-rose-200' : '' }}">{{ number_format($changeTotalActive) }}</div></div>
                        @if($can('dashboard.sales'))
                            <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">今日の来店済み予約金額</div><div class="mt-1 text-3xl font-black">¥{{ number_format($todaySales) }}</div></div>
                        @else
                            <div class="rounded-2xl bg-white/10 border border-white/15 p-4"><div class="text-xs text-white/60">サポート回答</div><div class="mt-1 text-3xl font-black">{{ number_format($supportUnreadCount) }}</div></div>
                        @endif
                    </div>
                </div>
                <div class="xl:w-[430px]">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold tracking-[0.18em] text-white/55">TODAY'S TASKS</div>
                            <div class="mt-1 text-sm font-black text-white">今日やること</div>
                        </div>
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-white/80">
                            {{ $attentionTaskCount > 0 ? $attentionTaskCount.'件' : '対応済み' }}
                        </span>
                    </div>
                    <div class="action-panel space-y-3" aria-label="今日やること">
                        @if(($showSetupGuide ?? false) && $setupTotalCount > 0)
                            <a href="{{ url('/company/setup') }}" class="action-item">
                                <span class="flex items-center gap-3 min-w-0">
                                    <i data-lucide="alert-circle" class="w-5 h-5 text-amber-200 shrink-0"></i>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold">初期設定</span>
                                        <span class="block text-xs text-white/55 truncate">{{ $setupDoneCount }} / {{ $setupTotalCount }} 完了</span>
                                    </span>
                                </span>
                                <span class="action-count">{{ max($setupTotalCount - $setupDoneCount, 0) }}</span>
                            </a>
                        @endif

                        @if($changeTotalActive > 0)
                            <a href="{{ route('company.reservation_change_notices.index') }}" class="action-item">
                                <span class="flex items-center gap-3 min-w-0">
                                    <i data-lucide="refresh-cw" class="w-5 h-5 text-rose-200 shrink-0"></i>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold">予約変更連絡</span>
                                        <span class="block text-xs text-white/55 truncate">確認待ち・電話対応待ちがあります</span>
                                    </span>
                                </span>
                                <span class="action-count">{{ number_format($changeTotalActive) }}</span>
                            </a>
                        @endif

                        @if($supportUnreadCount > 0)
                            <a href="{{ route('company.support.index') }}" class="action-item">
                                <span class="flex items-center gap-3 min-w-0">
                                    <i data-lucide="message-circle" class="w-5 h-5 text-sky-200 shrink-0"></i>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold">サポート回答</span>
                                        <span class="block text-xs text-white/55 truncate">未読の回答があります</span>
                                    </span>
                                </span>
                                <span class="action-count">{{ number_format($supportUnreadCount) }}</span>
                            </a>
                        @endif

                        @if($hasBusinessAlert)
                            <a href="{{ route('company.calendar.index') }}" class="action-item">
                                <span class="flex items-center gap-3 min-w-0">
                                    <i data-lucide="calendar-days" class="w-5 h-5 text-amber-200 shrink-0"></i>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold">営業日設定</span>
                                        <span class="block text-xs text-white/55 truncate">予約可能期間の登録を確認してください</span>
                                    </span>
                                </span>
                                <span class="action-count">!</span>
                            </a>
                        @endif

                        @if($hasShiftAlert)
                            <a href="{{ route('company.staff-shifts') }}" class="action-item">
                                <span class="flex items-center gap-3 min-w-0">
                                    <i data-lucide="clock" class="w-5 h-5 text-amber-200 shrink-0"></i>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold">勤務日程</span>
                                        <span class="block text-xs text-white/55 truncate">シフト登録期間を確認してください</span>
                                    </span>
                                </span>
                                <span class="action-count">!</span>
                            </a>
                        @endif

                        @if(!($showSetupGuide ?? false) && $changeTotalActive === 0 && $supportUnreadCount === 0 && !$hasBusinessAlert && !$hasShiftAlert)
                            <div class="rounded-2xl border border-white/12 bg-white/8 px-4 py-6 text-center">
                                <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto text-emerald-200"></i>
                                <div class="mt-3 text-sm font-bold">対応が必要なものはありません</div>
                                <div class="mt-1 text-xs text-white/55">通常運用に集中できます。</div>
                            </div>
                        @endif
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
                @if($hasShiftAlert && $can('card.month_shift'))
                    <div class="card {{ ($shiftWarning['has_alert'] ?? false) ? 'border-red-200 bg-red-50/80' : 'border-amber-200 bg-amber-50/80' }}"><div class="flex items-start gap-3"><div class="card-icon"><i data-lucide="clock"></i></div><div class="flex-1"><h3 class="font-bold {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-900' : 'text-amber-900' }}">勤務日程{{ ($shiftWarning['has_alert'] ?? false) ? 'の警告' : 'のワーニング' }}</h3><p class="text-sm mt-1 {{ ($shiftWarning['has_alert'] ?? false) ? 'text-red-800' : 'text-amber-800' }}">従業員の勤務日程登録期間を確認してください。</p><div class="mt-3 text-sm text-gray-700 leading-6">本日：{{ $settingWarnings['today'] ?? '-' }}<br>予約可能期間の末日：{{ $settingWarnings['alert_end'] ?? '-' }}<br>登録済み最終日：<b>{{ $shiftWarning['last_date'] ?? '未登録' }}</b></div><a href="{{ route('company.staff-shifts') }}" class="inline-flex mt-4 px-4 py-2.5 rounded-2xl text-white font-bold" style="background: {{ $theme }}">勤務日程を設定する</a></div></div></div>
                @endif
            </div>
        @endif
        @if($hasChangeNoticeAlert ?? false)
            <div class="card change-notice-attention" role="alert" aria-labelledby="change-notice-alert-title">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                            <div class="change-notice-alert-icon">
                                <i data-lucide="bell-ring"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="change-notice-alert-badge">
                                        <span class="change-notice-alert-dot"></span>
                                        要対応
                                    </span>
                                    <span class="rounded-full border border-rose-300 bg-white px-3 py-1 text-xs font-black text-rose-800">
                                        未対応 {{ number_format($changeTotalActive) }}件
                                    </span>
                                </div>
                                <h2 id="change-notice-alert-title" class="mt-3 text-xl font-black text-rose-950 md:text-2xl">
                                    予約変更連絡の未対応があります
                                </h2>
                                <p class="mt-2 text-sm font-semibold leading-7 text-rose-900">
                                    お客様への連絡または確認が完了していません。内容を確認し、早めに対応してください。
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border-2 border-rose-300 bg-white px-4 py-3 shadow-sm">
                                <div class="text-xs font-black text-rose-700">確認待ち</div>
                                <div class="mt-1 text-3xl font-black text-rose-800">{{ number_format($changePending) }}<span class="ml-1 text-sm">件</span></div>
                            </div>
                            <div class="rounded-2xl border-2 border-amber-300 bg-amber-50 px-4 py-3 shadow-sm">
                                <div class="text-xs font-black text-amber-800">電話対応待ち</div>
                                <div class="mt-1 text-3xl font-black text-amber-800">{{ number_format($changePhonePending) }}<span class="ml-1 text-sm">件</span></div>
                            </div>
                            <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 px-4 py-3 shadow-sm">
                                <div class="text-xs font-black text-emerald-700">確認済み</div>
                                <div class="mt-1 text-3xl font-black text-emerald-800">{{ number_format($changeConfirmed) }}<span class="ml-1 text-sm">件</span></div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('company.reservation_change_notices.index') }}"
                       class="change-notice-action-button inline-flex min-h-14 shrink-0 items-center justify-center gap-2 rounded-2xl px-6 py-4 text-base font-black text-white transition focus:outline-none focus:ring-4 focus:ring-rose-300">
                        未対応を確認する
                        <i data-lucide="arrow-right" class="h-5 w-5"></i>
                    </a>
                </div>
            </div>
        @endif
    </div>

    @if($notices->isNotEmpty())
    <section aria-labelledby="company-notices-title"
             class="relative mb-6 overflow-hidden rounded-2xl border-2 border-amber-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-amber-200 bg-amber-50 px-4 py-3 md:px-5">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white">
                    <i data-lucide="megaphone" class="h-5 w-5"></i>
                </span>
                <div>
                    <h2 id="company-notices-title" class="text-base font-black text-slate-950">企業向けお知らせ</h2>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">見出しを選択すると詳細を確認できます。</p>
                </div>
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full border border-amber-300 bg-white px-2.5 py-1 text-xs font-bold text-amber-800">
                {{ $notices->count() }}件
            </span>
        </div>

        <div class="grid gap-2 p-3 md:grid-cols-2 md:p-4">
            @foreach($notices as $notice)
                <a href="{{ route('company.dashboard-notices.show', $notice) }}"
                   class="group flex min-w-0 items-center justify-between gap-3 rounded-xl border bg-white px-4 py-3 transition hover:border-amber-300 hover:bg-amber-50 {{ $notice->is_important ? 'border-rose-200' : 'border-slate-200' }}">
                    <h3 class="min-w-0 truncate text-sm font-bold text-slate-800 group-hover:text-amber-900">{{ $notice->title }}</h3>
                    <i data-lucide="chevron-right" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-amber-700"></i>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <div class="tab-category-heading">
        <div>
            <h2>すべての機能</h2>
            <p>目的に合ったカテゴリを選ぶと、利用できる機能が表示されます。</p>
        </div>
        <button type="button"
                @click="toggleFeatureCards()"
                :aria-expanded="showFeatureCards.toString()"
                class="feature-menu-toggle inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition">
            <i data-lucide="layout-grid" class="h-4 w-4"></i>
            <span x-text="showFeatureCards ? '機能メニューを閉じる' : '機能メニューを開く'"></span>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform duration-200" :class="showFeatureCards ? 'rotate-180' : ''"></i>
        </button>
    </div>

    <nav x-show="showFeatureCards" class="tab-nav mb-6" aria-label="機能メニュー">
        <div class="tab-nav-grid">
            @if($canAny(['card.reserve', 'card.customers']))
                <button type="button" data-dashboard-tab="daily" @click="tab='daily'" :class="tab==='daily' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="calendar-check"></i><span>日常業務</span><span class="tab-sub">予約・顧客</span>
                </button>
            @endif
            @if($canAny(['card.reviews', 'card.style', 'card.notices', 'card.reservation_change_notices', 'card.menu_category_tag', 'card.menu', 'card.menu_staff']))
                <button type="button" data-dashboard-tab="outreach" @click="tab='outreach'" :class="tab==='outreach' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="megaphone"></i><span>メニュー・発信</span><span class="tab-sub">商品・連絡</span>
                </button>
            @endif
            @if($canAny(['card.staff', 'card.vacation', 'card.my_profile', 'card.business_calendar', 'card.month_shift', 'card.month_shift_view', 'card.default_shift', 'card.shift_patterns']))
                <button type="button" data-dashboard-tab="staffwork" @click="tab='staffwork'" :class="tab==='staffwork' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="users"></i><span>スタッフ・勤務</span><span class="tab-sub">人員・シフト</span>
                </button>
            @endif
            @if($canAny(['card.billing', 'card.support']))
                <button type="button" data-dashboard-tab="support" @click="tab='support'" :class="tab==='support' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="badge-help"></i><span>契約・サポート</span><span class="tab-sub">プラン・QA</span>
                </button>
            @endif
            @if($canAny(['card.company_info', 'card.theme', 'card.logo', 'dashboard.manage']))
                <button type="button" data-dashboard-tab="settings" @click="tab='settings'" :class="tab==='settings' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="settings"></i><span>店舗設定</span><span class="tab-sub">情報・デザイン</span>
                </button>
            @endif
            @if($can('dashboard.sales'))
                <button type="button" data-dashboard-tab="analytics" @click="tab='analytics'" :class="tab==='analytics' ? 'tab-btn active' : 'tab-btn'">
                    <i data-lucide="bar-chart-3"></i><span>売上分析</span><span class="tab-sub">実績確認</span>
                </button>
            @endif
        </div>
    </nav>

    <div x-show="tab==='dashboard'" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-4">
            <a href="{{ $todayReservationListUrl }}" class="kpi flex items-center justify-between gap-4 hover:shadow-lg transition">
                <div>
                    <div class="metric-label">今日の予約</div>
                    <div class="text-4xl font-black mt-1">{{ number_format($todayReservationCount) }}</div>
                    <div class="text-xs text-gray-400 mt-1">クリックで予約一覧を表示</div>
                </div>
                <div class="card-icon"><i data-lucide="calendar"></i></div>
            </a>
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
            </div>
        </div>
    </div>

    <div x-show="showFeatureCards && tab==='daily'" class="feature-card-grid feature-group-daily grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>予約・顧客管理</h3><p>日々の予約確認、登録、顧客情報の管理を行います。</p></div></div>
        @if($can('card.reserve'))<a href="{{ route('company.reserve') }}" class="card card-link"><div class="card-icon"><i data-lucide="calendar-check"></i></div><div><div class="font-bold">予約カレンダー</div><div class="text-sm text-gray-500">空き状況の確認と予約登録</div></div></a>@endif
        @if($can('card.reserve'))<a href="{{ route('company.reservations.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="list-checks"></i></div><div><div class="font-bold">予約一覧</div><div class="text-sm text-gray-500">予約状況、来店済、キャンセル、無断キャンセルの管理</div></div></a>@endif
        @if($can('card.customers'))<a href="{{ route('company.customers') }}" class="card card-link"><div class="card-icon"><i data-lucide="users"></i></div><div><div class="font-bold">顧客管理</div><div class="text-sm text-gray-500">来店履歴・顧客情報の管理</div></div></a>@endif
    </div>

    @if($canAny(['card.reviews', 'card.style', 'card.notices', 'card.reservation_change_notices']))
    <div x-show="showFeatureCards && tab==='outreach'" class="feature-card-grid feature-group-outreach grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>発信・連絡</h3><p>お客様へのお知らせや対応状況を管理します。</p></div></div>
        @if($can('card.reviews') && ($company->review_enabled ?? false))<a href="{{ route('company.reviews.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="star"></i></div><div><div class="font-bold">口コミ管理</div><div class="text-sm text-gray-500">評価確認・返信対応</div></div></a>@endif
        @if($can('card.style'))<a href="{{ route('company.style-posts.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="image"></i></div><div><div class="font-bold">最新スタイル投稿</div><div class="text-sm text-gray-500">ヘアスタイルの発信</div></div></a>@endif
        @if($can('card.notices'))<a href="{{ route('company.notices.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="megaphone"></i></div><div><div class="font-bold">お知らせ情報管理</div><div class="text-sm text-gray-500">キャンペーン・重要告知</div></div></a>@endif
        @if($can('card.reservation_change_notices'))
            <a href="{{ route('company.reservation_change_notices.index') }}" class="feature-card-alert card card-link md:col-span-2">
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
    @endif

    @if($canAny(['card.business_calendar', 'card.month_shift', 'card.month_shift_view']))
    <div x-show="showFeatureCards && tab==='staffwork'" class="feature-card-grid feature-group-staff grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>日常操作</h3><p>営業日・営業時間の変更や、毎月のシフト登録・確認に使います。</p></div></div>
        @if($can('card.business_calendar'))<a href="{{ route('company.calendar.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="calendar"></i></div><div><div class="font-bold">営業日・営業時間管理</div><div class="text-sm text-gray-500">営業日・休業日・営業時間の変更</div></div></a>@endif
        @if($can('card.month_shift'))<a href="{{ route('company.staff-shifts') }}" class="card card-link"><div class="card-icon"><i data-lucide="clock"></i></div><div><div class="font-bold">勤務管理</div><div class="text-sm text-gray-500">日別シフト登録</div></div></a>@endif
        @if($can('card.month_shift_view'))<a href="{{ route('company.staff-shifts.view') }}" class="card card-link"><div class="card-icon"><i data-lucide="layout-grid"></i></div><div><div class="font-bold">スタッフ別シフト表</div><div class="text-sm text-gray-500">稼働状況の確認</div></div></a>@endif
    </div>
    @endif

    @if($canAny(['card.staff', 'card.vacation', 'card.my_profile']))
    <div x-show="showFeatureCards && tab==='staffwork'" class="feature-card-grid feature-group-staff grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>スタッフ管理</h3><p>スタッフ情報と個人設定を管理します。</p></div></div>
        @if($can('card.staff'))<a href="{{ route('company.staff.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="user"></i></div><div><div class="font-bold">担当者管理</div><div class="text-sm text-gray-500">スタッフ登録・権限管理</div></div></a>@endif
        @if($can('card.vacation'))<a href="{{ route('company.vacation.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="calendar-x"></i></div><div><div class="font-bold">休暇管理</div><div class="text-sm text-gray-500">休み・有給の設定</div></div></a>@endif
        @if($can('card.my_profile'))<a href="{{ route('company.my-profile') }}" class="card card-link"><div class="card-icon"><i data-lucide="settings"></i></div><div><div class="font-bold">マイプロフィール</div><div class="text-sm text-gray-500">個人設定・アカウント管理</div></div></a>@endif
    </div>
    @endif

    @if($canAny(['card.default_shift', 'card.shift_patterns']))
    <div x-show="showFeatureCards && tab==='staffwork'" class="feature-card-grid feature-group-prep grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>事前設定</h3><p>繰り返し利用する勤務ルールを設定します。</p></div></div>
        @if($can('card.shift_patterns'))<a href="{{ route('company.shift-patterns') }}" class="card card-link"><div class="card-icon"><i data-lucide="layers"></i></div><div><div class="font-bold">シフトパターン</div><div class="text-sm text-gray-500">勤務時間テンプレート</div></div></a>@endif
        @if($can('card.default_shift'))<a href="{{ route('company.staff-default-shifts') }}" class="card card-link"><div class="card-icon"><i data-lucide="repeat"></i></div><div><div class="font-bold">基本シフト</div><div class="text-sm text-gray-500">定期シフト設定</div></div></a>@endif
    </div>
    @endif

    @if($canAny(['card.menu_category_tag', 'card.menu', 'card.menu_staff']))
    <div x-show="showFeatureCards && tab==='outreach'" class="feature-card-grid feature-group-menu grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>メニュー設定</h3><p>予約で選択するメニューと担当スタッフを設定します。</p></div></div>
        @if($can('card.menu_category_tag'))<a href="{{ route('company.menu.settings') }}" class="card card-link"><div class="card-icon"><i data-lucide="tag"></i></div><div><div class="font-bold">カテゴリー・タグ管理</div><div class="text-sm text-gray-500">分類・検索用タグ設定</div></div></a>@endif
        @if($can('card.menu'))<a href="{{ route('company.menu.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="list"></i></div><div><div class="font-bold">メニュー管理</div><div class="text-sm text-gray-500">料金・施術時間の設定</div></div></a>@endif
        @if($can('card.menu_staff'))<a href="{{ route('company.menu-staff.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="users"></i></div><div><div class="font-bold">メニュー対応スタッフ設定</div><div class="text-sm text-gray-500">担当可能スタッフ設定</div></div></a>@endif
    </div>
    @endif

    <div x-show="showFeatureCards && tab==='support'" class="feature-card-grid feature-group-support grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>契約・サポート</h3><p>契約内容の確認や、操作についてのお問い合わせを行います。</p></div></div>
        @if($can('card.billing'))<a href="{{ route('company.billing.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="credit-card"></i></div><div><div class="font-bold">契約管理</div><div class="text-sm text-gray-500">プラン・支払い情報</div>@if($billingWarning)<div class="mt-2 text-xs text-amber-700">{{ $billingWarning }}</div>@endif</div></a>@endif
        @if($can('card.support'))<a href="{{ route('company.support.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="help-circle"></i></div><div><div class="font-bold">よくあるご質問・お問い合わせ</div><div class="text-sm text-gray-500">サポート・FAQ</div>@if($supportUnreadCount > 0)<span class="inline-flex mt-2 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">{{ $supportUnreadCount }}件</span>@endif</div></a>@endif
    </div>

    <div x-show="showFeatureCards && tab==='settings'" class="feature-card-grid feature-group-settings grid md:grid-cols-2 gap-4 mb-6">
        <div class="feature-group-heading md:col-span-2"><div><h3>店舗・画面設定</h3><p>企業情報、ロゴ、テーマ、カード表示権限を設定します。</p></div></div>
        @if($can('card.company_info'))<a href="{{ route('company.info.edit') }}" class="card card-link"><div class="card-icon"><i data-lucide="building"></i></div><div><div class="font-bold">企業情報編集</div><div class="text-sm text-gray-500">店舗情報・基本設定</div></div></a>@endif
        @if($can('card.theme'))<a href="{{ route('company.theme') }}" class="card card-link"><div class="card-icon"><i data-lucide="palette"></i></div><div><div class="font-bold">テーマ設定</div><div class="text-sm text-gray-500">カラー・UI調整</div></div></a>@endif
        @if($can('card.logo'))<a href="{{ route('company.logo') }}" class="card card-link"><div class="card-icon"><i data-lucide="image"></i></div><div><div class="font-bold">ロゴ設定</div><div class="text-sm text-gray-500">ブランド設定</div></div></a>@endif
        @if($can('dashboard.manage'))<a href="{{ route('company.dashboard-settings.index') }}" class="card card-link"><div class="card-icon"><i data-lucide="sliders-horizontal"></i></div><div><div class="font-bold">ダッシュボード管理</div><div class="text-sm text-gray-500">権限別カード設定</div></div></a>@endif
    </div>

    @if($can('dashboard.sales'))
        <div x-show="showFeatureCards && tab==='analytics'" class="space-y-5">
            <div class="card">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5"><div><h2 class="text-2xl font-black">売上分析</h2><p class="text-sm text-gray-600 mt-1">来店済み予約の金額と、今後の予約見込額を分けて表示します。</p></div></div>
                <form method="GET" action="{{ route('company.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <select name="period" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full"><option value="month" {{ $period=='month' ? 'selected':'' }}>月別</option><option value="year" {{ $period=='year' ? 'selected':'' }}>年別</option></select>
                    <select name="year" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">@for($y = now()->year; $y >= now()->year - 5; $y--)<option value="{{ $y }}" {{ $year==$y ? 'selected':'' }}>{{ $y }}年</option>@endfor</select>
                    <select name="month" class="border border-gray-300 rounded-2xl px-3 py-2.5 w-full">@for($m = 1; $m <= 12; $m++)<option value="{{ $m }}" {{ $month==$m ? 'selected':'' }}>{{ $m }}月</option>@endfor</select>
                    <button class="rounded-2xl text-white font-bold px-4 py-2.5" style="background: {{ $theme }}">表示</button>
                </form>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="kpi"><div class="metric-label">今日の来店済み予約金額</div><div class="mt-2 text-2xl font-black">¥{{ number_format($todaySales) }}</div></div>
                <div class="kpi"><div class="metric-label">{{ $salesPeriodLabel }}の来店済み予約金額</div><div class="mt-2 text-2xl font-black">¥{{ number_format($totalSales) }}</div><div class="mt-1 text-xs text-slate-500">{{ number_format($salesMetrics['completed_count']) }}件</div></div>
                <div class="kpi kpi-forecast"><div class="metric-label">{{ $salesPeriodLabel }}の今後の予約見込額</div><div class="mt-2 text-2xl font-black text-sky-900">¥{{ number_format($salesMetrics['forecast_amount']) }}</div><div class="mt-1 text-xs text-sky-700">{{ number_format($salesMetrics['forecast_count']) }}件</div></div>
                <div class="kpi"><div class="metric-label">来店済み予約の平均金額</div><div class="mt-2 text-2xl font-black">¥{{ number_format($averagePrice) }}</div></div>
                <div class="kpi"><div class="metric-label">今年の来店済み予約金額</div><div class="mt-2 text-2xl font-black">¥{{ number_format($yearlySales) }}</div></div>
            </div>
            <p class="px-1 text-xs leading-5 text-slate-600">金額は予約に登録された価格です。会計・入金が確定した実売上ではありません。見込額は現在時刻以降の「予約済み」のみを集計します。</p>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="card" aria-label="前月・前年との比較">
                    <h3 class="section-title mb-1">来店済み予約金額の比較</h3>
                    <p class="mb-4 text-xs text-slate-600">{{ $salesPeriodLabel }}と比較します。進行中の期間は同じ日時までで比較します。</p>
                    <div class="space-y-3">
                        @foreach($salesComparisons as $comparison)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div><div class="text-xs font-bold text-slate-600">{{ $comparison['label'] }}</div><div class="mt-1 font-black text-slate-900">¥{{ number_format($comparison['amount']) }}</div></div>
                                <div class="text-right"><div class="text-sm font-black {{ $comparison['change_amount'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ $comparison['change_amount'] >= 0 ? '+' : '−' }}¥{{ number_format(abs($comparison['change_amount'])) }}</div><div class="mt-1 text-xs font-bold text-slate-600">{{ $comparison['change_rate'] === null ? '比較対象なし' : ($comparison['change_rate'] >= 0 ? '+' : '−') . number_format(abs($comparison['change_rate']), 1) . '%' }}</div></div>
                            </div>
                        @endforeach
                    </div>
                </section>
                <section class="card" aria-label="キャンセル・無断キャンセル">
                    <h3 class="section-title mb-1">キャンセル状況</h3>
                    <p class="mb-4 text-xs text-slate-600">{{ $salesPeriodLabel }}の予約総数 {{ number_format($salesMetrics['reservation_count']) }}件に対する割合です。</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border-2 border-amber-200 bg-amber-50 p-4"><div class="text-sm font-black text-amber-900">キャンセル</div><div class="mt-2 text-2xl font-black text-amber-950">{{ number_format($salesMetrics['cancelled_count']) }}<span class="ml-1 text-sm">件</span></div><div class="mt-1 text-sm font-bold text-amber-800">{{ number_format($salesMetrics['cancelled_rate'], 1) }}%</div></div>
                        <div class="rounded-xl border-2 border-rose-200 bg-rose-50 p-4"><div class="text-sm font-black text-rose-900">無断キャンセル</div><div class="mt-2 text-2xl font-black text-rose-950">{{ number_format($salesMetrics['no_show_count']) }}<span class="ml-1 text-sm">件</span></div><div class="mt-1 text-sm font-bold text-rose-800">{{ number_format($salesMetrics['no_show_rate'], 1) }}%</div></div>
                    </div>
                </section>
            </div>
            <div class="card"><h3 class="section-title mb-1">来店済み予約金額・今後の予約見込額（{{ $year }}年）</h3><p class="mb-4 text-xs text-slate-600">月ごとの予約日を基準に表示します。</p><div class="w-full overflow-x-auto"><div class="min-w-[560px]"><canvas id="salesChart"></canvas></div></div></div>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="card"><h3 class="section-title mb-4">担当者別 来店済み予約金額</h3><div class="space-y-2">@forelse($staffRanking as $i => $row)<div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2"><span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->staff->name ?? '未設定' }}</span><span class="text-sm font-bold whitespace-nowrap">¥{{ number_format($row->total) }}</span></div>@empty<div class="text-sm text-gray-400">データがありません</div>@endforelse</div></div>
                <div class="card"><h3 class="section-title mb-4">指名ランキング</h3><div class="space-y-2">@forelse($nominationRanking as $i => $row)<div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2"><span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->staff->name ?? '未設定' }}</span><span class="text-sm font-bold whitespace-nowrap">{{ $row->nomination_count }}回</span></div>@empty<div class="text-sm text-gray-400">データがありません</div>@endforelse</div></div>
                <div class="card"><h3 class="section-title mb-4">人気メニュー（来店済み）</h3><div class="space-y-2">@forelse($menuRanking as $i => $row)<div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2"><span class="text-sm text-gray-700">{{ $i + 1 }}. {{ $row->name }}</span><span class="text-sm font-bold whitespace-nowrap">{{ $row->total }}回</span></div>@empty<div class="text-sm text-gray-400">データがありません</div>@endforelse</div></div>
            </div>
        </div>
    @endif
</div>

<script>
const salesLabels = @json($monthlyChart->pluck('month')->values());
const completedSalesData = @json($monthlyChart->pluck('completed_amount')->values());
const forecastSalesData = @json($monthlyChart->pluck('forecast_amount')->values());
@if($can('dashboard.sales'))
const salesCanvas = document.getElementById('salesChart');
if (salesCanvas) {
    new Chart(salesCanvas, {
        type: 'bar',
        data: {
            labels: salesLabels.map((m) => m + '月'),
            datasets: [
                { label: '来店済み予約金額', data: completedSalesData, backgroundColor: '{{ $theme }}', borderRadius: 10, maxBarThickness: 28 },
                { label: '今後の予約見込額', data: forecastSalesData, backgroundColor: '#7dd3fc', borderRadius: 10, maxBarThickness: 28 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: true, position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { callback: (value) => '¥' + Number(value).toLocaleString() } } }
        }
    });
}
@endif
</script>
@endsection
