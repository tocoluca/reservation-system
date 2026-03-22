@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('company.dashboard') }}"
           class="inline-flex items-center px-4 py-2 rounded-xl border font-semibold"
           style="border-color: {{ $theme }}; color: {{ $theme }}">
            ← ダッシュボードへ戻る
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="p-6 md:p-8">

            <div class="flex flex-wrap items-center gap-2 mb-3">
                @if($notice->is_important)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">重要</span>
                @endif

                @if($notice->is_new)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">NEW</span>
                @endif

                <span class="text-xs text-gray-500">
                    {{ optional($notice->start_date)->format('Y/m/d') ?: '指定なし' }}
                    〜
                    {{ optional($notice->end_date)->format('Y/m/d') ?: '指定なし' }}
                </span>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                {{ $notice->title }}
            </h1>

            @if($notice->image)
                <div class="mb-6">
                    <img src="{{ asset($notice->image) }}"
                         class="w-full max-h-[420px] object-contain rounded-2xl border bg-gray-50">
                </div>
            @endif

            <div class="whitespace-pre-wrap text-gray-700 leading-7">
                {{ $notice->body }}
            </div>
        </div>
    </div>
</div>
@endsection