<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\PlanCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;
use Stripe\Subscription;

class BillingController extends Controller
{
    private function authorizeBilling(): void
    {
        $staff = Auth::guard('company')->user();
        abort_if(! $staff || ! $staff->canDashboard('card.billing'), 403);
    }

    public function index()
    {
        $this->authorizeBilling();
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $plans = PlanCatalog::checkoutPlans();

        return view('company.billing.index', compact('staff', 'company', 'plans'));
    }

    public function checkout(Request $request)
    {
        $this->authorizeBilling();
        $request->validate([
            'plan' => ['required', Rule::in(PlanCatalog::codes())],
        ], [
            'plan.required' => 'プランを選択してください。',
            'plan.in' => '選択したプランが正しくありません。',
        ]);

        $staff = Auth::guard('company')->user();
        $company = $staff->company;

        $priceId = PlanCatalog::priceId($request->plan);

        if (! $priceId) {
            return back()->with('error', 'Stripeの料金設定が見つかりません。.env を確認してください。');
        }

        if (empty($company->email)) {
            return back()->with('error', '企業情報にメールアドレスが未設定です。先に企業情報編集から設定してください。');
        }

        $plan = PlanCatalog::get($request->plan);
        $staffLimit = $plan['max_active_staff'] ?? null;

        if ($staffLimit !== null && ! $company->isEligibleForLightPlan($staffLimit)) {
            return back()->with(
                'error',
                'ライトプランは予約を担当する稼働中のスタッフが1名の店舗のみお申し込みいただけます。担当者を1名にしてから再度お申し込みください。'
            );
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
                    'success_url' => route('company.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('company.billing.index'),
                    'customer_update' => [
                        'name' => 'auto',
                        'address' => 'auto',
                    ],
                    'metadata' => [
                        'company_id' => (string) $company->id,
                        'company_code' => (string) $company->company_code,
                        'plan' => (string) $request->plan,
                    ],
                    'subscription_data' => [
                        'metadata' => [
                            'company_id' => (string) $company->id,
                            'company_code' => (string) $company->company_code,
                            'plan' => (string) $request->plan,
                        ],
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
                            'success_url' => route('company.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                            'cancel_url' => route('company.billing.index'),
                            'customer_update' => [
                                'name' => 'auto',
                                'address' => 'auto',
                            ],
                            'metadata' => [
                                'company_id' => (string) $company->id,
                                'company_code' => (string) $company->company_code,
                                'plan' => (string) $request->plan,
                            ],
                            'subscription_data' => [
                                'metadata' => [
                                    'company_id' => (string) $company->id,
                                    'company_code' => (string) $company->company_code,
                                    'plan' => (string) $request->plan,
                                ],
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

            return back()->with('error', 'Stripeエラー: '.$e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $this->authorizeBilling();
        $company = Auth::guard('company')->user()->company;
        $sessionId = $request->string('session_id')->toString();

        if (! $sessionId) {
            return redirect()
                ->route('company.billing.index')
                ->with('error', '決済結果を確認できませんでした。契約状態を再度ご確認ください。');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeCheckoutSession::retrieve($sessionId);
            $metadataCompanyId = (int) data_get($session, 'metadata.company_id');
            $customerId = (string) data_get($session, 'customer');

            abort_unless(
                $metadataCompanyId === (int) $company->id
                    && (! $company->stripe_id || hash_equals((string) $company->stripe_id, $customerId)),
                403
            );

            if (data_get($session, 'status') !== 'complete'
                || ! in_array(data_get($session, 'payment_status'), ['paid', 'no_payment_required'], true)) {
                return redirect()
                    ->route('company.billing.index')
                    ->with('error', '決済が完了していません。Stripeの決済画面をご確認ください。');
            }

            $subscriptionId = (string) data_get($session, 'subscription');
            $subscription = Subscription::retrieve($subscriptionId);
            $priceId = (string) data_get($subscription, 'items.data.0.price.id');
            $metadataPlan = (string) data_get($session, 'metadata.plan');
            $configuredMetadataPrice = PlanCatalog::priceId($metadataPlan);
            $planCode = in_array($metadataPlan, PlanCatalog::codes(), true)
                && $configuredMetadataPrice
                && hash_equals($configuredMetadataPrice, $priceId)
                    ? $metadataPlan
                    : PlanCatalog::codeForPriceId($priceId);

            if (! $planCode) {
                throw new \RuntimeException('Stripeの料金とプランの対応を特定できません。');
            }

            $status = (string) data_get($subscription, 'status');
            $currentPeriodEnd = data_get($subscription, 'current_period_end');
            $trialEnd = data_get($subscription, 'trial_end');
            $createdAt = data_get($subscription, 'created');
            $isAvailable = in_array($status, ['active', 'trialing', 'past_due'], true);

            $data = [
                'stripe_id' => $customerId ?: $company->stripe_id,
                'stripe_customer_id' => $customerId ?: $company->stripe_customer_id,
                'stripe_subscription_id' => $subscriptionId,
                'stripe_price_id' => $priceId,
                'subscription_status' => $status,
                'plan_code' => $planCode,
                'trial_ends_at' => $trialEnd ? Carbon::createFromTimestamp($trialEnd) : null,
                'current_period_end' => $currentPeriodEnd ? Carbon::createFromTimestamp($currentPeriodEnd) : null,
                'subscribed_at' => $company->subscribed_at
                    ?: ($createdAt ? Carbon::createFromTimestamp($createdAt) : now()),
                'grace_until' => $status === 'past_due' ? now()->addDays(10) : null,
                'is_billing_active' => $isAvailable,
            ];

            if ($planCode === 'light') {
                $data = array_merge($data, [
                    'trial_ends_at' => null,
                    'billing_starts_at' => null,
                    'max_simultaneous_reservations' => 1,
                    'review_enabled' => false,
                    'prefer_less_capable_staff_for_menu_assignment' => false,
                    'line_login_enabled' => false,
                    'customer_notification_channel' => 'email',
                ]);
            }

            $company->forceFill($data)->save();
        } catch (\Throwable $e) {
            Log::error('Stripe checkout success synchronization failed.', [
                'company_id' => $company->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('company.billing.index')
                ->with('error', '決済は完了しましたが、契約情報の反映に失敗しました。管理者へお問い合わせください。');
        }

        return redirect()
            ->route('company.billing.index')
            ->with('success', $company->fresh()->planLabel().'プランの契約情報を反映しました。');
    }

    public function portal()
    {
        $this->authorizeBilling();
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
                if (! str_contains($e->getMessage(), 'No such customer')) {
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
            'name' => $company->name,
            'phone' => $company->phone,
        ]);

        $company->refresh();
    }
}
