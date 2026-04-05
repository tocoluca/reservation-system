<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShiftPattern;

class ShiftPatternController extends Controller
{
    public function index()
    {
        $company = auth()->guard('company')->user()->company;

        $patterns = ShiftPattern::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $presetColors = [
            '#3b82f6',
            '#8b5cf6',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#06b6d4',
            '#6366f1',
            '#ec4899',
            '#64748b',
            '#14b8a6',
        ];

        return view('company.shift_patterns', compact('patterns', 'presetColors'));
    }

    public function store(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $request->validate(
            [
                'name'       => ['required', 'string', 'max:50'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
                'color'      => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'sort_order' => ['nullable', 'integer', 'min:1'],
            ],
            [
                'name.required'          => 'パターン名を入力してください。',
                'name.max'               => 'パターン名は50文字以内で入力してください。',
                'start_time.required'    => '開始時間を入力してください。',
                'start_time.date_format' => '開始時間の形式が正しくありません。',
                'end_time.required'      => '終了時間を入力してください。',
                'end_time.date_format'   => '終了時間の形式が正しくありません。',
                'end_time.after'         => '終了時間は開始時間より後の時間を設定してください。',
                'color.regex'            => '色の形式が正しくありません。',
                'sort_order.integer'     => '表示順は数字で入力してください。',
                'sort_order.min'         => '表示順は1以上で入力してください。',
            ]
        );

        $maxSortOrder = (int) ShiftPattern::where('company_id', $company->id)->max('sort_order');
        $newSortOrder = $request->filled('sort_order')
            ? (int) $request->sort_order
            : $maxSortOrder + 1;

        ShiftPattern::where('company_id', $company->id)
            ->where('sort_order', '>=', $newSortOrder)
            ->increment('sort_order');

        ShiftPattern::create([
            'company_id' => $company->id,
            'name'       => $request->name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'color'      => $request->filled('color') ? $request->color : '#64748b',
            'sort_order' => $newSortOrder,
        ]);

        return back()->with('success', 'シフトパターンを追加しました。');
    }

    public function updateOrder(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $request->validate([
            'orders' => ['required', 'array'],
            'orders.*' => ['required', 'integer', 'min:1'],
        ]);

        $patterns = ShiftPattern::where('company_id', $company->id)->get()->keyBy('id');

        foreach ($request->orders as $id => $sortOrder) {
            if (isset($patterns[$id])) {
                $patterns[$id]->update([
                    'sort_order' => (int) $sortOrder,
                ]);
            }
        }

        return back()->with('success', '表示順を更新しました。');
    }

    public function delete($id)
    {
        $company = auth()->guard('company')->user()->company;

        $pattern = ShiftPattern::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if ($pattern) {
            $deletedSortOrder = $pattern->sort_order;
            $pattern->delete();

            ShiftPattern::where('company_id', $company->id)
                ->where('sort_order', '>', $deletedSortOrder)
                ->decrement('sort_order');
        }

        return back()->with('success', 'シフトパターンを削除しました。');
    }
}