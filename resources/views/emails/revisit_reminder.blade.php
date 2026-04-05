@php
    $reserveUrl = url('/r/' . $company->company_code);
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>またのご来店をお待ちしております</title>
</head>
<body style="margin:0;padding:0;background:#f7f3ee;font-family:'Hiragino Kaku Gothic ProN','Yu Gothic',Arial,sans-serif;color:#4b3f35;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ee;margin:0;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #eadfd3;">
                <tr>
                    <td style="background:linear-gradient(135deg,#d8b89d,#bc8e6c);padding:24px 32px 28px;color:#ffffff;text-align:center;">
                        <div style="font-size:12px;letter-spacing:.12em;font-weight:bold;opacity:.9;">THANK YOU ALWAYS</div>
                        <h1 style="margin:10px 0 0;font-size:26px;line-height:1.4;font-weight:bold;">またのご来店をお待ちしております</h1>
                        <p style="margin:10px 0 0;font-size:14px;line-height:1.8;opacity:.95;">
                            次回のご予約も、心を込めてお待ちしております。
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 18px;font-size:15px;line-height:1.9;">
                            {{ $customer->name ?? 'お客様' }} 様
                        </p>

                        <p style="margin:0 0 22px;font-size:14px;line-height:1.95;color:#6b5b4d;">
                            いつも <strong>{{ $company->name }}</strong> をご利用いただきありがとうございます。<br>
                            前回のご来店から少しお日にちが経ちましたので、
                            ご都合のよいタイミングでぜひ次回のご予約をご検討ください。
                        </p>

                        <div style="margin:0 0 24px;padding:20px;background:#fcf8f4;border:1px solid #eadfd3;border-radius:16px;">
                            <div style="font-size:14px;font-weight:bold;margin-bottom:10px;color:#7a614d;">ご予約はこちら</div>

                            <div style="text-align:center;margin:18px 0;">
                                <a href="{{ $reserveUrl }}" style="display:inline-block;background:#bc8e6c;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:14px 28px;border-radius:999px;">
                                    予約ページを開く
                                </a>
                            </div>

                            <div style="word-break:break-all;background:#ffffff;border:1px solid #eadfd3;border-radius:12px;padding:14px;font-size:13px;line-height:1.8;color:#7b6654;">
                                {{ $reserveUrl }}
                            </div>
                        </div>

                        <div style="padding:18px 20px;background:#f8f2eb;border-radius:16px;">
                            <div style="font-size:13px;font-weight:bold;margin-bottom:10px;color:#7a614d;">店舗情報</div>
                            <div style="font-size:13px;line-height:1.9;color:#6b5b4d;">
                                <strong>{{ $company->name }}</strong><br>
                                @if($company->phone)
                                    TEL：{{ $company->phone }}<br>
                                @endif
                                @if($company->homepage)
                                    Web：{{ $company->homepage }}
                                @endif
                            </div>
                        </div>

                        <p style="margin:28px 0 0;font-size:14px;line-height:1.9;color:#6b5b4d;">
                            またのご来店を心よりお待ちしております。
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 32px;background:#f3ece4;font-size:12px;line-height:1.7;color:#9a8878;text-align:center;">
                        このメールは自動送信されています。<br>
                        © {{ date('Y') }} {{ $company->name }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>