<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('web_cancel_deadline_hours')
                ->default(24)
                ->comment('何時間前までWebキャンセルを許可するか')
                ->after('revisit_reminder_days');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('web_cancel_deadline_hours');
        });
    }
};