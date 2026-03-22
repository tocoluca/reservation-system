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
Schema::create('notices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();

    $table->string('title');
    $table->text('content')->nullable();
    $table->string('image')->nullable();

    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();

    $table->boolean('is_active')->default(true);
    $table->boolean('is_important')->default(false);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
