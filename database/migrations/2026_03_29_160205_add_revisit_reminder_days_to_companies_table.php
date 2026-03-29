<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('revisit_reminder_days')
                ->default(45)
                ->comment('最終来店日から何日後に再来店促進メールを送るか')
                ->after('reservation_close_hours');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('revisit_reminder_days');
        });
    }
};