@extends('layouts.company')

@section('content')

@php
    $company = auth()->guard('company')->user()->company;
    $theme = $company->theme_color ?? '#3b82f6';
    $mainPhoto = $customer->photos->first();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">

    {{-- 戻る --}}
    <div class="mb-5">
        <a href="{{ route('company.customers') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border shadow-sm hover:bg-gray-50 transition text-sm font-semibold"
           style="color: {{ $theme }}; border-color: {{ $theme }}22;">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 19l-7-7 7-7" />
            </svg>
            顧客一覧に戻る
        </a>
    </div>

    {{-- プロフィール --}}
    <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-gradient-to-br from-rose-50 via-white to-amber-50 shadow-sm mb-6">
        <div class="absolute top-0 left-0 w-full h-1.5" style="background: {{ $theme }};"></div>

        <div class="px-5 sm:px-8 py-6 sm:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_320px] gap-6 items-center">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $theme }}"></span>
                        CUSTOMER PROFILE
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        {{ $customer->name }}
                    </h1>

                    <p class="mt-2 text-sm text-gray-500">
                        顧客情報とメモ、写真をまとめて管理できます。
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mt-6">
                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">電話番号</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900 break-all">
                                {{ $customer->phone ?: '-' }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">来店回数</div>
                            <div class="mt-1 text-lg font-bold text-gray-900">
                                {{ number_format($customer->visit_count) }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">最終来店</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900">
                                @if($customer->last_visit)
                                    {{ \Carbon\Carbon::parse($customer->last_visit)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white/90 border border-gray-100 px-4 py-4 shadow-sm">
                            <div class="text-[11px] font-semibold text-gray-500">メール</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900 break-all">
                                {{ $customer->email ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center lg:justify-end">
                    <div class="w-full max-w-[320px]">
                        <div class="rounded-[1.75rem] bg-white p-4 shadow-sm border border-amber-100">
                            <div class="aspect-[4/5] overflow-hidden rounded-[1.5rem] bg-gray-100">
                                <img
                                    src="{{ $mainPhoto && $mainPhoto->path ? asset($mainPhoto->path) : asset('images/noimage.png') }}"
                                    alt="{{ $customer->name }}"
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="mt-3 text-sm text-gray-500 text-center">
                                登録写真
                                @if($customer->photos->count())
                                    {{ $customer->photos->count() }}件
                                @else
                                    0件
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 下段 --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- メモ --}}
        <div class="bg-white shadow-sm rounded-[1.75rem] border border-gray-100 overflow-hidden">
            <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-bold text-gray-900">顧客メモ</h2>
                <p class="text-sm text-gray-500 mt-1">
                    カウンセリング内容や対応履歴などを記録できます。
                </p>
            </div>

            <div class="p-5 sm:p-6">
                <form method="POST" action="{{ route('company.customers.note',$customer->id) }}">
                    @csrf

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        新しいメモを追加
                    </label>

                    <textarea
                        name="note"
                        rows="4"
                        class="w-full border border-gray-300 rounded-2xl p-4 text-sm focus:outline-none focus:ring-4 focus:border-transparent"
                        style="--tw-ring-color: {{ $theme }}22;"
                        placeholder="メモを入力してください"></textarea>

                    <div class="mt-4">
                        <button
                            type="submit"
                            style="background: {{ $theme }}"
                            class="text-white px-6 py-3 rounded-2xl font-semibold shadow-sm hover:opacity-90 transition">
                            保存する
                        </button>
                    </div>
                </form>

                <div class="mt-6 space-y-3">
                    @forelse($customer->notes as $note)
                        <div class="border border-gray-100 rounded-2xl p-4 bg-gray-50/80 flex justify-between items-start gap-4">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-800 whitespace-pre-line break-words">
                                    {{ $note->note }}
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ $note->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            <form method="POST"
                                  action="{{ route('company.customers.note.delete', $note->id) }}"
                                  onsubmit="return confirm('削除しますか？')"
                                  class="shrink-0">
                                @csrf
                                @method('DELETE')

                                <button class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-white border border-red-100 text-red-500 text-sm font-semibold hover:bg-red-50 transition">
                                    削除
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-gray-50 border border-dashed border-gray-200 p-6 text-center">
                            <div class="text-sm font-semibold text-gray-600">メモはまだありません</div>
                            <div class="text-xs text-gray-400 mt-1">必要な内容を追加するとここに表示されます。</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 写真 --}}
        <div class="bg-white shadow-sm rounded-[1.75rem] border border-gray-100 overflow-hidden">
            <div class="px-5 sm:px-6 py-5 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-bold text-gray-900">顧客写真</h2>
                <p class="text-sm text-gray-500 mt-1">
                    施術記録やスタイル履歴の写真を管理できます。
                </p>
            </div>

            <div class="p-5 sm:p-6">
                <form
                    method="POST"
                    action="{{ route('company.customers.photo',$customer->id) }}"
                    enctype="multipart/form-data"
                    class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    @csrf

                    <input
                        type="file"
                        name="photo"
                        class="flex-1 border border-gray-300 bg-white rounded-2xl px-4 py-3 text-sm">

                    <button
                        type="submit"
                        style="background: {{ $theme }}"
                        class="text-white px-6 py-3 rounded-2xl font-semibold shadow-sm hover:opacity-90 transition">
                        アップロード
                    </button>
                </form>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6">
                    @forelse($customer->photos as $photo)
                        <div class="group relative rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 shadow-sm">
                            <div class="aspect-[4/5] overflow-hidden">
                                <img
                                    src="{{ asset($photo->path) }}"
                                    class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-300">
                            </div>

                            <form method="POST"
                                  action="{{ route('company.customers.photo.delete', $photo->id) }}"
                                  onsubmit="return confirm('削除しますか？')"
                                  class="absolute top-2 right-2">
                                @csrf
                                @method('DELETE')

                                <button class="bg-black/55 hover:bg-black/70 text-white px-2.5 py-1.5 text-xs rounded-xl transition">
                                    削除
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl bg-gray-50 border border-dashed border-gray-200 p-8 text-center">
                            <div class="text-sm font-semibold text-gray-600">写真はまだありません</div>
                            <div class="text-xs text-gray-400 mt-1">アップロードするとここに表示されます。</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

@endsection