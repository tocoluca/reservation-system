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
    Schema::create('companies', function (Blueprint $table) {
        $table->id();

        // 8桁英数字（マルチテナントキー）
        $table->string('company_code', 8)->unique()->comment('企業識別コード');

        $table->string('name')->comment('企業名');
        $table->string('industry_type')->comment('beauty or dental');

        $table->string('logo_path')->nullable()->comment('企業ロゴ');
        $table->string('address')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('homepage')->nullable();

        // テーマカラー
        $table->string('theme_color')->default('blue');

        // 課金状態
        $table->boolean('is_active')->default(true)->comment('利用可否');
        $table->timestamp('grace_until')->nullable()->comment('猶予期限');

        // 支払い
        $table->string('stripe_customer_id')->nullable();
        $table->string('stripe_subscription_id')->nullable();

        // 予約制御
        $table->integer('slot_minutes')->default(30)->comment('時間刻み');
        $table->integer('max_simultaneous_reservations')->default(1);
        $table->boolean('menu_time_priority_flag')->default(true);

        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
