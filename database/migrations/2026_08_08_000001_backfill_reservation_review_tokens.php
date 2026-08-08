<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('reservations')
            ->whereNull('review_token')
            ->orderBy('id')
            ->chunkById(200, function ($reservations): void {
                foreach ($reservations as $reservation) {
                    do {
                        $token = Str::random(40);
                    } while (DB::table('reservations')->where('review_token', $token)->exists());

                    DB::table('reservations')
                        ->where('id', $reservation->id)
                        ->update(['review_token' => $token]);
                }
            });
    }

    public function down(): void
    {
        // Review tokens are intentionally not removed on rollback.
    }
};
