<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Staff;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $reservations = Reservation::where('company_id',$company->id)
            ->whereBetween('start_at',[$startOfMonth,$endOfMonth])
            ->where('status','reserved')
            ->get();

	$totalReservedMinutes = Reservation::where('company_id',$company->id)
	    ->whereBetween('start_at',[$startOfMonth,$endOfMonth])
	    ->where('status','reserved')
	    ->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE,start_at,end_at)) as total'))
	    ->value('total');

        /* ===============================
           今月の営業時間（分）
        =============================== */

        $totalAvailableMinutes = 0;

        $staffCount = Staff::where('company_id',$company->id)
            ->where('is_reservable',true)
            ->count();

        $patterns = $company->open_patterns ?? [];

        $date = $startOfMonth->copy();

        while($date <= $endOfMonth){

            $weekday = $date->dayOfWeek;

            $dayPatterns = $patterns[$weekday] ?? [];

            foreach($dayPatterns as $pattern){

                if(empty($pattern['open']) || empty($pattern['close'])){
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
           稼働率
        =============================== */

        $utilizationRate = 0;

        if($totalAvailableMinutes > 0){

            $utilizationRate =
                round(($totalReservedMinutes / $totalAvailableMinutes) * 100 ,1);

        }

        /* ===============================
           今日の予約数
        =============================== */

        $todayCount = Reservation::where('company_id',$company->id)
            ->whereDate('start_at',$now->toDateString())
            ->where('status','reserved')
            ->count();

        /* ===============================
           今月予約数
        =============================== */

        $monthlyCount = Reservation::where('company_id',$company->id)
            ->whereYear('start_at',$now->year)
            ->whereMonth('start_at',$now->month)
            ->where('status','reserved')
            ->count();

        /* ===============================
           今日の予約一覧
        =============================== */

        $todayReservations = Reservation::where('company_id',$company->id)
            ->whereDate('start_at',$now->toDateString())
            ->where('status','reserved')
            ->with(['staff','menus'])
            ->orderBy('start_at')
            ->get();


    /* ===============================
       今日の売上
    =============================== */

    $todaySales = Reservation::where('company_id',$company->id)
        ->whereDate('start_at',$now->toDateString())
        ->where('status','reserved')
        ->sum('total_price');

    /* ===============================
       今月の売上
    =============================== */

    $monthlySales = Reservation::where('company_id',$company->id)
        ->whereYear('start_at',$now->year)
        ->whereMonth('start_at',$now->month)
        ->where('status','reserved')
        ->sum('total_price');
    /* ===============================
       今年売上
    =============================== */

    $yearlySales = Reservation::where('company_id',$company->id)
        ->whereYear('start_at',$now->year)
        ->where('status','reserved')
        ->sum('total_price');


$period = request('period','month');
$year = request('year',now()->year);
$month = request('month',now()->month);

$query = Reservation::where('company_id',$company->id)
->where('status','reserved');

if($period=='month'){

$query->whereYear('start_at',$year)
      ->whereMonth('start_at',$month);

}else{

$query->whereYear('start_at',$year);

}



    /* ===============================
       月別売上（グラフ用）
    =============================== */

$monthlyChart = collect(range(1,12))->map(function($month) use ($company,$year){

    $total = Reservation::where('company_id',$company->id)
        ->whereYear('start_at',$year)
        ->whereMonth('start_at',$month)
        ->where('status','reserved')
        ->sum('total_price');

    return [
        'month'=>$month,
        'total'=>$total
    ];

});

    /* ===============================
       スタッフ売上ランキング
    =============================== */

$staffRanking = (clone $query)
->select(
'staff_id',
DB::raw('SUM(total_price) as total')
)
->groupBy('staff_id')
->with('staff')
->orderByDesc('total')
->limit(10)
->get();

$nominationRanking = (clone $query)
->where('nomination_fee','>',0)
->select(
'staff_id',
DB::raw('COUNT(*) as nomination_count'),
DB::raw('SUM(nomination_fee) as nomination_sales')
)
->groupBy('staff_id')
->with('staff')
->orderByDesc('nomination_count')
->limit(10)
->get();


    /* ===============================
       人気メニュー
    =============================== */

$menuRanking = DB::table('reservation_menus')
    ->join('reservations','reservations.id','=','reservation_menus.reservation_id')
    ->join('menus','menus.id','=','reservation_menus.menu_id')
    ->where('reservations.company_id',$company->id)
    ->where('reservations.status','reserved')
    ->select(
        'menus.name',
        DB::raw('COUNT(*) as total')
    )
    ->groupBy('menus.name')
    ->orderByDesc('total')
    ->limit(10)
    ->get();


$totalSales = (clone $query)->sum('total_price');

$totalReservations = (clone $query)->count();

$averagePrice = $totalReservations
? round($totalSales / $totalReservations)
: 0;


        return view('company.dashboard',compact(

            'staff',
            'todayCount',
            'monthlyCount',
            'utilizationRate',
            'todayReservations',
	        'todaySales',
	        'monthlySales',
        'yearlySales',
        'monthlyChart',
	        'staffRanking',
	        'menuRanking',
'nominationRanking',
'averagePrice',
'year',
'month',
'period'

        ));
    }

}