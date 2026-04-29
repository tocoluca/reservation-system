@php
    $company = $reservation->company;
    $customer = $reservation->customer;
    $staff = $reservation->staff;
    $menus = $reservation->menus ?? collect();
    $cancelUrl = !empty($reservation->cancel_token) ? url('/cancel/' . $reservation->cancel_token) : null;
    $cancelDeadlineAt = app(\App\Services\WebCancelDeadlineService::class)->deadlineFor($reservation);
    $cancelDeadlineText = $cancelDeadlineAt->format('Y年n月j日 G時')
        . ($cancelDeadlineAt->minute > 0 ? $cancelDeadlineAt->format('i分') : '');
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>明日のご予約のご案内</title>
</head>
<body style="margin:0;padding:0;background:#f7f3ee;font-family:'Hiragino Kaku Gothic ProN','Yu Gothic',Arial,sans-serif;color:#4b3f35;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ee;margin:0;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #eadfd3;">
                <tr>
                    <td style="background:linear-gradient(135deg,#d7b799,#b98a64);padding:24px 32px 28px;color:#ffffff;text-align:center;">
                        <div style="font-size:12px;letter-spacing:.12em;font-weight:bold;opacity:.9;">RESERVATION REMINDER</div>
                        <h1 style="margin:10px 0 0;font-size:26px;line-height:1.4;font-weight:bold;">明日のご予約のご案内</h1>
                        <p style="margin:10px 0 0;font-size:14px;line-height:1.8;opacity:.95;">
                            ご予約日時が近づいてまいりました。どうぞお気をつけてお越しください。
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 18px;font-size:15px;line-height:1.9;">
                            {{ $customer->name ?? 'お客様' }} 様
                        </p>

                        <p style="margin:0 0 24px;font-size:14px;line-height:1.9;color:#6b5b4d;">
                            いつもありがとうございます。<br>
                            <strong>{{ $company->name ?? 'ご予約店舗' }}</strong> です。<br>
                            明日のご予約内容をご案内いたします。
                        </p>

                        <div style="margin:0 0 24px;padding:22px;background:#fcf8f4;border:1px solid #eadfd3;border-radius:16px;">
                            <div style="font-size:12px;color:#9a7d63;font-weight:bold;letter-spacing:.08em;margin-bottom:12px;">ご予約内容</div>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="width:110px;padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">日時</td>
                                    <td style="padding:8px 0;font-size:15px;font-weight:bold;">
                                        {{ optional($reservation->start_at)->format('Y年n月j日 H:i') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">メニュー</td>
                                    <td style="padding:8px 0;font-size:14px;line-height:1.9;">
                                        @forelse($menus as $menu)
                                            ・{{ $menu->name }}<br>
                                        @empty
                                            ・ご予約内容
                                        @endforelse
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">担当者</td>
                                    <td style="padding:8px 0;font-size:14px;">
                                        {{ $staff->name ?? '未指定' }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="margin:0 0 24px;padding:20px;background:#fffaf5;border:1px solid #f0e2d4;border-radius:16px;">
                            <div style="font-size:14px;font-weight:bold;margin-bottom:10px;color:#6b533f;">キャンセルについて</div>
                            @if($cancelUrl)
                                <p style="margin:0 0 14px;font-size:14px;line-height:1.9;color:#6b5b4d;">
                                    キャンセルについては、下記URLにて <strong>{{ $cancelDeadlineText }}</strong> までに行ってください。
                                </p>

                                <div style="text-align:center;margin:18px 0;">
                                    <a href="{{ $cancelUrl }}" style="display:inline-block;background:#b7875c;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:14px 28px;border-radius:999px;">
                                        キャンセル手続きへ
                                    </a>
                                </div>

                                <p style="margin:14px 0 0;font-size:13px;line-height:1.8;color:#8a7665;">
                                    ※ {{ $cancelDeadlineText }} 以降のキャンセルは、店舗へご連絡願います。
                                </p>
                            @else
                                <p style="margin:0;font-size:13px;line-height:1.9;color:#7b6654;">
                                    ※ ご予約内容の変更・キャンセルをご希望の場合は、店舗までご連絡ください。
                                </p>
                            @endif
                        </div>

                        {{--
                            <p style="margin:0;font-size:13px;line-height:1.9;color:#7b6654;">
                                ※ ご予約内容の変更・キャンセルをご希望の場合は、店舗までご連絡ください。
                            </p>
                        --}}

                        <div style="padding:18px 20px;background:#f8f2eb;border-radius:16px;">
                            <div style="font-size:13px;font-weight:bold;margin-bottom:10px;color:#7a614d;">店舗情報</div>
                            <div style="font-size:13px;line-height:1.9;color:#6b5b4d;">
                                <strong>{{ $company->name ?? 'ご予約店舗' }}</strong><br>
                                @if(!empty($company->phone))
                                    TEL：{{ $company->phone }}<br>
                                @endif
                                @if(!empty($company->homepage))
                                    WEB：{{ $company->homepage }}
                                @endif
                            </div>
                        </div>

                        <p style="margin:28px 0 0;font-size:14px;line-height:1.9;color:#6b5b4d;">
                            ご来店を心よりお待ちしております。
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 32px;background:#f3ece4;font-size:12px;line-height:1.7;color:#9a8878;text-align:center;">
                        このメールは自動送信されています。<br>
                        © {{ date('Y') }} {{ $company->name ?? 'ご予約店舗' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
