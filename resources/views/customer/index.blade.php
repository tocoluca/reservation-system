@extends('layouts.customer')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow text-center">

    <h1 class="text-xl font-bold mb-4">
        ご予約はこちら
    </h1>

    <a href="{{ url('r/'.$company->company_code.'/calendar') }}"
       style="background-color: {{ $company->theme_color }}"
       class="block text-white py-3 rounded-full">
       予約を開始する
    </a>

</div>

@endsection