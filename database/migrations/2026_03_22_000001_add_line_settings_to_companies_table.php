<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('line_login_enabled')->default(false)->after('theme_color');
            $table->string('line_channel_id')->nullable()->after('line_login_enabled');
            $table->string('line_channel_secret')->nullable()->after('line_channel_id');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'line_login_enabled',
                'line_channel_id',
                'line_channel_secret',
            ]);
        });
    }
};
