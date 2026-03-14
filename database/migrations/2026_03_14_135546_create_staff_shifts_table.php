<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{

Schema::create('staff_shifts', function (Blueprint $table) {

$table->id();

$table->foreignId('staff_id')->constrained()->cascadeOnDelete();

$table->date('date');

$table->foreignId('shift_pattern_id')->nullable();

$table->boolean('is_work')->default(true);

$table->timestamps();

});

}

public function down()
{
Schema::dropIfExists('staff_shifts');
}

};