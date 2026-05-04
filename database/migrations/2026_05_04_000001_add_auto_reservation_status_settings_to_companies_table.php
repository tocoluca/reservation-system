<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'reservation_auto_status_mode')) {
                $table->string('reservation_auto_status_mode', 20)
                    ->default('no_show')
                    ->after('web_cancel_deadline_type');
            }

            if (!Schema::hasColumn('companies', 'reservation_auto_status_hours')) {
                $table->unsignedTinyInteger('reservation_auto_status_hours')
                    ->default(1)
                    ->after('reservation_auto_status_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'reservation_auto_status_hours')) {
                $table->dropColumn('reservation_auto_status_hours');
            }

            if (Schema::hasColumn('companies', 'reservation_auto_status_mode')) {
                $table->dropColumn('reservation_auto_status_mode');
            }
        });
    }
};
