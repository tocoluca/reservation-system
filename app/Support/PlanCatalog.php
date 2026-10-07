<?php

namespace App\Support;

final class PlanCatalog
{
    public static function all(): array
    {
        return config('plans.plans', []);
    }

    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function get(?string $code): ?array
    {
        if (! $code) {
            return null;
        }

        return self::all()[$code] ?? null;
    }

    public static function checkoutPlans(): array
    {
        return collect(self::all())
            ->map(fn (array $plan, string $code) => ['key' => $code] + $plan)
            ->all();
    }

    public static function priceId(?string $code): ?string
    {
        $priceId = self::get($code)['price_id'] ?? null;

        return filled($priceId) ? (string) $priceId : null;
    }

    public static function codeForPriceId(?string $priceId): ?string
    {
        if (! $priceId) {
            return null;
        }

        $matches = collect(self::all())
            ->filter(fn (array $plan) => filled($plan['price_id'] ?? null)
                && hash_equals((string) $plan['price_id'], $priceId))
            ->keys()
            ->values();

        // A duplicated Stripe Price ID is ambiguous and must never silently
        // grant the features of whichever plan happens to be listed first.
        return $matches->count() === 1 ? (string) $matches->first() : null;
    }

    public static function hasFeature(?string $code, string $feature): bool
    {
        // Existing campaign or manually managed companies may not have a plan_code.
        // Preserve their pre-Light behaviour by treating them as Standard.
        $plan = self::get($code) ?? self::get(config('plans.default', 'standard'));
        $features = $plan['features'] ?? [];

        if (in_array('*', $features, true)) {
            return ! in_array($feature, $plan['excluded_features'] ?? [], true);
        }

        return in_array($feature, $features, true);
    }
}
