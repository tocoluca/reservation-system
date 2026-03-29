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
            'slot_minutes' => 'required|integer|min:5|max:120',
            'max_simultaneous_reservations' => 'required|integer|min:1|max:10',
            'open_patterns' => 'nullable|array',
            'regular_holidays' => 'nullable|array',
            'reservation_month_limit' => 'nullable|integer|min:1|max:12',
            'reservation_open_days' => 'nullable|integer|min:0|max:30',
            'reservation_close_hours' => 'nullable|integer|min:0|max:48',
            'revisit_reminder_days' => 'nullable|integer|min:1|max:365',
            'web_cancel_deadline_hours' => 'nullable|integer|min:0|max:168',
        ];

        if ((int) $company->line_login_enabled === 1) {
            $rules['line_channel_id'] = 'nullable|string|max:255';
            $rules['line_channel_secret'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules, [
            'line_channel_id.max' => 'LINE Channel ID は255文字以内で入力してください。',
            'line_channel_secret.max' => 'LINE Channel Secret は255文字以内で入力してください。',
            'revisit_reminder_days.integer' => '再来店促進メール送信日数は数字で入力してください。',
            'revisit_reminder_days.min' => '再来店促進メール送信日数は1日以上で入力してください。',
            'revisit_reminder_days.max' => '再来店促進メール送信日数は365日以内で入力してください。',
            'web_cancel_deadline_hours.integer' => 'Webキャンセル締切時間は数字で入力してください。',
            'web_cancel_deadline_hours.min' => 'Webキャンセル締切時間は0時間以上で入力してください。',
            'web_cancel_deadline_hours.max' => 'Webキャンセル締切時間は168時間以内で入力してください。',
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
                    ->filter(fn($p) => !empty($p['open']) && !empty($p['close']))
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
            'reservation_month_limit' => $validated['reservation_month_limit'] ?? 3,
            'reservation_open_days' => $validated['reservation_open_days'] ?? 0,
            'reservation_close_hours' => $validated['reservation_close_hours'] ?? 1,
            'revisit_reminder_days' => $validated['revisit_reminder_days'] ?? 45,
            'web_cancel_deadline_hours' => $validated['web_cancel_deadline_hours'] ?? 24,
            'slot_minutes' => $validated['slot_minutes'],
            'max_simultaneous_reservations' => $validated['max_simultaneous_reservations'],
            'open_patterns' => $patterns,
            'regular_holidays' => $validated['regular_holidays'] ?? [],
            'holiday_is_closed' => $request->boolean('holiday_is_closed'),
            'menu_time_priority_flag' => $request->boolean('menu_time_priority_flag'),
        ];

        if ((int) $company->line_login_enabled === 1) {
            $updateData['line_channel_id'] = $validated['line_channel_id'] ?? null;
            $updateData['line_channel_secret'] = $validated['line_channel_secret'] ?? null;
        }

        $company->update($updateData);

        return redirect()
            ->route('company.info.edit')
            ->with('success', '会社情報を更新しました');
    }
}