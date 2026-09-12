<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffDefaultShift;
use App\Models\StaffShift;
use App\Models\ShiftPattern;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftPatternController extends Controller
{
    private function authorizeShiftPatterns(): void
    {
        $staff = auth()->guard('company')->user();
        abort_if(!$staff || !$staff->canDashboard('card.shift_patterns'), 403);
    }

    public function index()
    {
        $this->authorizeShiftPatterns();

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
        $this->authorizeShiftPatterns();

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
        $this->authorizeShiftPatterns();

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
        $this->authorizeShiftPatterns();

        $company = auth()->guard('company')->user()->company;

        $pattern = ShiftPattern::where('id', $id)
            ->where('company_id', $company->id)
            ->first();

        if ($pattern) {
            $deletedSortOrder = $pattern->sort_order;
            $activeStaffIds = Staff::where('company_id', $company->id)
                ->where(fn ($query) => $query->whereNull('retired_at')->orWhere('retired_at', '>', today()->toDateString()))
                ->pluck('id');
            $defaultStaffIds = StaffDefaultShift::where('shift_pattern_id', $pattern->id)
                ->whereIn('staff_id', $activeStaffIds)
                ->distinct()
                ->pluck('staff_id');
            $monthlyRows = StaffShift::where('shift_pattern_id', $pattern->id)->get(['staff_id', 'date']);
            $monthlyStaff = Staff::where('company_id', $company->id)
                ->whereIn('id', $monthlyRows->pluck('staff_id'))
                ->get()
                ->keyBy('id');
            $monthlyUses = $monthlyRows
                ->filter(function ($shift) use ($monthlyStaff) {
                    $staff = $monthlyStaff->get($shift->staff_id);
                    return $staff && !$staff->isRetired((string) $shift->date);
                })
                ->map(fn ($shift) => [
                    'staff_id' => (int) $shift->staff_id,
                    'period' => substr((string) $shift->date, 0, 7),
                ])
                ->unique(fn ($use) => $use['staff_id'].'|'.$use['period'])
                ->values();

            DB::transaction(function () use ($pattern, $company, $deletedSortOrder, $defaultStaffIds, $monthlyUses) {
                StaffDefaultShift::where('shift_pattern_id', $pattern->id)->update([
                    'shift_pattern_id' => null,
                    'is_work' => false,
                ]);
                StaffShift::where('shift_pattern_id', $pattern->id)->update([
                    'shift_pattern_id' => null,
                    'is_work' => false,
                ]);

                $now = now();
                foreach ($defaultStaffIds as $staffId) {
                    DB::table('shift_review_requirements')->updateOrInsert(
                        ['company_id' => $company->id, 'staff_id' => $staffId, 'scope' => 'default', 'period' => ''],
                        ['reason' => $pattern->name, 'updated_at' => $now, 'created_at' => $now]
                    );
                }
                foreach ($monthlyUses as $use) {
                    DB::table('shift_review_requirements')->updateOrInsert(
                        ['company_id' => $company->id, 'staff_id' => $use['staff_id'], 'scope' => 'monthly', 'period' => $use['period']],
                        ['reason' => $pattern->name, 'updated_at' => $now, 'created_at' => $now]
                    );
                }

                $pattern->delete();
                ShiftPattern::where('company_id', $company->id)
                    ->where('sort_order', '>', $deletedSortOrder)
                    ->decrement('sort_order');
            });

            $affected = $defaultStaffIds->count() + $monthlyUses->count();
            $message = 'シフトパターンを削除しました。';
            if ($affected > 0) {
                $message .= ' 利用中だったシフトは「休み」に変更したため、基本シフト・勤務管理を確認して保存してください。';
            }

            if ($defaultStaffIds->isNotEmpty()) {
                return redirect()->route('company.staff-default-shifts')->with('success', $message);
            }
            if ($monthlyUses->isNotEmpty()) {
                return redirect()->route('company.staff-shifts', ['month' => $monthlyUses->first()['period']])
                    ->with('success', $message);
            }

            return back()->with('success', $message);
        }

        return back()->with('error', 'シフトパターンが見つかりません。');
    }
}
