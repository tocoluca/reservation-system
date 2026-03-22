<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'stripe_price_id')) {
                $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
            }

            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status', 50)->nullable()->after('stripe_price_id');
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

            if (Schema::hasColumn('companies', 'stripe_price_id')) {
                $dropColumns[] = 'stripe_price_id';
            }

            if (Schema::hasColumn('companies', 'subscription_status')) {
                $dropColumns[] = 'subscription_status';
            }

            if (Schema::hasColumn('companies', 'subscription_started_at')) {
                $dropColumns[] = 'subscription_started_at';
            }

            if (Schema::hasColumn('companies', 'subscription_ends_at')) {
                $dropColumns[] = 'subscription_ends_at';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};