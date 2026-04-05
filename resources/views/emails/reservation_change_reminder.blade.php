@php
    $customerName = $item->customer_name ?: 'お客様';
    $startAt = $reservation && $reservation->start_at
        ? \Carbon\Carbon::parse($reservation->start_at)->format('Y/m/d H:i')
        : '';
@endphp

{{ $customerName }} 様

いつもご利用ありがとうございます。

先日ご案内しておりますご予約変更につきまして、
まだご確認の登録が完了していないため、再度ご連絡しております。

【ご予約日時】
{{ $startAt }}

【ご案内理由】
{{ $notice->reason_text ?? '店舗都合によるご予約変更のお願い' }}

お手数をおかけして申し訳ございませんが、
以下のURLより内容確認のお手続きをお願いいたします。

{{ $confirmUrl }}

ご不明な点がありましたら店舗までご連絡ください。