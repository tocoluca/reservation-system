<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'line_notifications_enabled')) {
                $table->boolean('line_notifications_enabled')
                    ->default(true)
                    ->after('line_linked_at');
            }

            if (!Schema::hasColumn('customers', 'line_review_opt_in')) {
                $table->boolean('line_review_opt_in')
                    ->default(true)
                    ->after('line_notifications_enabled');
            }

            if (!Schema::hasColumn('customers', 'line_friend_flag')) {
                $table->boolean('line_friend_flag')
                    ->default(false)
                    ->after('line_review_opt_in');
            }

            if (!Schema::hasColumn('customers', 'last_line_sent_at')) {
                $table->timestamp('last_line_sent_at')
                    ->nullable()
                    ->after('line_friend_flag');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'line_notifications_enabled',
                'line_review_opt_in',
                'line_friend_flag',
                'last_line_sent_at',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};