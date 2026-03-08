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

	$table->integer('reservation_month_limit')
	->default(3)
	->comment('何ヶ月先の月末まで予約可能');

	$table->integer('reservation_open_days')
	->default(0)
	->comment('何日前から予約可能');

	$table->integer('reservation_close_hours')
	->default(1)
	->comment('予約締切（何時間前まで）');

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
