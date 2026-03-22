<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('company_applications', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('status');
            }

            if (!Schema::hasColumn('company_applications', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reject_reason');
            }

            if (!Schema::hasColumn('company_applications', 'approved_company_id')) {
                $table->unsignedBigInteger('approved_company_id')->nullable()->after('reviewed_at');
            }

            if (!Schema::hasColumn('company_applications', 'initial_staff_code')) {
                $table->string('initial_staff_code', 100)->nullable()->after('approved_company_id');
            }

            if (!Schema::hasColumn('company_applications', 'initial_password_plain')) {
                $table->string('initial_password_plain', 255)->nullable()->after('initial_staff_code');
            }

            if (!Schema::hasColumn('company_applications', 'login_url')) {
                $table->string('login_url', 255)->nullable()->after('initial_password_plain');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_applications', function (Blueprint $table) {
            $dropColumns = [];

            foreach ([
                'reject_reason',
                'reviewed_at',
                'approved_company_id',
                'initial_staff_code',
                'initial_password_plain',
                'login_url',
            ] as $column) {
                if (Schema::hasColumn('company_applications', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};