<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_change_notice_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('notice_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->string('contact_type')->nullable();   // mail / phone
            $table->string('contact_status')->default('pending'); // pending / mail_sent / phone_pending / confirmed / phone_confirmed / closed

            $table->string('response_status')->default('waiting'); // waiting / confirmed / phone_confirmed / closed / no_response
            $table->string('response_token', 100)->nullable()->unique();

            $table->timestamp('mail_sent_at')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->unsignedInteger('reminder_send_count')->default(0);
            $table->timestamp('mail_opened_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->string('cancel_reason_type')->nullable(); // shop / customer
            $table->unsignedBigInteger('cancel_processed_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->foreign('notice_id')->references('id')->on('reservation_change_notices')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_change_notice_items');
    }
};