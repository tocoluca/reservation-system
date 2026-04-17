<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (!$secret) {
            Log::error('Stripe webhook secret is not configured.');
            return response()->json(['message' => 'Webhook secret not configured.'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (UnexpectedValueException $e) {
            Log::warning('Stripe webhook payload invalid.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Invalid payload.'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature invalid.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'invoice.paid':
                    $this->handleInvoicePaid($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                default:
                    Log::info('Stripe webhook ignored event.', [
                        'type' => $event->type,
                    ]);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handling failed.', [
                'type' => $event->type ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Webhook handling failed.'], 500);
        }

        return response()->json(['received' => true]);
    }

    protected function handleCheckoutSessionCompleted($session): void
    {
        $companyId = data_get($session, 'metadata.company_id');
        $companyCode = data_get($session, 'metadata.company_code');
        $planCode = data_get($session, 'metadata.plan');

        $customerId = data_get($session, 'customer');
        $subscriptionId = data_get($session, 'subscription');

        $company = Company::query()
            ->when($companyId, fn ($q) => $q->where('id', $companyId))
            ->when(!$companyId && $companyCode, fn ($q) => $q->where('company_code', $companyCode))
            ->first();

        if (!$company) {
            Log::warning('Stripe checkout.session.completed: company not found.', [
                'company_id' => $companyId,
                'company_code' => $companyCode,
            ]);
            return;
        }

        $company->forceFill([
            'stripe_id' => $customerId ?: $company->stripe_id,
            'stripe_subscription_id' => $subscriptionId ?: $company->stripe_subscription_id,
            'plan_code' => $planCode ?: $company->plan_code,
            'subscribed_at' => $company->subscribed_at ?: now(),
            'is_billing_active' => true,
        ])->save();

        Log::info('Stripe checkout.session.completed handled.', [
            'company_id' => $company->id,
            'stripe_id' => $customerId,
            'stripe_subscription_id' => $subscriptionId,
            'plan_code' => $planCode,
        ]);
    }

    protected function handleSubscriptionUpdated($subscription): void
    {
        $customerId = data_get($subscription, 'customer');
        $subscriptionId = data_get($subscription, 'id');
        $status = data_get($subscription, 'status');
        $priceId = data_get($subscription, 'items.data.0.price.id');

        $currentPeriodEnd = data_get($subscription, 'current_period_end');
        $trialEnd = data_get($subscription, 'trial_end');
        $canceledAt = data_get($subscription, 'canceled_at');

        $company = Company::query()
            ->where('stripe_id', $customerId)
            ->orWhere('stripe_subscription_id', $subscriptionId)
            ->first();

        if (!$company) {
            Log::warning('Stripe subscription event: company not found.', [
                'stripe_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $company->forceFill([
            'stripe_id' => $customerId ?: $company->stripe_id,
            'stripe_subscription_id' => $subscriptionId ?: $company->stripe_subscription_id,
            'stripe_price_id' => $priceId ?: $company->stripe_price_id,
            'subscription_status' => $status,
            'trial_ends_at' => $trialEnd
                ? Carbon::createFromTimestamp($trialEnd)
                : $company->trial_ends_at,
            'current_period_end' => $currentPeriodEnd
                ? Carbon::createFromTimestamp($currentPeriodEnd)
                : $company->current_period_end,
            'canceled_at' => $canceledAt
                ? Carbon::createFromTimestamp($canceledAt)
                : $company->canceled_at,
            'subscribed_at' => $company->subscribed_at ?: now(),
            'is_billing_active' => in_array($status, ['trialing', 'active', 'past_due'], true),
        ]);

        if (in_array($status, ['canceled', 'incomplete_expired', 'unpaid'], true)) {
            $company->is_billing_active = false;
        }

        $company->save();

        Log::info('Stripe subscription event handled.', [
            'company_id' => $company->id,
            'status' => $status,
            'price_id' => $priceId,
        ]);
    }

    protected function handleInvoicePaid($invoice): void
    {
        $customerId = data_get($invoice, 'customer');
        $subscriptionId = data_get($invoice, 'subscription');
        $periodEnd = data_get($invoice, 'lines.data.0.period.end');

        $company = Company::query()
            ->where('stripe_id', $customerId)
            ->orWhere('stripe_subscription_id', $subscriptionId)
            ->first();

        if (!$company) {
            Log::warning('Stripe invoice.paid: company not found.', [
                'stripe_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $company->forceFill([
            'stripe_id' => $customerId ?: $company->stripe_id,
            'stripe_subscription_id' => $subscriptionId ?: $company->stripe_subscription_id,
            'subscription_status' => 'active',
            'current_period_end' => $periodEnd
                ? Carbon::createFromTimestamp($periodEnd)
                : $company->current_period_end,
            'is_billing_active' => true,
        ])->save();

        Log::info('Stripe invoice.paid handled.', [
            'company_id' => $company->id,
        ]);
    }

    protected function handleInvoicePaymentFailed($invoice): void
    {
        $customerId = data_get($invoice, 'customer');
        $subscriptionId = data_get($invoice, 'subscription');

        $company = Company::query()
            ->where('stripe_id', $customerId)
            ->orWhere('stripe_subscription_id', $subscriptionId)
            ->first();

        if (!$company) {
            Log::warning('Stripe invoice.payment_failed: company not found.', [
                'stripe_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $company->forceFill([
            'stripe_id' => $customerId ?: $company->stripe_id,
            'stripe_subscription_id' => $subscriptionId ?: $company->stripe_subscription_id,
            'subscription_status' => 'past_due',
            'is_billing_active' => true,
        ])->save();

        Log::warning('Stripe invoice.payment_failed handled.', [
            'company_id' => $company->id,
        ]);
    }
}