<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>利用申請を受け付けました</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,'Hiragino Kaku Gothic ProN','Yu Gothic',sans-serif;color:#334155;">
    <div style="max-width:720px;margin:0 auto;padding:32px 16px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#0284c7,#2563eb);padding:24px 28px;color:#ffffff;">
                <div style="font-size:12px;font-weight:bold;letter-spacing:.08em;opacity:.9;">APPLICATION RECEIVED</div>
                <h1 style="margin:10px 0 0;font-size:24px;line-height:1.4;">利用申請を受け付けました</h1>
            </div>

            <div style="padding:28px;">
                <p style="margin:0 0 18px;font-size:14px;line-height:1.9;color:#475569;">
                    {{ $application->company_name }} ご担当者様
                </p>

                <p style="margin:0 0 20px;font-size:14px;line-height:1.9;color:#475569;">
                    このたびはシステム利用申請をいただき、ありがとうございます。<br>
                    以下の内容で申請を受け付けました。<br>
                    管理者にて確認後、あらためてご案内いたします。
                </p>

                <div style="margin-bottom:20px;padding:16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;">
                    <div style="font-size:12px;color:#1d4ed8;font-weight:bold;margin-bottom:6px;">受付番号</div>
                    <div style="font-size:28px;font-weight:bold;color:#0f172a;letter-spacing:.08em;">{{ $application->id }}</div>
                </div>

                <table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
                    <tr>
                        <td style="width:160px;padding:12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-weight:bold;font-size:14px;">企業名</td>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;">{{ $application->company_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-weight:bold;font-size:14px;">業種</td>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;">{{ $application->industry_label }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-weight:bold;font-size:14px;">担当者名</td>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;">{{ $application->contact_person }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-weight:bold;font-size:14px;">メールアドレス</td>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;">{{ $application->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-weight:bold;font-size:14px;">電話番号</td>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;">{{ $application->phone }}</td>
                    </tr>
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;background:#f8fafc;font-weight:bold;font-size:14px;">受付状態</td>
                        <td style="padding:12px;border-bottom:1px solid #e2e8f0;font-size:14px;">{{ $application->status_label }}</td>
                    </tr>
                </table>

                <div style="margin-bottom:24px;">
                    <div style="font-size:14px;font-weight:bold;margin-bottom:8px;">補足・お問い合わせ</div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px;font-size:14px;line-height:1.8;white-space:pre-wrap;color:#475569;">{{ $application->message ?: '（なし）' }}</div>
                </div>

                <div style="padding:16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;font-size:13px;line-height:1.8;color:#1e3a8a;">
                    内容確認後、利用開始のご案内または確認事項をメールでお送りします。
                </div>
            </div>
        </div>
    </div>
</body>
</html>