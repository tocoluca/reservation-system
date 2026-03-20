<h2>{{ $customer->name }}様</h2>

<p>

そろそろヘアメンテナンスの時期です。

</p>

<p>

ご予約はこちら

</p>

<p>

<a href="{{ url('/r/'.$customer->company->company_code) }}">
予約ページ
</a>

</p>