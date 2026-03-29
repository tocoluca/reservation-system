<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('company_id');
                $table->foreign('customer_id')
                    ->references('id')
                    ->on('customers')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('reservations', 'visit_reflected_at')) {
                $table->timestamp('visit_reflected_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'visit_reflected_at')) {
                $table->dropColumn('visit_reflected_at');
            }

            if (Schema::hasColumn('reservations', 'customer_id')) {
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Throwable $e) {
                    // 既に無い場合でも落とさない
                }

                $table->dropColumn('customer_id');
            }
        });
    }
};