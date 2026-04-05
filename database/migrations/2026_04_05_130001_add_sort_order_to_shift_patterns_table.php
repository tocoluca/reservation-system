<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_patterns', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('color');
        });

        $rows = DB::table('shift_patterns')->orderBy('id')->get();

        foreach ($rows as $index => $row) {
            DB::table('shift_patterns')
                ->where('id', $row->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('shift_patterns', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};