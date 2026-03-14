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
Schema::create('staff_default_shifts', function (Blueprint $table) {

    $table->id();

    $table->foreignId('staff_id')->constrained()->cascadeOnDelete();

    $table->tinyInteger('weekday');

    $table->foreignId('shift_pattern_id')->nullable();

    $table->boolean('is_work')->default(true);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_default_shifts');
    }
};
