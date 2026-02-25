<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use Carbon\Carbon;
use App\Models\Staff;

class DashboardController extends Controller
{

	public function index()
	{
	    $staff = auth()->guard('company')->user();
	    $company = $staff->company;

	    $now = Carbon::now();
	    $startOfMonth = $now->copy()->startOfMonth();
	    $endOfMonth = $now->copy()->endOfMonth();

	    /* ===============================
	       今月の予約時間（分）
	    =============================== */
	    $reservations = Reservation::where('company_id', $company->id)
	        ->whereBetween('start_at', [$startOfMonth, $endOfMonth])
	        ->where('status','reserved')
	        ->get();

	    $totalReservedMinutes = 0;

	    foreach ($reservations as $r) {
	        $totalReservedMinutes +=
	            Carbon::parse($r->start_at)
	                ->diffInMinutes($r->end_at);
	    }

	    /* ===============================
	       今月の営業時間（分）
	    =============================== */

	    $totalAvailableMinutes = 0;

	    $staffCount = Staff::where('company_id',$company->id)
	        ->where('is_reservable',true)
	        ->count();

	    $patterns = $company->open_patterns ?? [];

	    $date = $startOfMonth->copy();

	    while ($date <= $endOfMonth) {

	        $weekday = $date->dayOfWeek;

	        $dayPatterns = $patterns[$weekday] ?? [];

	        foreach ($dayPatterns as $pattern) {

	            if (empty($pattern['open']) || empty($pattern['close'])) {
	                continue;
	            }

	            $open = Carbon::parse($date->format('Y-m-d').' '.$pattern['open']);
	            $close = Carbon::parse($date->format('Y-m-d').' '.$pattern['close']);

	            $totalAvailableMinutes +=
	                $open->diffInMinutes($close) * $staffCount;
	        }

	        $date->addDay();
	    }

	    /* ===============================
	       稼働率計算
	    =============================== */

	    $utilizationRate = 0;

	    if ($totalAvailableMinutes > 0) {
	        $utilizationRate =
	            round(($totalReservedMinutes / $totalAvailableMinutes) * 100, 1);
	    }

	        $now = Carbon::now();

	        // 今日の予約数
	        $todayCount = Reservation::where('company_id', $company->id)
	            ->whereDate('start_at', $now->toDateString())
	            ->where('status', 'reserved')
	            ->count();

	        // 今月の予約数
	        $monthlyCount = Reservation::where('company_id', $company->id)
	            ->whereYear('start_at', $now->year)
	            ->whereMonth('start_at', $now->month)
	            ->where('status', 'reserved')
	            ->count();

	    return view('company.dashboard', compact(
	        'staff',
            'todayCount',
            'monthlyCount',
	        'utilizationRate'
	    ));
	}
}