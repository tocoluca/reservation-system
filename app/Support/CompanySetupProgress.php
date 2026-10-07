<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Menu;
use App\Models\ShiftPattern;
use App\Models\Staff;
use App\Models\StaffDefaultShift;
use App\Models\StaffShift;

final class CompanySetupProgress
{
    public static function for(Company $company): array
    {
        $configuredPlan = PlanCatalog::get($company->plan_code);
        // Companies initialized before plan selection was introduced keep the
        // historical Standard behaviour without being forced back into setup.
        $planSelected = $configuredPlan !== null
            || ($company->is_initialized && blank($company->plan_code));
        $isLight = $company->isLightPlan();
        $companyInfoDone = self::companyInfoDone($company);
        $menuDone = Menu::where('company_id', $company->id)->exists();

        $steps = [
            [
                'key' => 'plan',
                'step' => 1,
                'label' => '利用プラン',
                'done' => $planSelected,
                'required' => true,
                'description' => '利用するプランを選択します。',
            ],
        ];

        // A plan must be selected before the rest of the setup flow is shown.
        if (! $planSelected) {
            return self::result($steps, false);
        }

        if ($isLight) {
            $reserveCheckDone = $companyInfoDone && $menuDone;

            $steps[] = [
                'key' => 'company_info',
                'step' => 2,
                'label' => '企業情報・営業時間',
                'done' => $companyInfoDone,
                'required' => true,
                'description' => '営業日・営業時間や予約受付の基本条件を設定します。',
            ];
            $steps[] = [
                'key' => 'menu',
                'step' => 3,
                'label' => 'メニュー',
                'done' => $menuDone,
                'required' => true,
                'description' => 'メニュー名・時間・料金を設定します。',
            ];
            $steps[] = [
                'key' => 'reserve',
                'step' => 4,
                'label' => '予約確認',
                'done' => $reserveCheckDone,
                'required' => true,
                'description' => '予約カレンダーが表示できる状態か確認します。',
            ];

            return self::result($steps, true);
        }

        $staffDone = $company->is_initialized || $company->staff_setup_confirmed_at !== null;
        $shiftPatternDone = ShiftPattern::where('company_id', $company->id)->exists();
        $staffIds = Staff::where('company_id', $company->id)->pluck('id');
        $defaultShiftDone = false;
        $monthlyShiftDone = false;

        if ($staffIds->isNotEmpty()) {
            $defaultShiftDone = StaffDefaultShift::whereIn('staff_id', $staffIds)
                ->where('is_work', 1)
                ->exists();
            $monthlyShiftDone = StaffShift::whereIn('staff_id', $staffIds)->exists();
        }

        $shiftDone = $monthlyShiftDone || ($shiftPatternDone && $defaultShiftDone);
        $reserveCheckDone = $companyInfoDone && $staffDone && $menuDone && $shiftDone;

        $steps = array_merge($steps, [
            [
                'key' => 'staff',
                'step' => 2,
                'label' => '担当者',
                'done' => $staffDone,
                'required' => true,
                'description' => '担当者一覧を確認し、必要に応じてスタッフを登録します。',
            ],
            [
                'key' => 'company_info',
                'step' => 3,
                'label' => '企業情報',
                'done' => $companyInfoDone,
                'required' => true,
                'description' => '営業時間や予約受付の基本条件を設定します。',
            ],
            [
                'key' => 'menu',
                'step' => 4,
                'label' => 'メニュー',
                'done' => $menuDone,
                'required' => true,
                'description' => 'メニュー名・時間・料金を設定します。',
            ],
            [
                'key' => 'shift',
                'step' => 5,
                'label' => 'シフト',
                'done' => $shiftDone,
                'required' => true,
                'description' => 'スタッフが対応できる時間を設定します。',
            ],
            [
                'key' => 'reserve',
                'step' => 6,
                'label' => '予約確認',
                'done' => $reserveCheckDone,
                'required' => true,
                'description' => '予約カレンダーが表示できる状態か確認します。',
            ],
            [
                'key' => 'my_profile',
                'step' => null,
                'label' => 'マイプロフィール',
                'done' => false,
                'required' => false,
                'description' => '自分のプロフィール情報を設定します。',
            ],
        ]);

        return self::result($steps, false);
    }

    private static function companyInfoDone(Company $company): bool
    {
        $openPatterns = $company->open_patterns;

        if (is_string($openPatterns)) {
            $openPatterns = json_decode($openPatterns, true);
        }

        if (! is_array($openPatterns)) {
            return false;
        }

        foreach ($openPatterns as $weekdayPatterns) {
            if (! is_array($weekdayPatterns)) {
                continue;
            }

            foreach ($weekdayPatterns as $pattern) {
                if (! is_array($pattern)) {
                    continue;
                }

                if (
                    (! empty($pattern['open']) && ! empty($pattern['close']))
                    || (! empty($pattern['open_time']) && ! empty($pattern['close_time']))
                ) {
                    return ! empty($company->slot_minutes);
                }
            }
        }

        return false;
    }

    private static function result(array $steps, bool $isLight): array
    {
        $requiredSteps = collect($steps)->where('required', true)->values();
        $requiredDoneCount = $requiredSteps->where('done', true)->count();
        $requiredTotalCount = $requiredSteps->count();

        return [
            'steps' => $steps,
            'is_light' => $isLight,
            'plan_selected' => (bool) ($steps[0]['done'] ?? false),
            'required_done_count' => $requiredDoneCount,
            'required_total_count' => $requiredTotalCount,
            'all_required_completed' => $requiredTotalCount > 0
                && $requiredDoneCount === $requiredTotalCount,
        ];
    }
}
