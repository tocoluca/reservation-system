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
        ['key' => 'trouble', 'label' => '困った時', 'title' => '確認する順番', 'icon' => 'list-checks', 'desc' => '予約できない、スタッフが出ない時の確認手順です。'],
    ];
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
.screen-mock {
    border-radius: 22px;
    border: 1px solid rgba(148,163,184,.22);
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    overflow: hidden;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
}
.screen-top { height: 34px; background: #111827; display: flex; align-items: center; gap: 6px; padding: 0 14px; }
.screen-dot { width: 8px; height: 8px; border-radius: 999px; background: rgba(255,255,255,.35); }
.screen-body { padding: 16px; }
.screen-line { height: 10px; border-radius: 999px; background: #e2e8f0; }
.screen-pill { border-radius: 14px; background: {{ $themeSoft }}; border: 1px solid {{ $theme }}30; }
.manual-shot {
    border-radius: 22px;
    border: 1px solid rgba(148,163,184,.24);
    box-shadow: 0 18px 48px rgba(15,23,42,.12);
    width: 100%;
    background: white;
}
.shot-caption {
    border-radius: 18px;
    background: rgba(255,255,255,.86);
    border: 1px solid rgba(148,163,184,.18);
    padding: 14px 16px;
}
.faq-item summary::-webkit-details-marker { display: none; }
</style>

<div x-data="{ section: 'first' }" class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">
    <section class="support-hero rounded-[2rem] overflow-hidden text-white">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-3 py-1.5 text-xs font-bold tracking-[0.18em] text-white/75">
                        SUPPORT CENTER
                    </div>
                    <h1 class="mt-5 text-2xl sm:text-4xl font-black">操作マニュアル・よくある質問</h1>
                    <p class="mt-3 text-sm sm:text-base text-white/72 leading-7 max-w-3xl">
                        初めて使う方やPC操作に慣れていない方でも、上から順番に確認すれば運用を始められるようにまとめています。
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
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm bg-red-50 border-red-200 text-red-700">
            入力内容を確認してください。
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="panel-card p-5"><div class="text-sm text-gray-500">受付中</div><div class="mt-2 flex items-end justify-between"><div class="text-3xl font-black text-amber-600">{{ $openCount }}</div><span class="rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-bold">open</span></div></div>
        <div class="panel-card p-5"><div class="text-sm text-gray-500">回答済み</div><div class="mt-2 flex items-end justify-between"><div class="text-3xl font-black text-emerald-600">{{ $answeredCount }}</div><span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">answered</span></div></div>
        <div class="panel-card p-5"><div class="text-sm text-gray-500">完了</div><div class="mt-2 flex items-end justify-between"><div class="text-3xl font-black text-gray-600">{{ $closedCount }}</div><span class="rounded-full bg-gray-200 text-gray-700 px-3 py-1 text-xs font-bold">closed</span></div></div>
    </div>

    <section class="manual-card p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-gray-900">操作マニュアル</h2>
                <p class="text-sm text-gray-500 mt-1">目的を選ぶと、確認する順番と画面イメージが表示されます。</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 mb-6">
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
                    ['企業情報を確認する', ['店舗名、住所、電話番号、メールアドレスに間違いがないか確認します。', '予約受付期間、Webキャンセル期限、再来店促進メールの日数を確認します。', '営業時間の考え方や注意事項など、お客様に見せたい基本情報を整えます。']],
                    ['ロゴとテーマを設定する', ['ロゴ設定で店舗ロゴを登録します。', 'テーマ設定で予約画面や管理画面の基調カラーを選びます。', 'お客様が見ても店舗らしさが伝わる状態にします。']],
                    ['スタッフを登録する', ['スタッフ管理で担当者を登録します。', '予約対象にしたいスタッフは有効状態にします。', '表示名、写真、指名可否、権限を確認します。']],
                    ['カテゴリー・タグを用意する', ['カテゴリー・タグ管理でメニューを分類します。', 'カット、カラー、ネイルなど、お客様が探しやすい名前にします。', '不要な分類は増やしすぎないようにします。']],
                    ['メニューを登録する', ['メニュー管理でメニュー名、料金、施術時間を登録します。', '予約画面に表示する順番を確認します。', '説明文には、対象者や注意点を書いておくと問い合わせが減ります。']],
                    ['メニュー対応スタッフを設定する', ['メニューごとに担当できるスタッフを選びます。', 'スタッフを登録しただけでは、メニューを担当できない場合があります。', '予約画面にスタッフが出ない時は、まずここを確認します。']],
                    ['営業日を登録する', ['営業日管理で営業日、休業日、営業時間を登録します。', '祝日や臨時休業日も先に入れておくと安心です。', '予約可能期間より先まで営業日を登録しておきます。']],
                    ['シフトパターン・基本シフトを作る', ['シフトパターンで早番、遅番、通常勤務などの時間テンプレートを作ります。', '基本シフトで曜日ごとの標準勤務を登録します。', '毎月の勤務管理を入力しやすくするための下準備です。']],
                    ['勤務管理で実際のシフトを入れる', ['勤務管理でスタッフごとの勤務日、勤務時間を登録します。', '基本シフトを使ってから、休みや時間変更だけ修正すると楽です。', '営業日、勤務時間、対応メニューがそろうと予約受付できます。']],
                    ['予約画面で確認する', ['お客様側の予約画面を開いて、日付、メニュー、スタッフが選べるか確認します。', 'テスト予約を入れて、管理画面に表示されるか確認します。', '問題なければ初期設定は完了です。']],
                ] as $i => $step)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4">
                        <div class="flex gap-3"><span class="step-number">{{ $i + 1 }}</span><div class="flex-1"><div class="font-bold text-gray-900">{{ $step[0] }}</div><ul class="mt-2 space-y-1.5 text-sm text-gray-600 leading-6">@foreach($step[1] as $detail)<li class="flex gap-2"><span class="text-gray-300">●</span><span>{{ $detail }}</span></li>@endforeach</ul></div></div>
                    </div>
                @endforeach
            </div>
            <div class="space-y-4">
                <img src="{{ asset('images/manual/support-first-setup.png') }}" alt="初回設定チェック画面のスクリーンショット" class="manual-shot">
                <div class="shot-caption text-sm text-gray-600 leading-7">
                    画面上では、完了している項目と未完了の項目を分けて確認します。初期設定アラートが出ている場合は、未完了の項目から順番に設定してください。
                </div>
            </div>
        </div>        <div x-show="section==='daily'" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-black text-gray-900">通常運用でよく使う操作</h3>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">朝に確認する</div><p class="text-sm text-gray-600 mt-1">ダッシュボードで今日の予約、明日の予約、予約変更連絡、サポート回答を確認します。</p></div>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">予約を確認・変更する</div><p class="text-sm text-gray-600 mt-1">予約管理から対象予約を開き、時間、スタッフ、メニュー、連絡事項を確認します。変更した場合は、お客様への連絡が必要かも確認します。</p></div>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">スタッフが休んだ時</div><p class="text-sm text-gray-600 mt-1">勤務管理でそのスタッフの該当日を休みにします。すでに予約が入っている場合は、予約管理で別スタッフへの変更、日時変更、キャンセルの順に対応します。</p></div>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">電話・来店で予約依頼が来た時</div><p class="text-sm text-gray-600 mt-1">予約管理から空き時間を確認し、メニュー、スタッフ、日時、お客様情報を登録します。登録後は予約一覧に反映されているか確認します。</p></div>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">電話・来店でキャンセル依頼が来た時</div><p class="text-sm text-gray-600 mt-1">予約管理で対象予約を開き、キャンセル処理を行います。キャンセル理由や連絡済みかをメモしておくと後から確認しやすくなります。</p></div>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">シフトを更新する</div><p class="text-sm text-gray-600 mt-1">勤務管理で日別シフトを入れます。先の日付までまとめて登録しておくと、予約受付期間内で予約できない日が出にくくなります。</p></div>
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-bold">お知らせを出す</div><p class="text-sm text-gray-600 mt-1">お知らせ情報管理でキャンペーン、臨時休業、注意事項を登録します。表示期間を設定すると古い案内が残りにくくなります。</p></div>
            </div>
            <div class="space-y-4">
                <img src="{{ asset('images/manual/support-daily-dashboard.png') }}" alt="通常運用ダッシュボードのスクリーンショット" class="manual-shot">
                <div class="shot-caption text-sm text-gray-600 leading-7">
                    毎朝は、今日の予約、明日の予約、予約変更連絡、サポート回答を上から確認します。未対応がある時は先に処理してください。
                </div>
                <img src="{{ asset('images/manual/support-staff-absence.png') }}" alt="スタッフが休んだ時の確認手順スクリーンショット" class="manual-shot">
                <div class="shot-caption text-sm text-gray-600 leading-7">
                    スタッフが休んだ時は、先に予約管理で予約の有無を確認してから勤務管理を変更します。
                </div>
                <img src="{{ asset('images/manual/support-phone-reservation.png') }}" alt="電話・来店予約登録フォームのスクリーンショット" class="manual-shot">
                <div class="shot-caption text-sm text-gray-600 leading-7">
                    電話・来店で予約を受けた時は、空き時間を確認し、お客様名、メニュー、スタッフ、日時を登録します。
                </div>
            </div>
        </div>        <div x-show="section==='features'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach([
                ['予約管理', '予約の確認、登録、変更、キャンセル対応を行います。電話や来店で受けた予約もここで管理します。'],
                ['顧客管理', '来店履歴や顧客情報を確認します。過去の予約内容を見たい時にも使います。'],
                ['スタッフ管理', '担当者の登録、有効/無効、表示名、権限を設定します。スタッフが予約対象に出るための基本情報です。'],
                ['マイプロフィール', 'ログイン中の担当者自身の情報や個人設定を確認・変更します。'],
                ['休暇管理', 'スタッフの休みや有給など、通常シフトとは別の休暇情報を登録します。'],
                ['営業日管理', '店舗の営業日、休業日、営業時間を設定します。予約できる日付の土台になります。'],
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
                ['契約管理', 'プラン、支払い状況、請求関連の情報を確認します。'],
                ['ロゴ設定', '店舗ロゴを登録・変更します。予約画面や管理画面のブランド表示に使います。'],
                ['テーマ設定', '画面の基調カラーを設定します。店舗の雰囲気に合わせて見た目を整えます。'],
                ['ダッシュボード管理', '役職や権限ごとに、ダッシュボードで表示するカードを設定します。'],
                ['よくあるご質問・お問い合わせ', '操作マニュアルの確認、困った時の確認順、サポートへの問い合わせを行います。'],
            ] as $feature)
                <div class="rounded-2xl bg-white border border-gray-100 p-4"><div class="font-black text-gray-900">{{ $feature[0] }}</div><div class="text-sm text-gray-600 mt-2 leading-6">{{ $feature[1] }}</div></div>
            @endforeach
        </div><div x-show="section==='trouble'" class="space-y-4">
            <h3 class="text-lg font-black text-gray-900">こんなときはこの順番で確認</h3>
            @foreach([
                ['予約画面にスタッフが出ない', ['スタッフ管理で有効になっているか', '勤務管理で対象日にシフトが入っているか', '営業日管理で対象日が営業日か', 'メニュー対応スタッフ設定で対象メニューを担当できるか']],
                ['予約できるはずの日が選べない', ['営業日管理で営業日になっているか', '営業時間が入っているか', 'スタッフの勤務日程が入っているか', '予約受付期間の範囲内か']],
                ['設定を変えたのに画面に反映されない', ['保存ボタンを押したか', '画面を再読み込みしたか', '別の設定画面にも同じ内容がないか', '別ブラウザやスマートフォンで確認する']],
                ['キャンセルできない', ['予約日時を確認する', 'Webキャンセル期限を確認する', '期限を過ぎている場合は店舗側で対応する']],
            ] as $faq)
                <details class="faq-item rounded-2xl bg-white border border-gray-100 p-4">
                    <summary class="cursor-pointer font-black text-gray-900 flex items-center justify-between gap-3">
                        {{ $faq[0] }}
                        <span class="text-gray-400">+</span>
                    </summary>
                    <ol class="mt-4 space-y-3">
                        @foreach($faq[1] as $i => $item)
                            <li class="flex gap-3 text-sm text-gray-700"><span class="step-number">{{ $i + 1 }}</span><span class="pt-1">{{ $item }}</span></li>
                        @endforeach
                    </ol>
                </details>
            @endforeach
        </div>
    </section>

    <section class="panel-card overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100" style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <h2 class="text-lg sm:text-xl font-black text-gray-900">解決しない場合はお問い合わせください</h2>
            <p class="text-sm text-gray-500 mt-1">いつ、どの画面で、何をした時に起きたかを書いていただくと確認が早くなります。</p>
        </div>
        <div class="p-5 sm:p-6">
            <form action="{{ route('company.support.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">カテゴリ</label>
                    <select name="category" class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white text-sm">
                        <option value="">選択してください</option>
                        <option value="first_setup" @selected(old('category') === 'first_setup')>初期設定</option>
                        <option value="daily_operation" @selected(old('category') === 'daily_operation')>通常運用</option>
                        <option value="staff_shift" @selected(old('category') === 'staff_shift')>スタッフ・シフト</option>
                        <option value="reservation" @selected(old('category') === 'reservation')>予約</option>
                        <option value="menu" @selected(old('category') === 'menu')>メニュー</option>
                        <option value="other" @selected(old('category') === 'other')>その他</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">件名</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white text-sm">
                    @error('subject')<div class="mt-1 text-sm text-red-500">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">お問い合わせ内容</label>
                    <textarea name="body" rows="8" class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white text-sm">{{ old('body') }}</textarea>
                    @error('body')<div class="mt-1 text-sm text-red-500">{{ $message }}</div>@enderror
                </div>
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600 leading-7">
                    書くと確認が早くなる内容：発生日時、対象スタッフ名、対象メニュー名、対象日、操作した画面、実際に表示された内容。
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="text-white px-5 py-3 rounded-2xl text-sm font-bold shadow hover:opacity-90 transition" style="background: linear-gradient(135deg, {{ $theme }} 0%, #111827 120%);">お問い合わせを送信する</button>
                    <a href="{{ route('company.dashboard') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-gray-300 text-sm text-gray-700 bg-white hover:bg-gray-50 transition">ダッシュボードへ戻る</a>
                </div>
            </form>
        </div>
    </section>

    <section class="panel-card overflow-hidden">
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
            {{ $inquiries->links() }}
        </div>
    </section>
</div>
@endsection
