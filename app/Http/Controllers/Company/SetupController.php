<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SetupController extends Controller
{
    public function index()
    {
//	Log::debug('ログ1');

        return view('company.setup');
    }

    public function store(Request $request)
    {

//	Log::debug('ログ2');
        $request->validate([
            'slot_minutes' => 'required|integer',
            'max_simultaneous_reservations' => 'required|integer'
        ]);
//	Log::debug('ログ3');

        $company = Auth::guard('company')->user()->company;
//	Log::debug('ログ4');

	$company->update([
	    'slot_minutes' => $request->slot_minutes,
	    'max_simultaneous_reservations' => $request->max_simultaneous_reservations,
	    'open_time' => $request->open_time,
	    'close_time' => $request->close_time,
	    'regular_holidays' => json_encode($request->regular_holidays),
	    'holiday_is_closed' => $request->holiday_is_closed ? true : false,
	    'is_initialized' => true
	]);
//	Log::debug('ログ5');

        return redirect()->route('company.dashboard');
    }
}