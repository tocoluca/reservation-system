<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\CompanySetupProgress;
use App\Support\PlanCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SetupController extends Controller
{
    /**
     * はじめての設定ガイド表示
     */
    public function index()
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;
        $progress = CompanySetupProgress::for($company);
        $setupSteps = $progress['steps'];
        $requiredDoneCount = $progress['required_done_count'];
        $requiredTotalCount = $progress['required_total_count'];
        $allRequiredCompleted = $progress['all_required_completed'];
        $planSelected = $progress['plan_selected'];
        $isLight = $progress['is_light'];
        $plans = PlanCatalog::checkoutPlans();
        $selectedPlanName = PlanCatalog::get($company->plan_code)['name']
            ?? PlanCatalog::get(config('plans.default', 'standard'))['name']
            ?? 'スタンダードプラン';
        $canChangePlan = ! $company->is_initialized
            && ! $company->isSubscribed()
            && blank($company->stripe_subscription_id);

        return view('company.setup', compact(
            'company',
            'staff',
            'setupSteps',
            'requiredDoneCount',
            'requiredTotalCount',
            'allRequiredCompleted',
            'planSelected',
            'isLight',
            'plans',
            'selectedPlanName',
            'canChangePlan'
        ));
    }

    /**
     * 初期設定で利用するプランを保存
     */
    public function selectPlan(Request $request)
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        abort_if(
            $company->is_initialized || $company->isSubscribed() || filled($company->stripe_subscription_id),
            403,
            '契約中または初期設定完了後のプランは、契約管理画面から変更してください。'
        );

        $validated = $request->validate([
            'plan_code' => ['required', Rule::in(PlanCatalog::codes())],
        ], [
            'plan_code.required' => '利用するプランを選択してください。',
            'plan_code.in' => '選択したプランが正しくありません。',
        ]);

        $company->update(['plan_code' => $validated['plan_code']]);

        // Light is a one-person plan. The signed-in owner becomes its fixed
        // reservable staff automatically, so no staff setup screen is needed.
        if ($validated['plan_code'] === 'light' && ! $staff->isStoreOperator()) {
            $staff->update(['is_reservable' => true]);
        }

        return redirect()
            ->route('company.setup')
            ->with('success', $company->fresh()->planLabel().'プランを選択しました。');
    }

    /**
     * ガイド確認完了
     */
    public function complete(Request $request)
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $progress = CompanySetupProgress::for($company);

        if (! $progress['plan_selected']) {
            return redirect()
                ->route('company.setup')
                ->with('error', '最初に利用するプランを選択してください。');
        }

        if (! $progress['all_required_completed']) {
            $incompleteLabels = collect($progress['steps'])
                ->where('required', true)
                ->where('done', false)
                ->pluck('label')
                ->implode('、');

            return redirect()
                ->route('company.setup')
                ->with('error', '未完了の必須設定があります：'.$incompleteLabels);
        }

        if ($company->isLightPlan() && ! $staff->isStoreOperator() && ! $staff->is_reservable) {
            $staff->update(['is_reservable' => true]);
        }

        $company->update([
            'is_initialized' => true,
        ]);

        return redirect()
            ->route('company.dashboard')
            ->with('success', '初期設定が完了しました。予約受付を開始できます。');
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
