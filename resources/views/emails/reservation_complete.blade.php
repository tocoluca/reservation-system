@php
    $cancelUrl = url('/cancel/' . $reservation->cancel_token);
    $cancelDeadlineAt = app(\App\Services\WebCancelDeadlineService::class)->deadlineFor($reservation);
    $cancelDeadlineText = $cancelDeadlineAt->format('Y年n月j日 G時')
        . ($cancelDeadlineAt->minute > 0 ? $cancelDeadlineAt->format('i分') : '');
    $reservation->loadMissing(['details.menu', 'details.staff', 'staff']);
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご予約ありがとうございます</title>
</head>
<body style="margin:0;padding:0;background:#f7f3ee;font-family:'Hiragino Kaku Gothic ProN','Yu Gothic',Arial,sans-serif;color:#4b3f35;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f3ee;margin:0;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #eadfd3;">
                <tr>
                    <td style="background:linear-gradient(135deg,#c9a27e,#b7875c);padding:24px 32px 28px;color:#ffffff;text-align:center;">
                        <div style="font-size:12px;letter-spacing:.12em;font-weight:bold;opacity:.9;">RESERVATION CONFIRMED</div>
                        <h1 style="margin:10px 0 0;font-size:28px;line-height:1.4;font-weight:bold;">ご予約ありがとうございます</h1>
                        <p style="margin:10px 0 0;font-size:14px;line-height:1.8;opacity:.95;">
                            ご予約を承りました。ご来店を心よりお待ちしております。
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 20px;font-size:15px;line-height:1.9;">
                            {{ $reservation->customer_name }} 様
                        </p>

                        <p style="margin:0 0 24px;font-size:14px;line-height:1.9;color:#6b5b4d;">
                            このたびは <strong>{{ $company->name }}</strong> へご予約いただき、誠にありがとうございます。<br>
                            以下の内容でご予約を受け付けいたしました。
                        </p>

                        <div style="margin:0 0 24px;padding:22px;background:#fcf8f4;border:1px solid #eadfd3;border-radius:16px;">
                            <div style="font-size:12px;color:#9a7d63;font-weight:bold;letter-spacing:.08em;margin-bottom:12px;">ご予約内容</div>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="width:110px;padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">日時</td>
                                    <td style="padding:8px 0;font-size:15px;font-weight:bold;color:#4b3f35;">
                                        {{ \Carbon\Carbon::parse($reservation->start_at)->format('Y年m月d日 H:i') }}
                                        〜
                                        {{ \Carbon\Carbon::parse($reservation->end_at)->format('H:i') }}
                                    </td>
                                </tr>

                                @if(!empty($reservation->staff?->name))
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">代表担当</td>
                                    <td style="padding:8px 0;font-size:14px;">{{ $reservation->staff->name }}</td>
                                </tr>
                                @endif

                                @if(!empty($reservation->total_price))
                                <tr>
                                    <td style="padding:8px 0;font-size:13px;color:#8a7665;vertical-align:top;">料金目安</td>
                                    <td style="padding:8px 0;font-size:14px;">{{ number_format($reservation->total_price) }}円</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <div style="margin:0 0 24px;padding:20px;background:#fffaf5;border:1px solid #f0e2d4;border-radius:16px;">
                            <div style="font-size:14px;font-weight:bold;margin-bottom:12px;color:#6b533f;">施術内容</div>

                            @if($reservation->details->isNotEmpty())
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:10px 0;border-bottom:1px solid #eadfd3;font-size:12px;font-weight:bold;color:#8a7665;">メニュー</td>
                                        <td style="padding:10px 0;border-bottom:1px solid #eadfd3;font-size:12px;font-weight:bold;color:#8a7665;">担当</td>
                                        <td style="padding:10px 0;border-bottom:1px solid #eadfd3;font-size:12px;font-weight:bold;color:#8a7665;">時間</td>
                                        <td align="right" style="padding:10px 0;border-bottom:1px solid #eadfd3;font-size:12px;font-weight:bold;color:#8a7665;">料金</td>
                                    </tr>

                                    @foreach($reservation->details as $detail)
                                        <tr>
                                            <td style="padding:12px 0;border-bottom:1px solid #f3e8dc;font-size:14px;color:#4b3f35;">
                                                {{ $detail->menu->name ?? 'メニュー' }}
                                            </td>
                                            <td style="padding:12px 0;border-bottom:1px solid #f3e8dc;font-size:14px;color:#4b3f35;">
                                                {{ $detail->staff->name ?? '未設定' }}
                                            </td>
                                            <td style="padding:12px 0;border-bottom:1px solid #f3e8dc;font-size:14px;color:#4b3f35;">
                                                {{ \Carbon\Carbon::parse($detail->start_at)->format('H:i') }}
                                                〜
                                                {{ \Carbon\Carbon::parse($detail->end_at)->format('H:i') }}
                                            </td>
                                            <td align="right" style="padding:12px 0;border-bottom:1px solid #f3e8dc;font-size:14px;color:#4b3f35;">
                                                {{ number_format((int) ($detail->price ?? 0)) }}円
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <div style="font-size:14px;line-height:1.9;color:#6b5b4d;">
                                    @if(!empty($reservation->staff?->name))
                                        担当：{{ $reservation->staff->name }}<br>
                                    @endif

                                    @foreach(($reservation->menus ?? collect()) as $menuRow)
                                        ・{{ $menuRow->menu->name ?? 'メニュー' }}
                                        @if(!empty($menuRow->price))
                                            （{{ number_format((int) $menuRow->price) }}円）
                                        @endif
                                        <br>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div style="margin:0 0 24px;padding:20px;background:#fffaf5;border:1px solid #f0e2d4;border-radius:16px;">
                            <div style="font-size:14px;font-weight:bold;margin-bottom:10px;color:#6b533f;">キャンセルについて</div>
                            <p style="margin:0 0 14px;font-size:14px;line-height:1.9;color:#6b5b4d;">
                                ご予約のキャンセルは、下記ボタンまたはURLにて <strong>{{ $cancelDeadlineText }}</strong> までにお手続きください。
                            </p>

                            <div style="text-align:center;margin:18px 0;">
                                <a href="{{ $cancelUrl }}" style="display:inline-block;background:#b7875c;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:14px 28px;border-radius:999px;">
                                    キャンセル手続きへ
                                </a>
                            </div>

                            <p style="margin:14px 0 0;font-size:13px;line-height:1.8;color:#8a7665;">
                                ※ {{ $cancelDeadlineText }} 以降のキャンセルは、お手数ですがお電話にてご連絡をお願いいたします。
                            </p>
                        </div>

                        <div style="padding:18px 20px;background:#f8f2eb;border-radius:16px;">
                            <div style="font-size:13px;font-weight:bold;margin-bottom:10px;color:#7a614d;">店舗情報</div>
                            <div style="font-size:13px;line-height:1.9;color:#6b5b4d;">
                                <strong>{{ $company->name }}</strong><br>
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
                        © {{ date('Y') }} {{ $company->name }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
