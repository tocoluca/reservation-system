@extends('layouts.app')

@section('content')

@php
    $theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="min-h-screen bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-3xl mx-auto px-4 py-6 sm:py-10">

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 sm:px-8 py-8 text-center text-white"
                 style="background: linear-gradient(135deg, {{ $theme }}, #111827)">
                <div class="w-16 h-16 mx-auto rounded-full bg-white/15 flex items-center justify-center text-2xl mb-4">
                    i
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold mb-2">
                    お知らせ
                </h1>

                <p class="text-sm sm:text-base text-white/80 leading-6">
                    ご来店前にご確認ください
                </p>
            </div>

            <div class="p-5 sm:p-8">

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @if(!empty($notice->is_important))
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">
                            重要
                        </span>
                    @endif

                    @if(method_exists($notice, 'isNew') && $notice->isNew())
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600">
                            NEW
                        </span>
                    @endif

                    <span class="text-xs text-gray-400">
                        {{ $notice->created_at?->format('Y/m/d') }}
                    </span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ $notice->title }}
                </h2>

                @if($notice->image)
                    <div class="mb-6">
                        <img
                            src="{{ asset($notice->image) }}"
                            alt="{{ $notice->title }}"
                            class="w-full rounded-3xl border border-gray-100 object-contain max-h-[640px] bg-gray-50">
                    </div>
                @endif

                <div class="bg-gray-50 rounded-3xl p-5 sm:p-6">
                    <div class="whitespace-pre-line text-gray-700 leading-8 text-sm sm:text-base">
                        {{ $notice->content }}
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ url('/r/'.$company->company_code) }}"
                       class="block w-full text-white py-4 rounded-2xl text-center font-semibold"
                       style="background: {{ $theme }}">
                        予約トップへ戻る
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
