<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\ShiftPattern;
use App\Models\StaffDefaultShift;

class StaffDefaultShiftController extends Controller
{
    public function index()
    {
        $company = auth()->guard('company')->user()->company;

        $staffs = Staff::where('company_id', $company->id)
            ->orderBy('priority_order')
            ->get();

        $patterns = ShiftPattern::where('company_id', $company->id)->get();

        $shifts = StaffDefaultShift::whereIn('staff_id', $staffs->pluck('id'))->get();

        return view('company.staff_default_shifts', [
            'staffs'   => $staffs,
            'patterns' => $patterns,
            'shifts'   => $shifts,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'shifts' => ['required', 'array'],
            ], [
                'shifts.required' => 'シフト情報がありません。',
            ]);

            foreach ($request->shifts as $staffId => $days) {
                foreach ($days as $weekday => $pattern) {
                    StaffDefaultShift::updateOrCreate(
                        [
                            'staff_id' => $staffId,
                            'weekday'  => $weekday,
                        ],
                        [
                            'shift_pattern_id' => $pattern ?: null,
                            'is_work'          => $pattern ? 1 : 0,
                        ]
                    );
                }
            }

            return back()->with('success', '基本シフトを保存しました。');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', '保存に失敗しました。もう一度お試しください。');
        }
    }
}