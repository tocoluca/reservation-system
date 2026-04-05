<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->string('cancelled_type')->nullable()->after('cancelled_at'); // customer / shop
            $table->text('cancelled_reason')->nullable()->after('cancelled_type');
        });

        // 既存で status='cancelled' がある場合の暫定補完
        DB::table('reservations')
            ->where('status', 'cancelled')
            ->whereNull('cancelled_type')
            ->update([
                'cancelled_type' => 'customer',
            ]);
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'cancelled_at',
                'cancelled_type',
                'cancelled_reason',
            ]);
        });
    }
};