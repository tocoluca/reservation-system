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
	    Schema::create('staff', function (Blueprint $table) {
	        $table->id();

	        $table->foreignId('company_id')
	              ->constrained()
	              ->cascadeOnDelete()
	              ->comment('企業ID');

	        $table->string('staff_code', 10)
	              ->comment('担当者ログインコード');

	        $table->string('name')->comment('氏名');

	        $table->string('password');

	        $table->string('role')
	              ->default('member')
	              ->comment('master / area_leader / leader / member');

	        $table->boolean('is_reservable')
	              ->default(true)
	              ->comment('予約可否');

	        $table->integer('priority_order')
	              ->default(99)
	              ->comment('予約優先順');

	        $table->string('image_path')->nullable();
	        $table->text('comment')->nullable();

	        $table->timestamps();
	        $table->softDeletes();

	        $table->unique(['company_id', 'staff_code']);
	    });
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
