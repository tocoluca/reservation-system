@extends('layouts.company')

@section('content')

@php
$company = auth()->guard('company')->user()->company;
$theme = $company->theme_color ?? '#3b82f6';
@endphp

<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- ヘッダー --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                シフトパターン
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                勤務時間の種類を登録します
            </p>
        </div>

        <a href="{{ route('company.dashboard') }}"
           class="px-3 py-2 border rounded-lg text-sm font-medium hover:bg-gray-50 transition"
           style="border-color:{{ $theme }}; color:{{ $theme }};">
            ← ダッシュボード
        </a>
    </div>

    {{-- 説明 --}}
    <div class="mb-5 rounded-xl border bg-gray-50 px-4 py-3 text-sm text-gray-600 leading-6">
        <p>
            勤務時間の種類が複数ある場合は、<span class="font-medium text-gray-800">早番・遅番などをそれぞれ登録</span>してください。
            1種類のみの場合は、<span class="font-medium text-gray-800">「通常」</span>などの名前で登録してください。
        </p>
    </div>

    {{-- エラー表示 --}}
    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">入力内容をご確認ください。</div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 完了メッセージ --}}
    @if (session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- 登録フォーム --}}
    <form method="POST" action="{{ route('company.shift-patterns.store') }}" class="mb-6">
        @csrf

        <div class="bg-white shadow rounded-2xl p-4 md:p-5 border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-start">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        パターン名 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="例：早番"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 @error('name') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        開始時間 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="start_time"
                        value="{{ old('start_time') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 @error('start_time') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        終了時間 <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="time"
                        name="end_time"
                        value="{{ old('end_time') }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 @error('end_time') border-red-400 focus:ring-red-200 @else border-gray-300 focus:ring-blue-100 @enderror">
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full px-4 py-2.5 text-white rounded-lg text-sm font-semibold shadow hover:opacity-90 transition"
                        style="background:{{ $theme }};">
                        追加
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- 一覧 --}}
    <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-100">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">登録済みパターン</h2>
        </div>

        @if($patterns->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:{{ $theme }}; color:white;">
                            <th class="p-3 text-left font-semibold">名前</th>
                            <th class="p-3 text-left font-semibold">勤務時間</th>
                            <th class="p-3 text-center font-semibold w-24">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patterns as $p)
                            <tr class="border-t">
                                <td class="p-3">{{ $p->name }}</td>
                                <td class="p-3">{{ substr($p->start_time,0,5) }} ～ {{ substr($p->end_time,0,5) }}</td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('company.shift-patterns.delete', $p->id) }}"
                                       class="text-red-500 hover:text-red-700 font-medium"
                                       onclick="return confirm('このシフトパターンを削除しますか？')">
                                        削除
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-10 text-center text-sm text-gray-500">
                まだシフトパターンは登録されていません。
            </div>
        @endif
    </div>

</div>

@endsection