<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('companies', 'staff_setup_confirmed_at')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->timestamp('staff_setup_confirmed_at')
                    ->nullable()
                    ->after('is_initialized');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'staff_setup_confirmed_at')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('staff_setup_confirmed_at');
            });
        }
    }
};
