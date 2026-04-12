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
        $secret = env('STRIPE_WEBHOOK_SECRET');

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
            'stripe_customer_id' => $customerId ?: $company->stripe_customer_id,
            'stripe_subscription_id' => $subscriptionId ?: $company->stripe_subscription_id,
        ])->save();

        Log::info('Stripe checkout.session.completed handled.', [
            'company_id' => $company->id,
            'stripe_customer_id' => $customerId,
            'stripe_subscription_id' => $subscriptionId,
        ]);
    }

    protected function handleSubscriptionUpdated($subscription): void
    {
        $customerId = data_get($subscription, 'customer');
        $subscriptionId = data_get($subscription, 'id');
        $status = data_get($subscription, 'status');
        $priceId = data_get($subscription, 'items.data.0.price.id');

        $currentPeriodStart = data_get($subscription, 'current_period_start');
        $currentPeriodEnd = data_get($subscription, 'current_period_end');
        $cancelAt = data_get($subscription, 'cancel_at');
        $canceledAt = data_get($subscription, 'canceled_at');

        $company = Company::query()
            ->where('stripe_customer_id', $customerId)
            ->orWhere('stripe_subscription_id', $subscriptionId)
            ->first();

        if (!$company) {
            Log::warning('Stripe subscription event: company not found.', [
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $company->forceFill([
            'stripe_customer_id' => $customerId,
            'stripe_subscription_id' => $subscriptionId,
            'stripe_price_id' => $priceId,
            'subscription_status' => $status,
            'subscription_started_at' => $currentPeriodStart ? Carbon::createFromTimestamp($currentPeriodStart) : $company->subscription_started_at,
            'subscription_ends_at' => $currentPeriodEnd ? Carbon::createFromTimestamp($currentPeriodEnd) : null,
            'grace_until' => $cancelAt
                ? Carbon::createFromTimestamp($cancelAt)
                : ($currentPeriodEnd ? Carbon::createFromTimestamp($currentPeriodEnd) : null),
            'is_active' => in_array($status, ['trialing', 'active', 'past_due'], true),
        ]);

        if ($status === 'canceled') {
            $company->is_active = false;
            $company->grace_until = null;
        }

        if ($canceledAt) {
            $company->subscription_ends_at = $currentPeriodEnd
                ? Carbon::createFromTimestamp($currentPeriodEnd)
                : Carbon::createFromTimestamp($canceledAt);
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
            ->where('stripe_customer_id', $customerId)
            ->orWhere('stripe_subscription_id', $subscriptionId)
            ->first();

        if (!$company) {
            Log::warning('Stripe invoice.paid: company not found.', [
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $company->forceFill([
            'subscription_status' => 'active',
            'subscription_ends_at' => $periodEnd ? Carbon::createFromTimestamp($periodEnd) : $company->subscription_ends_at,
            'grace_until' => null,
            'is_active' => true,
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
            ->where('stripe_customer_id', $customerId)
            ->orWhere('stripe_subscription_id', $subscriptionId)
            ->first();

        if (!$company) {
            Log::warning('Stripe invoice.payment_failed: company not found.', [
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $graceUntil = now()->addDays(3);

        $company->forceFill([
            'subscription_status' => 'past_due',
            'grace_until' => $graceUntil,
            'is_active' => true,
        ])->save();

        Log::warning('Stripe invoice.payment_failed handled.', [
            'company_id' => $company->id,
            'grace_until' => $graceUntil->format('Y-m-d H:i:s'),
        ]);
    }
}