<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤務シフト表</title>
    @php
        use Carbon\Carbon;

        $monthCarbon = Carbon::parse(($month ?? now()->format('Y-m')) . '-01');
        $daysInMonth = $monthCarbon->daysInMonth;
        $weekMap = ['日', '月', '火', '水', '木', '金', '土'];

        $patternMap = collect($patterns ?? [])->keyBy('id');

        $dayColumns = collect(range(1, $daysInMonth))->map(function ($day) use ($monthCarbon, $weekMap) {
            $date = $monthCarbon->copy()->day($day);

            return [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'weekday' => $weekMap[$date->dayOfWeek],
                'day_of_week' => $date->dayOfWeek,
            ];
        });

        $defaultShiftMap = [];
        foreach (($defaultShifts ?? collect()) as $staffId => $rowsByWeekday) {
            foreach ($rowsByWeekday as $weekday => $rows) {
                $default = collect($rows)->first();
                if ($default) {
                    $defaultShiftMap[$staffId][(int) $weekday] = $default;
                }
            }
        }

        $shiftMap = [];
        foreach (($shifts ?? collect()) as $staffId => $rowsByDate) {
            foreach ($rowsByDate as $dateKey => $rows) {
                $shift = collect($rows)->first();
                if ($shift) {
                    $shiftMap[$staffId][$dateKey] = $shift;
                }
            }
        }

        $vacationMap = [];
        foreach (($vacations ?? collect()) as $staffId => $rows) {
            foreach ($rows as $vacation) {
                $startDate = Carbon::parse($vacation->start_at)->startOfDay();
                $endDate = Carbon::parse($vacation->end_at)->endOfDay();

                $cursor = $startDate->copy();
                while ($cursor->lte($endDate)) {
                    if ($cursor->format('Y-m') === $monthCarbon->format('Y-m')) {
                        $vacationMap[$staffId][$cursor->format('Y-m-d')] = true;
                    }
                    $cursor->addDay();
                }
            }
        }

        $businessDayMap = [];
        foreach (($businessDays ?? collect()) as $key => $businessDay) {
            $dateKey = Carbon::parse($businessDay->date)->format('Y-m-d');
            $businessDayMap[$dateKey] = $businessDay;
        }

        $getCell = function ($staffId, $dateYmd, $weekdayNum) use ($shiftMap, $defaultShiftMap, $patternMap, $vacationMap) {
            if (!empty($vacationMap[$staffId][$dateYmd])) {
                return ['text' => '休', 'class' => 'off'];
            }

            $shift = $shiftMap[$staffId][$dateYmd] ?? null;

            if ($shift) {
                if ((int) ($shift->is_work ?? 0) !== 1) {
                    return ['text' => '休', 'class' => 'off'];
                }

                $pattern = $patternMap->get($shift->shift_pattern_id);
                if ($pattern) {
                    $start = substr((string) $pattern->start_time, 0, 5);
                    $end = substr((string) $pattern->end_time, 0, 5);
                    return ['text' => $start . "\n〜\n" . $end, 'class' => 'work'];
                }

                return ['text' => '出勤', 'class' => 'work'];
            }

            $default = $defaultShiftMap[$staffId][$weekdayNum] ?? null;
            if ($default) {
                if ((int) ($default->is_work ?? 0) !== 1) {
                    return ['text' => '休', 'class' => 'off'];
                }

                $pattern = $patternMap->get($default->shift_pattern_id);
                if ($pattern) {
                    $start = substr((string) $pattern->start_time, 0, 5);
                    $end = substr((string) $pattern->end_time, 0, 5);
                    return ['text' => $start . "\n〜\n" . $end, 'class' => 'work'];
                }

                return ['text' => '出勤', 'class' => 'work'];
            }

            return ['text' => '―', 'class' => 'empty'];
        };

        $isBusinessClosed = function ($dateYmd) use ($businessDayMap) {
            if (!isset($businessDayMap[$dateYmd])) {
                return false;
            }
            return (int) ($businessDayMap[$dateYmd]->is_open ?? 1) === 0;
        };
    @endphp

    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: 'NotoSansJP';
            font-size: 9px;
            line-height: 1.25;
            color: #222;
            margin: 0;
        }

        .header {
            margin-bottom: 8px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .sub {
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #999;
            text-align: center;
            vertical-align: middle;
            padding: 2px 1px;
            word-break: break-word;
        }

        thead th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .staff-col {
            width: 86px;
            background: #fafafa;
            font-weight: bold;
            font-size: 9px;
        }

        .day-col {
            width: 21px;
            font-size: 8px;
            padding: 2px 0;
        }

        .sun {
            color: #c62828;
        }

        .sat {
            color: #1565c0;
        }

        .closed-day {
            background: #f5f5f5;
        }

        .work {
            background: #ecfdf5;
            white-space: pre-line;
            font-size: 7.5px;
        }

        .off {
            background: #f3f4f6;
            color: #666;
            font-size: 8px;
        }

        .empty {
            color: #aaa;
            font-size: 8px;
        }

        .footer {
            margin-top: 6px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">勤務シフト表</div>
        <div class="sub">
            {{ $company->name ?? '' }} ／ 対象月：{{ $monthCarbon->format('Y年m月') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="staff-col">担当者</th>
                @foreach ($dayColumns as $col)
                    @php
                        $headClass = '';
                        if ($col['day_of_week'] === 0) {
                            $headClass .= ' sun';
                        } elseif ($col['day_of_week'] === 6) {
                            $headClass .= ' sat';
                        }
                        if ($isBusinessClosed($col['date'])) {
                            $headClass .= ' closed-day';
                        }
                    @endphp
                    <th class="day-col{{ $headClass }}">
                        <div>{{ $col['day'] }}</div>
                        <div>{{ $col['weekday'] }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach (($staffs ?? []) as $staff)
                <tr>
                    <td class="staff-col">{{ $staff->name }}</td>
                    @foreach ($dayColumns as $col)
                        @php
                            $cell = $getCell($staff->id, $col['date'], $col['day_of_week']);
                            $tdClass = $cell['class'];
                            if ($isBusinessClosed($col['date'])) {
                                $tdClass .= ' closed-day';
                            }
                        @endphp
                        <td class="{{ $tdClass }}">{{ $cell['text'] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        出力日時：{{ now()->format('Y/m/d H:i') }}
    </div>
</body>
</html>