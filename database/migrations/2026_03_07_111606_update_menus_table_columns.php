<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {

        Schema::table('menus', function (Blueprint $table) {

            // カラム名変更
            $table->renameColumn('duration_minutes','duration');

            // description追加
            $table->text('description')->nullable()->after('name');

        });

    }

    public function down()
    {

        Schema::table('menus', function (Blueprint $table) {

            $table->renameColumn('duration','duration_minutes');

            $table->dropColumn('description');

        });

    }

};