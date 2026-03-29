{{ $customer->name ?? 'お客様' }} 様

いつも {{ $company->name }} をご利用いただきありがとうございます。

前回のご来店から少しお日にちが経ちましたので、
ご都合のよいタイミングで、ぜひお早めに次回のご予約をご検討ください。

ご予約はこちら
{{ url('/r/' . $company->company_code) }}

またのご来店を心よりお待ちしております。

{{ $company->name }}
@if($company->phone)
TEL：{{ $company->phone }}
@endif
@if($company->homepage)
Web：{{ $company->homepage }}
@endif