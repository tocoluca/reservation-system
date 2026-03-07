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
        Schema::table('reservations', function (Blueprint $table) {

            $table->integer('price')
                ->default(0)
                ->comment('メニュー料金')
                ->after('end_at');

            $table->integer('nomination_fee')
                ->default(0)
                ->comment('指名料')
                ->after('price');

            $table->integer('total_price')
                ->default(0)
                ->comment('合計料金')
                ->after('nomination_fee');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            $table->dropColumn([
                'price',
                'nomination_fee',
                'total_price'
            ]);

        });
    }
};