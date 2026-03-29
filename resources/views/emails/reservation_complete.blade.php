@php
    $cancelHours = $company->web_cancel_deadline_hours ?? 24;
    $cancelUrl = url('/cancel/' . $reservation->cancel_token);
@endphp

{{ $reservation->customer_name }} 様

このたびは {{ $company->name }} へご予約いただき、誠にありがとうございます。
以下の内容でご予約を承りました。

■ ご予約内容
日時：{{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}

@if(!empty($reservation->staff?->name))
担当：{{ $reservation->staff->name }}
@endif

@if(!empty($reservation->total_price))
料金目安：{{ number_format($reservation->total_price) }}円
@endif

■ キャンセルについて
ご予約のキャンセルは、ご予約時間の{{ $cancelHours }}時間前まで、
下記URLよりお手続きいただけます。

{{ $cancelUrl }}

※ {{ $cancelHours }}時間を過ぎている場合は、
お手数ですがお電話にてご連絡をお願いいたします。

@if(!empty($company->phone))
電話番号：{{ $company->phone }}
@endif

@if(!empty($company->homepage))
ホームページ：{{ $company->homepage }}
@endif

ご来店を心よりお待ちしております。

{{ $company->name }}