<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('reservations', function (Blueprint $table) {
        $table->id();

        $table->foreignId('company_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('staff_id')
              ->nullable()
              ->constrained('staff')
              ->nullOnDelete();

        $table->string('customer_name');
        $table->string('customer_email')->nullable();
        $table->string('customer_phone')->nullable();

        $table->timestamp('start_at');
        $table->timestamp('end_at');

        $table->string('status')
              ->default('reserved')
              ->comment('reserved / cancelled / no_show');

        $table->string('fingerprint')->nullable();

        $table->timestamps();
        $table->softDeletes();

        // 重要インデックス
        $table->index(['company_id', 'start_at']);
        $table->index(['company_id', 'staff_id', 'start_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
