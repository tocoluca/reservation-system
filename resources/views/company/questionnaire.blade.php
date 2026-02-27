@extends('customer.layout')

@section('content')

<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-10">

    <div class="bg-white w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-lg">

        {{-- タイトル --}}
        <h1 class="text-xl sm:text-2xl font-bold mb-6 text-center">
            問診票入力
        </h1>

        {{-- 予約日時 --}}
        <div class="mb-6 text-sm text-gray-600 text-center">
            予約日時：
            {{ $reservation->start_at->format('Y年m月d日 H:i') }}
        </div>

        {{-- エラー表示 --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 mb-6 rounded-lg text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- フォーム --}}
        <form method="POST">
            @csrf

            <div class="mb-6">
                <label class="block mb-2 text-sm font-semibold text-gray-700">
                    現在の症状
                </label>

                <textarea name="symptoms"
                          rows="5"
                          class="w-full border p-3 rounded-lg text-base
                                 focus:ring-2 focus:outline-none"
                          style="--tw-ring-color: {{ $company->theme_color }}"
                          placeholder="現在の症状をご記入ください">{{ old('symptoms') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full text-white py-4 rounded-full
                           shadow-lg hover:opacity-90
                           transition duration-200 font-semibold"
                    style="background-color: {{ $company->theme_color }}">
                送信する
            </button>

        </form>

    </div>

</div>

@endsection