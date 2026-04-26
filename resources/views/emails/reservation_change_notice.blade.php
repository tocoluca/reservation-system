@php
    $customerName = $item->customer_name ?: 'お客様';
    $startAt = $reservation && $reservation->start_at
        ? \Carbon\Carbon::parse($reservation->start_at)->format('Y/m/d H:i')
        : '';
    $company = $item->company ?? $notice?->company ?? $reservation?->company ?? null;
    $companyName = $company?->name ?? '店舗';
    $companyPhone = $company?->phone ?? null;
    $telLink = $companyPhone ? preg_replace('/[^0-9+]/', '', $companyPhone) : null;@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご予約変更のお願い</title>
</head>
<body style="margin:0;padding:0;background:#f7f3ee;font-family:'Hiragino Kaku Gothic ProN','Yu Gothic',Arial,sans-serif;color:#4b3f35;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ee;margin:0;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #eadfd3;">
                <tr>
                    <td style="background:linear-gradient(135deg,#b98a64,#9f6b52);padding:24px 32px 28px;color:#ffffff;text-align:center;">
                        <div style="font-size:12px;letter-spacing:.12em;font-weight:bold;opacity:.9;">RESERVATION UPDATE</div>
                        <h1 style="margin:10px 0 0;font-size:26px;line-height:1.4;font-weight:bold;">ご予約変更のお願い</h1>
                        <p style="margin:10px 0 0;font-size:14px;line-height:1.8;opacity:.95;">
                            大切なお知らせがございます。内容をご確認いただけますと幸いです。
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 18px;font-size:15px;line-height:1.9;">
                            {{ $customerName }} 様
                        </p>

                        <p style="margin:0 0 22px;font-size:14px;line-height:1.9;color:#6b5b4d;">
                            いつもご利用ありがとうございます。<br>
                            ご予約いただいております内容につきまして、担当者休暇または営業日変更等の理由により、
                            通常どおりのご案内ができなくなりました。
                        </p>

                        <div style="margin:0 0 24px;padding:22px;background:#fcf8f4;border:1px solid #eadfd3;border-radius:16px;">
                            <div style="font-size:12px;color:#9a7d63;font-weight:bold;letter-spacing:.08em;margin-bottom:12px;">対象のご予約</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="width:110px;padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">ご予約日時</td>
                                    <td style="padding:8px 0;font-size:15px;font-weight:bold;">{{ $startAt }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">ご案内理由</td>
                                    <td style="padding:8px 0;font-size:14px;line-height:1.8;">
                                        {{ $notice->reason_text ?? '店舗都合によるご予約変更のお願い' }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="margin:0 0 24px;padding:20px;background:#fff7f2;border:1px solid #efd7c9;border-radius:16px;">
                            <div style="font-size:14px;font-weight:bold;margin-bottom:10px;color:#8c5f43;">ご確認のお願い</div>
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.9;color:#6b5b4d;">
                                お手数をおかけして申し訳ございません。<br>
                                以下より内容をご確認ください。
                            </p>

                            <div style="text-align:center;margin:18px 0;">
                                <a href="{{ $confirmUrl }}" style="display:inline-block;background:#9f6b52;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:14px 28px;border-radius:999px;">
                                    内容を確認する
                                </a>
                            </div>

                            <div style="word-break:break-all;background:#ffffff;border:1px solid #eadfd3;border-radius:12px;padding:14px;font-size:13px;line-height:1.8;color:#7b6654;">
                                {{ $confirmUrl }}
                            </div>
                        </div>

                        <p style="margin:0;font-size:14px;line-height:1.9;color:#6b5b4d;">
                            ご不明点がございましたら、店舗までご連絡ください。<br>
                            ご迷惑をおかけし申し訳ございませんが、何卒よろしくお願いいたします。
                        </p>

                        <div style="margin:0 0 24px;padding:18px 20px;background:#f8f1ea;border:1px solid #eadfd3;border-radius:16px;">
                            <div style="font-size:14px;font-weight:bold;margin-bottom:10px;color:#4b3f35;">お問い合わせ先</div>
                            <div style="font-size:14px;line-height:1.9;color:#6b5b4d;">
                                <div>店舗名：{{ $companyName }}</div>
                                @if(!empty($companyPhone))
                                    <div>電話番号：<a href="tel:{{ $telLink }}" style="color:#8c5f43;font-weight:bold;text-decoration:none;">{{ $companyPhone }}</a></div>
                                    <div style="margin-top:8px;">ご不明点がございましたら、上記電話番号までお電話ください。</div>
                                @else
                                    <div>ご不明点がございましたら、店舗までご連絡ください。</div>
                                @endif
                            </div>
                        </div>                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 32px;background:#f3ece4;font-size:12px;line-height:1.7;color:#9a8878;text-align:center;">
                        このメールは自動送信されています。
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>