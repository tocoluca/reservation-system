<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->user('company');

        return $staff && $staff->canDashboard('card.company_info');
    }

    public function rules(): array
    {
        return [
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
            'reservation_hero_heading' => ['nullable', 'string', 'max:120'],
            'reservation_hero_subheading' => ['nullable', 'string', 'max:240'],
            'reservation_hero_heading_size' => ['required', 'integer', 'min:20', 'max:96'],
            'reservation_hero_subheading_size' => ['required', 'integer', 'min:12', 'max:48'],
            'reservation_hero_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'hero_image.image' => 'メイン画像には画像ファイルを選択してください。',
            'hero_image.mimes' => 'メイン画像はJPEG、PNG、WebP形式にしてください。',
            'hero_image.max' => 'メイン画像は10MB以内にしてください。',
            'reservation_hero_text_color.regex' => '文字色を正しく指定してください。',
        ];
    }
}
