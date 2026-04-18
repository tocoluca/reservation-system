@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#c08457';
    $themeSoft = $theme . '15';

    $openCount = $inquiries->where('status', 'open')->count();
    $answeredCount = $inquiries->where('status', 'answered')->count();
    $closedCount = $inquiries->where('status', 'closed')->count();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

    {{-- ヘッダー --}}
    <div class="relative overflow-hidden rounded-3xl shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background:
                radial-gradient(circle at top right, #ffffff 0%, transparent 35%),
                radial-gradient(circle at bottom left, #ffffff 0%, transparent 30%);">
        </div>

        <div class="relative px-6 sm:px-8 py-7 sm:py-8 text-white"
             style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <p class="text-xs sm:text-sm tracking-widest uppercase opacity-80">Support Center</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">よくあるご質問・お問い合わせ</h1>
                    <p class="text-sm sm:text-base opacity-90 mt-2 leading-6">
                        操作方法や設定の確認、解決しない場合のお問い合わせ送信、回答履歴の確認ができます。
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 backdrop-blur-sm transition text-sm font-medium">
                        ← ダッシュボード
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm"
             style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border px-5 py-4 text-sm shadow-sm"
             style="background-color: #fef2f2; border-color: #fecaca; color: #b91c1c;">
            入力内容をご確認ください。
        </div>
    @endif

    {{-- サマリー --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-stone-500">受付中</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-amber-500">{{ $openCount }}</div>
                <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">
                    open
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-stone-500">回答済み</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-emerald-500">{{ $answeredCount }}</div>
                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">
                    answered
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-stone-500">完了</div>
            <div class="mt-2 flex items-end justify-between">
                <div class="text-3xl font-bold text-stone-500">{{ $closedCount }}</div>
                <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-semibold">
                    closed
                </span>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-stone-800">よくあるご質問</h2>
                <p class="text-sm text-stone-500 mt-1">
                    はじめて使う方でも分かりやすいように、よくあるつまずきをカテゴリーごとにまとめています。
                    まずは近い内容を開いてご確認ください。
                </p>
            </div>
        </div>

        <div class="p-5 sm:p-6 space-y-5">

            {{-- スタッフ・担当者 --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden" open>
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">スタッフ・担当者について</div>
                        <div class="text-xs text-stone-500 mt-1">スタッフが出ない、選べない、予約対象にならない場合</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">登録したスタッフが表示されません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>スタッフを登録しても、すぐに予約画面へ出ないことがあります。設定が足りない場合に起こりやすいです。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「担当者管理」で、そのスタッフが有効になっているか確認してください。</li>
                                    <li>次に「勤務管理」で、そのスタッフの勤務シフトが対象日に入っているか確認してください。</li>
                                    <li>次に「営業日カレンダー」で、その日が営業日になっているか確認してください。</li>
                                    <li>最後に「メニュー対応担当者設定」で、そのスタッフが対象メニューを担当できるか確認してください。</li>
                                </ol>
                            </div>

                            <p>スタッフを登録しただけでは、予約画面に出ない場合があります。</p>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">スタッフを登録したのに予約対象になりません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>スタッフ情報を作成しても、予約を受けるための設定が不足していると予約対象として使えません。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「担当者管理」で、公開対象や有効状態を確認してください。</li>
                                    <li>次に「メニュー対応担当者設定」で、対応メニューが設定されているか確認してください。</li>
                                    <li>次に「勤務管理」で、勤務日と勤務時間が入っているか確認してください。</li>
                                    <li>最後に、実際に予約したい日が営業日になっているか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">日によってスタッフが表示されたり、表示されなかったりします</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>これは異常ではなく、日ごとの設定によって変わることがあります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「勤務管理」で、その日にシフトが入っているか確認してください。</li>
                                    <li>次に「休暇管理」で、休暇になっていないか確認してください。</li>
                                    <li>次に「営業日カレンダー」で、その日が営業日か確認してください。</li>
                                    <li>最後に、選んでいるメニューに対応しているスタッフか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">担当者を選べない日時があります</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>その時間に勤務していない、すでに予約が入っている、または選んだメニューに対応していない可能性があります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「勤務管理」で、その時間に勤務しているか確認してください。</li>
                                    <li>次に「予約管理」で、すでに予約が入っていないか確認してください。</li>
                                    <li>次に「メニュー対応担当者設定」で、そのメニューを担当できるか確認してください。</li>
                                    <li>最後に、営業時間内かどうかも確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                </div>
            </details>

            {{-- 営業日・営業時間 --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden">
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">営業日・営業時間について</div>
                        <div class="text-xs text-stone-500 mt-1">営業日を変えたい、営業時間が反映されない場合</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">営業日を非営業日にしたいです</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>営業日カレンダーで対象の日付を選び、非営業日に変更して保存してください。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「営業日カレンダー」を開いて、変更したい日付を表示してください。</li>
                                    <li>次に、対象の日付が間違っていないか確認してください。</li>
                                    <li>次に、その日を「非営業日」に変更して保存してください。</li>
                                    <li>最後に「予約管理」で、その日にすでに予約が入っていないか確認してください。</li>
                                </ol>
                            </div>

                            <p>すでに予約が入っている場合は、自動では消えません。</p>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">非営業日を営業日にしたのに、予約できません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>営業日に変更しただけでは、予約できるようにならないことがあります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「営業日カレンダー」で、その日が営業日になっているか確認してください。</li>
                                    <li>次に、その日の営業時間が入っているか確認してください。</li>
                                    <li>次に「勤務管理」で、その日に働くスタッフのシフトが入っているか確認してください。</li>
                                    <li>最後に、予約可能期間の外ではないか確認してください。</li>
                                </ol>
                            </div>

                            <p>特に多いのは、「営業日にはしたが、営業時間が空のまま」というケースです。</p>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">営業時間を変更したのに反映されません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>保存できていないか、別の設定が優先されていることがあります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、変更後に保存できているか確認してください。</li>
                                    <li>次に、画面を再読み込みしてください。</li>
                                    <li>次に、「曜日別営業時間」を変更したのか、「特定日の個別設定」を変更したのか確認してください。</li>
                                    <li>最後に、特定日の個別設定が残っていないか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">非営業日にしたのに予約が残っています</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>すでに入っている予約は自動で削除されません。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、その日が本当に非営業日になっているか確認してください。</li>
                                    <li>次に「予約管理」で、その日に入っている予約内容を確認してください。</li>
                                    <li>次に、必要に応じて予約変更やキャンセル対応を行ってください。</li>
                                    <li>最後に、お客様への連絡が必要か確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">曜日別営業時間と特定日の設定は、どちらが優先されますか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>通常は、特定の日に個別で設定した内容が優先されます。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、曜日別営業時間を確認してください。</li>
                                    <li>次に、特定日の個別設定が入っていないか確認してください。</li>
                                    <li>最後に、実際に見たい日付に対して、どちらの設定が効いているか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                </div>
            </details>

            {{-- 予約・受付設定 --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden">
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">予約・受付設定について</div>
                        <div class="text-xs text-stone-500 mt-1">予約できない、空きが出ない、受付不可になる場合</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">予約できない時間が「予約あり」と表示されます</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>その時間に本当に予約が入っている場合もありますが、実際には「受付できない状態」でも似たように見えることがあります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「予約管理」で、その時間に本当に予約が入っているか確認してください。</li>
                                    <li>次に、その時間が営業時間内か確認してください。</li>
                                    <li>次に、予約締切時間を過ぎていないか確認してください。</li>
                                    <li>次に、勤務しているスタッフがいるか確認してください。</li>
                                    <li>最後に、同時予約数の上限に達していないか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">予約画面に空きが表示されません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>設定に問題がある場合と、条件に合う空きが本当にない場合があります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、予約を取りたい日が予約可能期間の中に入っているか確認してください。</li>
                                    <li>次に、その日が営業日になっているか確認してください。</li>
                                    <li>次に、その日の営業時間が入っているか確認してください。</li>
                                    <li>次に、スタッフの勤務シフトが入っているか確認してください。</li>
                                    <li>次に、対象メニューに対応できるスタッフがいるか確認してください。</li>
                                    <li>最後に、同時予約数の上限に達していないか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">予約可能期間外とは何ですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>何ヶ月先まで予約を受け付けるか、何日前から受け付けるか、何時間前まで受け付けるか、という条件の外にある状態です。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「企業情報設定」で「予約可能期間（月）」を確認してください。</li>
                                    <li>次に「予約受付開始（日）」を確認してください。</li>
                                    <li>次に「予約締切（時間前）」を確認してください。</li>
                                    <li>最後に、予約したい日時がその条件の中に入っているか見比べてください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">予約受付開始（日）とは何ですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7">
                            何日前から予約を受け付けるかを決める設定です。たとえば「1」の場合、当日は受け付けず翌日から予約できます。
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">予約締切（時間前）とは何ですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7">
                            予約時間の何時間前まで受け付けるかを決める設定です。たとえば「2」の場合、開始2時間前を過ぎると予約できません。
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">同時予約数とは何ですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>同じ時間に何件まで予約を受け付けるかの目安です。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「企業情報設定」で、同時予約数がいくつになっているか確認してください。</li>
                                    <li>次に「予約管理」で、その時間にすでに何件予約が入っているか確認してください。</li>
                                    <li>最後に、見た目では空いていても上限に達していないか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">時間刻み（分）とは何ですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7">
                            何分ごとに予約枠を区切るかの設定です。たとえば30分なら、10:00、10:30、11:00のように表示されます。
                        </div>
                    </details>

                </div>
            </details>

            {{-- メニュー --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden">
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">メニューについて</div>
                        <div class="text-xs text-stone-500 mt-1">メニューが出ない、選べない、複数メニューで困る場合</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">メニューが表示されません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>メニュー自体の設定や、対応スタッフ設定が不足していることがあります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「メニュー管理」で、そのメニューが登録されているか確認してください。</li>
                                    <li>次に、そのメニューが利用できる状態になっているか確認してください。</li>
                                    <li>次に「メニュー対応担当者設定」で、対応できるスタッフが設定されているか確認してください。</li>
                                    <li>最後に、その日時に勤務している対応スタッフがいるか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">一部のメニューだけ選べません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>そのメニューに対応できるスタッフが足りないことが多いです。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、そのメニューが有効になっているか確認してください。</li>
                                    <li>次に、そのメニューに対応できるスタッフが設定されているか確認してください。</li>
                                    <li>次に、その日時に対応スタッフが勤務しているか確認してください。</li>
                                    <li>最後に、営業時間や予約締切時間も確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">複数メニューを選ぶと担当者が出ません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>複数のメニューをまとめて予約する場合、選んだすべてのメニューに対応できる条件が必要になります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、選んだメニューそれぞれに対応スタッフが設定されているか確認してください。</li>
                                    <li>次に、その日時に勤務しているスタッフがいるか確認してください。</li>
                                    <li>次に、複数メニューの合計時間が長くなりすぎていないか確認してください。</li>
                                    <li>最後に、自動割当設定や担当条件で候補が絞られていないか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">メニュー時間を変更したのに反映されません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>「メニュー所要時間で予約」が有効かどうかで見え方が変わります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「メニュー管理」で、メニュー時間が保存されているか確認してください。</li>
                                    <li>次に「企業情報設定」で「メニュー所要時間で予約」が有効か確認してください。</li>
                                    <li>最後に、画面を再読み込みして反映を確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                </div>
            </details>

            {{-- メール・通知 --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden">
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">メール・通知について</div>
                        <div class="text-xs text-stone-500 mt-1">予約確認メールやキャンセル案内に関するご質問</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">予約確認メールが届きません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>メールアドレス間違い、迷惑メール、受信拒否設定が多いです。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、登録したメールアドレスが正しいか確認してください。</li>
                                    <li>次に、迷惑メールフォルダを確認してください。</li>
                                    <li>次に、受信拒否設定がないか確認してください。</li>
                                    <li>最後に、実際に予約が完了しているか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">再来店促進メールはいつ送られますか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>企業情報設定の「再来店促進メール送信日数」で設定した日数をもとに送信対象が決まります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず「企業情報設定」で、再来店促進メール送信日数を確認してください。</li>
                                    <li>次に、お客様の最終来店日を確認してください。</li>
                                    <li>最後に、送信対象の日数に達しているか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">Webでキャンセルできないのはなぜですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>Webキャンセルできる期限を過ぎている場合があります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、予約日時を確認してください。</li>
                                    <li>次に「企業情報設定」で「Webキャンセル締切（時間前）」を確認してください。</li>
                                    <li>最後に、今の時刻がその条件を過ぎていないか確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                </div>
            </details>

            {{-- 表示・反映 --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden">
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">表示・反映について</div>
                        <div class="text-xs text-stone-500 mt-1">設定を変えたのに画面に出ない場合</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">設定を変更したのに反映されません</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>保存忘れ、再読み込み不足、別設定の優先が考えられます。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず保存できているか確認してください。</li>
                                    <li>次に、画面を再読み込みしてください。</li>
                                    <li>次に、同じ内容を別の場所でも設定していないか確認してください。</li>
                                    <li>最後に、別ブラウザや再ログインで確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">スマートフォンで見たときだけ表示が違います</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>画面幅や表示キャッシュの影響で見え方が変わることがあります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">確認する順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>まず、画面を再読み込みしてください。</li>
                                    <li>次に、別ブラウザで確認してください。</li>
                                    <li>次に、PCとスマホで同じ日時・同じ条件を見ているか確認してください。</li>
                                    <li>最後に、対象日・対象スタッフ・対象メニューをそろえて再確認してください。</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                </div>
            </details>

            {{-- その他 --}}
            <details class="rounded-3xl border border-stone-200 bg-stone-50/60 overflow-hidden">
                <summary class="cursor-pointer list-none px-5 py-4 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-bold text-stone-900">その他</div>
                        <div class="text-xs text-stone-500 mt-1">上記に当てはまらない内容はこちら</div>
                    </div>
                    <span class="faq-toggle text-stone-400 text-xl font-light">＋</span>
                </summary>

                <div class="px-5 pb-5 space-y-4">
                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">どこを見れば原因が分かりますか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>多くの場合は、次の4つを見ると原因が見つかりやすいです。</p>

                            <div>
                                <div class="font-semibold text-stone-800">まず見る順番</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>営業日</li>
                                    <li>営業時間</li>
                                    <li>スタッフの勤務シフト</li>
                                    <li>メニュー対応設定</li>
                                </ol>
                            </div>
                        </div>
                    </details>

                    <details class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <summary class="cursor-pointer font-bold text-stone-900">解決しない場合は、何を書いて問い合わせればよいですか？</summary>
                        <div class="mt-4 text-sm text-stone-600 leading-7 space-y-3">
                            <p>次の内容を書いていただくと、確認がかなり早くなります。</p>

                            <div>
                                <div class="font-semibold text-stone-800">書いていただきたい内容</div>
                                <ol class="list-decimal pl-5 mt-2 space-y-2">
                                    <li>いつ起きたか</li>
                                    <li>誰に関する設定か（スタッフ名、メニュー名）</li>
                                    <li>どの日・どの時間か</li>
                                    <li>どの画面で起きたか</li>
                                    <li>実際に何が起きているか</li>
                                    <li>本来どうなってほしいか</li>
                                </ol>
                            </div>
                        </div>
                    </details>
                </div>
            </details>

        </div>
    </div>

    {{-- 問い合わせフォーム --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-stone-800">解決しない場合はお問い合わせください</h2>
                <p class="text-sm text-stone-500 mt-1">
                    発生日時・対象スタッフ・対象日時・画面名・実際の表示内容をできるだけ詳しくご記入ください。
                </p>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <form action="{{ route('company.support.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">カテゴリ</label>
                    <select name="category"
                            class="w-full border border-stone-300 rounded-2xl px-4 py-3 bg-white text-sm focus:outline-none focus:ring-2"
                            style="--tw-ring-color: {{ $theme }};">
                        <option value="">選択してください</option>
                        <option value="staff" @selected(old('category') === 'staff')>スタッフ表示</option>
                        <option value="business_day" @selected(old('category') === 'business_day')>営業日設定</option>
                        <option value="reservation" @selected(old('category') === 'reservation')>予約設定</option>
                        <option value="mail" @selected(old('category') === 'mail')>メール</option>
                        <option value="other" @selected(old('category') === 'other')>その他</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">件名</label>
                    <input type="text" name="subject" value="{{ old('subject') }}"
                           class="w-full border border-stone-300 rounded-2xl px-4 py-3 bg-white text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $theme }};">
                    @error('subject')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">お問い合わせ内容</label>
                    <textarea name="body" rows="8"
                              class="w-full border border-stone-300 rounded-2xl px-4 py-3 bg-white text-sm focus:outline-none focus:ring-2"
                              style="--tw-ring-color: {{ $theme }};">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div class="rounded-2xl bg-stone-50 border border-stone-200 p-4 text-sm text-stone-600 leading-6">
                    例：<br>
                    ・発生日時<br>
                    ・対象スタッフ名<br>
                    ・対象日付 / 時間<br>
                    ・操作した画面<br>
                    ・実際の表示内容<br>
                    ・本来どうなってほしいか
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit"
                            class="text-white px-5 py-3 rounded-2xl text-sm font-semibold shadow hover:opacity-90 transition"
                            style="background: linear-gradient(135deg, {{ $theme }} 0%, #7c5a43 100%);">
                        お問い合わせを送信する
                    </button>

                    <a href="{{ route('company.dashboard') }}"
                       class="inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-stone-300 text-sm text-stone-700 bg-white hover:bg-stone-50 transition">
                        ダッシュボードに戻る
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- 過去のお問い合わせ --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 sm:px-7 py-5 border-b border-gray-100"
             style="background: linear-gradient(180deg, {{ $themeSoft }} 0%, #ffffff 100%);">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-stone-800">過去のお問い合わせ</h2>
                <p class="text-sm text-stone-500 mt-1">
                    これまで送信したお問い合わせと、管理者からの回答内容を確認できます。
                </p>
            </div>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
            @forelse($inquiries as $inquiry)
                <div class="rounded-2xl border border-stone-200 p-4 sm:p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-stone-900">{{ $inquiry->subject }}</div>
                            <div class="mt-1 text-xs text-stone-500">
                                {{ optional($inquiry->created_at)->format('Y/m/d H:i') }}
                            </div>
                        </div>

                        <div>
                            @if($inquiry->status === 'answered')
                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">
                                    回答済み
                                </span>
                            @elseif($inquiry->status === 'closed')
                                <span class="inline-flex rounded-full bg-stone-200 text-stone-700 px-3 py-1 text-xs font-semibold">
                                    完了
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-3 py-1 text-xs font-semibold">
                                    受付中
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 text-sm text-stone-600 leading-6">
                        {{ \Illuminate\Support\Str::limit($inquiry->body, 160) }}
                    </div>

                    @if($inquiry->admin_reply)
                        <div class="mt-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                            <div class="text-sm font-bold text-emerald-700">回答</div>
                            <div class="mt-2 text-sm text-stone-700 leading-7 whitespace-pre-line">
                                {{ \Illuminate\Support\Str::limit($inquiry->admin_reply, 160) }}
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('company.support.show', $inquiry) }}"
                           class="inline-flex items-center rounded-2xl border px-4 py-2.5 text-sm font-medium bg-white hover:bg-stone-50 transition"
                           style="border-color: {{ $theme }}; color: {{ $theme }};">
                            詳細を見る
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-14 text-center text-stone-400">
                    まだお問い合わせはありません。
                </div>
            @endforelse
        </div>

        <div class="px-5 py-4 border-t border-stone-100 bg-white">
            {{ $inquiries->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('details').forEach(function (detail) {
        const toggle = detail.querySelector('.faq-toggle');
        if (!toggle) return;

        const updateMark = () => {
            toggle.textContent = detail.open ? '−' : '＋';
        };

        updateMark();
        detail.addEventListener('toggle', updateMark);
    });
});
</script>

@endsection