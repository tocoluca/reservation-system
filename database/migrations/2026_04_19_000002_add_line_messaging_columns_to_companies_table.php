<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'line_channel_access_token')) {
                $table->text('line_channel_access_token')
                    ->nullable()
                    ->after('line_channel_secret');
            }

            if (!Schema::hasColumn('companies', 'line_official_account_id')) {
                $table->string('line_official_account_id')
                    ->nullable()
                    ->after('line_channel_access_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach ([
                'line_channel_access_token',
                'line_official_account_id',
            ] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};