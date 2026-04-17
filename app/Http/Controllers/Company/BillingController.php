<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class BillingController extends Controller
{
    public function index()
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $plans = [
            'standard' => [
                'key' => 'standard',
                'name' => 'スタンダードプラン',
                'price_id' => env('STRIPE_PRICE_STANDARD'),
                'amount' => 6000,
                'description' => '基本プランです。',
            ],
            'platinum' => [
                'key' => 'platinum',
                'name' => 'プラチナプラン',
                'price_id' => env('STRIPE_PRICE_PLATINUM'),
                'amount' => 9000,
                'description' => 'LINEログイン機能が付いたプランです。',
            ],
        ];

        return view('company.billing.index', compact('staff', 'company', 'plans'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'in:standard,platinum'],
        ], [
            'plan.required' => 'プランを選択してください。',
            'plan.in' => '選択したプランが正しくありません。',
        ]);

        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $priceMap = [
            'standard' => env('STRIPE_PRICE_STANDARD'),
            'platinum' => env('STRIPE_PRICE_PLATINUM'),
        ];

        $priceId = $priceMap[$request->plan] ?? null;

        if (!$priceId) {
            return back()->with('error', 'Stripeの料金設定が見つかりません。.env を確認してください。');
        }

        if (empty($company->email)) {
            return back()->with('error', '企業情報にメールアドレスが未設定です。先に企業情報編集から設定してください。');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $this->ensureValidStripeCustomer($company);
            $company->refresh();

            Log::info('Stripe checkout start.', [
                'company_id' => $company->id,
                'company_code' => $company->company_code,
                'plan' => $request->plan,
                'price_id' => $priceId,
                'stripe_id' => $company->stripe_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Stripe customer preparation failed.', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Stripe顧客情報の作成に失敗しました。設定をご確認ください。');
        }

        try {
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
        } catch (InvalidRequestException $e) {
            Log::warning('Stripe checkout invalid request.', [
                'company_id' => $company->id,
                'message' => $e->getMessage(),
                'stripe_id' => $company->stripe_id,
                'price_id' => $priceId,
            ]);

            if (str_contains($e->getMessage(), 'No such customer')) {
                try {
                    $company->forceFill([
                        'stripe_id' => null,
                        'stripe_customer_id' => null,
                    ])->save();

                    $this->ensureValidStripeCustomer($company);
                    $company->refresh();

                    Log::info('Stripe checkout retry with recreated customer.', [
                        'company_id' => $company->id,
                        'stripe_id' => $company->stripe_id,
                        'price_id' => $priceId,
                    ]);

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
                } catch (\Throwable $retryException) {
                    Log::error('Stripe checkout retry failed.', [
                        'company_id' => $company->id,
                        'error' => $retryException->getMessage(),
                    ]);

                    return back()->with('error', 'Stripe顧客情報を再作成しましたが、決済開始に失敗しました。Stripe設定をご確認ください。');
                }
            }

            return back()->with('error', 'Stripeエラー: ' . $e->getMessage());
        }
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

        if (empty($company->email)) {
            return redirect()
                ->route('company.billing.index')
                ->with('error', '企業情報にメールアドレスが未設定です。先に企業情報編集から設定してください。');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $this->ensureValidStripeCustomer($company);
            $company->refresh();
        } catch (\Throwable $e) {
            Log::error('Stripe portal customer preparation failed.', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('company.billing.index')
                ->with('error', 'Stripeの顧客情報確認に失敗しました。');
        }

        return $company->redirectToBillingPortal(route('company.billing.index'));
    }

    protected function ensureValidStripeCustomer($company): void
    {
        $stripeId = $company->stripe_id;

        if ($stripeId) {
            try {
                Customer::retrieve($stripeId);
                return;
            } catch (InvalidRequestException $e) {
                if (!str_contains($e->getMessage(), 'No such customer')) {
                    throw $e;
                }

                Log::warning('Stored Stripe customer was not found. Recreating.', [
                    'company_id' => $company->id,
                    'stripe_id' => $stripeId,
                ]);

                $company->forceFill([
                    'stripe_id' => null,
                    'stripe_customer_id' => null,
                ])->save();
            }
        }

        $company->createAsStripeCustomer([
            'email' => $company->email,
            'name'  => $company->name,
            'phone' => $company->phone,
        ]);

        $company->refresh();
    }
}