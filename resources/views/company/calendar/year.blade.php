@extends('layouts.company')

@section('content')
<div class="max-w-6xl mx-auto p-4">

    <h2 class="text-2xl font-bold mb-6">
        {{ $year }}年 年間カレンダー
    </h2>

    <div class="grid grid-cols-3 gap-6">
        @for($m = 1; $m <= 12; $m++)
            <a href="{{ route('company.calendar.index', ['year'=>$year,'month'=>$m]) }}"
               class="p-4 border rounded hover:bg-gray-100 text-center">
                {{ $m }}月
            </a>
        @endfor
    </div>

</div>
@endsection