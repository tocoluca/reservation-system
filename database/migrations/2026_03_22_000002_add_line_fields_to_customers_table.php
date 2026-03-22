<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('line_user_id')->nullable()->after('email');
            $table->string('line_name')->nullable()->after('line_user_id');
            $table->text('line_picture_url')->nullable()->after('line_name');
            $table->timestamp('line_linked_at')->nullable()->after('line_picture_url');
            $table->unique(['company_id', 'line_user_id'], 'customers_company_line_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_company_line_unique');
            $table->dropColumn([
                'line_user_id',
                'line_name',
                'line_picture_url',
                'line_linked_at',
            ]);
        });
    }
};
