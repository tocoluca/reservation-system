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
Schema::table('menus', function (Blueprint $table) {

    $table->integer('sort_order')->default(0);
    $table->boolean('is_popular')->default(false);
    $table->boolean('is_active')->default(true);

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
