<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function edit()
    {
        $company = Auth::guard('company')->user()->company;
        return view('company.company_edit', compact('company'));
    }

    public function update(Request $request)
    {
        $company = Auth::guard('company')->user()->company;
        $current = Auth::guard('company')->user();

        if ($current->role !== 'master') {
            abort(403);
        }

        $rules = [
            'email' => 'nullable|email:rfc,dns',
            'homepage' => 'nullable|url',
            'theme_color' => 'required',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',

            'salon_message' => 'nullable|string|max:5000',
            'business_hours_text' => 'nullable|string|max:5000',
            'parking_info' => 'nullable|string|max:5000',
            'payment_methods' => 'nullable|string|max:5000',
            'access_info' => 'nullable|string|max:5000',
            'salon_note' => 'nullable|string|max:5000',

            'slot_minutes' => 'required|integer|min:5|max:120',
            'max_simultaneous_reservations' => 'required|integer|min:1|max:10',
            'open_patterns' => 'nullable|array',
            'regular_holidays' => 'nullable|array',
            'reservation_month_limit' => 'nullable|integer|min:1|max:12',
            'reservation_open_days' => 'nullable|integer|min:0|max:30',
            'reservation_close_hours' => 'nullable|integer|min:0|max:48',
            'revisit_reminder_days' => 'nullable|integer|min:1|max:365',
            'web_cancel_deadline_type' => 'nullable|in:hours,business_open_minus_1_hour',
            'web_cancel_deadline_hours' => 'nullable|integer|min:0|max:168',
            'reservation_auto_status_mode' => 'nullable|in:manual,completed,no_show',
            'reservation_auto_status_hours' => 'nullable|integer|min:1|max:3',
            'review_enabled' => 'nullable|boolean',
            'prefer_less_capable_staff_for_menu_assignment' => 'nullable|boolean',
            'customer_notification_channel' => 'nullable|in:both,email,line',
        ];

        $validated = $request->validate($rules, [
            'revisit_reminder_days.integer' => '再来店促進メール送信日数は数字で入力してください。',
            'revisit_reminder_days.min' => '再来店促進メール送信日数は1日以上で入力してください。',
            'revisit_reminder_days.max' => '再来店促進メール送信日数は365日以内で入力してください。',
            'web_cancel_deadline_hours.integer' => 'Webキャンセル締切時間は数字で入力してください。',
            'web_cancel_deadline_hours.min' => 'Webキャンセル締切時間は0時間以上で入力してください。',
            'web_cancel_deadline_hours.max' => 'Webキャンセル締切時間は168時間以内で入力してください。',
            'salon_message.max' => 'サロンからのメッセージは5000文字以内で入力してください。',
            'business_hours_text.max' => '営業時間は5000文字以内で入力してください。',
            'parking_info.max' => '駐車場案内は5000文字以内で入力してください。',
            'payment_methods.max' => '支払い方法は5000文字以内で入力してください。',
            'access_info.max' => 'アクセス案内は5000文字以内で入力してください。',
            'salon_note.max' => 'ご来店時のご案内は5000文字以内で入力してください。',
        ]);

        $dayNames = ['日', '月', '火', '水', '木', '金', '土'];

        foreach ($validated['open_patterns'] ?? [] as $weekday => $patterns) {
            foreach ($patterns as $index => $pattern) {
                if (!empty($pattern['open']) && !empty($pattern['close'])) {
                    if ($pattern['open'] >= $pattern['close']) {
                        $dayLabel = $dayNames[$weekday] ?? $weekday;
                        $slotNumber = $index + 1;

                        return back()
                            ->withErrors([
                                "open_patterns.$weekday.$index.open" =>
                                    "{$dayLabel}曜日 {$slotNumber}枠目：開始時間は終了時間より前にしてください"
                            ])
                            ->withInput();
                    }
                }
            }
        }

        $patterns = collect($request->open_patterns ?? [])
            ->map(function ($day) {
                return collect($day)
                    ->filter(fn ($p) => !empty($p['open']) && !empty($p['close']))
                    ->values()
                    ->toArray();
            })
            ->toArray();

        $updateData = [
            'email' => $validated['email'] ?? null,
            'homepage' => $validated['homepage'] ?? null,
            'theme_color' => $validated['theme_color'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,

            'salon_message' => $validated['salon_message'] ?? null,
            'business_hours_text' => $validated['business_hours_text'] ?? null,
            'parking_info' => $validated['parking_info'] ?? null,
            'payment_methods' => $validated['payment_methods'] ?? null,
            'access_info' => $validated['access_info'] ?? null,
            'salon_note' => $validated['salon_note'] ?? null,

            'reservation_month_limit' => $validated['reservation_month_limit'] ?? 3,
            'reservation_open_days' => $validated['reservation_open_days'] ?? 0,
            'reservation_close_hours' => $validated['reservation_close_hours'] ?? 1,
            'revisit_reminder_days' => $validated['revisit_reminder_days'] ?? 45,
            'web_cancel_deadline_type' => $validated['web_cancel_deadline_type'] ?? 'hours',
            'web_cancel_deadline_hours' => $validated['web_cancel_deadline_hours'] ?? 24,
            'reservation_auto_status_mode' => $validated['reservation_auto_status_mode'] ?? 'no_show',
            'reservation_auto_status_hours' => $validated['reservation_auto_status_hours'] ?? 1,
            'slot_minutes' => $validated['slot_minutes'],
            'max_simultaneous_reservations' => $validated['max_simultaneous_reservations'],
            'open_patterns' => $patterns,
            'regular_holidays' => $validated['regular_holidays'] ?? [],
            'holiday_is_closed' => $request->boolean('holiday_is_closed'),
            'menu_time_priority_flag' => $request->boolean('menu_time_priority_flag'),
            'review_enabled' => (bool) $request->input('review_enabled', 0),
            'prefer_less_capable_staff_for_menu_assignment' => $request->boolean('prefer_less_capable_staff_for_menu_assignment'),
            'customer_notification_channel' => $company->plan_code === 'platinum'
                ? ($validated['customer_notification_channel'] ?? 'both')
                : 'both',
        ];

        $company->update($updateData);

        return redirect()
            ->route('company.info.edit')
            ->with('success', '会社情報を更新しました');
    }
}
