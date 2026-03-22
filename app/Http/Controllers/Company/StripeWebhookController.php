<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Carbon\Carbon;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    public function handleCustomerSubscriptionCreated(array $payload)
    {
        $subscription = $payload['data']['object'];
        $company = Company::where('stripe_customer_id', $subscription['customer'] ?? null)->first();

        if ($company) {
            $company->update([
                'stripe_subscription_id'   => $subscription['id'] ?? null,
                'stripe_price_id'          => $subscription['items']['data'][0]['price']['id'] ?? null,
                'subscription_status'      => $subscription['status'] ?? null,
                'subscription_started_at'  => now(),
                'subscription_ends_at'     => null,
                'is_active'                => 1,
                'grace_until'              => null,
            ]);
        }

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        $subscription = $payload['data']['object'];
        $company = Company::where('stripe_customer_id', $subscription['customer'] ?? null)->first();

        if ($company) {
            $endsAt = null;

            if (!empty($subscription['cancel_at'])) {
                $endsAt = Carbon::createFromTimestamp($subscription['cancel_at']);
            } elseif (!empty($subscription['current_period_end']) && !empty($subscription['cancel_at_period_end'])) {
                $endsAt = Carbon::createFromTimestamp($subscription['current_period_end']);
            }

            $status = $subscription['status'] ?? null;

            $company->update([
                'stripe_subscription_id' => $subscription['id'] ?? null,
                'stripe_price_id' => $subscription['items']['data'][0]['price']['id'] ?? null,
                'subscription_status' => $status,
                'subscription_ends_at' => $endsAt,
                'is_active' => in_array($status, ['active', 'trialing', 'past_due'], true),
            ]);
        }

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        $subscription = $payload['data']['object'];
        $company = Company::where('stripe_customer_id', $subscription['customer'] ?? null)->first();

        if ($company) {
            $company->update([
                'subscription_status' => 'canceled',
                'subscription_ends_at' => now(),
                'is_active' => 0,
            ]);
        }

        return $this->successMethod();
    }

    public function handleInvoicePaymentFailed(array $payload)
    {
        $invoice = $payload['data']['object'];
        $company = Company::where('stripe_customer_id', $invoice['customer'] ?? null)->first();

        if ($company) {
            $company->update([
                'subscription_status' => 'past_due',
                'grace_until' => now()->addDays(7),
            ]);
        }

        return $this->successMethod();
    }

    public function handleInvoicePaid(array $payload)
    {
        $invoice = $payload['data']['object'];
        $company = Company::where('stripe_customer_id', $invoice['customer'] ?? null)->first();

        if ($company) {
            $company->update([
                'subscription_status' => 'active',
                'is_active' => 1,
                'grace_until' => null,
            ]);
        }

        return $this->successMethod();
    }
}