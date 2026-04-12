<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->index()->after('email');
            }

            if (!Schema::hasColumn('companies', 'pm_type')) {
                $table->string('pm_type')->nullable()->after('stripe_id');
            }

            if (!Schema::hasColumn('companies', 'pm_last_four')) {
                $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            }

            if (!Schema::hasColumn('companies', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable()->index()->after('pm_last_four');
            }

            if (!Schema::hasColumn('companies', 'stripe_price_id')) {
                $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
            }

            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->nullable()->index()->after('stripe_price_id');
            }

            if (!Schema::hasColumn('companies', 'plan_code')) {
                $table->string('plan_code')->nullable()->index()->after('subscription_status');
            }

            if (!Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('plan_code');
            }

            if (!Schema::hasColumn('companies', 'current_period_end')) {
                $table->timestamp('current_period_end')->nullable()->after('trial_ends_at');
            }

            if (!Schema::hasColumn('companies', 'subscribed_at')) {
                $table->timestamp('subscribed_at')->nullable()->after('current_period_end');
            }

            if (!Schema::hasColumn('companies', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('subscribed_at');
            }

            if (!Schema::hasColumn('companies', 'is_billing_active')) {
                $table->boolean('is_billing_active')->default(false)->index()->after('canceled_at');
            }
        });

        // もし旧カラム stripe_customer_id があるなら stripe_id へデータ移行
        if (
            Schema::hasColumn('companies', 'stripe_customer_id') &&
            Schema::hasColumn('companies', 'stripe_id')
        ) {
            DB::statement("
                UPDATE companies
                SET stripe_id = stripe_customer_id
                WHERE stripe_id IS NULL
                  AND stripe_customer_id IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $dropColumns = [];

            foreach ([
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'stripe_subscription_id',
                'stripe_price_id',
                'subscription_status',
                'plan_code',
                'trial_ends_at',
                'current_period_end',
                'subscribed_at',
                'canceled_at',
                'is_billing_active',
            ] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};