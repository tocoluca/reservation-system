<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

public function up()
{
Schema::create('menu_categories', function (Blueprint $table) {

$table->id();

$table->foreignId('company_id')->constrained()->cascadeOnDelete();

$table->string('name',100);

$table->timestamps();

});
}

public function down()
{
Schema::dropIfExists('menu_categories');
}

};