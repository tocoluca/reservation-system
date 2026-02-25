<p>{{ $company->name }} 様</p>

<p>予約システムのアカウントが発行されました。</p>

<p>
ログインURL：<br>
https://reserve.tocoluca.com/company/login
</p>

<p>
企業コード　：{{ $company->company_code }}<br>
担当者コード：{{ $staff_code }}<br>
担当者名：{{ $staff_name }}<br>
仮パスワード：{{ $password }}
</p>

<p>
※初回ログイン時にパスワード変更が必要です。
</p>