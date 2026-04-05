@php
    $customerName = $item->customer_name ?: 'お客様';
    $startAt = $reservation && $reservation->start_at
        ? \Carbon\Carbon::parse($reservation->start_at)->format('Y/m/d H:i')
        : '';
@endphp

{{ $customerName }} 様

いつもご利用ありがとうございます。

ご予約いただいております下記内容につきまして、
担当者休暇または営業日変更等の理由により、
通常どおりのご案内ができなくなりました。

【ご予約日時】
{{ $startAt }}

【ご案内理由】
{{ $notice->reason_text ?? '店舗都合によるご予約変更のお願い' }}

お手数をおかけして申し訳ございません。
以下のURLより、内容確認のお手続きをお願いいたします。

{{ $confirmUrl }}

ご不明点がございましたら店舗までご連絡ください。