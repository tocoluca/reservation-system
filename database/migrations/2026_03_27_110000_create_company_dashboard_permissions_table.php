<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_dashboard_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50);
            $table->string('permission_key', 100);
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();

            $table->unique(
                ['company_id', 'role', 'permission_key'],
                'company_dashboard_permissions_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_dashboard_permissions');
    }
};