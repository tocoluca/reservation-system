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
	    Schema::create('company_applications', function (Blueprint $table) {
	        $table->id();

	        $table->string('company_name')->comment('企業名');
	        $table->string('industry_type')->comment('beauty or dental');

	        $table->string('contact_person');
	        $table->string('email');
	        $table->string('phone');

	        $table->text('message')->nullable();

	        $table->string('status')
	              ->default('pending')
	              ->comment('pending / approved / rejected');

	        $table->timestamps();
	    });
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_applications');
    }
};
