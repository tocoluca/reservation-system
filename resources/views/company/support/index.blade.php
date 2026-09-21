@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '18';

    $openCount = $inquiries->where('status', 'open')->count();
    $answeredCount = $inquiries->where('status', 'answered')->count();
    $closedCount = $inquiries->where('status', 'closed')->count();

    $manualCards = [
        ['key' => 'first', 'label' => '初回設定', 'title' => '最初にやること', 'icon' => 'flag', 'desc' => '予約を受ける前に必要な設定を順番に確認します。'],
        ['key' => 'daily', 'label' => '通常運用', 'title' => '毎日の操作', 'icon' => 'calendar-check', 'desc' => '予約確認、変更、シフト確認など日々使う操作です。'],
        ['key' => 'features', 'label' => '機能説明', 'title' => '各機能の役割', 'icon' => 'layout-dashboard', 'desc' => 'どの画面で何を設定するのかを一覧で確認できます。'],
    ];

    $featureRoutes = [
        '予約カレンダー' => 'company.reserve', '予約一覧' => 'company.reservations.index',
        '顧客管理' => 'company.customers', '担当者管理' => 'company.staff.index',
        'マイプロフィール' => 'company.my-profile', '休暇管理' => 'company.vacation.index',
        '営業日・営業時間管理' => 'company.calendar.index', '勤務管理' => 'company.staff-shifts',
        'スタッフ別シフト表' => 'company.staff-shifts.view', 'シフトパターン' => 'company.shift-patterns',
        '基本シフト' => 'company.staff-default-shifts', 'カテゴリー・タグ管理' => 'company.menu.settings',
        'メニュー管理' => 'company.menu.index', 'メニュー対応スタッフ設定' => 'company.menu-staff.index',
        'お知らせ情報管理' => 'company.notices.index', '口コミ管理' => 'company.reviews.index',
        '最新スタイル投稿' => 'company.style-posts.index', '予約変更連絡管理' => 'company.reservation_change_notices.index',
        '売上分析' => 'company.dashboard', '契約管理' => 'company.billing.index',
        'ロゴ設定' => 'company.logo', 'テーマ設定' => 'company.theme',
        'ダッシュボード管理' => 'company.dashboard-settings.index',
        'よくあるご質問・お問い合わせ' => 'company.support.index',
    ];

    $faqCategories = [
        'reservation' => [
            'label' => '予約・顧客',
            'icon' => 'calendar-check',
            'items' => [
                ['予約画面にスタッフが表示されません', ['担当者管理で対象スタッフが予約対象として有効か確認します。', '営業日・営業時間管理で対象日が営業日か確認します。', '勤務管理で対象日の勤務時間を確認します。', 'メニュー対応スタッフ設定で選択したメニューを担当できるか確認します。'], [['担当者管理', 'company.staff.index'], ['営業日・営業時間管理', 'company.calendar.index'], ['勤務管理', 'company.staff-shifts'], ['メニュー対応スタッフ設定', 'company.menu-staff.index']]],
                ['予約できるはずの日付が選べません', ['営業日・営業時間管理で対象日を確認します。', '勤務管理で担当スタッフの勤務時間を確認します。', '企業情報編集で予約受付開始・予約可能期間を確認します。', 'メニューの所要時間が営業時間と勤務時間に収まるか確認します。'], [['営業日・営業時間管理', 'company.calendar.index'], ['勤務管理', 'company.staff-shifts'], ['企業情報編集', 'company.info.edit']]],
                ['お客様がWebからキャンセルできません', ['予約日時に対するWebキャンセル締切を確認します。', '企業情報編集の「Webキャンセル締切」を確認します。', '期限を過ぎた場合は店舗側の予約一覧または予約カレンダーから対応します。'], [['企業情報編集', 'company.info.edit'], ['予約一覧', 'company.reservations.index']]],
                ['電話や来店で受けた予約はどこから登録しますか', ['予約カレンダーで空いている日時を選びます。', 'メニュー、担当スタッフ、お客様情報を入力して保存します。', '保存後、予約一覧でも確認できます。'], [['予約カレンダー', 'company.reserve'], ['予約一覧', 'company.reservations.index']]],
                ['営業時間や休業日を変更した後、既存予約はどう確認しますか', ['営業日・営業時間管理で対象日を変更します。', '当日以降の予約に影響する場合は、予約変更連絡管理で対応が必要な予約を確認します。', 'お客様への連絡と確認状況を記録します。'], [['営業日・営業時間管理', 'company.calendar.index'], ['予約変更連絡管理', 'company.reservation_change_notices.index']]],
            ],
        ],
        'staff' => [
            'label' => 'スタッフ・シフト',
            'icon' => 'users',
            'items' => [
                ['スタッフが急に休む場合はどうすればよいですか', ['予約一覧または予約カレンダーで、そのスタッフの予約を先に確認します。', '必要な予約変更・キャンセルとお客様への連絡を行い、予約変更連絡管理の未対応も確認します。', '勤務管理で該当日のシフトを変更します。'], [['予約一覧', 'company.reservations.index'], ['予約変更連絡管理', 'company.reservation_change_notices.index'], ['勤務管理', 'company.staff-shifts']]],
                ['シフトを登録したのに予約枠が出ません', ['営業日・営業時間管理で対象日を確認します。', '勤務管理でスタッフの勤務時間を確認します。', 'メニュー対応スタッフ設定で担当可能なメニューを確認します。', '施術時間を確保できる空き時間があるか確認します。'], [['営業日・営業時間管理', 'company.calendar.index'], ['勤務管理', 'company.staff-shifts'], ['メニュー対応スタッフ設定', 'company.menu-staff.index']]],
                ['スタッフのパスワードを忘れました', ['権限のある担当者に初期化を依頼します。', '担当者管理で対象スタッフを開き、パスワードを初期化します。', '初期化後は新しいパスワードへ変更します。'], [['担当者管理', 'company.staff.index']]],
            ],
        ],
        'settings' => [
            'label' => '設定・表示',
            'icon' => 'settings',
            'items' => [
                ['設定を変更しても画面に反映されません', ['対象画面の保存ボタンを押したか確認します。', '画面を再読み込みします。', '営業時間は企業情報編集の通常設定と、日付ごとの営業日・営業時間管理の両方を確認します。', '解決しない場合は画面名と操作内容を添えてお問い合わせください。'], [['企業情報編集', 'company.info.edit'], ['営業日・営業時間管理', 'company.calendar.index']]],
                ['ロゴやテーマカラーはどこで変更できますか', ['ダッシュボードの店舗・画面設定からロゴ設定またはテーマ設定を開きます。', '保存後、お客様向け予約画面で見え方を確認します。'], [['ロゴ設定', 'company.logo'], ['テーマ設定', 'company.theme']]],
                ['役職によってダッシュボードの項目を変えられますか', ['ダッシュボード管理で役職ごとの表示項目を設定します。', '変更を保存すると、対象役職のダッシュボードに反映されます。'], [['ダッシュボード管理', 'company.dashboard-settings.index']]],
                ['メニューが予約画面に表示されません', ['メニュー管理で公開状態、料金、施術時間を確認します。', 'カテゴリー・タグ管理でカテゴリーを確認します。', 'メニュー対応スタッフ設定で担当者との対応関係を確認します。'], [['メニュー管理', 'company.menu.index'], ['カテゴリー・タグ管理', 'company.menu.settings'], ['メニュー対応スタッフ設定', 'company.menu-staff.index']]],
                ['カテゴリー内のメニューを担当者にまとめて設定できますか', ['メニュー対応スタッフ設定で担当者の列を確認します。', 'カテゴリーのチェックを入れると、そのカテゴリーに所属するメニューをまとめて選択できます。', '保存して対応関係一覧で確認します。'], [['メニュー対応スタッフ設定', 'company.menu-staff.index']]],
            ],
        ],
        'support' => [
            'label' => 'アカウント・サポート',
            'icon' => 'circle-help',
            'items' => [
                ['問い合わせへの回答はどこで確認できますか', ['この画面上部の「問い合わせ履歴」を開きます。', '回答済みの問い合わせの詳細を開きます。', '回答を確認すると、ダッシュボードの未読件数にも反映されます。'], [['問い合わせ履歴', 'company.support.index', ['view' => 'history']]]],
                ['問い合わせには何を書けばよいですか', ['発生した日時と画面名を書きます。', '対象の予約日、スタッフ名、メニュー名を書きます。', '行った操作と実際に表示された内容を書きます。', '個人のパスワードやカード番号は記載しないでください。']],
                ['操作中にエラーが表示されました', ['画面に表示されたエラーメッセージを控えます。', '入力内容を確認し、もう一度保存します。', '同じエラーが続く場合は、画面名とメッセージを添えてお問い合わせください。']],
            ],
        ],
    ];

    $allFaqSearchParts = [];
    foreach ($faqCategories as $category) {
        foreach ($category['items'] as $faq) {
            $allFaqSearchParts[] = $faq[0];
            $allFaqSearchParts = array_merge($allFaqSearchParts, $faq[1]);
        }
    }
    $allFaqSearchText = mb_strtolower(implode(' ', $allFaqSearchParts));
@endphp

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>

<style>
body {
    background:
        radial-gradient(circle at top left, {{ $theme }}20, transparent 32rem),
        linear-gradient(180deg, #f8f6f1 0%, #eef2f7 46%, #f8fafc 100%);
}
.support-hero {
    background:
        radial-gradient(circle at top right, {{ $theme }}70, transparent 24rem),
        linear-gradient(135deg, #111827 0%, #1f2937 52%, #0f172a 100%);
    border: 1px solid rgba(255,255,255,.18);
    box-shadow: 0 28px 78px rgba(15,23,42,.24);
}
.manual-card, .panel-card {
    border-radius: 24px;
    background: rgba(255,255,255,.82);
    border: 1px solid rgba(255,255,255,.68);
    box-shadow: 0 14px 38px rgba(15,23,42,.08);
    backdrop-filter: blur(16px);
}
.manual-tab {
    border-radius: 18px;
    padding: 14px;
    background: rgba(255,255,255,.78);
    border: 1px solid rgba(148,163,184,.18);
    text-align: left;
}
.manual-tab.active {
    background: #111827;
    color: white;
    box-shadow: 0 18px 40px rgba(15,23,42,.22);
}
.step-number {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    background: {{ $theme }};
    flex: none;
}
.screen-guide { border: 1px solid #cbd5e1; border-radius: 20px; overflow: hidden; background: #f8fafc; box-shadow: 0 12px 30px rgba(15,23,42,.08); }
.screen-guide-head { display: flex; justify-content: space-between; gap: 12px; padding: 12px 16px; background: #1e293b; color: white; font-size: 12px; font-weight: 800; }
.screen-guide-body { padding: 16px; }
.manual-links { display: flex; flex-wrap: wrap; gap: 8px; }
.support-deep-link { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; padding: 7px 11px; border: 1px solid #bfdbfe; border-radius: 11px; background: #eff6ff; color: #1e40af; font-size: 12px; font-weight: 800; text-decoration: none; transition: background .15s, border-color .15s, transform .15s; }
.support-deep-link:hover { background: #dbeafe; border-color: #60a5fa; transform: translateY(-1px); }
.support-deep-link:focus-visible { outline: 3px solid #60a5fa; outline-offset: 2px; }
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item[open] summary [data-lucide="chevron-down"] { transform: rotate(180deg); }
.faq-item summary [data-lucide="chevron-down"] { transition: transform .18s ease; }
.faq-search:focus { border-color: {{ $theme }}; box-shadow: 0 0 0 3px {{ $themeSoft }}; outline: none; }
[x-cloak] { display: none !important; }
</style>

<div x-data="{ section: 'first', supportView: @js($errors->any() ? 'contact' : ((session('success') || request('view') === 'history') ? 'history' : 'manual')), faqQuery: '', faqCategory: 'all' }"
     class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">
    <section class="support-hero rounded-[2rem] overflow-hidden text-white">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3 py-1.5 text-xs font-bold tracking-[0.18em] text-white/75">
                        SUPPORT CENTER
                    </div>
                    <h1 class="mt-5 text-2xl sm:text-4xl font-black">操作マニュアル・よくある質問</h1>
                    <p class="mt-3 text-sm sm:text-base text-white/72 leading-7 max-w-3xl">
                        初めて使う方でも、設定から日々の操作まで順番に確認できます。各項目から対象画面へ移動できます。
                    </p>
                </div>
                <a href="{{ route('company.dashboard') }}"
                   class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white/12 hover:bg-white/18 border border-white/15 text-sm font-bold transition">
                    ダッシュボードへ戻る
                </a>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm bg-emerald-50 border-emerald-200 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm bg-red-50 border-red-200 text-red-700" role="alert">
            <p class="font-bold">入力内容を確認してください。</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="sticky z-30 rounded-[1.5rem] border border-white/80 bg-white/95 p-2 shadow-lg backdrop-blur"
         style="top: calc(var(--company-topbar-height, 6rem) + .75rem);">
        <div class="grid grid-cols-4 gap-2">
            <button type="button" @click="supportView='manual'"
                    :class="supportView === 'manual' ? 'text-white bg-slate-900' : 'text-slate-600 bg-slate-100'"
                    class="rounded-2xl px-3 py-3 text-xs sm:text-sm font-black transition">
                <span class="hidden sm:inline">操作マニュアル</span><span class="sm:hidden">使い方</span>
            </button>
            <button type="button" @click="supportView='faq'"
                    :class="supportView === 'faq' ? 'text-white bg-slate-900' : 'text-slate-600 bg-slate-100'"
                    class="rounded-2xl px-2 sm:px-3 py-3 text-xs sm:text-sm font-black transition">
                FAQ
            </button>
            <button type="button" @click="supportView='contact'"
                    :class="supportView === 'contact' ? 'text-white bg-slate-900' : 'text-slate-600 bg-slate-100'"
                    class="rounded-2xl px-3 py-3 text-xs sm:text-sm font-black transition">
                <span class="hidden sm:inline">問い合わせ</span><span class="sm:hidden">質問する</span>
            </button>
            <button type="button" @click="supportView='history'"
                    :class="supportView === 'history' ? 'text-white bg-slate-900' : 'text-slate-600 bg-slate-100'"
                    class="rounded-2xl px-3 py-3 text-xs sm:text-sm font-black transition">
                <span class="hidden sm:inline">問い合わせ履歴 </span><span class="sm:hidden">履歴 </span>{{ $inquiries->total() }}件
            </button>
        </div>
    </div>

    <div x-show="supportView === 'history'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="panel-card p-5"><div class="text-sm text-gray-500">受付中</div><div class="mt-2 flex items-end justify-between"><div class="text-3xl font-black text-amber-600">{{ $openCount }}</div><span class="rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-bold">open</span></div></div>
        <div class="panel-card p-5"><div class="text-sm text-gray-500">回答済み</div><div class="mt-2 flex items-end justify-between"><div class="text-3xl font-black text-emerald-600">{{ $answeredCount }}</div><span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">answered</span></div></div>
        <div class="panel-card p-5"><div class="text-sm text-gray-500">完了</div><div class="mt-2 flex items-end justify-between"><div class="text-3xl font-black text-gray-600">{{ $closedCount }}</div><span class="rounded-full bg-gray-200 text-gray-700 px-3 py-1 text-xs font-bold">closed</span></div></div>
    </div>

    <section x-show="supportView === 'manual'" x-cloak class="manual-card p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-gray-900">操作マニュアル</h2>
                <p class="text-sm text-gray-500 mt-1">目的を選ぶと、現在の操作手順と画面構成の案内が表示されます。</p>
            </div>
        </div>
        <p class="mb-5 text-xs text-slate-600">※ 役職や契約状態により、リンク先の画面を利用できない場合があります。</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-6">
            @foreach($manualCards as $card)
                <button type="button"
                        @click="section='{{ $card['key'] }}'"
                        :class="section==='{{ $card['key'] }}' ? 'manual-tab active' : 'manual-tab'">
                    <div class="flex items-start gap-3">
                        <i data-lucide="{{ $card['icon'] }}" class="w-5 h-5 mt-0.5"></i>
                        <div>
                            <div class="text-xs font-bold opacity-70">{{ $card['label'] }}</div>
                            <div class="font-black mt-1">{{ $card['title'] }}</div>
                            <div class="text-xs mt-1 opacity-70">{{ $card['desc'] }}</div>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

                <div x-show="section==='first'" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-black text-gray-900">初回にやっておく操作</h3>
                <p class="text-sm text-gray-500 leading-7">上から順番に進めると、予約受付に必要な設定がそろいます。各項目は「保存」まで行ってから次へ進んでください。</p>
                @foreach([
                    ['企業情報を確認する', ['店舗名と連絡先、予約受付開始・予約可能期間、Webキャンセル締切を確認します。', '曜日別営業時間は通常営業する可能性がある曜日の時間枠です。休業日は営業日・営業時間管理で設定します。'], [['企業情報編集', 'company.info.edit']]],
                    ['ロゴとテーマを設定する', ['ロゴ設定で店舗ロゴ、テーマ設定で画面カラーを保存します。', 'お客様向け予約画面で見え方を確認します。'], [['ロゴ設定', 'company.logo'], ['テーマ設定', 'company.theme']]],
                    ['担当者を登録する', ['担当者管理で予約対象のスタッフを登録します。', '表示名、写真、指名可否、権限を確認します。'], [['担当者管理', 'company.staff.index']]],
                    ['カテゴリー・タグを用意する', ['カテゴリー・タグ管理でメニューの分類を作ります。', '予約画面ではカテゴリーごとにメニューを探せます。'], [['カテゴリー・タグ管理', 'company.menu.settings']]],
                    ['メニューを登録する', ['メニュー管理で名前、料金、施術時間、公開状態を確認します。', 'お客様に見せる説明と表示順も整えます。'], [['メニュー管理', 'company.menu.index']]],
                    ['メニュー対応スタッフを設定する', ['担当者ごとに対応できるメニューをチェックします。', 'カテゴリー単位のチェックで、そのカテゴリー内のメニューをまとめて切り替えられます。'], [['メニュー対応スタッフ設定', 'company.menu-staff.index']]],
                    ['シフトパターンと基本シフトを作る', ['シフトパターンで勤務時間の選択肢を作ります。', '基本シフトで曜日ごとの標準勤務を登録します。'], [['シフトパターン', 'company.shift-patterns'], ['基本シフト', 'company.staff-default-shifts']]],
                    ['営業日・営業時間を設定する', ['営業日カレンダーで休業日と日付ごとの営業時間を確認します。', '実際の月のシフトを入力する前に、予約受付期間内の休日を設定します。'], [['営業日・営業時間管理', 'company.calendar.index']]],
                    ['勤務管理で実際のシフトを入れる', ['勤務管理で対象月のスタッフ別シフトを作成・調整します。', '営業日、勤務時間、メニュー対応がそろうと予約枠を出せます。'], [['勤務管理', 'company.staff-shifts']]],
                    ['予約枠を確認する', ['予約カレンダーで日付、メニュー、担当者の選択と空き枠を確認します。', '実際の予約は予約一覧で確認できます。'], [['予約カレンダー', 'company.reserve'], ['予約一覧', 'company.reservations.index']]],
                ] as $i => $step)
                    <details class="faq-item rounded-2xl border border-gray-100 bg-white p-4" @if($i === 0) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="step-number">{{ $i + 1 }}</span>
                                <span class="font-bold text-gray-900">{{ $step[0] }}</span>
                            </span>
                            <i data-lucide="chevron-down" class="h-4 w-4 shrink-0 text-gray-400"></i>
                        </summary>
                        <ul class="ml-11 mt-3 space-y-1.5 text-sm leading-6 text-gray-600">
                            @foreach($step[1] as $detail)
                                <li class="flex gap-2"><span class="text-gray-300">●</span><span>{{ $detail }}</span></li>
                            @endforeach
                        </ul>
                        <div class="manual-links ml-11 mt-4">
                            @foreach($step[2] as $target)
                                <a href="{{ route($target[1], $target[2] ?? []) }}" class="support-deep-link"><i data-lucide="arrow-up-right" class="h-4 w-4"></i>{{ $target[0] }}を開く</a>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
            <div class="space-y-4">
                <div class="screen-guide">
                    <div class="screen-guide-head"><span>現在の画面構成</span><span>初回設定の順番</span></div>
                    <div class="screen-guide-body">
                        <p class="text-sm font-bold text-slate-800">ダッシュボードから各設定画面へ</p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach([['店舗・画面設定', '企業情報編集 / ロゴ設定 / テーマ設定'], ['スタッフ管理', '担当者管理'], ['メニュー設定', 'カテゴリー・タグ / メニュー / 対応スタッフ'], ['日常操作', '営業日・営業時間管理 / 勤務管理']] as $area)
                                <div class="rounded-xl border border-slate-200 bg-white p-3"><div class="text-xs font-black text-slate-500">{{ $area[0] }}</div><div class="mt-1 text-sm font-semibold text-slate-800">{{ $area[1] }}</div></div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm font-semibold text-sky-900">営業日・休業日を決めてから、勤務管理でシフトを入力します。</div>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="section==='daily'" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-black text-gray-900">通常運用でよく使う操作</h3>
                @foreach([
                    ['朝に確認する', 'ダッシュボードで今日・明日の予約と、予約変更連絡の未対応、サポート回答を確認します。', [['ダッシュボード', 'company.dashboard'], ['予約変更連絡管理', 'company.reservation_change_notices.index']]],
                    ['予約を確認・変更する', '予約一覧で状態を確認し、予約カレンダーで日時・担当者を確認します。店舗都合の変更・キャンセル後は連絡管理も確認します。', [['予約一覧', 'company.reservations.index'], ['予約カレンダー', 'company.reserve']]],
                    ['スタッフが急に休んだ時', '該当日の予約を先に確認し、必要な変更とお客様への連絡を行います。その後、勤務管理でシフトを調整します。', [['予約一覧', 'company.reservations.index'], ['勤務管理', 'company.staff-shifts']]],
                    ['電話・来店予約を登録する', '予約カレンダーの空き枠からメニュー、担当者、お客様情報を入力して保存します。', [['予約カレンダー', 'company.reserve']]],
                    ['営業日・営業時間を変える', '営業日カレンダーで対象日を選び、休業日や営業時間を変更します。予約がある当日以降の日付では連絡管理も確認します。', [['営業日・営業時間管理', 'company.calendar.index'], ['予約変更連絡管理', 'company.reservation_change_notices.index']]],
                    ['シフトを更新する', '営業日を確認してから勤務管理でスタッフ別の勤務時間を入力・調整します。', [['営業日・営業時間管理', 'company.calendar.index'], ['勤務管理', 'company.staff-shifts']]],
                    ['お客様向けのお知らせを出す', 'お知らせ情報管理で内容と表示期間を設定します。', [['お知らせ情報管理', 'company.notices.index']]],
                ] as $task)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4"><div class="font-bold text-slate-900">{{ $task[0] }}</div><p class="mt-1 text-sm leading-6 text-slate-600">{{ $task[1] }}</p><div class="manual-links mt-3">@foreach($task[2] as $target)<a href="{{ route($target[1]) }}" class="support-deep-link"><i data-lucide="arrow-up-right" class="h-4 w-4"></i>{{ $target[0] }}を開く</a>@endforeach</div></div>
                @endforeach
            </div>
            <div class="space-y-4">
                <div class="screen-guide"><div class="screen-guide-head"><span>現在の画面構成</span><span>日々の操作</span></div><div class="screen-guide-body space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-xs font-black text-slate-500">ダッシュボード › 日常操作</div><div class="mt-2 flex flex-wrap gap-2 text-xs font-bold"><span class="rounded-lg bg-sky-50 px-3 py-2 text-sky-900">営業日・営業時間管理</span><span class="rounded-lg bg-sky-50 px-3 py-2 text-sky-900">勤務管理</span><span class="rounded-lg bg-sky-50 px-3 py-2 text-sky-900">スタッフ別シフト表</span></div></div>
                    <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-xs font-black text-slate-500">ダッシュボード › 予約・顧客管理</div><div class="mt-2 flex flex-wrap gap-2 text-xs font-bold"><span class="rounded-lg bg-indigo-50 px-3 py-2 text-indigo-900">予約カレンダー</span><span class="rounded-lg bg-indigo-50 px-3 py-2 text-indigo-900">予約一覧</span><span class="rounded-lg bg-indigo-50 px-3 py-2 text-indigo-900">顧客管理</span></div></div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-900">予約変更連絡に未対応がある場合は「発信・連絡」から確認します。</div>
                </div></div>
                <p class="text-xs leading-5 text-slate-600">※ 旧スクリーンショットに代えて、現在のダッシュボードの分類を示しています。</p>
            </div>
        </div>
        <div x-show="section==='features'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach([
                ['予約カレンダー', '空き枠の確認と、電話・来店で受けた予約の登録を行います。'],
                ['予約一覧', '予約の状態や詳細、来店済み・キャンセルを確認します。'],
                ['顧客管理', '来店履歴や顧客情報を確認します。過去の予約内容を見たい時にも使います。'],
                ['担当者管理', 'スタッフの登録、予約対象の設定、表示名や権限を管理します。'],
                ['マイプロフィール', 'ログイン中の担当者自身の情報や個人設定を確認・変更します。自分のパスワード変更もここから行えます。'],
                ['休暇管理', 'スタッフの休みや有給など、通常シフトとは別の休暇情報を登録します。'],
                ['営業日・営業時間管理', '営業日カレンダーで休業日と日付ごとの営業時間を設定します。シフト入力より先に確認します。'],
                ['勤務管理', 'スタッフごとの勤務日、勤務時間を登録します。予約枠を作るために必要です。'],
                ['スタッフ別シフト表', 'スタッフごとの稼働状況を一覧で確認します。誰がいつ出勤しているかを見る画面です。'],
                ['シフトパターン', '早番、遅番、通常勤務など、よく使う勤務時間のテンプレートを作ります。勤務管理の入力を楽にします。'],
                ['基本シフト', '曜日ごとの標準的な勤務予定を登録します。毎週同じ勤務が多い場合に便利です。'],
                ['カテゴリー・タグ管理', 'メニューを分類するためのカテゴリーや検索用タグを管理します。お客様がメニューを探しやすくなります。'],
                ['メニュー管理', 'メニュー名、料金、施術時間、説明文、表示順を設定します。予約時にお客様が選ぶ内容です。'],
                ['メニュー対応スタッフ設定', 'メニューごとに担当できるスタッフを設定します。スタッフが予約画面に出ない時の重要確認ポイントです。'],
                ['お知らせ情報管理', '予約画面に表示するキャンペーン、休業案内、注意事項を登録します。'],
                ['口コミ管理', 'お客様からの口コミや評価を確認し、必要に応じて返信します。'],
                ['最新スタイル投稿', 'スタイル写真やおすすめ事例を投稿します。お客様へのアピールに使います。'],
                ['予約変更連絡管理', '店舗都合で予約変更が必要な場合の連絡状況を管理します。未対応を残さないための画面です。'],
                ['売上分析', 'ダッシュボードの売上分析タブで来店済み予約金額、予約見込額、キャンセル状況、曜日・時間帯別の予約状況を確認します。'],
                ['契約管理', 'プラン、支払い状況、請求関連の情報を確認します。'],
                ['ロゴ設定', '店舗ロゴを登録・変更します。予約画面や管理画面のブランド表示に使います。'],
                ['テーマ設定', '画面の基調カラーを設定します。店舗の雰囲気に合わせて見た目を整えます。'],
                ['ダッシュボード管理', '役職や権限ごとに、ダッシュボードで表示するカードを設定します。'],
                ['よくあるご質問・お問い合わせ', '操作マニュアルの確認、困った時の確認順、サポートへの問い合わせを行います。'],
            ] as $feature)
                <div class="rounded-2xl border border-slate-200 bg-white p-4"><div class="font-black text-gray-900">{{ $feature[0] }}</div><div class="mt-2 text-sm leading-6 text-gray-600">{{ $feature[1] }}</div><div class="manual-links mt-4"><a href="{{ route($featureRoutes[$feature[0]], $feature[0] === '売上分析' ? ['period' => 'month'] : []) }}" class="support-deep-link"><i data-lucide="arrow-up-right" class="h-4 w-4"></i>画面を開く</a></div></div>
            @endforeach
        </div>
    </section>

    {{-- よくある質問 --}}
    <section x-show="supportView === 'faq'" x-cloak class="panel-card overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-5 sm:px-7" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-xl font-black text-gray-900 sm:text-2xl">よくある質問</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">質問を検索するか、カテゴリを選んで確認してください。</p>
                </div>
                <div class="relative w-full lg:max-w-md">
                    <i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                    <label for="faqSearch" class="sr-only">よくある質問を検索</label>
                    <input id="faqSearch" type="search" x-model="faqQuery" placeholder="例：スタッフが出ない、キャンセル"
                           class="faq-search h-12 w-full rounded-2xl border border-gray-300 bg-white pl-11 pr-4 text-sm">
                </div>
            </div>

            <div class="mt-4 flex gap-2 overflow-x-auto pb-1" aria-label="FAQカテゴリ">
                <button type="button" @click="faqCategory='all'" :class="faqCategory === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600'" class="shrink-0 rounded-xl border border-gray-200 px-4 py-2 text-xs font-bold">すべて</button>
                @foreach($faqCategories as $categoryKey => $category)
                    <button type="button" @click="faqCategory='{{ $categoryKey }}'" :class="faqCategory === '{{ $categoryKey }}' ? 'bg-slate-900 text-white' : 'bg-white text-gray-600'" class="shrink-0 rounded-xl border border-gray-200 px-4 py-2 text-xs font-bold">
                        {{ $category['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-7 p-5 sm:p-7">
            @foreach($faqCategories as $categoryKey => $category)
                @php
                    $categorySearchParts = [];
                    foreach ($category['items'] as $categoryFaq) {
                        $categorySearchParts[] = $categoryFaq[0];
                        $categorySearchParts = array_merge($categorySearchParts, $categoryFaq[1]);
                    }
                    $categorySearchText = mb_strtolower(implode(' ', $categorySearchParts));
                @endphp
                <div x-show="(faqCategory === 'all' || faqCategory === '{{ $categoryKey }}') && (!faqQuery || @js($categorySearchText).includes(faqQuery.toLocaleLowerCase('ja')))" class="faq-category space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl text-white" style="background: {{ $theme }};">
                            <i data-lucide="{{ $category['icon'] }}" class="h-4 w-4"></i>
                        </span>
                        <div>
                            <h3 class="font-black text-gray-900">{{ $category['label'] }}</h3>
                            <p class="text-xs text-gray-500">{{ count($category['items']) }}件の質問</p>
                        </div>
                    </div>

                    @foreach($category['items'] as $faq)
                        @php
                            $faqSearchText = mb_strtolower($faq[0].' '.implode(' ', $faq[1]));
                        @endphp
                        <details x-show="!faqQuery || @js($faqSearchText).includes(faqQuery.toLocaleLowerCase('ja'))"
                                 class="faq-item rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold text-gray-900">
                                <span class="flex items-start gap-3"><span class="text-base font-black" style="color: {{ $theme }};">Q</span><span>{{ $faq[0] }}</span></span>
                                <i data-lucide="chevron-down" class="h-4 w-4 shrink-0 text-gray-400"></i>
                            </summary>
                            <div class="ml-0 mt-4 border-t border-gray-100 pt-4 sm:ml-7">
                                <p class="mb-3 text-xs font-bold uppercase tracking-[0.12em] text-gray-400">確認する順番</p>
                                <ol class="space-y-3">
                                    @foreach($faq[1] as $i => $item)
                                        <li class="flex gap-3 text-sm leading-6 text-gray-700"><span class="step-number">{{ $i + 1 }}</span><span class="pt-1">{{ $item }}</span></li>
                                    @endforeach
                                </ol>
                                @if(!empty($faq[2]))
                                    <div class="manual-links mt-5 border-t border-slate-100 pt-4">
                                        @foreach($faq[2] as $target)
                                            <a href="{{ route($target[1], $target[2] ?? []) }}" class="support-deep-link"><i data-lucide="arrow-up-right" class="h-4 w-4"></i>{{ $target[0] }}を開く</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            @endforeach

            <div x-show="faqQuery && !@js($allFaqSearchText).includes(faqQuery.toLocaleLowerCase('ja'))" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-10 text-center">
                <i data-lucide="search-x" class="mx-auto h-8 w-8 text-gray-300"></i>
                <p class="mt-3 font-bold text-gray-700">該当する質問が見つかりません</p>
                <p class="mt-1 text-sm text-gray-500">短い言葉で検索するか、お問い合わせをご利用ください。</p>
            </div>

            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-5 text-center">
                <p class="font-bold text-gray-800">解決しませんでしたか？</p>
                <p class="mt-1 text-sm text-gray-500">状況を詳しくお知らせいただくと、確認がスムーズです。</p>
                <button type="button" @click="supportView='contact'; window.scrollTo({ top: 0, behavior: 'smooth' })" class="mt-4 rounded-xl px-5 py-2.5 text-sm font-bold text-white" style="background: {{ $theme }};">問い合わせを作成</button>
            </div>
        </div>
    </section>

    <section x-show="supportView === 'contact'" x-cloak class="panel-card overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="text-lg sm:text-xl font-black text-gray-900">解決しない場合はお問い合わせください</h2>
            <p class="text-sm text-gray-500 mt-1">いつ、どの画面で、何をした時に起きたかを書いていただくと確認が早くなります。</p>
        </div>
        <div class="p-5 sm:p-6">
            <form action="{{ route('company.support.store') }}" method="POST" class="space-y-5" x-data="{ body: @js(old('body', '')) }" data-busy-form="true" data-busy-label="送信中…">
                @csrf
                <div>
                    <label for="supportCategory" class="block text-sm font-bold text-gray-700 mb-2">カテゴリ <span class="ml-1 text-xs font-normal text-gray-400">任意</span></label>
                    <select id="supportCategory" name="category" class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white text-sm focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $themeSoft }};">
                        <option value="">選択してください</option>
                        <option value="first_setup" @selected(old('category') === 'first_setup')>初期設定</option>
                        <option value="daily_operation" @selected(old('category') === 'daily_operation')>通常運用</option>
                        <option value="staff_shift" @selected(old('category') === 'staff_shift')>スタッフ・シフト</option>
                        <option value="reservation" @selected(old('category') === 'reservation')>予約</option>
                        <option value="menu" @selected(old('category') === 'menu')>メニュー</option>
                        <option value="settings" @selected(old('category') === 'settings')>企業・画面設定</option>
                        <option value="account" @selected(old('category') === 'account')>アカウント・権限</option>
                        <option value="billing" @selected(old('category') === 'billing')>契約・請求</option>
                        <option value="other" @selected(old('category') === 'other')>その他</option>
                    </select>
                </div>
                <div>
                    <label for="supportSubject" class="block text-sm font-bold text-gray-700 mb-2">件名 <span class="ml-1 rounded bg-red-50 px-2 py-0.5 text-xs text-red-600">必須</span></label>
                    <input id="supportSubject" type="text" name="subject" value="{{ old('subject') }}" required maxlength="255" placeholder="例：予約画面にスタッフが表示されない"
                           class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white text-sm focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $themeSoft }};">
                    @error('subject')<div class="mt-1 text-sm text-red-500">{{ $message }}</div>@enderror
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="supportBody" class="block text-sm font-bold text-gray-700">お問い合わせ内容 <span class="ml-1 rounded bg-red-50 px-2 py-0.5 text-xs text-red-600">必須</span></label>
                        <span class="text-xs text-gray-400"><span x-text="body.length"></span> / 5,000文字</span>
                    </div>
                    <textarea id="supportBody" name="body" rows="8" required maxlength="5000" x-model="body"
                              placeholder="いつ、どの画面で、何をした時に、どのような表示になったかを入力してください。"
                              class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white text-sm leading-6 focus:outline-none focus:ring-2" style="--tw-ring-color: {{ $themeSoft }};"></textarea>
                    @error('body')<div class="mt-1 text-sm text-red-500">{{ $message }}</div>@enderror
                </div>
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600 leading-7">
                    書くと確認が早くなる内容：発生日時、対象スタッフ名、対象メニュー名、対象日、操作した画面、実際に表示された内容。
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" data-busy-button class="text-white px-5 py-3 rounded-2xl text-sm font-bold shadow hover:opacity-90 transition" style="background: linear-gradient(135deg, {{ $theme }} 0%, #111827 120%);"><span data-busy-text>お問い合わせを送信する</span></button>
                    <a href="{{ route('company.dashboard') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-gray-300 text-sm text-gray-700 bg-white hover:bg-gray-50 transition">ダッシュボードへ戻る</a>
                </div>
            </form>
        </div>
    </section>

    <section x-show="supportView === 'history'" x-cloak class="panel-card overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="text-lg sm:text-xl font-black text-gray-900">過去のお問い合わせ</h2>
            <p class="text-sm text-gray-500 mt-1">送信したお問い合わせと回答を確認できます。</p>
        </div>
        <div class="p-5 sm:p-6 space-y-4">
            @forelse($inquiries as $inquiry)
                <div class="rounded-2xl border border-gray-200 p-4 sm:p-5 bg-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div><div class="text-sm font-black text-gray-900">{{ $inquiry->subject }}</div><div class="mt-1 text-xs text-gray-500">{{ optional($inquiry->created_at)->format('Y/m/d H:i') }}</div></div>
                        <div>
                            @if($inquiry->status === 'answered')
                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">回答済み</span>
                            @elseif($inquiry->status === 'closed')
                                <span class="inline-flex rounded-full bg-gray-200 text-gray-700 px-3 py-1 text-xs font-bold">完了</span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-bold">受付中</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-600 leading-6">{{ \Illuminate\Support\Str::limit($inquiry->body, 160) }}</div>
                    @if($inquiry->admin_reply)
                        <div class="mt-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                            <div class="text-sm font-bold text-emerald-700">回答</div>
                            <div class="mt-2 text-sm text-gray-700 leading-7 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($inquiry->admin_reply, 160) }}</div>
                        </div>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('company.support.show', $inquiry) }}" class="inline-flex items-center rounded-2xl border px-4 py-2.5 text-sm font-bold bg-white hover:bg-gray-50 transition" style="border-color: {{ $theme }}; color: {{ $theme }};">詳細を見る</a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center text-gray-400">まだお問い合わせはありません。</div>
            @endforelse
        </div>
        <div class="px-5 py-4 border-t border-gray-100 bg-white">
            {{ $inquiries->appends(['view' => 'history'])->links() }}
        </div>
    </section>
</div>
@endsection
