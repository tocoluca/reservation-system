<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('salon_message')->nullable()->after('homepage')->comment('サロンからのメッセージ');
            $table->text('business_hours_text')->nullable()->after('salon_message')->comment('営業時間テキスト');
            $table->text('parking_info')->nullable()->after('business_hours_text')->comment('駐車場案内');
            $table->text('payment_methods')->nullable()->after('parking_info')->comment('支払い方法');
            $table->text('access_info')->nullable()->after('payment_methods')->comment('アクセス案内');
            $table->text('salon_note')->nullable()->after('access_info')->comment('来店時の案内');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'salon_message',
                'business_hours_text',
                'parking_info',
                'payment_methods',
                'access_info',
                'salon_note',
            ]);
        });
    }
};