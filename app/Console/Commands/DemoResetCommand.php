<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DemoResetCommand extends Command
{
    /**
     * 実行例:
     * php artisan demo:reset
     * php artisan demo:reset --company=1
     */
    protected $signature = 'demo:reset
                            {--company= : 対象company_idを指定}
                            {--seed=1 : 初期化後に DemoInitialSeeder を再投入するか (1/0)}';

    protected $description = 'デモ環境の動的データを初期化し、必要に応じて初期データを再投入する';

    public function handle(): int
    {
        $companyId = $this->option('company');
        $shouldSeed = (string) $this->option('seed') !== '0';

        if (app()->environment('production') && !config('app.demo_reset_enabled', false)) {
            $this->error('本番環境では demo reset は無効です。');
            return self::FAILURE;
        }

        $this->info('デモ初期化を開始します...');
        $this->line('対象 company_id: ' . ($companyId ?: '全社対象'));
        $this->line('Seeder再投入: ' . ($shouldSeed ? 'する' : 'しない'));

        DB::beginTransaction();

        try {
            $this->disableForeignKeys();

            /*
            |--------------------------------------------------------------------------
            | 動的データ削除
            |--------------------------------------------------------------------------
            */
            $this->deleteReservations($companyId);
            $this->deleteCustomers($companyId);
            $this->deleteQuestionnaires($companyId);
            $this->deleteApplications($companyId);
            $this->deleteTempLikeData($companyId);

            $this->enableForeignKeys();

            /*
            |--------------------------------------------------------------------------
            | デモ用初期データ再投入
            |--------------------------------------------------------------------------
            */
            if ($shouldSeed && class_exists(\Database\Seeders\DemoInitialSeeder::class)) {
                $this->line('DemoInitialSeeder を再投入中...');

                $this->call('db:seed', [
                    '--class' => \Database\Seeders\DemoInitialSeeder::class,
                    '--force' => true,
                ]);
            } elseif ($shouldSeed) {
                $this->warn('DemoInitialSeeder が見つからないため、再投入はスキップしました。');
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | ファイル削除
            |--------------------------------------------------------------------------
            | DBロールバックの影響を受けないよう commit 後に実施
            |--------------------------------------------------------------------------
            */
            $this->deleteDemoUploads();

            $this->info('デモ初期化が完了しました。');
            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->enableForeignKeys();

            $this->error('デモ初期化に失敗しました。');
            $this->error($e->getMessage());

            report($e);

            return self::FAILURE;
        }
    }

    /**
     * 外部キー制約を一時無効化
     */
    private function disableForeignKeys(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
    }

    /**
     * 外部キー制約を再有効化
     */
    private function enableForeignKeys(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * 予約関連削除
     */
    private function deleteReservations($companyId = null): void
    {
        $this->line('予約データ削除中...');

        if (!$this->hasTable('reservations')) {
            $this->warn('reservations テーブルが無いためスキップ');
            return;
        }

        $reservationIds = DB::table('reservations')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id');

        if ($reservationIds->isNotEmpty()) {
            foreach ([
                'reservation_details',
                'reservation_menus',
                'reservation_options',
                'reservation_histories',
                'reservation_logs',
            ] as $childTable) {
                if ($this->hasTable($childTable) && $this->hasColumn($childTable, 'reservation_id')) {
                    DB::table($childTable)
                        ->whereIn('reservation_id', $reservationIds)
                        ->delete();
                }
            }
        }

        DB::table('reservations')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->delete();
    }

    /**
     * 顧客関連削除
     */
    private function deleteCustomers($companyId = null): void
    {
        $this->line('顧客データ削除中...');

        if (!$this->hasTable('customers')) {
            $this->warn('customers テーブルが無いためスキップ');
            return;
        }

        $customerIds = DB::table('customers')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id');

        if ($customerIds->isNotEmpty()) {
            foreach ([
                'customer_notes',
                'customer_photos',
                'customer_histories',
                'customer_tags',
            ] as $childTable) {
                if ($this->hasTable($childTable) && $this->hasColumn($childTable, 'customer_id')) {
                    DB::table($childTable)
                        ->whereIn('customer_id', $customerIds)
                        ->delete();
                }
            }
        }

        DB::table('customers')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->delete();
    }

    /**
     * 問診・アンケート関連削除
     */
    private function deleteQuestionnaires($companyId = null): void
    {
        $this->line('問診データ削除中...');

        foreach ([
            'questionnaire_answer_details',
            'questionnaire_answers',
            'questionnaire_results',
        ] as $table) {
            if ($this->hasTable($table)) {
                $query = DB::table($table);

                if ($companyId && $this->hasColumn($table, 'company_id')) {
                    $query->where('company_id', $companyId);
                }

                $query->delete();
            }
        }
    }

    /**
     * 申請系削除
     */
    private function deleteApplications($companyId = null): void
    {
        $this->line('申請データ削除中...');

        if ($this->hasTable('applications')) {
            $query = DB::table('applications');

            if ($companyId && $this->hasColumn('applications', 'company_id')) {
                $query->where('company_id', $companyId);
            }

            $query->delete();
        }
    }

    /**
     * その他、一時・ログ・テスト系の削除
     */
    private function deleteTempLikeData($companyId = null): void
    {
        $this->line('一時データ削除中...');

        $tables = [
            'line_login_logs',
            'line_temp_links',
            'password_reset_tokens',
            'sessions',
            'failed_jobs',
            'job_batches',
        ];

        foreach ($tables as $table) {
            if (!$this->hasTable($table)) {
                continue;
            }

            $query = DB::table($table);

            if ($companyId && $this->hasColumn($table, 'company_id')) {
                $query->where('company_id', $companyId);
            }

            $query->delete();
        }
    }

    /**
     * デモ用アップロード/一時ファイル削除
     */
    private function deleteDemoUploads(): void
    {
        $this->line('デモ生成ファイル確認中...');

        $paths = [
            storage_path('app/public/customer_photos/demo'),
            storage_path('app/public/notices/demo'),
            storage_path('app/public/tmp/demo'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path) && File::isDirectory($path)) {
                File::cleanDirectory($path);
                $this->line("削除: {$path}");
            }
        }
    }

    /**
     * テーブル存在確認
     */
    private function hasTable(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }

    /**
     * カラム存在確認
     */
    private function hasColumn(string $table, string $column): bool
    {
        return $this->hasTable($table) && DB::getSchemaBuilder()->hasColumn($table, $column);
    }
}