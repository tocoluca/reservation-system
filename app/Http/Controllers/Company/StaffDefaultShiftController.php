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
        $current = auth()->guard('company')->user();
        abort_if(!$current || !$current->canDashboard('card.default_shift'), 403);

        $company = $current->company;

        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
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
        $current = auth()->guard('company')->user();
        abort_if(!$current || !$current->canDashboard('card.default_shift'), 403);

        try {
            $request->validate([
                'shifts' => ['required', 'array'],
            ], [
                'shifts.required' => 'シフト情報がありません。',
            ]);

            $validStaffIds = Staff::where('company_id', $current->company_id)
                ->where('role', '!=', 'store_operator')
                ->pluck('id')
                ->map(fn ($id) => (string) $id);

            foreach ($request->shifts as $staffId => $days) {
                if (!$validStaffIds->contains((string) $staffId)) {
                    continue;
                }

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
