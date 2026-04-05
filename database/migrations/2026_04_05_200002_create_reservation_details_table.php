<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->unsignedInteger('duration')->default(0);
            $table->integer('price')->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['staff_id', 'start_at', 'end_at']);
            $table->index(['reservation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_details');
    }
};