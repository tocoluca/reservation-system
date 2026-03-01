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
        Schema::create('company_business_calendars', function (Blueprint $table) {
            $table->id();

            // 会社ID
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // 日付（営業日判定用）
            $table->date('date');

            // 営業フラグ
            $table->boolean('is_open')->default(true);

            // 営業時間（その日だけ変更可能）
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();

            $table->timestamps();

            // 1会社につき1日1レコード
            $table->unique(['company_id', 'date']);

            // 検索高速化
            $table->index(['company_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_business_calendars');
    }
};