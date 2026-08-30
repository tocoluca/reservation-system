@extends('layouts.company')

@section('content')
@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <div class="rounded-3xl shadow-sm overflow-hidden mb-6 border border-gray-100 bg-white">
        <div class="p-6 sm:p-8 text-white" style="background: var(--company-theme-gradient);">
            <p class="text-sm opacity-90 mb-2">Edit Notice</p>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">お知らせ編集</h1>
            <p class="text-sm sm:text-base opacity-90 mt-2">「{{ $notice->title }}」の内容と公開設定を変更します。</p>
        </div>
    </div>
    <div class="mb-6">@include('company.notices._notice_nav', ['current' => 'edit'])</div>
    @include('company.notices._form', ['notice' => $notice, 'editing' => true, 'theme' => $theme, 'action' => route('company.notices.update', $notice)])
</div>
@endsection
