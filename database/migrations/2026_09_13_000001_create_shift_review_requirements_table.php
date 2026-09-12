<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_review_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('scope', 20);
            $table->string('period', 7)->default('');
            $table->string('reason');
            $table->timestamps();

            $table->unique(['company_id', 'staff_id', 'scope', 'period'], 'shift_review_requirement_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_review_requirements');
    }
};
