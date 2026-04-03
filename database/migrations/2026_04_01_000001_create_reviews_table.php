<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('nickname', 100)->nullable();

            $table->boolean('is_public')->default(false);
            $table->string('status', 20)->default('pending'); // pending / approved / rejected

            $table->text('owner_reply')->nullable();
            $table->timestamp('owner_replied_at')->nullable();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique('reservation_id');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_public']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'review_token')) {
                $table->string('review_token', 64)->nullable()->unique()->after('cancel_token');
            }

            if (!Schema::hasColumn('reservations', 'review_requested_at')) {
                $table->timestamp('review_requested_at')->nullable()->after('review_token');
            }

            if (!Schema::hasColumn('reservations', 'review_submitted_at')) {
                $table->timestamp('review_submitted_at')->nullable()->after('review_requested_at');
            }
        });

        DB::table('reservations')
            ->whereNull('review_token')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('reservations')
                        ->where('id', $row->id)
                        ->update([
                            'review_token' => bin2hex(random_bytes(20)),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'review_submitted_at')) {
                $table->dropColumn('review_submitted_at');
            }

            if (Schema::hasColumn('reservations', 'review_requested_at')) {
                $table->dropColumn('review_requested_at');
            }

            if (Schema::hasColumn('reservations', 'review_token')) {
                $table->dropUnique(['review_token']);
                $table->dropColumn('review_token');
            }
        });

        Schema::dropIfExists('reviews');
    }
};