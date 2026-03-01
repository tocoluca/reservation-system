<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\CompanyBusinessCalendar;
use Carbon\Carbon;

class GenerateBusinessCalendar extends Command
{
    protected $signature = 'calendar:generate 
                            {company_id : 会社ID}
                            {year? : 年（未指定なら今年）}';

    protected $description = '指定した会社の年間営業カレンダーを生成する';

    public function handle()
    {
        $companyId = $this->argument('company_id');
        $year = $this->argument('year') ?? now()->year;

        $company = Company::find($companyId);

        if (!$company) {
            $this->error('会社が見つかりません');
            return;
        }

        $start = Carbon::create($year, 1, 1);
        $end   = Carbon::create($year, 12, 31);

        $count = 0;

        while ($start->lte($end)) {

            CompanyBusinessCalendar::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'date' => $start->format('Y-m-d'),
                ],
                [
                    'is_open' => true,
                ]
            );

            $start->addDay();
            $count++;
        }

        $this->info("{$year}年のカレンダーを{$count}日分生成しました。");
    }
}