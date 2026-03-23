<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DemoExportSeedCommand extends Command
{
    protected $signature = 'demo:export-seed
                            {--company= : 対象のcompany_id}
                            {--tables=companies,staffs,menus,open_patterns,menu_categories,menu_tags,notices : 出力対象テーブルをカンマ区切りで指定}';

    protected $description = '現在のDBデータから DemoInitialSeeder.php を自動生成する';

    public function handle(): int
    {
        $companyId = $this->option('company');
        $tablesOption = $this->option('tables');

        $tables = collect(explode(',', (string) $tablesOption))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();

        if (empty($tables)) {
            $this->error('対象テーブルがありません。');
            return self::FAILURE;
        }

        $schema = DB::getSchemaBuilder();
        $exportData = [];

        foreach ($tables as $table) {
            if (!$schema->hasTable($table)) {
                $this->warn("テーブルが存在しないためスキップ: {$table}");
                continue;
            }

            $query = DB::table($table);

            if ($companyId && $schema->hasColumn($table, 'company_id')) {
                $query->where('company_id', $companyId);
            }

            $rows = $query->get()->map(function ($row) {
                return (array) $row;
            })->all();

            $exportData[$table] = $rows;
            $this->line("取得: {$table} (" . count($rows) . "件)");
        }

        if (empty($exportData)) {
            $this->error('出力対象データがありませんでした。');
            return self::FAILURE;
        }

        $content = $this->buildSeederContent($exportData, $companyId);

        $path = database_path('seeders/DemoInitialSeeder.php');
        File::put($path, $content);

        $this->info("Seeder生成完了: {$path}");
        $this->line('実行例: php artisan db:seed --class=DemoInitialSeeder');

        return self::SUCCESS;
    }

    private function buildSeederContent(array $exportData, $companyId = null): string
    {
        $body = [];

        foreach ($exportData as $table => $rows) {
            $body[] = $this->buildTableBlock($table, $rows, $companyId);
        }

        $bodyText = implode("\n\n", $body);

        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoInitialSeeder extends Seeder
{
    public function run(): void
    {
{$this->indent($bodyText, 2)}
    }
}

PHP;
    }

    private function buildTableBlock(string $table, array $rows, $companyId = null): string
    {
        $schema = DB::getSchemaBuilder();

        $lines = [];
        $lines[] = "/* {$table} */";
        $lines[] = "if (DB::getSchemaBuilder()->hasTable('{$table}')) {";

        if ($companyId && $schema->hasColumn($table, 'company_id')) {
            $lines[] = "    DB::table('{$table}')->where('company_id', " . $this->exportValue($companyId) . ")->delete();";
        } else {
            $lines[] = "    DB::table('{$table}')->delete();";
        }

        if (!empty($rows)) {
            $rowsCode = $this->exportArray($rows, 1);
            $lines[] = "    DB::table('{$table}')->insert({$rowsCode});";
        }

        $lines[] = "}";

        return implode("\n", $lines);
    }

    private function exportArray(array $data, int $level = 0): string
    {
        $indent = str_repeat('    ', $level);
        $nextIndent = str_repeat('    ', $level + 1);

        if ($this->isAssoc($data)) {
            $lines = ["["];
            foreach ($data as $key => $value) {
                $lines[] = $nextIndent . $this->exportKey($key) . ' => ' . $this->exportValue($value, $level + 1) . ',';
            }
            $lines[] = $indent . "]";
            return implode("\n", $lines);
        }

        $lines = ["["];
        foreach ($data as $value) {
            $lines[] = $nextIndent . $this->exportValue($value, $level + 1) . ',';
        }
        $lines[] = $indent . "]";
        return implode("\n", $lines);
    }

    private function exportValue($value, int $level = 0): string
    {
        if (is_array($value)) {
            return $this->exportArray($value, $level);
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return var_export((string) $value, true);
    }

    private function exportKey($key): string
    {
        return var_export((string) $key, true);
    }

    private function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function indent(string $text, int $level): string
    {
        $pad = str_repeat('    ', $level);

        return collect(explode("\n", $text))
            ->map(fn ($line) => $line === '' ? $line : $pad . $line)
            ->implode("\n");
    }
}