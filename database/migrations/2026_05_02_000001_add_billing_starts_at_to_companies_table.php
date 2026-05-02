<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'billing_starts_at')) {
                $table->timestamp('billing_starts_at')->nullable()->after('grace_until')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'billing_starts_at')) {
                $table->dropColumn('billing_starts_at');
            }
        });
    }
};
