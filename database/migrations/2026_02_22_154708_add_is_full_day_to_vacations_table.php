<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->boolean('is_full_day')
                  ->default(false)
                  ->after('end_at'); // 好きな位置に変更可
        });
    }

    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropColumn('is_full_day');
        });
    }
};