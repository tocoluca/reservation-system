<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('category')->nullable()->index(); // staff, business_day, reservation, mail, other
            $table->string('subject');
            $table->text('body');

            $table->string('status')->default('open')->index(); // open / answered / closed

            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();

            // adminテーブル名が違う場合はここを変更
            $table->foreignId('replied_admin_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->boolean('is_read_by_company')->default(false)->index();
            $table->timestamp('company_read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};