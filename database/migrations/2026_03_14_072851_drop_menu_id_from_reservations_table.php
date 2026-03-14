<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMenuIdFromReservationsTable extends Migration
{
    public function up()
    {
Schema::table('reservations', function (Blueprint $table) {

    $table->dropForeign(['menu_id']); // ← Laravelが自動で探す

    $table->dropColumn('menu_id');

});
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {

            $table->foreignId('menu_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

        });
    }
}