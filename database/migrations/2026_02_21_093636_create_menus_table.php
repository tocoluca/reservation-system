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
	    Schema::create('menus', function (Blueprint $table) {

	        $table->id();

	        $table->foreignId('company_id')
	              ->constrained()
	              ->cascadeOnDelete();

	        $table->string('name')->comment('メニュー名');

	        $table->integer('duration_minutes')
	              ->comment('所要時間（分）');

	        $table->integer('price')->nullable();

	        $table->timestamps();
	        $table->softDeletes();
	    });
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
