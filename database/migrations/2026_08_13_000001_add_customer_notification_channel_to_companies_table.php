<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('companies', 'customer_notification_channel')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('customer_notification_channel', 20)
                    ->default('both')
                    ->after('line_official_account_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'customer_notification_channel')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('customer_notification_channel');
            });
        }
    }
};
