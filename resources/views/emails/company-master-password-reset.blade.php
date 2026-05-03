<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マスター権限パスワード初期化のお知らせ</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, 'Hiragino Kaku Gothic ProN', Meiryo, sans-serif; color:#111827;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:24px 0;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background:#ffffff; border-radius:16px; overflow:hidden;">
                <tr>
                    <td style="background:#7c3aed; color:#ffffff; padding:24px 28px;">
                        <div style="font-size:12px; font-weight:bold; letter-spacing:.08em; opacity:.85;">MASTER PASSWORD RESET</div>
                        <div style="font-size:22px; font-weight:bold; margin-top:8px;">マスター権限パスワードを初期化しました</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px; font-size:14px; line-height:1.9;">
                        <p style="margin:0 0 16px;">{{ $company->name }} 様</p>
                        <p style="margin:0 0 16px;">
                            ご依頼により、予約管理システムのマスター権限担当者のパスワードを初期化しました。
                        </p>

                        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:18px; margin:20px 0;">
                            <div style="font-weight:bold; margin-bottom:10px;">ログイン情報</div>
                            <div>企業コード: <strong>{{ $company->company_code }}</strong></div>
                            <div>対象マスターコード: <strong>{{ implode('、', $masterStaffCodes) }}</strong></div>
                            <div>初期パスワード: <strong>{{ $initialPassword }}</strong></div>
                        </div>

                        <p style="margin:0 0 16px;">
                            初期パスワードでログイン後、画面の案内に従って新しいパスワードへ変更してください。
                        </p>
                        <p style="margin:0 0 20px; color:#b91c1c; font-weight:bold;">
                            セキュリティのため、このメールを第三者へ転送しないでください。
                        </p>

                        <p style="text-align:center; margin:24px 0;">
                            <a href="{{ route('company.login') }}"
                               style="display:inline-block; background:#7c3aed; color:#ffffff; text-decoration:none; border-radius:12px; padding:12px 22px; font-weight:bold;">
                                ログイン画面へ
                            </a>
                        </p>

                        <p style="margin:0; color:#6b7280; font-size:12px;">
                            送信元: system@tocoluca.com
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
