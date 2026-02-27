<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>アカウント発行のお知らせ</title>
</head>

<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:20px 0;">
<tr>
<td align="center">

<table width="100%" cellpadding="0" cellspacing="0"
       style="max-width:600px; background:#ffffff; border-radius:8px; padding:30px;">

<tr>
<td style="font-size:18px; font-weight:bold; padding-bottom:20px;">
{{ $company->name }} 様
</td>
</tr>

<tr>
<td style="font-size:14px; line-height:1.8; padding-bottom:20px;">
予約システムのアカウントが発行されました。
</td>
</tr>

<tr>
<td align="center" style="padding-bottom:25px;">
<a href="https://reserve.tocoluca.com/company/login"
   style="background:#3b82f6; color:#ffffff; padding:12px 24px;
          text-decoration:none; border-radius:6px; display:inline-block; font-size:14px;">
ログインページへ
</a>
</td>
</tr>

<tr>
<td style="background:#f9fafb; padding:20px; border-radius:6px; font-size:14px; line-height:1.8;">

<strong>ログイン情報</strong><br><br>

企業コード：{{ $company->company_code }}<br>
担当者コード：{{ $staff_code }}<br>
担当者名：{{ $staff_name }}<br>
仮パスワード：{{ $password }}

</td>
</tr>

<tr>
<td style="font-size:13px; color:#6b7280; padding-top:20px; line-height:1.6;">
※初回ログイン時にパスワード変更が必要です。<br>
セキュリティのため、第三者に共有しないようご注意ください。
</td>
</tr>

<tr>
<td style="padding-top:30px; font-size:12px; color:#9ca3af; text-align:center;">
© {{ date('Y') }} {{ $company->name }}
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>