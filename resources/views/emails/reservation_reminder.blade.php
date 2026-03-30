@php
    $company = $reservation->company;
    $customer = $reservation->customer;
    $staff = $reservation->staff;
    $menus = $reservation->menus ?? collect();
@endphp
{{ $customer->name ?? 'お客様' }} 様

いつもありがとうございます。
{{ $company->name ?? 'ご予約店舗' }} です。

明日のご予約についてご案内いたします。

【ご予約日時】
{{ optional($reservation->start_at)->format('Y年n月j日 H:i') }}

【ご予約メニュー】
@forelse($menus as $menu)
・{{ $menu->name }}
@empty
・ご予約内容
@endforelse

【担当者】
{{ $staff->name ?? '未指定' }}

ご来店を心よりお待ちしております。

※ご予約内容の変更・キャンセルをご希望の場合は、店舗までご連絡ください。

--------------------------------------------------
{{ $company->name ?? 'ご予約店舗' }}
@if(!empty($company->phone))
TEL：{{ $company->phone }}
@endif
@if(!empty($company->homepage))
WEB：{{ $company->homepage }}
@endif
--------------------------------------------------