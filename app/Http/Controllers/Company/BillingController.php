<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $plans = [
            'light' => [
                'key' => 'light',
                'name' => 'ライトプラン',
                'price_id' => env('STRIPE_PRICE_LIGHT'),
                'amount' => 6000,
                'description' => '小規模店舗向けの基本プランです。',
            ],
            'standard' => [
                'key' => 'standard',
                'name' => 'スタンダードプラン',
                'price_id' => env('STRIPE_PRICE_STANDARD'),
                'amount' => 9000,
                'description' => 'より実用的に使いたい店舗向けのおすすめプランです。',
            ],
        ];

        return view('company.billing.index', compact('staff', 'company', 'plans'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'in:light,standard'],
        ], [
            'plan.required' => 'プランを選択してください。',
            'plan.in' => '選択したプランが正しくありません。',
        ]);

        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $priceMap = [
            'light' => env('STRIPE_PRICE_LIGHT'),
            'standard' => env('STRIPE_PRICE_STANDARD'),
        ];

        $priceId = $priceMap[$request->plan] ?? null;

        if (!$priceId) {
            return back()->with('error', 'Stripeの料金設定が見つかりません。.env を確認してください。');
        }

        if (empty($company->email)) {
            return back()->with('error', '企業情報にメールアドレスが未設定です。先に企業情報編集から設定してください。');
        }

        if (!$company->hasStripeId()) {
            $company->createAsStripeCustomer([
                'email' => $company->email,
                'name'  => $company->name,
                'phone' => $company->phone,
            ]);
        }

        return $company
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('company.billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('company.billing.index'),
                'customer_update' => [
                    'name' => 'auto',
                    'address' => 'auto',
                ],
                'metadata' => [
                    'company_id' => (string) $company->id,
                    'company_code' => (string) $company->company_code,
                    'plan' => (string) $request->plan,
                ],
            ]);
    }

    public function success()
    {
        return redirect()
            ->route('company.billing.index')
            ->with('success', 'お支払い手続きが完了しました。契約状態が反映されるまで少しお待ちください。');
    }

    public function portal()
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        if (!$company->hasStripeId()) {
            return redirect()
                ->route('company.billing.index')
                ->with('error', 'Stripeの顧客情報がまだ作成されていません。');
        }

        return $company->redirectToBillingPortal(route('company.billing.index'));
    }
}