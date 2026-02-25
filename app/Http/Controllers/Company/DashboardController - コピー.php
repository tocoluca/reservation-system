<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $staff = Auth::guard('company')->user();
        $company = $staff->company;

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
            'monthlyCount'
        ));
    }
}