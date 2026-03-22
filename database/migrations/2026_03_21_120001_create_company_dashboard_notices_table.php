<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_dashboard_notices', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('image')->nullable();

            $table->boolean('is_new')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_active')->default(true);

            $table->enum('target_type', ['all', 'company'])->default('all');
            $table->unsignedBigInteger('company_id')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'target_type']);
            $table->index(['company_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_dashboard_notices');
    }
};