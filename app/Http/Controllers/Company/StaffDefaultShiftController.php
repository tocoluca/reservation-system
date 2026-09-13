<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\ShiftPattern;
use App\Models\StaffDefaultShift;
use Illuminate\Support\Facades\DB;

class StaffDefaultShiftController extends Controller
{
    public function index()
    {
        $current = auth()->guard('company')->user();
        abort_if(!$current || !$current->canDashboard('card.default_shift'), 403);

        $company = $current->company;

        $staffs = Staff::where('company_id', $company->id)
            ->where('role', '!=', 'store_operator')
            ->where(fn ($query) => $query->whereNull('retired_at')->orWhere('retired_at', '>', today()->toDateString()))
            ->orderBy('priority_order')
            ->get();

        $patterns = ShiftPattern::where('company_id', $company->id)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $shifts = StaffDefaultShift::whereIn('staff_id', $staffs->pluck('id'))->get();
        $reviewStaffIds = DB::table('shift_review_requirements')
            ->where('company_id', $company->id)
            ->where('scope', 'default')
            ->whereIn('staff_id', $staffs->pluck('id'))
            ->pluck('staff_id')
            ->map(fn ($id) => (int) $id);

        return view('company.staff_default_shifts', [
            'staffs'   => $staffs,
            'patterns' => $patterns,
            'shifts'   => $shifts,
            'reviewStaffIds' => $reviewStaffIds,
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
                ->where(fn ($query) => $query->whereNull('retired_at')->orWhere('retired_at', '>', today()->toDateString()))
                ->pluck('id')
                ->map(fn ($id) => (string) $id);
            $validPatternIds = ShiftPattern::where('company_id', $current->company_id)
                ->pluck('id')
                ->map(fn ($id) => (string) $id);
            $savedStaffIds = collect();

            foreach ($request->shifts as $staffId => $days) {
                if (!$validStaffIds->contains((string) $staffId) || !is_array($days)) {
                    continue;
                }
                foreach ($days as $pattern) {
                    if ($pattern !== null && $pattern !== '' && !$validPatternIds->contains((string) $pattern)) {
                        return back()->withInput()->with('error', '削除済み、または利用できないシフトパターンが含まれています。画面を再読み込みして選び直してください。');
                    }
                }
            }

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

                $savedStaffIds->push((int) $staffId);
            }

            DB::table('shift_review_requirements')
                ->where('company_id', $current->company_id)
                ->where('scope', 'default')
                ->whereIn('staff_id', $savedStaffIds)
                ->delete();

            $nextMonthlyReview = DB::table('shift_review_requirements')
                ->where('company_id', $current->company_id)
                ->where('scope', 'monthly')
                ->orderBy('period')
                ->first();

            if ($nextMonthlyReview) {
                return redirect()->route('company.staff-shifts', ['month' => $nextMonthlyReview->period])
                    ->with('success', '基本シフトを保存しました。続けて、削除されたパターンを利用していた勤務シフトを確認してください。');
            }

            return back()->with('success', '基本シフトを保存しました。');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', '保存に失敗しました。もう一度お試しください。');
        }
    }
}
