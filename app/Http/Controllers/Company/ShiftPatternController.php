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
            ->orderBy('id')
            ->get();

        return view('company.shift_patterns', compact('patterns'));
    }

    public function store(Request $request)
    {
        $company = auth()->guard('company')->user()->company;

        $request->validate(
            [
                'name'       => ['required', 'string', 'max:50'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            ],
            [
                'name.required'       => 'パターン名を入力してください。',
                'name.max'            => 'パターン名は50文字以内で入力してください。',
                'start_time.required' => '開始時間を入力してください。',
                'start_time.date_format' => '開始時間の形式が正しくありません。',
                'end_time.required'   => '終了時間を入力してください。',
                'end_time.date_format' => '終了時間の形式が正しくありません。',
                'end_time.after'      => '終了時間は開始時間より後の時間を設定してください。',
            ]
        );

        ShiftPattern::create([
            'company_id' => $company->id,
            'name'       => $request->name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'color'      => $request->input('color', null),
        ]);

        return back()->with('success', 'シフトパターンを追加しました。');
    }

    public function delete($id)
    {
        $company = auth()->guard('company')->user()->company;

        ShiftPattern::where('id', $id)
            ->where('company_id', $company->id)
            ->delete();

        return back()->with('success', 'シフトパターンを削除しました。');
    }
}