<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('open_patterns');
            }

            if (!Schema::hasColumn('companies', 'grace_until')) {
                $table->timestamp('grace_until')->nullable()->after('is_active');
            }

            if (!Schema::hasColumn('companies', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('grace_until')->index();
            }

            if (!Schema::hasColumn('companies', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id')->index();
            }

            if (!Schema::hasColumn('companies', 'stripe_price_id')) {
                $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
            }

            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->nullable()->after('stripe_price_id')->index();
            }

            if (!Schema::hasColumn('companies', 'subscription_started_at')) {
                $table->timestamp('subscription_started_at')->nullable()->after('subscription_status');
            }

            if (!Schema::hasColumn('companies', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('subscription_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $dropColumns = [];

            foreach ([
                'subscription_ends_at',
                'subscription_started_at',
                'subscription_status',
                'stripe_price_id',
                'stripe_subscription_id',
                'stripe_customer_id',
                'grace_until',
                'is_active',
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