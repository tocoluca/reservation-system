@extends('customer.layout')

@section('content')

<div class="min-h-screen bg-gradient-to-b from-stone-50 to-white px-4 py-10 sm:py-14">

    <div class="max-w-xl mx-auto">

        {{-- ヘッダー --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center rounded-full px-4 py-1.5 text-xs font-semibold"
                 style="background-color: {{ $company->theme_color }}15; color: {{ $company->theme_color }};">
                Questionnaire
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-stone-800 mt-4">
                問診票入力
            </h1>

            <p class="text-sm sm:text-base text-stone-500 mt-2 leading-6">
                ご来店前に症状をご記入いただくことで、当日のご案内がスムーズになります。
            </p>
        </div>

        <div class="bg-white w-full rounded-3xl shadow-xl border border-stone-200 overflow-hidden">

            <div class="px-6 sm:px-8 py-5 border-b border-stone-100"
                 style="background: linear-gradient(180deg, {{ $company->theme_color }}10 0%, #ffffff 100%);">
                <div class="text-sm text-stone-500">予約日時</div>
                <div class="mt-1 text-lg font-bold text-stone-800">
                    {{ $reservation->start_at->format('Y年m月d日 H:i') }}
                </div>
            </div>

            <div class="p-6 sm:p-8">

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-2xl text-sm">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST">
                    @csrf

                    <div class="mb-6">
                        <label class="block mb-2 text-sm font-semibold text-gray-700">
                            現在の症状
                        </label>

                        <textarea name="symptoms"
                                  rows="6"
                                  class="w-full border border-stone-300 p-4 rounded-2xl text-base
                                         focus:ring-2 focus:outline-none"
                                  style="--tw-ring-color: {{ $company->theme_color }}"
                                  placeholder="例：肩こり、頭痛、腰の違和感など、気になる症状をご記入ください。">{{ old('symptoms') }}</textarea>

                        <p class="text-xs text-stone-500 mt-2 leading-6">
                            強い痛みや気になる症状がある場合は、できるだけ具体的にご記入ください。
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full text-white py-4 rounded-2xl
                                   shadow-lg hover:opacity-90
                                   transition duration-200 font-semibold"
                            style="background: linear-gradient(135deg, {{ $company->theme_color }} 0%, {{ $company->theme_color }}dd 100%);">
                        送信する
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>

@endsection