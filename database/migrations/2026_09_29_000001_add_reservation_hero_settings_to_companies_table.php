<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('reservation_hero_image_path')->nullable()->after('logo_path');
            $table->string('reservation_hero_heading', 120)->nullable()->after('reservation_hero_image_path');
            $table->string('reservation_hero_subheading', 240)->nullable()->after('reservation_hero_heading');
            $table->unsignedTinyInteger('reservation_hero_heading_size')->default(40)->after('reservation_hero_subheading');
            $table->unsignedTinyInteger('reservation_hero_subheading_size')->default(16)->after('reservation_hero_heading_size');
            $table->string('reservation_hero_text_color', 7)->default('#ffffff')->after('reservation_hero_subheading_size');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_hero_image_path',
                'reservation_hero_heading',
                'reservation_hero_subheading',
                'reservation_hero_heading_size',
                'reservation_hero_subheading_size',
                'reservation_hero_text_color',
            ]);
        });
    }
};
