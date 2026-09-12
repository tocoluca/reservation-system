<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('staff')
            ->where('role', 'store_operator')
            ->update([
                'name' => '店舗ユーザ',
                'is_reservable' => false,
            ]);
    }

    public function down(): void
    {
        // 元の個別名称は復元できないため、ロールバック時も現在値を維持します。
    }
};
