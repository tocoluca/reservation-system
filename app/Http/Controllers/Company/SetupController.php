<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use App\Models\Menu;
use App\Models\ShiftPattern;
use App\Models\StaffDefaultShift;
use App\Models\StaffShift;

class SetupController extends Controller
{
    /**
     * はじめての設定ガイド表示
     */
    public function index()
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        /*
        |--------------------------------------------------------------------------
        | 1. 企業情報
        |--------------------------------------------------------------------------
        | 営業時間のどれか1つ以上 + 刻み時間 が入っていれば最低限OK
        */
		$openPatterns = $company->open_patterns;

		if (is_string($openPatterns)) {
		    $decoded = json_decode($openPatterns, true);
		    if (json_last_error() === JSON_ERROR_NONE) {
		        $openPatterns = $decoded;
		    }
		}

		$companyInfoDone =
		    !empty($company->slot_minutes) &&
		    !empty($openPatterns);

        /*
        |--------------------------------------------------------------------------
        | 2. 担当者
        |--------------------------------------------------------------------------
        */
        $staffDone = Staff::where('company_id', $company->id)->exists();

        /*
        |--------------------------------------------------------------------------
        | 3. メニュー
        |--------------------------------------------------------------------------
        */
        $menuDone = Menu::where('company_id', $company->id)->exists();

        /*
        |--------------------------------------------------------------------------
        | 4. シフト
        |--------------------------------------------------------------------------
        | シフトパターンがあり、
        | 基本シフト または 月シフト のどちらかが1件でもあればOK
        */
        $shiftPatternDone = ShiftPattern::where('company_id', $company->id)->exists();

        $staffIds = Staff::where('company_id', $company->id)->pluck('id');

        $defaultShiftDone = false;
        $monthlyShiftDone = false;

        if ($staffIds->isNotEmpty()) {
            $defaultShiftDone = StaffDefaultShift::whereIn('staff_id', $staffIds)
                ->where('is_work', 1)
                ->exists();

            $monthlyShiftDone = StaffShift::whereIn('staff_id', $staffIds)
                ->exists();
        }

        $shiftDone = $monthlyShiftDone || ($shiftPatternDone && $defaultShiftDone);

        /*
        |--------------------------------------------------------------------------
        | 5. 予約確認
        |--------------------------------------------------------------------------
        */
        $reserveCheckDone = $companyInfoDone && $staffDone && $menuDone && $shiftDone;

        /*
        |--------------------------------------------------------------------------
        | 任意項目
        |--------------------------------------------------------------------------
        | マイプロフィールは初期設定の完了条件に含めない。
        | 会社アカウント名が入っているだけで「設定済み」と誤判定しないよう、
        | ガイド上は完了判定を持たせず、任意項目として扱う。
        */
        $myProfileDone = false;

        /*
        |--------------------------------------------------------------------------
        | 進捗情報
        |--------------------------------------------------------------------------
        */
        $setupSteps = [
            [
                'key' => 'staff',
                'step' => 1,
                'label' => '担当者',
                'done' => $staffDone,
                'required' => true,
                'description' => 'スタッフ情報や権限を登録します。',
            ],
            [
                'key' => 'company_info',
                'step' => 2,
                'label' => '企業情報',
                'done' => $companyInfoDone,
                'required' => true,
                'description' => '営業時間や予約受付の基本条件を設定します。',
            ],
            [
                'key' => 'menu',
                'step' => 3,
                'label' => 'メニュー',
                'done' => $menuDone,
                'required' => true,
                'description' => 'メニュー名・時間・料金を設定します。',
            ],
            [
                'key' => 'shift',
                'step' => 4,
                'label' => 'シフト',
                'done' => $shiftDone,
                'required' => true,
                'description' => 'スタッフが対応できる時間を設定します。',
            ],
            [
                'key' => 'reserve',
                'step' => 5,
                'label' => '予約確認',
                'done' => $reserveCheckDone,
                'required' => true,
                'description' => '予約カレンダーが表示できる状態か確認します。',
            ],
            [
                'key' => 'my_profile',
                'step' => null,
                'label' => 'マイプロフィール',
                'done' => $myProfileDone,
                'required' => false,
                'description' => '自分のプロフィール情報を設定します。',
            ],
        ];

        $requiredSteps = collect($setupSteps)->where('required', true)->values();
        $requiredDoneCount = $requiredSteps->where('done', true)->count();
        $requiredTotalCount = $requiredSteps->count();
        $allRequiredCompleted = $requiredDoneCount === $requiredTotalCount;

        return view('company.setup', compact(
            'company',
            'staff',
            'setupSteps',
            'requiredDoneCount',
            'requiredTotalCount',
            'allRequiredCompleted'
        ));
    }

    /**
     * ガイド確認完了
     */
    public function complete(Request $request)
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $company->update([
            'is_initialized' => true,
        ]);

        return redirect()
            ->route('company.dashboard')
            ->with('success', '初回ガイドを確認しました。続けて必要な設定を進めてください。');
    }

    /**
     * 初期設定保存
     */
    public function store(Request $request)
    {
        $request->validate([
            'slot_minutes' => ['required', 'integer'],
            'max_simultaneous_reservations' => ['required', 'integer'],
        ], [
            'slot_minutes.required' => '予約カレンダーの刻み時間を入力してください。',
            'slot_minutes.integer' => '予約カレンダーの刻み時間は数値で入力してください。',
            'max_simultaneous_reservations.required' => '同時予約数を入力してください。',
            'max_simultaneous_reservations.integer' => '同時予約数は数値で入力してください。',
        ]);

        $company = Auth::guard('company')->user()->company;

        $company->update([
            'slot_minutes' => $request->slot_minutes,
            'max_simultaneous_reservations' => $request->max_simultaneous_reservations,
            'regular_holidays' => json_encode($request->regular_holidays ?? []),
            'holiday_is_closed' => $request->holiday_is_closed ? true : false,
            'is_initialized' => true,
        ]);

        return redirect()
            ->route('company.dashboard')
            ->with('success', '初期設定を保存しました。');
    }
}
