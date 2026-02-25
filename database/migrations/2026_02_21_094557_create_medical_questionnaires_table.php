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
	Schema::create('medical_questionnaires', function (Blueprint $table) {
	    $table->id();
	    $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
	    $table->text('symptoms')->nullable();
	    $table->text('medical_history')->nullable();
	    $table->boolean('is_pregnant')->default(false);
	    $table->timestamps();
	});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_questionnaires');
    }
};
