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
	    Schema::table('companies', function (Blueprint $table) {

	        $table->time('open_time')->default('09:00');
	        $table->time('close_time')->default('18:00');

	        $table->json('regular_holidays')
	              ->nullable()
	              ->comment('休業曜日 0=日〜6=土');

	        $table->boolean('holiday_is_closed')
	              ->default(false)
	              ->comment('祝日を休業日にするか');
	    });
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            //
        });
    }
};
