<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>口コミのご協力をお願いします</title>
</head>
<body style="margin:0; padding:0; background:#f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color:#334155; line-height:1.8;">
    <div style="max-width:640px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden;">
            <div style="padding:32px 28px; background:linear-gradient(135deg, #fff7ed 0%, #ffffff 100%); border-bottom:1px solid #f1f5f9;">
                <div style="display:inline-block; padding:6px 12px; border-radius:9999px; background:#111827; color:#ffffff; font-size:12px; font-weight:700; letter-spacing:0.04em;">
                    THANK YOU
                </div>
                <h1 style="margin:16px 0 0; font-size:24px; line-height:1.5; color:#0f172a;">
                    ご来店ありがとうございました
                </h1>
            </div>

            <div style="padding:28px;">
                <p style="margin:0 0 18px;">{{ $reservation->customer_name }} 様</p>

                <p style="margin:0 0 18px;">
                    このたびは <strong>{{ $company->name }}</strong> にご来店いただき、ありがとうございました。
                </p>

                <p style="margin:0 0 18px;">
                    その後、施術や接客はいかがでしたでしょうか。<br>
                    今後のサービス向上のため、よろしければご感想をお聞かせください。
                </p>

                <div style="margin:28px 0; text-align:center;">
                    <a href="{{ $reviewUrl }}"
                       style="display:inline-block; padding:14px 24px; background:#111827; color:#ffffff; text-decoration:none; border-radius:12px; font-weight:700;">
                        口コミを投稿する
                    </a>
                </div>

                <div style="margin:0 0 18px; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
                    <div style="font-size:13px; color:#64748b; margin-bottom:8px;">ボタンが開けない場合はこちら</div>
                    <div style="font-size:14px; word-break:break-all; color:#0f172a;">{{ $reviewUrl }}</div>
                </div>

                <p style="margin:0 0 18px; font-size:14px; color:#64748b;">
                    ※ 1〜2分ほどでご回答いただけます。<br>
                    ※ このURLはお客様専用です。
                </p>

                <p style="margin:24px 0 0;">
                    またのご来店を心よりお待ちしております。<br>
                    {{ $company->name }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>